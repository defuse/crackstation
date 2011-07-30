<?php
mb_language('uni');
mb_internal_encoding('UTF-8');

if(isset($_GET['p']))
{
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: http://crackstation.net/");
}

function CrackHashes($hashes)
{
	echo "<table class=\"results\">";
	echo "<tr><th>Hash</th><th>Type</th><th>Result</th></tr>";
	$lookups = 0;
	for($i = 0; $i < count($hashes) && $lookups < 10; $i++)
	{
		$hash = trim($hashes[$i]);
		if(empty($hash) || strlen($hash) > 130)
			continue;
		if(!CrackHash($hash))
			echo "<tr class=\"fail\"><td>" . htmlspecialchars($hash, ENT_QUOTES) . "</td><td>Unknown</td><td>Not Found</td></tr>";
		else
			$lookups++;
	}
	echo "</table><br /><br />";
}

function ValidHash($hash)
{
	$hash = strtolower($hash);
	for($i = 0; $i < strlen($hash); $i++)
	{
		$c = substr($hash,$i,1);
		if( !($c <= 'f' && $c >= 'a' || $c <= '9' && $c >= '0' ))
			return false;
	}
	return true;
}

function CrackHash($hash)
{
	if(!ValidHash($hash))
		return false;

	switch(strlen($hash))
	{
		case 16:
			TryCrack($hash, "md5_med", "md5-half") or TryCrack($hash, "md5_big", "md5-half") or NotFound($hash);
			break;
		case 32:
			TryCrack($hash, "dualmd5_med", "md5(md5)") or TryCrack($hash, "md5_med", "md5")  or TryCrack($hash, "md5_big", "md5") or NotFound($hash);
			break;
		case 40:
			TryCrack($hash, "sha1_med", "SHA1") or TryCrack($hash, "ripeMD160_med", "RipeMD160") or NotFound($hash);
			break;
		case 64:
			TryCrack($hash, "sha256_med", "SHA256") or NotFound($hash);
			break;
		case 128:
			TryCrack($hash, "sha512_med", "SHA512") or NotFound($hash);
			break;
		default:
			return false;
	}
	return true;
}

function TryCrack($hash, $db, $type)
{
	$db = mysql_real_escape_string($db);
	$a = hexdec(substr($hash,0,8));
	$b = hexdec(substr($hash,8,8));
	
	$q = mysql_query("SELECT * FROM $db WHERE a='$a' AND b='$b' LIMIT 1");
	if($q && mysql_num_rows($q) > 0)
	{
		$info = mysql_fetch_array($q);
		echo "<tr class=\"suc\"><td>" . htmlspecialchars($hash, ENT_QUOTES) . "</td><td>$type</td><td>" . htmlspecialchars($info['password'], ENT_QUOTES) . "</td></tr>";
		return true;
	}
	else
	{
		return false;
	}
}

function NotFound($hash)
{
	echo "<tr class=\"fail\"><td>" . htmlspecialchars($hash, ENT_QUOTES) . "</td><td>Unknown</td><td>Not Found</td></tr>";
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
}

.suc
{
	background-color: #00FF00;
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
				<form action="index.php" method="post">
				<textarea style="width: 100%;" rows=10 name="hashes" ><?php if(isset($_POST['hashes'])) echo htmlspecialchars($_POST['hashes'], ENT_QUOTES); ?></textarea><br />
				<input type="submit" name="crack" value="Crack Hashes" style="width: 200px;" /> <b>(MAX: 10)</b>
				</form>
				<br />


				<?php

				if(isset($_POST['crack']))
				{
					
					$dbhost = '68.144.23.245';
					$dbuser = 'exterior';
					$dbpass = 'BcKuDClNVfM8LUZo';

					$conn = @mysql_connect($dbhost, $dbuser, $dbpass);
					if($conn)
					{
						$dbname = 'crackstation';
						mysql_select_db($dbname);

						$hashes = explode("\n", $_POST['hashes']);
						CrackHashes($hashes);
					}
					else
					{
						echo "<p style=\"color: red;\"><b>There was an error connecting to the CrackStation database. We will fix this shortly.</b></p>";
					}
				}
				?>
				<b>Supported Hash Types:</b> md5, md5(md5), md5-half, sha1, sha256, sha512, ripeMD160			
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
				<tr><th>md5</th><td>15,171,326,912</td><td>530 GB</td></tr>
				<tr><th>md5(md5)</th><td>1,493,677,782</td><td>51 GB</td></tr>
				<tr><th>sha1</th><td>1,493,677,782</td><td>51 GB</td></tr>
				<tr><th>sha256</th><td>1,493,677,782</td><td>51 GB</td></tr>
				<tr><th>sha512</th><td>1,493,677,782</td><td>51 GB</td></tr>
				<tr><th>ripeMD160</th><td>1,493,677,782</td><td>51 GB</td></tr>
				<tr><th>TOTAL</th><td>24,133,393,604</td><td>836 GB</td></tr>
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
