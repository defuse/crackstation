<?php
mb_language('uni');
mb_internal_encoding('UTF-8');

require_once('recaptchalib.php');

$rec_pub_key = "6LeKzs0SAAAAALT5EZVDjlNHtYeuU_2rWlMGvDho";
$rec_priv_key = "6LeKzs0SAAAAACV1bvVMaC5haTQT-yHc_-FMbQyn";

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

// http://wezfurlong.org/blog/2006/nov/http-post-from-php-without-curl/
function do_post_request($url, $data, $optional_headers = null)
{
  $params = array('http' => array(
              'method' => 'POST',
              'content' => $data
            ));
  if ($optional_headers !== null) {
    $params['http']['header'] = $optional_headers;
  }
  $ctx = stream_context_create($params);
  $fp = @fopen($url, 'rb', false, $ctx);
  if (!$fp) {
      return FALSE;
  }
  $response = @stream_get_contents($fp);
  if ($response === false) {
      return FALSE;
  }
  return $response;
}

function CrackHashes($hashes)
{
    $attempt_increment = count($hashes);
    $cracked_increment = 0;
	echo "<table class=\"results\">";
	echo "<tr><th>Hash</th><th>Type</th><th>Result</th></tr>";
    $url = "http://site-two.defuse.ca:1985/crack.php";
    $result = do_post_request($url, "hashes=" . urlencode(implode(",", $hashes)));
    if($result === FALSE)
        return false;
    $result = explode("\n", $result);
    foreach($result as $line)
    {
        $data = explode("||#||", $line);
        if(count($data) == 4)
        {
            $cracked_increment++;
            $hash = "";
            while(strlen($data[3]) > 0)
            {
                $hash .= htmlspecialchars(substr($data[3], 0, 64), ENT_QUOTES) . "<br />";
                $data[3] = substr($data[3], 64);
            }
            $type = htmlspecialchars($data[1], ENT_QUOTES);
            $pass = htmlspecialchars($data[2], ENT_QUOTES);
            if($data[0] == "FULLMATCH")
                echo '<tr class="suc">';
            elseif($data[0] == "PARTIALMATCH")
                echo '<tr class="part">';
            echo "<td>$hash</td><td>$type</td><td>$pass</td></tr>";
        }
        elseif(count($data) == 2 && $data[0] == "NOTFOUND")
        {
            $hash = htmlspecialchars($data[1], ENT_QUOTES);
            echo "<tr class=\"fail\"><td>$hash</td><td>Unknown</td><td>Not Found</td></tr>";
        }
    }
	echo "</table>";
    echo '<p style="font-size: 8pt;"><strong>Color Codes:</strong> <span style="background-color: #00FF00;">Green:</span> Exact match, <span style="background-color: #FFFF00;">Yellow:</span> Partial match, <span style="background-color: #FF0000;">Red:</span> Not found.</p>';
    incrementCounter($cracked_increment, $attempt_increment);
    return true;
}
?>
 <script type="text/javascript">
 var RecaptchaOptions = {
    theme : 'blackglass'
 };
 </script>

    <h1>Free Password Hash Cracker</h1>
        <div class="downloaddiv">
            <a class="downloadlink" href="/buy-crackstation-wordlist-password-cracking-dictionary.htm">Download CrackStation's Dictionaries</a>
        </div>
        <p>
            Enter up to 10 non-salted hashes:
        </p>
        <form action="/" method="post">
        <table style="width: 100%;">
        <tr>
            <td style="width: 550px;">
                <textarea style="width: 100%; height: 180px; border: solid black 1px; background-color: #e9e9e9;" name="hashes" ><?php if(isset($_POST['hashes'])) echo htmlspecialchars($_POST['hashes'], ENT_QUOTES); ?></textarea>
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
        <p style="font-size: 8pt; margin: 0; padding: 0;">
        <b>Supports:</b> LM, NTLM, md2, md4, md5, md5(md5), md5-half, sha1, sha1(sha1_bin()), sha224, sha256, sha384, sha512, ripeMD160, whirlpool, MySQL 4.1+	<br />
        </p>
        <div class="crackresults">
        <?php

        if(isset($_POST['crack']))
        {
            $rec_result = recaptcha_check_answer($rec_priv_key,
                                                 $_SERVER["REMOTE_ADDR"],
                                                 $_POST["recaptcha_challenge_field"],
                                                 $_POST["recaptcha_response_field"]);
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
                        echo "<p style=\"color: red;\"><b>There was an error connecting to the CrackStation database. We will fix this shortly.</b></p>";
                    }
                }
                else
                {
                    echo "<p style=\"color: red;\"><b>Please enter <strong>10</strong> or less hashes.</b></p>";
                }
            }
            else
            {
                echo "<p style=\"color: red;\"><b>Incorrect captcha. Please try again.</b></p>";
            }
        }
        ?>
        </div>

<a name="cracking-hashes"></a>
<h2>How Crackstation Cracks Hashes</h2>

<p>
Crackstation uses massive pre-computed lookup tables to crack password hashes. These tables store a mapping between the hash of a password, and the correct password for that hash. The hash values are indexed so that it is possible to quickly search the database for a given hash. If the hash is present in the database, the password can be recovered in less only a fraction of a second. This cracking method only works for "unsalted" hashes. For information on password hashing systems that are not vulnerable to pre-computed lookup tables, see our <a href="hashing-security.htm">hashing security page</a>.
</p>

<p>
The effectiveness of any lookup table based cracking services is directly proportional to the quality and number of passwords in the lookup table. Crackstation's lookup tables were created by extracting every word from the Wikipedia databases and adding with every password cracking dictionary we could find on the internet. We also applied intelligent word mangling (brute force hybrid) to our wordlists to make them much more effective. The following table shows the exact size of our lookup table for each hash type.
</p>


<table cellspacing=10 >
<tr><th>Hash</th><th>Lookup Table Entries</th><th>Database Size</th></tr>
<tr><th>md5</th><td>15,171,326,912</td><td>190 GB</td></tr>
<tr><th>sha1</th><td>15,171,326,912</td><td>190 GB</td></tr>
<tr><th>md5(md5)</th><td>1,493,677,782</td><td>16 GB</td></tr>
<tr><th>md2</th><td>1,493,677,782</td><td>16 GB</td></tr>
<tr><th>md4</th><td>1,493,677,782</td><td>16 GB</td></tr>
<tr><th>MySQL 4.1+</th><td>1,493,677,782</td><td>16 GB</td></tr>
<tr><th>sha224</th><td>1,493,677,782</td><td>16 GB</td></tr>
<tr><th>sha256</th><td>1,493,677,782</td><td>16 GB</td></tr>
<tr><th>sha384</th><td>1,493,677,782</td><td>16 GB</td></tr>
<tr><th>sha512</th><td>1,493,677,782</td><td>16 GB</td></tr>
<tr><th>ripeMD160</th><td>1,493,677,782</td><td>16 GB</td></tr>
<tr><th>whirlpool</th><td>1,493,677,782</td><td>16 GB</td></tr>
<tr><th>NTLM</th><td>1,493,677,782</td><td>16 GB</td></tr>
</table>
