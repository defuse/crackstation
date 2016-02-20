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

require_once('/etc/creds.php');

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
    $creds = Creds::getCredentials("cs_cracking_server");
    $url = "http://" . $creds[C_HOST] . "/" . $creds[C_DATB];
    unset($creds);
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
    return true;
}
?>
