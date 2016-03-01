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

require_once(getenv("CRACKSTATION_DEPENDS_DIR") . "/crackstation-hashdb/LookupTable.php");

function CrackHashes($hashes)
{
	echo "<table class=\"results\">";
	echo "<tr><th>Hash</th><th>Type</th><th>Result</th></tr>";

    foreach($hashes as $hash) {
        $supported_lookups = array(
            array(
                'index' => 'lm.idx',
                'dict' => 'REALUNIQ.lst',
                'alg' => 'LM',
            ),
            array(
                'index' => 'md2.idx',
                'dict' => 'REALUNIQ.lst',
                'alg' => 'md2',
            ),
            array(
                'index' => 'md4.idx',
                'dict' => 'REALUNIQ.lst',
                'alg' => 'md4',
            ),
            array(
                'index' => 'md5.idx',
                'dict' => 'REALUNIQ.lst',
                'alg' => 'md5',
            ),
            array(
                'index' => 'md5md5.idx',
                'dict' => 'REALUNIQ.lst',
                'alg' => 'md5(md5)',
            ),
            array(
                'index' => 'mysql4.1+.idx',
                'dict' => 'REALUNIQ.lst',
                'alg' => 'MySQL4.1+',
            ),
            array(
                'index' => 'ntlm.idx',
                'dict' => 'REALUNIQ.lst',
                'alg' => 'NTLM',
            ),
            array(
                'index' => 'ripemd160.idx',
                'dict' => 'REALUNIQ.lst',
                'alg' => 'ripemd160',
            ),
            array(
                'index' => 'sha1.idx',
                'dict' => 'REALUNIQ.lst',
                'alg' => 'sha1',
            ),
            array(
                'index' => 'sha224.idx',
                'dict' => 'REALUNIQ.lst',
                'alg' => 'sha224',
            ),
            array(
                'index' => 'sha256.idx',
                'dict' => 'REALUNIQ.lst',
                'alg' => 'sha256',
            ),
            array(
                'index' => 'sha384.idx',
                'dict' => 'REALUNIQ.lst',
                'alg' => 'sha384',
            ),
            array(
                'index' => 'sha512.idx',
                'dict' => 'REALUNIQ.lst',
                'alg' => 'sha512',
            ),
            array(
                'index' => 'whirlpool.idx',
                'dict' => 'REALUNIQ.lst',
                'alg' => 'whirlpool',
            ),
            /* Big ones. */
            array(
                'index' => 'md5-huge.idx',
                'dict' => 'HUGELIST.lst',
                'alg' => 'md5',
            ),
            array(
                'index' => 'sha1-huge.idx',
                'dict' => 'HUGELIST.lst',
                'alg' => 'sha1',
            ),
        );

        $html_escaped_hash = htmlentities($hash, ENT_QUOTES);

        /* Try to crack the hash with every lookup table, collecting all of the
            results. */
        $results = array();
        foreach($supported_lookups as $lookup) {

            $index_path = getenv("CRACKSTATION_DEPENDS_DIR") . "/cracking/" . $lookup['index'];
            $dict_path = getenv("CRACKSTATION_DEPENDS_DIR") . "/cracking/" . $lookup['dict'];

            $lut = new LookupTable($index_path, $dict_path, $lookup['alg']);

            try {
                $results = array_merge($results, $lut->crack($hash));
            } catch (HashFormatException $ex) {
                echo "<tr class=\"fail\"><td>$html_escaped_hash</td><td>Unknown</td><td>Unrecognized hash format.</td></tr>";
                /* WARNING: Curently a throw of HashFormatException for one
                   algorithm means it will throw for *all* algorithms. That may
                   not remain to be the case. */
                break;
            }
        }

        /* Show all of the results for this hash. */
        if (count($results) == 0) {
            echo "<tr class=\"fail\"><td>$html_escaped_hash</td><td>Unknown</td><td>Not found.</td></tr>";
        } else {
            foreach ($results as $result) {
                if ($result->isFullMatch()) {
                    $tr_class = "suc";
                } else {
                    $tr_class = "part";
                }
                echo "<tr class=\"$tr_class\">";
                echo "<td>$html_escaped_hash</td>";
                $html_escaped_alg = htmlentities($result->getAlgorithmName(), ENT_QUOTES);
                echo "<td>$html_escaped_alg</td>";
                $html_escaped_plaintext = htmlentities($result->getPlaintext(), ENT_QUOTES);
                echo "<td>$html_escaped_plaintext</td>";
                echo "</tr>";
            }
        }
    }

    echo "</table>";
    echo '<p style="font-size: 8pt;"><strong>Color Codes:</strong> <span style="background-color: #00FF00;">Green:</span> Exact match, <span style="background-color: #FFFF00;">Yellow:</span> Partial match, <span style="background-color: #FF0000;">Red:</span> Not found.</p>';
}
?>
