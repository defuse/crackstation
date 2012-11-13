<?php
/*==============================================================================

        Defuse Security's Secure & Lightweight CMS in PHP for Linux.

                      PUBLIC DOMAIN CONTRIBUTION NOTICE                             
   This work has been explicitly placed into the Public Domain for the
    benefit of anyone who may find it useful for any purpose whatsoever.

    This CMS is heavily dependant upon GRC's Script-Free Menuing System:
                http://www.grc.com/menudemo.htm
    
==============================================================================*/

// Standardize the times & dates to UTC because people don't live in the same timezone as the server.
date_default_timezone_set("UTC"); 

//Strengthen the server's CSPRNG
$entropy = implode(gettimeofday()) . implode($_SERVER) . implode($_GET) . implode($_POST) . implode($_COOKIE) . implode($_ENV) . microtime() . mt_rand() . mt_rand();
file_put_contents("/dev/random", $entropy);

require_once('libs/URLParse.php'); 
require_once('libs/hitcounter.php');
require_once('libs/phpcount.php');

$name = URLParse::ProcessURL();
PHPCount::AddHit($name, $_SERVER['REMOTE_ADDR']);

// Prevent pages from being displayed in iframes. Not supported by all browsers.
header('X-Frame-Options: SAMEORIGIN'); 

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
<title><?php 
            $title = URLParse::getPageTitle($name);
            echo htmlspecialchars($title, ENT_QUOTES);
?></title>
<meta name="description" content="<?php 
            $metd = URLParse::getPageMetaDescription($name);
            echo htmlspecialchars($metd, ENT_QUOTES);
?>" />
<meta name="keywords" content="<?php 
            $metk = URLParse::getPageMetaKeywords($name);
            echo htmlspecialchars($metk, ENT_QUOTES);
?>" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="google-site-verification" content="bJfNsQVzNQLRCAQiZD0kUNF-gFYS16YnaCZDJEX-dLk" />
<link rel="stylesheet" media="all" type="text/css" href="/mainmenu2.css" />
<link rel="stylesheet" media="all" type="text/css" href="/main.css" />
<link rel="stylesheet" media="all" type="text/css" href="/css/style.css" />
<!--[if !IE 7]>
	<style type="text/css">
		#wrap {display:table;height:100%}
	</style>
<![endif]-->
</head>
<body <?php if( $name == "" ) echo 'style="background:white;" '; ?> >
<div id="wrap">

<!-- This menuing system was made by Steve Gibson at GRC.COM 
            see more at http://www.grc.com/menudemo.htm -->

<div class="menuminwidth0"><div class="menuminwidth1"><div class="menuminwidth2">
<div id="masthead">
    <div style="font-size:30px;"><img src="/images/1by1.gif" alt="CrackStation" /></div>
</div>

<div class="menu">

<ul>
    <li class="headerlink" ><a href="/">CrackStation<img class="downimg" src="/images/downarrow.gif" alt="&#9660;"/><!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
        <ul class="leftbutton">
            <li><a href="/">&nbsp;Free Hash Cracker</a></li>
            <li><a href="/cracking-services.htm">&nbsp;Advanced Services</a></li>
            <li><a href="/about-us.htm">&nbsp;About Us</a></li>
            <li><a href="/contact-us.htm">&nbsp;Contact Us</a></li>
            <li><a href="/legal-privacy.htm">&nbsp;ToS &amp; Privacy Policy</a></li>
        </ul>
        <!--[if lte IE 6]></td></tr></table></a><![endif]-->
    </li>
</ul>


<ul>
    <li class="headerlink" ><a href="/hashing-security.htm">Password Hashing Security<img class="downimg" src="/images/downarrow.gif" alt="&#9660;"/><!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
        <!--[if lte IE 6]></td></tr></table></a><![endif]-->
    </li>
</ul>

<ul>
    <li class="headerlink" ><a href="https://defuse.ca/">Defuse Security<img class="downimg" src="/images/downarrow.gif" alt="&#9660;"/><!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
        <!--[if lte IE 6]></td></tr></table></a><![endif]-->
    </li>
</ul>

</div> <!-- close "menu" div -->
<hr style="display:none" />
</div></div></div> <!-- close the "minwidth" wrappers -->
<!-- End of menu -->

<!--[if !IE]>-->
<div id="undergrad"></div>
<!--<![endif]-->

<!--[If gt IE 6]>
<div id="undergrad"></div>
<![endif]-->

<div id="content">
<?php
    $included = URLParse::IncludePageContents();
?>
</div>

</div> <!-- Wrap -->
    <div id="footerwrapper">
    <div id="footerborder"></div>
    <div id="footer">
    <?php
        $last_modified = htmlspecialchars(
                                date("F j, Y, g:ia e", filemtime($included)),
                                ENT_QUOTES
                                );
        $unique =  PHPCount::GetHits($name, true);
        $hits = PHPCount::GetHits($name);
        $total = PHPCount::GetTotalHits();
        $totalu = PHPCount::GetTotalHits(true);
    ?>
    <table>
        <tr>
            <th>Last Modified: &nbsp;&nbsp;</th>
            <td><?php echo $last_modified;?></td>
        </tr>
        <tr>
            <th>Page Hits:</th>
            <td><?php echo $hits; ?></td>
        </tr>
        <tr>
            <th>Unique Hits:</th>
            <td><?php echo $unique; ?></td>
        </tr>
    </table>
    Copyright &copy; 2012 <a href="/about.htm">Defuse Security</a> | 
    <a href="/pastebin.htm">Secure Pastebin</a> | 
    <a href="/passgen.htm">Password Generator</a>
    </div> <!-- end footer -->
    </div> <!-- footerwrapper -->
</body>
</html>
