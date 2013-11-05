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

function getCrackedCount()
{
    $str = file_get_contents("count.dat");
    $counts = explode(":", $str);
    return (int)$counts[0];
}

function getCrackAttemptCount()
{
    $str = file_get_contents("count.dat");
    $counts = explode(":", $str);
    return (int)$counts[1];
}

function incrementCounter($cracked, $attempts)
{
    $str = (getCrackedCount() + $cracked) . ":" . (getCrackAttemptCount() + $attempts);
    file_put_contents("count.dat", $str);
}

?>
