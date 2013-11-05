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

// Standardize the times & dates to UTC because people don't live in the same timezone as the server.
date_default_timezone_set("UTC"); 

//Strengthen the server's CSPRNG
$entropy = implode(gettimeofday()) . implode($_SERVER) . implode($_GET) . implode($_POST) . implode($_COOKIE) . implode($_ENV) . microtime() . mt_rand() . mt_rand();
file_put_contents("/dev/urandom", $entropy);

require_once('libs/URLParse.php'); 
require_once('libs/hitcounter.php');
require_once('libs/phpcount.php');
require_once('libs/VimHighlight.php');

function printHlString($text, $ft, $numbers = false) {
    $hl = new VimHighlight();
    $hl->caching = true;
    $hl->color_scheme = "dw_cyan";
    $hl->show_lines = $numbers;
    $hl->use_css = true;
    $hl->file_type = $ft;
    $hl->setVimCommand("vim");
    echo '<div class="vimhighlight">' . $hl->processText($text, true) . '</div>';
}

function printSourceFile($path, $numbers = false) {
    $hl = new VimHighlight();
    $hl->caching = true;
    $hl->color_scheme = "dw_cyan";
    $hl->show_lines = $numbers;
    $hl->use_css = true;
    $hl->setVimCommand("vim");
    echo '<div class="vimhighlight">' . $hl->processFile($path, true) . '</div>';
}

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
<link rel="stylesheet" media="all" type="text/css" href="/vimhl.css" />
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
    <div id="sm">
        <a href="https://defuse.ca/" title="Defuse Security">Defuse.ca</a>
        &nbsp;&middot;&nbsp;
        <a href="https://twitter.com/defusesec" title="Follow @DefuseSec on twitter!">
            <img id="twitterlogo" src="/images/twitter.png" alt="Follow me on twitter!" height="25" width="30" />
            Twitter
        </a>
    </div>
</div>

<div class="menu">

<ul>
    <li class="headerlink" ><a href="/">CrackStation<img class="downimg" src="/images/downarrow.gif" alt="&#9660;"/><!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
        <ul class="leftbutton">
            <li><a href="/">&nbsp;Free Hash Cracker</a></li>
            <li><a href="buy-crackstation-wordlist-password-cracking-dictionary.htm">&nbsp;Wordlist Download</a></li>
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

<div style="padding: 10px; border-bottom: solid #CC0000 5px; text-align: center; font-size: 16pt;">
<b>News:</b>
I'm crowdfunding <a href="http://www.indiegogo.com/projects/better-web-forums">a
better web forum</a>. Please contribute if you can.
</div>

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
<a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/deed.en_US"><img alt="Creative Commons License" style="border-width:0; vertical-align: bottom;" src="/images/cc-by-sa.png" /></a>
<a href="https://defuse.ca/">Defuse Security</a> | 
    <a href="https://defuse.ca/pastebin.htm">Secure Pastebin</a> | 
    <a href="https://defuse.ca/passgen.htm">Password Generator</a>
    </div> <!-- end footer -->
    </div> <!-- footerwrapper -->
</body>
</html>
