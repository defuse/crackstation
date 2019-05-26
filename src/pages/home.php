<?php

/*
 * CrackStation, a web-based hash cracking website.
 * Copyright (C) 2013  Taylor Hornby
 * 
 * This file is part of CrackStation.
 * 
 * CrackStation is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * 
 * CrackStation is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

mb_language('uni');
mb_internal_encoding('UTF-8');

require_once('libs/recaptchalib.php');
require_once('libs/CrackHashes.php');
require_once('/storage/creds.php');

// Copied from: https://stackoverflow.com/a/30749288
function checkReCAPTCHA() 
{
    try {
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $creds = Creds::getCredentials("timecapsule_recaptcha");
        $data = ['secret'   => $creds[C_PASS],
                 'response' => $_POST['g-recaptcha-response'],
                 'remoteip' => $_SERVER['REMOTE_ADDR']];

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data) 
            ]
        ];

        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return json_decode($result)->success;
    }
    catch (Exception $e) {
        return null;
    }
}

if(isset($_GET['p']))
{
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: http://crackstation.net/");
}

function trim_value(&$value)
{
    $value = trim($value);
    $value = trim($value, "*"); // For MySQL 4.1+ hashes
}

?>
<!-- Set the recaptcha theme. -->
<script type="text/javascript">
var RecaptchaOptions = {
   theme : 'blackglass'
};
</script>

<h1>Free Password Hash Cracker</h1>

<p>
    Enter up to 20 non-salted hashes, one per line:
</p>

<script>
    function onRecaptchaChecked() {
        document.getElementById("submitbutton").disabled = false;
    }
    function onRecaptchaExpired() {
        document.getElementById("submitbutton").disabled = true;
    }
</script>

<!-- Hash cracking form. -->
<form action="/" method="post">
<table style="width: 100%;">
<tr>
    <td style="width: 550px;">
        <textarea
            style="width: 100%; height: 180px; border: solid black 1px; background-color: #e9e9e9;"
            name="hashes" ><?php 
                if(isset($_POST['hashes'])) {
                    echo htmlspecialchars($_POST['hashes'], ENT_QUOTES);
                } 
        ?></textarea>
    </td>
    <td>
        <center>
            <div class="g-recaptcha" data-theme="dark" data-sitekey="6LcnNi8UAAAAALJikXrc6jwNWUm00Yjx_rHCJW7u" data-callback="onRecaptchaChecked" data-expired-callback="onRecaptchaExpired"></div>
            <input id="submitbutton" type="submit" name="crack" value="Crack Hashes" style="width: 200px; margin-top: 10px;" disabled/>
        </center>
    </td>
</tr>
</table>
</form>

<!-- Supported hash types. -->
<p style="font-size: 8pt; margin: 0; padding: 0;">
<b>Supports:</b>
LM, NTLM, md2, md4, md5, md5(md5_hex), md5-half, sha1, sha224, sha256, sha384,
sha512, ripeMD160, whirlpool, MySQL 4.1+ (sha1(sha1_bin)), QubesV3.1BackupDefaults
<br />
</p>

<!-- Crack results (only shown after a POST) -->
<div class="crackresults">
<?php
if(isset($_POST['crack']))
{
    if(checkReCaptcha() === true)
    {
        $hashes = str_replace("\r\n", "\n", $_POST['hashes']);
        $hashes = str_replace("\r", "\n", $hashes);
        $hashes = explode("\n", $hashes);
        array_walk($hashes, 'trim_value');
        $hashes = array_filter($hashes, function ($item) { return !empty($item); });
        if(count($hashes) <= 20) {
            CrackHashes($hashes);
        } else {
            echo "<p style=\"color: red;\">
                    <b>Please enter <strong>20</strong> or less hashes.</b>
                  </p>";
        }
    }
    else
    {
        echo "<p style=\"color: red;\">
                <b>Incorrect captcha. Please try again.</b>
              </p>";
    }
}
?>
</div>

<div class="downloaddiv">
    <a class="downloadlink"
       href="/crackstation-wordlist-password-cracking-dictionary.htm">
            Download CrackStation's Wordlist
    </a>
</div>

<a name="cracking-hashes"></a>
<h2>How CrackStation Works</h2>

<p>
CrackStation uses massive pre-computed lookup tables to crack password hashes.
These tables store a mapping between the hash of a password, and the correct
password for that hash. The hash values are indexed so that it is possible to
quickly search the database for a given hash. If the hash is present in the
database, the password can be recovered in a fraction of a second.  This only
works for "unsalted" hashes. For information on password hashing systems that
are not vulnerable to pre-computed lookup tables, see our <a
href="hashing-security.htm">hashing security page</a>.
</p>

<p>
Crackstation's lookup tables were created by extracting every word from the
Wikipedia databases and adding with every password list we could find. We also
applied intelligent word mangling (brute force hybrid) to our wordlists to make
them much more effective. For MD5 and SHA1 hashes, we have a 190GB,
15-billion-entry lookup table, and for other hashes, we have a 19GB
1.5-billion-entry lookup table.
</p>

<p>
You can download CrackStation's dictionaries <a
href="/crackstation-wordlist-password-cracking-dictionary.htm">here</a>, and
the lookup table implementation (PHP and C) is available <a
href="https://github.com/defuse/crackstation-hashdb">here</a>.
</p>
