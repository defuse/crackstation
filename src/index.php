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
	echo "<table class=\"results\">";
	echo "<tr><th>Hash</th><th>Type</th><th>Result</th></tr>";
    $url = "http://firexware.defuse.ca:1985/crack.php";
    $result = do_post_request($url, "hashes=" . urlencode(implode(",", $hashes)));
    if($result === FALSE)
        return false;
    $result = explode("\n", $result);
    foreach($result as $line)
    {
        $data = explode("||#||", $line);
        if(count($data) == 4)
        {
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
	echo "</table><br />";
    echo '<p><strong>Color Codes:</strong> <span style="background-color: #00FF00;">Green:</span> Exact match, <span style="background-color: #FFF000;">Yellow:</span> Partial match, <span style="background-color: #cacaca;">Gray:</span> Not found.</p>';
    return true;
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--

	Nonzero1.0 by nodethirtythree design
	http://www.nodethirtythree.com
	missing in a maze

-->
<html>
<head>
<meta name="google-site-verification" content="bJfNsQVzNQLRCAQiZD0kUNF-gFYS16YnaCZDJEX-dLk" />
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>Crackstation - Online Password Hash Cracking - MD5, SHA1, Linux, Unix, Zip, RAR</title>
<meta name="keywords" content="md5 cracking, sha1 cracking, hash cracking, password cracking" />
<meta name="description" content="CrackStation is the most effective hash cracking service. We Crack: MD5, SHA1, SHA2, WPA, and Much More..." />
<link rel="stylesheet" type="text/css" href="style.css" />
<style type="text/css">
.results
{
	width: 100%;
}

.results td, th
{
	text-align:left;
}

.fail
{
	background-color: #cacaca;
}

.results td
{
	padding:5px;
    font-family: monospace;
}

.suc
{
	background-color: #00FF00;
}

.part
{
    background-color: #FFF000;
}
</style>
</head>
<body>

<div id="header">

	<div id="header_inner" class="fluid">

		<div id="logo">
			<h1><span>CrackStation</span></h1><br />
			<h2>MD5, SHA1, SHA256, SHA512, RipeMD Password Hash Cracking</h2>
		</div>
		
		<div id="menu">
			<ul>
				<li><a href="/" class="active">Crack</a></li>
				<li><a href="cracking-services.html">Advanced Services</a></li>
				<li><a href="hashing-security.html" >Hashing Security</a></li>
				<li><a href="legal-privacy.html">Legal &amp; Privacy</a></li>
			</ul>
		</div>
		
	</div>
</div>

<div id="main">

	<div id="main_inner" class="fluid">

		<div id="primaryContent_columnless">

			<div id="columnA_columnless">
				<h3>Crack Hashes</h3>
				<p>
				CrackStation allows you to crack many types of password hashes. Enter up to 10 hashes in the field below (one per line) and CrackStation will attempt to crack them with <a href="#cracking-hashes">our lookup tables</a>. 
				</p>
				<form action="/" method="post">
				<textarea style="width: 100%;" rows=10 name="hashes" ><?php if(isset($_POST['hashes'])) echo htmlspecialchars($_POST['hashes'], ENT_QUOTES); ?></textarea><br />
                <?php
                    echo recaptcha_get_html($rec_pub_key);
                ?>
				<input type="submit" name="crack" value="Crack Hashes" style="width: 200px;" /> <b>(MAX: 10)</b>
				</form>
				<br />


				<?php
                function trim_value(&$value)
                {
                    $value = trim($value);
                    $value = trim($value, "*"); // For MySQL 4.1+ hashes
                }

				if(isset($_POST['crack']))
				{
                    $rec_result = recaptcha_check_answer($rec_priv_key,
                                                         $_SERVER["REMOTE_ADDR"],
                                                         $_POST["recaptcha_challenge_field"],
                                                         $_POST["recaptcha_response_field"]);
                    if($rec_result->is_valid)
                    {
                        $hashes = explode("\n", $_POST['hashes']);
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
				<b>Supported Hash Types:</b> NTLM, md2, md4, md5, md5(md5), md5-half, sha1, sha1(sha1_bin()), sha224, sha256, sha384, sha512, ripeMD160, whirlpool, MySQL 4.1+	<br />
                <b>Coming Soon:</b> LM, MySQL pre-4.1
				<br /><br />
				<a name="cracking-hashes"></a>
				<h3>How Crackstation Cracks Hashes</h3>

				<p>
				Crackstation uses massive pre-computed lookup tables to crack password hashes. These tables store a mapping between the hash of a password, and the correct password for that hash. The hash values are indexed so that it is possible to quickly search the database for a given hash. If the hash is present in the database, the password can be recovered in less only a fraction of a second. This cracking method only works for "unsalted" hashes. For information on password hashing systems that are not vulnerable to pre-computed lookup tables, see our <a href="hashing-security.html">hashing security page</a>.
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
			</div>
	
		</div>

		<br class="clear" />

	</div>

</div>

<div id="footer" class="fluid">
	Copyright &copy; 2011. All rights reserved. Design by <a href="http://www.nodethirtythree.com/" rel="nofollow" >NodeThirtyThree Design</a>.
</div>

</body>
</html>
