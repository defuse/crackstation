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

require_once('recaptchalib.php');
require_once('inc/CrackHashes.php');

/* Get the recaptcha credentials from a protected file. */
require_once('/etc/creds.php');
$rec_pub_creds = Creds::getCredentials("cs_recaptcha_pub");
$rec_pub_key = $rec_pub_creds[C_PASS];
unset($rec_pub_creds);
$rec_priv_creds = Creds::getCredentials("cs_recaptcha_priv");
$rec_priv_key = $rec_priv_creds[C_PASS];
unset($rec_priv_creds);

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
    Enter up to 10 non-salted hashes:
</p>

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
            <?php
                echo recaptcha_get_html($rec_pub_key, null, true);
            ?>
            <input type="submit" name="crack" value="Crack Hashes" style="width: 200px; margin-top: 10px;" />
        </center>
    </td>
</tr>
</table>
</form>

<!-- Supported hash types. -->
<p style="font-size: 8pt; margin: 0; padding: 0;">
<b>Supports:</b>
LM, NTLM, md2, md4, md5, md5(md5), md5-half, sha1, sha1(sha1_bin()), sha224,
sha256, sha384, sha512, ripeMD160, whirlpool, MySQL 4.1+
<br />
</p>

<!-- Crack results (only shown after a POST) -->
<div class="crackresults">
<?php
if(isset($_POST['crack']))
{
    $rec_result = recaptcha_check_answer(
        $rec_priv_key,
        $_SERVER["REMOTE_ADDR"],
        $_POST["recaptcha_challenge_field"],
        $_POST["recaptcha_response_field"]
    );
    if($rec_result->is_valid)
    {
        $hashes = str_replace("\r\n", "\n", $_POST['hashes']);
        $hashes = str_replace("\r", "\n", $hashes);
        $hashes = explode("\n", $hashes);
        array_walk($hashes, 'trim_value');
        $hashes = array_filter($hashes, function ($item) { return !empty($item); });
        if(count($hashes) <= 10)
        {
            if(!CrackHashes($hashes))
            {
                echo "<p style=\"color: red;\">
                        <b>There was an error connecting to the CrackStation
                        database. We will fix this shortly.</b>
                      </p>";
            }
        }
        else
        {
            echo "<p style=\"color: red;\">
                    <b>Please enter <strong>10</strong> or less hashes.</b>
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
       href="/buy-crackstation-wordlist-password-cracking-dictionary.htm">
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
href="/buy-crackstation-wordlist-password-cracking-dictionary.htm">here</a>, and
the lookup table implementation (PHP and C) is available <a
href="https://github.com/defuse/crackstation-hashdb">here</a>.
</p>
