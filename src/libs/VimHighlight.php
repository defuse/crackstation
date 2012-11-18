<?php
/*
 * Syntax highlighting with Vim on Linux.
 * Version: 0.2
 * Author: havoc@defuse.ca
 * WWW: https://defuse.ca/syntax-highlighting-in-php-with-vim.htm
 * License: Do whatever you want, but please give credit!
 * Dependencies: Vim, and optionally gVim and Xvfb for better colors.
 * NOTE: Vim will be executed as the user running this script, so any additional
 * color schemes will have to be put either in the master Vim config folder, or
 * in that user's ~/.vim folder. No other configuration should be necessary.
 *
 * When used with command-line vim, only 256 colors are available, so unless
 * the color scheme was designed for 256-color, it will probably look bad. The
 * solution is to install gVim and Xvfb and do the syntax highlighting with 
 * gVim (Xvfb is a virtual X server, so you can do it on a headless server).
 * gVim is slower. However, to speed things up you can grab the CSS produced
 * by gVim with use_css set to true, then splice it into the <head> section
 * of the HTML produced by command-line Vim, and it will look the same.
 */
class VimHighlight
{
    // ---------------------- CONSTANT SETTINGS -----------------------------
    // Set this to an unused display number to use with Xvfb for gvim.
    const XVFB_DISPLAY = ":7";
    // Override the cache directories if necessary. MUST NOT end in a slash.
    const STRING_CACHE_DIR = "/var/vimhl"; // default: system's temp folder
    const CACHE_SUFFIX = ".highlighted.html";


    // ----------------------- RUNTIME SETTINGS -----------------------------

    // Set to true to cache the result, since executing Vim is slow.
    public $caching = false;
    // The color scheme to render (passed to :colorsheme)
    public $color_scheme = "default";
    // The language of the file/string. Set to null to make Vim auto-detect.
    public $file_type = null;
    // Whether or not to show line numbers.
    public $show_lines = true;
    // Use CSS instead of inline font tags. Doesn't work with $body_only unless
    // you manually put the theme's CSS into your page's <head>.
    public $use_css = false;
    // Extra CSS to add in the style="" section of the wrapper <div> tag
    // generated when processText() or processFile() is called with 
    // $body_only = true.
    public $div_css = "";
    // The command to run Vim. Set to 'gvim' to get better color rendering.
    private $vim_command = "vim";

    // Set this to an array of '~/.vim'-like folders if GetAvailableColorSchemes
    // or GetAvailableFileTypes is taking too long. Paths may begin with '~'
    // which will be expanded to the running user's home folder, and MUST NOT
    // end in a slash.
    private static $RUNTIME_DIRS = null;

    // ----------------------- STATIC METHODS -------------------------------

    public static function GetAvailableColorSchemes() {
        return self::GetVimFileNamesFromFolders(self::GetRuntimePaths("colors"));
    }

    public static function GetAvailableFileTypes() {
        return self::GetVimFileNamesFromFolders(self::GetRuntimePaths("syntax"));
    }

    // ----------------------- VIM HIGHLIGHTING -----------------------------

    public function setVimCommand($cmd) {
        $cmd = strtolower($cmd);
        if($cmd == "vi" || $cmd == "vim" || $cmd == "gvim")
            $this->vim_command = $cmd;
        else
            trigger_error("Invalid Vim command", E_USER_WARNING);
    }

    public function processText($str, $body_only = true) {
        // Write the string to a temp file for Vim to read.
        $input_path = tempnam(sys_get_temp_dir(), "hlinput");
        touch($input_path);
        chmod($input_path, 0600);
        file_put_contents($input_path, $str);

        $tmp = self::STRING_CACHE_DIR;
        if($tmp === null)
            $tmp = sys_get_temp_dir();
        $output_path = $tmp . "/" . md5($str) . self::CACHE_SUFFIX;
        // Since we just created the input file, it will be newer than the
        // cache file, so we must ignore the modified timestamp when checking
        // the cache (the md5 ensures it's the same text).
        $html =  $this->runVim($input_path, $output_path, $body_only, true);

        // Clean up the input file.
        unlink($input_path);

        return $html;
    }

    public function processFile($input_path, $body_only = true) {
        if($this->caching)
            $output_path = $input_path . self::CACHE_SUFFIX;
        else
            $output_path = tempnam(sys_get_temp_dir(), "hloutput");

        return $this->runVim($input_path, $output_path, $body_only, false);
    }

    // ----------------------- PRIVATE STUFF --------------------------------

    private function runVim($input_path, $output_path, $body_only, $ignoretime) {
        if(!file_exists($input_path)) {
            trigger_error("Input file does not exist", E_USER_WARNING);
            return;
        }

        if($this->caching) {
            // If another instance of this script is processing the same file, and
            // caching is enabled for this instance, it's probably enabled for the
            // other, so it'll be more efficient to wait for it to finish then grab
            // its cache. If the other instance isn't caching, then we just fall
            // through the cache check and do it ourselves.
            $lock = fopen($input_path, "r");
            if(!flock($lock, LOCK_EX)) {
                trigger_error("Unable to obtain lock", E_USER_WARNING);
            }

            // Use the cache file if it exists and has the same parameters.
            if(
                file_exists($output_path) &&
                ($ignoretime || filemtime($output_path) > filemtime($input_path))
            ) {
                $cached = file_get_contents($output_path);
                $cache_info = $this->extractInfo($cached);
                if( $cache_info['color_scheme'] == $this->color_scheme &&
                    $cache_info['file_type'] == $this->file_type &&
                    $cache_info['show_lines'] == $this->show_lines &&
                    $cache_info['use_css'] == $this->use_css && 
                    $cache_info['vim_command'] == $this->vim_command
                ) {
                    flock($lock, LOCK_UN);
                    fclose($lock);
                    if($body_only) {
                        return $this->extractBody($cached);
                    } else {
                        return $this->stripInfo($cached);
                    }
                }
            }
        }

        // Set permissions to avoid leaking potentially sensitive data to 
        // other users, and to not let other users mess with the cache file
        // (potentially XSSing the site or exploiting unserialize()).
        if(!file_exists($output_path)) {
            touch($output_path);
            chmod($output_path, 0600);
        }

        $colorscheme = "-c " . escapeshellarg("colo $this->color_scheme");
        // Default to not setting the filetype, to take advantage of Vim's autodetect.
        $ft = $this->file_type !== null ? "-c " . escapeshellarg("set filetype=$this->file_type") : "";
        $nu = "-c " . escapeshellarg($this->show_lines ? "set number" : "set nonumber");
        $use_html_css = $this->use_css ? "1" : "0";
        $write_html_cmd = "-c " . escapeshellarg("w! $output_path");

        if($this->vim_command == "gvim") {
            exec("Xvfb " . escapeshellarg(self::XVFB_DISPLAY) . " > /dev/null 2>&1 & sleep 3");
            $display = " -display " . escapeshellarg(self::XVFB_DISPLAY) . " ";
        } else {
            $display = "";
        }

        system(
            $this->vim_command . $display .
            // "Don't" connect to X; pretend we have xterm with 256 colors;
            // disable plugins,  swap file, and wildcard expansion.
            " -X -T xterm -c 'set t_Co=256' --noplugin -n --literal " .
            // Enable syntax highlighting and disable CSS output.
            " -f -c 'syn on' -c 'let html_use_css = $use_html_css' " .
            // Set the colorsheme, file (language) type, and line numbering.
            " $colorscheme $ft $nu " .
            // Generate the html, write it to the output file, then quit both buffers.
            " -c 'run! syntax/2html.vim' $write_html_cmd -c 'q!' -c 'q!' " .
            // Input file. Pipe all output to /dev/null.
            escapeshellarg($input_path) . " > /dev/null 2>&1 "
        );

        // Get the HTML written by Vim.
        $html = file_get_contents($output_path);
        if($body_only) {
            $html = $this->extractBody($html);
        }

        if($this->caching) {
            $this->appendInfo($output_path);
        } else {
            unlink($output_path);
        }

        if($this->caching) {
            flock($lock, LOCK_UN);
            fclose($lock);
        }

        return $html;
    }

    private function extractInfo($string) {
        $start = strrpos($string, "<!--") + strlen("<!--");
        $end = strrpos($string, "-->") - 1;
        return $this->decodeInfo(
            html_entity_decode(
                substr($string, $start, $end - $start + 1)
            )
        );
    }

    private function stripInfo($string) {
        $start = strrpos($string, "<!--");
        return substr($string, 0, $start);
    }

    private function appendInfo($file) {
        $fh = fopen($file, "ab");
        fseek($fh, 0, SEEK_END); 
        fwrite($fh, "\n<!-- " . htmlentities($this->encodeInfo()) . " -->\n");
        fclose($fh);
    }

    private function encodeInfo() {
        $info = array(
            'color_scheme' => $this->color_scheme,
            'file_type' => $this->file_type,
            'show_lines' => $this->show_lines,
            'use_css' => $this->use_css,
            'vim_command' => $this->vim_command
        );
        return serialize($info);
    }

    private function decodeInfo($str) {
        return unserialize(trim($str));
    }

    private function extractBody($html) {
        if($this->use_css) {
            $bodyStart = strpos($html, "<pre>");
            $bodyEnd = strrpos($html, "</pre>") + strlen("</pre>");
            return substr($html, $bodyStart, $bodyEnd - $bodyStart + 1);
        } else {
            // Find <body... and </body>
            $bodyStart = strpos($html, "<body");
            $bodyEnd = strrpos($html, "</body>") - 1;

            // Get the background color and text color from the body tag.
            preg_match(
                "/\\<body bgcolor=\"([^\"]+)\" text=\"([^\"]+)\"\\>/i",
                $html,
                $matches,
                0, // no flags
                $bodyStart
            );
            $bgcolor = $matches[1];
            $textcolor = $matches[2];
            $div_css = htmlentities($this->div_css);
            // Replace the body tag with a div, so it can be put in existing HTML pages.
            $divStart = "<div class=\"vimhighlight\" " . 
                "style=\"color: $textcolor; background-color: $bgcolor; $div_css\">";

            // Adjust the starting point to just after the body opening tag.
            $bodyStart += strlen($matches[0]);

            $innerHTML = substr($html, $bodyStart, $bodyEnd - $bodyStart + 1);
            return $divStart . $innerHTML . "</div>";
        }
    }

    private static function GetVimFileNamesFromFolders($folders) {
        // Get a unique list of all *.vim files in every folder.
        // This would look great in Ruby. :)
        $files = array();
        foreach($folders as $folder) {
            $files = array_unique(array_merge(
                // List all the *.vim files in the folder.
                array_filter(
                    scandir($folder),
                    function ($path) {
                        return pathinfo($path, PATHINFO_EXTENSION) == "vim";
                    }
                ),
                // Merge and unique with the current list.
                $files
            ));
        }
        // Return only the filename part (no extension).
        return array_map(
            function ($path) {
                return pathinfo($path, PATHINFO_FILENAME);
            },
            $files
        );
    }

    private static function GetRuntimePaths($suffix) {
        if(self::$RUNTIME_DIRS) {
            return array_map(
                function($path) use($suffix) {
                    // NOTE: This doesn't work with the ~username notation.
                    $path = preg_replace("^~", $_SERVER["HOME"], $path);
                    return $path . "/" . $suffix;
                },
                self::$RUNTIME_DIRS
            );
        } else {
            exec("vim --cmd 'echo \$VIMRUNTIME' --cmd 'quit!' 2>&1", $out);
            // First line is warning that output is not to a terminal, second line
            // is the VIMRUNTIME path. On my system, Vim prints some non-printable
            // characters right after the path, so get rid of them.
            $main = trim(self::GetPrintablePrefix($out[1]));
            // Add the ~/.vim folder if it's there.
            $homePath = $_SERVER["HOME"] . "/.vim/";
            if(is_dir($homePath)) {
                return array($main . "/" . $suffix, $homePath . $suffix);
            } else {
                return array($main . "/" . $suffix);
            }
        }
    }

    private static function GetPrintablePrefix($str) {
        $printable = "";
        for($i = 0; $i < strlen($str); $i++) {
            // Keep everything between SPACE and DEL
            if(ord($str[$i]) >= ord(' ') && ord($str[$i]) < 127) {
                $printable .= $str[$i];
            } else {
                break;
            }
        }
        return $printable;
    }
}

?>
