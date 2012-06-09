<?php
// WARNING: HACKED ---- table prefixes 
/*
 * phpcount.php Ver.1.0- Provides a MySQL-based "Anonymous" hit counter.
 * Copyright (C) 2011  FireXware (firexware@gmail.com)
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
//==============================================================================
/*
 * This PHP Class provides a hit counter that is able to track unique hits
 * without recording the visitor's IP address in the database. It does so by 
 * recording the hash of the IP address and page name.
 *
 * By hashing the IP address with page name as salt, you prevent yourself from
 * being able to track a user as they navigate your site. You also prevent 
 * yourself from being able to recover anyone's IP address without brute forcing
 * the 2^32 possible IP addresses. In the case of IPv6, it becomes 2^128.
 *
 * Contact: firexware@gmail.com
 * WWW:     http://ossbox.com/
 *
 * USAGE:
 *        In your script, use reqire_once() to import this script, then call the
 *        functions like PHPCount::AddHit(...); See each function for help.
 */
 
//This defines how many seconds a hit should be rememberd for. This prevents the
//database from perpetually increasing in size. One month (the default) should 
//be adequate. If someone visits a page and comes back in a month, I think that
//should count as another unique hit.
define("HIT_OLD_AFTER_SECONDS", 4 * 7 * 24 * 3600);

// MySQL Login and Database Information
$dbserver = "localhost";
$username = "phpcount";
$password = "YRgPlRJgTY9C";
$database = "phpcount";

$phpcount_con = mysql_connect($dbserver, $username, $password);

if(!$phpcount_con)
{
	die("Count not connect to PHPCount MySQL server!");
}

mysql_select_db($database, $phpcount_con) or die("Count not select PHPCount database.");

class PHPCount
{
	/*
	 * AddHit(<page identifier>, <visitor identifier>)
	 * Adds a hit. Takes care of checking uniqueness.
	 * $pageID - A unique string that identifies the page
	 * $visitorID - A unique string that represents the visitor (IP address)
	 *
	 * For example, on a page called coolstuff.php...
	 *      PHPCount::("coolstuff", $_SERVER['REMOTE_ADDR']);
	 */
	public static function AddHit($pageID, $visitorID)
	{
		self::Cleanup();
		self::CreateCountsIfNotPresent($pageID);
		if(self::UniqueHit($pageID, $visitorID))
		{
			self::CountHit($pageID, true);
			self::LogHit($pageID, $visitorID);
		}
		self::CountHit($pageID, false);
	}
	
	/*
	 * Returns (int) the amount of hits a page has
	 * $pageID - the page identifier
	 * $unique - true if you want unique hit count
	 */
	public static function GetHits($pageID, $unique = false)
	{
		global $phpcount_con;
		self::CreateCountsIfNotPresent($pageID);
		
		$pageID = mysql_real_escape_string($pageID);
		$unique = $unique ? '1' : '0';
		$q = mysql_query("SELECT hitcount FROM cshits WHERE pageid='$pageID' AND isunique='$unique'", $phpcount_con);
		if(mysql_num_rows($q) >= 1)
		{
			$hitInfo = mysql_fetch_array($q);
			return (int)$hitInfo['hitcount'];
		}
		else
		{
			die("Fatal: Missing hit count from database!");
		}
	}
	
	/*
	 * Returns the total amount of hits to the entire website
	 * When $unique is FALSE, it returns the sum of all non-unique hit counts
	 * for every page. When $unique is TRUE, it returns the sum of all unique
	 * hit counts for every page, so the value that's returned IS NOT the 
	 * amount of site-wide unique hits, it is the sum of each page's unique
	 * hit count.
	 */
	public static function GetTotalHits($unique = false)
	{
		global $phpcount_con;
		$total = 0;
		$unique = $unique ? '1' : '0';
		$q = mysql_query("SELECT hitcount FROM cshits WHERE isunique='$unique'", $phpcount_con);
		while($q && $info = mysql_fetch_array($q))
		{
			$total += (int)$info['hitcount'];
		}
		return $total;
	}
	
	/*====================== PRIVATE METHODS =============================*/
	
	private static function UniqueHit($pageID, $visitorID)
	{
		global $phpcount_con;
		$ids_hash = self::IDHash($pageID, $visitorID);
		$q = mysql_query("SELECT time FROM csnodupes WHERE ids_hash='$ids_hash'", $phpcount_con);
		if(mysql_num_rows($q) > 0)
		{
			$hitInfo = mysql_fetch_array($q);
			if($hitInfo['time'] > time() - HIT_OLD_AFTER_SECONDS) 
			{
				return false;
			}
			else
			{
				return true;
			}
		}
		else
		{
			return true;
		}
	}
	
	private static function LogHit($pageID, $visitorID)
	{
		global $phpcount_con;
		$ids_hash = self::IDHash($pageID, $visitorID);
		$q = mysql_query("SELECT time FROM csnodupes WHERE ids_hash='$ids_hash'", $phpcount_con);
		$curTime = time();
		if(mysql_num_rows($q) > 0)
		{
			mysql_query("UPDATE csnodupes SET time='$curTime' WHERE ids_hash='$ids_hash'", $phpcount_con);
		}
		else
		{
			mysql_query("INSERT INTO csnodupes (ids_hash, time) VALUES('$ids_hash', '$curTime')", $phpcount_con);
		}
	}
	
	private static function CountHit($pageID, $unique)
	{
		global $phpcount_con;
		$unique = $unique ? '1' : '0';
		$safeID = mysql_real_escape_string($pageID);
		mysql_query("UPDATE cshits SET hitcount = hitcount + 1 WHERE pageid='$safeID' AND isunique='$unique'", $phpcount_con);
	}
	
	private static function IDHash($pageID, $visitorID)
	{
		$ids_hash = mysql_real_escape_string(hash("SHA256", $pageID . $visitorID));
		return $ids_hash;
	}
	
	private static function CreateCountsIfNotPresent($pageID)
	{
		global $phpcount_con;
		$pageID = mysql_real_escape_string($pageID);
		//check non-unique row
		$q = mysql_query("SELECT pageid FROM cshits WHERE pageid='$pageID' AND isunique='0'", $phpcount_con);
		if($q === false || mysql_num_rows($q) < 1)
		{
			mysql_query("INSERT INTO cshits (pageid, isunique, hitcount) VALUES ('$pageID', '0', '0')", $phpcount_con);
		}
		
		//check unique row
		$q = mysql_query("SELECT pageid FROM cshits WHERE pageid='$pageID' AND isunique='1'", $phpcount_con);
		if($q === false || mysql_num_rows($q) < 1)
		{
			mysql_query("INSERT INTO cshits (pageid, isunique, hitcount) VALUES('$pageID', '1', '0')", $phpcount_con);
			echo mysql_error();
		}
	}
	
	private static function Cleanup()
	{
		global $phpcount_con;
		$last_interval = time() - HIT_OLD_AFTER_SECONDS;
		mysql_query("DELETE FROM csnodupes WHERE time < '$last_interval'", $phpcount_con);
		echo mysql_error();
	}
}
