<?php
/*==============================================================================

        Defuse Cyber-Security's Secure & Lightweight CMS in PHP for Linux.
        Setup & Usage Instructions: https://defuse.ca/helloworld-cms.htm

                      PUBLIC DOMAIN CONTRIBUTION NOTICE                             
   This work has been explicitly placed into the Public Domain for the
    benefit of anyone who may find it useful for any purpose whatsoever.

==============================================================================*/
require_once('libs/URLParse.php'); 
require_once('libs/hitcounter.php');
require_once('libs/phpcount.php');

$name = URLParse::ProcessURL();
PHPCount::AddHit($name, $_SERVER['REMOTE_ADDR']);


header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
	<head>
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
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <meta name="google-site-verification" content="bJfNsQVzNQLRCAQiZD0kUNF-gFYS16YnaCZDJEX-dLk" />
		<link rel="stylesheet" href="/css/reset.css" type="text/css" media="screen">
		<link rel="stylesheet" href="/css/style.css" type="text/css" media="screen">
		<link rel="stylesheet" href="/css/grid.css" type="text/css" media="screen">
		<script src="/js/cufon-yui.js" type="text/javascript"></script>
		<script src="/js/cufon-replace.js" type="text/javascript"></script>
		<script src="/js/Mate_400.font.js" type="text/javascript"></script>
		<script src="/js/FF-cash.js" type="text/javascript"></script>
		<!--[if lt IE 7]>
		<div style=' clear: both; text-align:center; position: relative;'>
			<a href="http://windows.microsoft.com/en-US/internet-explorer/products/ie/home?ocid=ie6_countdown_bannercode">
				<img src="http://storage.ie6countdown.com/assets/100/images/banners/warning_bar_0000_us.jpg" border="0" height="42" width="820" alt="You are using an outdated browser. For a faster, safer browsing experience, upgrade for free today." />
			</a>
		</div>
		<![endif]-->
		<!--[if lt IE 9]>
			<script type="text/javascript" src="js/html5.js"></script>
			<link rel="stylesheet" href="/css/ie.css" type="text/css" media="screen">
		<![endif]-->
	</head>
	<body id="page1">
		<div class="extra">
			<div class="main">
<!--==============================header=================================-->
				<header>
					<div class="wrapper p4">
						<h1>
                            <a id="headerheads" href="/">CrackStation</a>
                            <div style="font-size: 8pt; color: #6e6e6e; text-align: center;">
                                By <a href="https://defuse.ca/">defuse.ca</a>. &nbsp;&nbsp;<?php echo htmlentities(number_format(getCrackedCount(), 0) . " of " . number_format(getCrackAttemptCount(), 0), ENT_QUOTES) . " hashes cracked."; ?>
                            </div>
                        </h1>
						<ul class="list-services">
							<li><a href="/about-us.htm">About Us</a></li>
							<li><a href="/contact-us.htm">Contact Us</a></li>
							<li><a href="/legal-privacy.htm">Privacy Policy</a></li>
						</ul>
					</div>
					<nav>
						<ul class="menu">
							<?php if($name == "") echo '<li class="active">'; else echo '<li>'; ?>
                                <a href="/index.htm">Crack</a></li>
							<?php if($name == "cracking-services") echo '<li class="active">'; else echo '<li>'; ?>
							    <a href="/cracking-services.htm">Advanced Services</a></li>
							<!--<?php if($name == "downloads") echo '<li class="active">'; else echo '<li>'; ?>
							    <a href="/downloads.htm">Tools & Downloads</a></li>-->
							<?php if($name == "hashing-security") echo '<li class="last active">'; else echo '<li class="last">'; ?>
						        <a href="/hashing-security.htm">Hashing Security</a></li>
                            <li style="background: none; margin-left: 70px; text-align: right;">
                                <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
                                <input type="hidden" name="cmd" value="_s-xclick">
                                <input type="hidden" name="hosted_button_id" value="G26CP283D9XAG">
                                <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                                <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
                                </form>
                            </li>
						</ul>
					</nav>
				</header>
<!--==============================content================================-->
				<section id="content">
                    <?php
                        URLParse::IncludePageContents();
                    ?>
				</section>
			</div>
		</div>
	</body>
</html>

	
