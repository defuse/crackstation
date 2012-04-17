<?php
/*==============================================================================

        Defuse Cyber-Security's Secure & Lightweight CMS in PHP for Linux.
        Setup & Usage Instructions: https://defuse.ca/helloworld-cms.htm

                      PUBLIC DOMAIN CONTRIBUTION NOTICE                             
   This work has been explicitly placed into the Public Domain for the
    benefit of anyone who may find it useful for any purpose whatsoever.

==============================================================================*/

require_once('libs/URLParse.php'); 

$name = URLParse::ProcessURL();

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
<title><?php
        $title = URLParse::getPageTitle($name);
        echo htmlspecialchars($title, ENT_QUOTES);
?></title>
<meta name="keywords" content="<?php
        $metk = URLParse::getPageMetaKeywords($name);
        echo htmlspecialchars($metk, ENT_QUOTES);
?>" />
<meta name="description" content="<?php
        $metd = URLParse::getPageMetaDescription($name);
        echo htmlspecialchars($metd, ENT_QUOTES);
?>" />
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
.ic{
    color:#2020FF;
    font-family:monospace;
}
#shortcuts{

    width:100%;
}
#shortcuts td {
    background-color:#ccffff;
    border: solid 1px black;
    padding: 5px;
    font-weight:bold;
}
#shortcuts td a{
    color: black;
    font-weight:normal;

}
#shortcuts td a:visited{
    color: black;
}
.passcrack{
    font-family:monospace;
    margin: 10px;
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
				<li><a href="/" <?php 
                            if($name == "") echo 'class="active"'; 
                        ?>>Crack</a></li>
				<li><a href="cracking-services.htm" <?php 
                            if($name == "cracking-services") echo 'class="active"'; 
                        ?>>Advanced Services</a></li>
				<li><a href="hashing-security.htm" <?php
                            if($name == "hashing-security") echo 'class="active"'; 
                        ?>>Hashing Security</a></li>
				<li><a href="legal-privacy.htm" <?php
                            if($name == "legal-privacy") echo 'class="active"'; 
                        ?>>Legal &amp; Privacy</a></li>
			</ul>
		</div>
		
	</div>
</div>

<div id="main">

	<div id="main_inner" class="fluid">

		<div id="primaryContent_columnless">

			<div id="columnA_columnless">
            <?php
                URLParse::IncludePageContents();
            ?>
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
