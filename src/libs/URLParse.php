<?php
/*==============================================================================

        Defuse Cyber-Security's Secure & Lightweight CMS in PHP for Linux.
        Setup & Usage Instructions: https://defuse.ca/helloworld-cms.htm

                      PUBLIC DOMAIN CONTRIBUTION NOTICE                             
   This work has been explicitly placed into the Public Domain for the
    benefit of anyone who may find it useful for any purpose whatsoever.

==============================================================================*/

/*
 * The purpose of this class is to process the current request URL to 
 * determine which page is to be displayed to the user, or to which URL
 * the user should be redirected. Once the user has been redirected to
 * the correct URL, and the desired page is determined, the page contents
 * can be loaded from a file into a dynamically generated web page.
 *
 * The URL parsing is split into four processes:
 * 1. First, the hostname (domain name) the request was made to is verified 
 *    against a list of "accepted hosts." If the hostname doesn't match any of
 *    these accepted hosts, the user is redirected to the same URL on the
 *    "master host." The accepted hosts and master host variables can be set
 *     by modifying the $ACCEPTED_HOSTS and $MASTER_HOST variables respectively.
 * 2. Second, an HTTPS connection is enforced if $FORCE_HTTPS is set to true.
 *    If $FORCE_HTTPS === true and the current connection is not secure, the
 *    user is redirected to a secure (https) URL.
 * 3. The desired page is determined from the URL (see below). If this page
 *    is really an alias for another page, the user is redirected to the proper
 *    page.
 * 4. If the user did not request the page using the cannonical filename,
 *    they are redirected to the cannonical URL for the page (see below).
 *
 * How the desired page is determined from the URL:
 *
 * Every page has a name, and there are two valid URLs for each page name.
 * For a page named "foobar", the following are valid URLs for the page:
 *      1. http://example.com/foobar
 *      2. http://example.com/foobar.htm
 * (2) is the cannonical URL for the page. So if the user were to type (1) into
 * their browser, they would be redirected to (2). The URL without the .htm
 * extension is recognized as a convienience so the URL can be spoken without
 * explicitly pronouncing the "dot h-t-m."
 *
 * Names can also contain forward slashes, allowing virtual directories to be
 * created. For example, the page name "foo/bar" is valid, with the following
 * URLs:
 *      1. http://example.com/foo/bar
 *      2. http://example.com/foo/bar.htm
 * With (2) being the cannonical form.
 * There is a special case of these names where no ".htm" extension is allowed.
 * For example, the name "" (meaning the homepage) is accessible though:
 *          http://example.com/
 * but NOT through:
 *          http://example.com/.htm
 * The same applies to names ending in "/", e.g. "foo/" is accessible through:
 *          http://example.com/foo/
 * but NOT through:
 *          http://example.com/foo/.htm
 * Note that a page named "foo/" and "foo" can exist simultaneously, but since
 * it is common to ommit the trailing "/" when typing the URL, this practice
 * is strongly discouraged. If the name "foo/" exists and the user omits the 
 * trailing "/", they will be redirected to the "foo/" URL. But if "foo/" and
 * "foo" both exist, they will be redirected to "foo.htm".
 */

// Keys used for definining page data arrays
define('P_FILE', 0); // File content (suffix to $ROOT_FOLDER)
define('P_TITL', 1); // <title>text</title>
define('P_METD', 2); // META tag description
define('P_METK', 3); // META tag keywords
define('P_RDIR', 4); // Redirect URL (has precidence)

class URLParse
{
    private static $ROOT_FOLDER = "pages/";
    private static $MASTER_HOST = "crackstation.net";
    private static $ACCEPTED_HOSTS = array(
                                            "crackstation.h.defuse.ca",
                                            "crackstation"
                                            );
    private static $FORCE_HTTPS = false;
    private static $DEFAULT_TITLE = "CrackStation - Online Password Hash Cracking - MD5, SHA1, Linux, Rainbow Tables, etc.";
    private static $DEFAULT_META_DESC = "Crackstation is the most effective hash cracking service. We crack: MD5, SHA1, SHA2, WPA, and much more...";
    private static $DEFAULT_META_KEYWORDS = "md5 cracking, sha1 cracking, hash cracking, password cracking";

    private static $PAGE_INFO = array(
            "" =>           array(
                P_FILE => "home.php",
                ),
            // Handles /index and /index.htm
            "index" =>      array(
                P_RDIR => "",
                ),
            "index.html" =>  array(
                P_RDIR => "",
                ),
            "index.php" =>  array(
                P_RDIR => "",
                ),
            "cracking-services.html" => array(
                P_RDIR => "cracking-services",
            ),
            "cracking-services" => array(
                P_FILE => "cracking-services.php",
                P_TITL => "CrackStation - Advanced Password Hash Cracking Services",
                P_METD => "Hash and encryption cracking. MD5, LM, WPA, WEP, ANY algorithm. Bulk cracking.",
                P_METK => "password cracking, wpa cracking, bulk cracking, md5 cracking",
                ),
            "hashing-security.html" => array(
                P_RDIR => "hashing-security",
                ),
            "hashing-security" => array(
                P_FILE => "hashing-security.php",
                P_TITL => "Secure Salted Password Hashing - How to do it Properly",
                P_METD => "How to hash passwords properly using salt. Why hashes should be salted and how to use salt correctly.",
                P_METK => "salt, salted hashing, secure password hashing, password hashing, proper way to hash passwords",
                ),
            "downloads" => array(
                P_FILE => "downloads.php",
                P_TITL => "CrackStation Tools & Downloads",
                P_METD => "Free tools & Downloads provided by CrackStation",
                P_METK => "hash tools, hash cracking, password cracking",
                ),
            "contact-us" => array(
                P_FILE => "contactus.php",
                P_TITL => "CrackStation Contact",
                P_METD => "Instructions for contacting CrackStation",
                P_METK => "crackstation contact",
                ),
            "about-us" => array(
                P_FILE => "aboutus.php",
                P_TITL => "CrackStation Contact",
                P_METD => "What CrackStation is and why we exist",
                P_METK => "crackstation contact",
                ),
            "legal-privacy.html" => array(
                P_RDIR => "legal-privacy"
                ),
            "legal-privacy" => array(
                P_FILE => "legal-privacy.php",
                P_TITL => "CrackStation - Legal and Privacy",
                P_METD => "CrackStation.net's privacy policy",
                P_METK => "hash cracking legal, penetration testing, password security",
                ),
            );

    // Page to be displayed for invalid URLs
    private static $FILE_NOT_FOUND = array(
                        P_FILE => "404.php",
                        P_TITL => "File Not Found",
                        );
                        

    private static $to_show;

    public static function ProcessURL()
    {
        // Check the host the request was made to, and redirect if necessary.
        self::checkHost(); 
        // Check the HTTPS status, redirect if necessary.
        self::checkHTTPS();

        $page_info_key = self::getPageArrayKey();
        if($page_info_key === false)
        {
            self::send404Headers();
            self::$to_show = self::$FILE_NOT_FOUND;
            return "404";
        }
        else
        {
            $page_array = self::$PAGE_INFO[$page_info_key];
            self::checkRedirectRequest($page_array);
            self::ensureHTMOrSlashExtension($page_array, $page_info_key);
            self::$to_show = $page_array;
            return $page_info_key;
        }
    }

    public static function getPageTitle($name)
    {
        if(array_key_exists($name, self::$PAGE_INFO))
        {
            $page_array = self::$PAGE_INFO[$name];
            if(array_key_exists(P_TITL, $page_array))
                return $page_array[P_TITL];
            else
                return self::$DEFAULT_TITLE;
        }
        else
            return self::$DEFAULT_TITLE;
    }

    public static function getPageMetaDescription($name)
    {
        if(array_key_exists($name, self::$PAGE_INFO))
        {
            $page_array = self::$PAGE_INFO[$name];
            if(array_key_exists(P_METD, $page_array))
                return $page_array[P_METD];
            else
                return self::$DEFAULT_META_DESC;
        }
        else
            return self::$DEFAULT_META_DESC;
    }

    public static function getPageMetaKeywords($name)
    {
        if(array_key_exists($name, self::$PAGE_INFO))
        {
            $page_array = self::$PAGE_INFO[$name];
            if(array_key_exists(P_METK, $page_array))
                return $page_array[P_METK];
            else
                return self::$DEFAULT_META_KEYWORDS;
        }
        else
            return self::$DEFAULT_META_KEYWORDS;
    }

    // Includes the page contents (ProcessURL must be called first). 
    // Returns the name of the included page.
    public static function IncludePageContents()
    {
        $included = "";
        if(isset(self::$to_show) && array_key_exists(P_FILE, self::$to_show) && 
                        file_exists(self::$ROOT_FOLDER . self::$to_show[P_FILE]))
        {
            $included = self::$ROOT_FOLDER . self::$to_show[P_FILE]; 
            include($included);
        }
        else
        {
            $included = self::$ROOT_FOLDER . self::$FILE_NOT_FOUND[P_FILE]; 
            include($included); 
        }
        return $included;
    }

    // Make sure the request is coming to one of the accepted hosts, and if not,
    // redirect to the master host.
    private static function checkHost()
    {
        $http_host = $_SERVER['HTTP_HOST'];
        if($http_host != self::$MASTER_HOST && !in_array($http_host, self::$ACCEPTED_HOSTS))
        {
            // We anticipate the HTTPS requirement here so that we can avoid a 
            // second redirect from checkHTTPS()
            // Use https:// protocol if:
            //          1. $FORCE_HTTPS is true
            //      or, 2. HTTPS is already in use.
            if(self::$FORCE_HTTPS || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'))
                $protocol = "https://";
            else
                $protocol = "http://";

            // Redirect to the master host
            self::permRedirect($protocol . self::$MASTER_HOST . "/" . 
                            self::getUrlFile() . self::getUrlParams());
        }
    }

    // If $FORCE_HTTPS is true and HTTPS is not in use, redirect to an HTTPS URL
    private static function checkHTTPS()
    {
        if(self::$FORCE_HTTPS && (empty($_SERVER["HTTPS"]) || $_SERVER['HTTPS'] == 'off'))
        {
            self::permRedirect("https://" . $_SERVER['HTTP_HOST'] . "/" . 
                            self::getUrlFile() . self::getUrlParams());
        }
    }

    // Returns the page name ($PAGE_INFO key) for the currently requested page, or
    // false if the page requested is not present in $PAGE_INFO.
    // e.g. if the url is either:
    //    a) http://example.com/foo.htm
    // or b) http://example.com/foo
    // this method will return 'foo', if $PAGE_INFO['foo'] exists.
    private static function getPageArrayKey()
    {
        $page_name = strtolower(self::getUrlFile());
        $htm_removed = false;

        // Remove the .htm extension if present
        if(strpos($page_name, ".htm") === strlen($page_name) - 4)
        {
            $page_name = substr($page_name, 0, strlen($page_name) - 4);
            $htm_removed = true;

            // If the page name ends in a "/", it is not valid, e.g:
            // http://example.com/.htm
            // http://example.com/foo/.htm
            if(empty($page_name) || $page_name[strlen($page_name) - 1] == "/")
                return false;
        }

        // Return the page array if the page exists, otherwise boolean false.
        if(array_key_exists($page_name, self::$PAGE_INFO))
        {
            return $page_name;
        }
        elseif(array_key_exists($page_name . "/", self::$PAGE_INFO) && !$htm_removed)
        {
            return $page_name . "/";
        }
        else
        {
            return false;
        }
    }

    // Checks if the P_RDIR index exists in the page array and redirects to
    // the specified page if so.
    private static function checkRedirectRequest($page_array)
    {
        if(array_key_exists(P_RDIR, $page_array))
        {
            $redir = $page_array[P_RDIR];

            // Anticipate the need for .htm extension to avoid a second redirect
            // All pages that don't end in a / must end in .htm
            if(!empty($redir) && $redir[strlen($redir) - 1] != "/")
            {
                $redir .= ".htm";
            }

            // Redirect, keeping the URL parameters.
            self::permRedirect(self::getUrlFront() . $redir . self::getUrlParams());
        }
    }

    // Ensures that the current URL ends in .htm, if it is the URL of a normal
    // page, or ends in "/" if it is the URL of a virtual directory root.
    // http://example.com/?bar => http://example.com/?bar
    // http://example.com/foo/bar?baz => http://example.com/foo/bar.htm?baz
    // http://example.com/hello => http://example.com/hello/ (if $proper_name is "hello/")
    private static function ensureHTMOrSlashExtension($page_array, $proper_name)
    {
        $file = self::getUrlFile();

        // If the page is a directory (other than the root)...
        if(!empty($proper_name) && $proper_name[strlen($proper_name) - 1] == "/") 
        {
            if($file[strlen($file) - 1] != "/") // ... make sure it ends in "/"
            {
                // Redirect to the / version, preserving the parameters
                self::permRedirect(self::getUrlFront() . $file . "/" . self::getUrlParams()); 
            }
        }
        // Otherwise, if it's a normal page name, it should end in .htm
        elseif(!empty($file) && strpos($file, ".htm") != strlen($file) - 4)
        {
            // Redirect to the .htm version, preserving the parameters
            self::permRedirect(self::getUrlFront() . $file . ".htm" . self::getUrlParams()); 
        }
    }

    // Returns the URL parameters, if any.
    // If there are URL parameters, a the parameter string (including "?")
    // will be returned. If there are none, the empty string is returned.
    private static function getUrlParams()
    {
        $url = $_SERVER['REQUEST_URI'];
        $question = strpos($url, "?");
        if($question !== FALSE)
            return substr($url, $question);
        else
            return "";
    }

    // Returns the file part of the URL. This is everything after (not including)
    // the first "/" after the host name and before (not including) the "?" in
    // front of the URL parameters.
    // e.g. http://example.com/foo/bar.htm?baz=foo returns "foo/bar.htm"
    private static function getUrlFile()
    {
        $url = $_SERVER['REQUEST_URI'];
        $first_slash = self::getFirstSlashIndex($url);
        $question = strpos($url, "?");
        if($question === false)
            return substr($url, $first_slash + 1);
        else
            return substr($url, $first_slash + 1, $question - $first_slash - 1);
    }

    // Returns the protocol and host part of the URL, including the "/" after
    // the host name.
    private static function getUrlFront()
    {
        $url = $_SERVER['REQUEST_URI'];
        $first_slash = self::getFirstSlashIndex($url);
        return substr($url, 0, $first_slash + 1);
    }

    // Returns the index of the slash after the hostname, or the index of the
    // last character in the string if there is none.
    private static function getFirstSlashIndex($url)
    {
        $prot_end = strpos($url, "://");
        if($prot_end === false)
            $prot_end = 0;
        else
            $prot_end += 3; // skip over the ://

        // find the first slash after the end of the protocol specifier
        $first_slash = strpos($url, "/", $prot_end);

        // If there is no slash after the protocol specifier, the entire URL
        // is considered the "front" so we return the index of the last element.
        if($first_slash === false)
            return strlen($url) - 1;
        else
            return $first_slash;
    }

    // Send a HTTP 301 Moved Permanently redirect and cease script execution.
    private static function permRedirect($newUrl)
    {
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: $newUrl");
        die();
    }

    // Send the HTTP 404 Not Found header
    private static function send404Headers()
    {
        header("HTTP/1.0 404 Not Found");
        header("Status: 404 Not Found");
    }
}
?>
