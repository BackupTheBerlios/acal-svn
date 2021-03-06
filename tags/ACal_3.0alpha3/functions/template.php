<?php
/*
	ACal web based event calendar.
    Copyright (C) 2005  Arthur Wiebe

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// Add arguments to the HTTP request header
function pend_requests($args, $html = true) {
	$nuri = $_SERVER['REQUEST_URI'];
	if (preg_match('/\?/', $_SERVER['REQUEST_URI'])) {
		foreach ($args as $key => $arg) {
			if (isset($_GET[$key])) {
				if ($_GET[$key] == $arg) {
					continue;
				}
				else {
					$nuri = edit_requests($key, $arg, $nuri);
					continue;
				}
			}
			if ($html) {
				$nuri .= "&amp;$key=$arg";
			}
			else {
				$nuri .= "&$key=$arg";
			}
		}
	}
	else {
		$done = false;
		foreach ($args as $key => $arg) {
			if (!$done) {
				$nuri .= "?$key=$arg";
				$done = true;
				continue;
			}
			$nuri .= "&amp;$key=$arg";
		}
	}
	return $nuri;
}

// Edit arguments from HTTP request
function edit_requests($name, $value, $uri = NULL, $remove = false) {
	if ($uri == NULL) {
		$uri = $_SERVER['REQUEST_URI'];
	}
	if (!$remove) {
		// Change the value of GET query
		$req = str_replace("$name=$_GET[$name]", "$name=$value", $uri);
	}
	else {
		// Do my best to remove this GET query
		$ustr = parse_url($uri);
		if (isset($ustr['query'])) {
			$ostr = $ustr['query'];
		}
		else {
			$ostr = '';
		}
		// Explode the URI and remove the offending part
		$ustr = explode('&', $ostr);
		foreach ($ustr as $key => $str) {
			if (preg_match("|($name)(\=)(.*)|", $str)) {
				// delete!
				unset($ustr[$key]);
			}
		}
		// Put it back together
		$nurl = $_SERVER['SCRIPT_NAME'];
		$c = count($ustr);
		if ($ostr != '' && $c >= 1) {
			$nurl .= '?';
		}
		$i = 1;
		foreach ($ustr as $arg) {
			$nurl .= $arg;
			if ($i != $c) {
				$nurl .= '&';
			}
			$i++;
		}
		$req = $nurl;
	}
	return $req;
}

// Put all the date variables into an array (Set C)
function cset() {
	global $time;
	$arr = array();
	
	// Year
	if (isset($_GET['year'])) {
		$arr['year'] = $_GET['year'];
	}
	elseif (isset($_POST['year'])) {
		$arr['year'] = $_POST['year'];
	}
	else {
		$arr['year'] = $time->date('Y');
	}
	
	// Month
	if (isset($_GET['month'])) {
		$arr['month'] = $_GET['month'];
	}
	elseif (isset($_POST['month'])) {
		$arr['month'] = $_POST['month'];
	}
	else {
		$arr['month'] = $time->date('m');
	}
	
	// Day
	if (isset($_GET['day'])) {
		$arr['day'] = $_GET['day'];
	}
	elseif (isset($_POST['day'])) {
		$arr['day'] = $_POST['day'];
	}
	else {
		$arr['day'] = $time->date('d');
	}
	
	// Amount of days in month
	$arr['days'] = $time->date('t', $time->make(0, 0, 0, $arr['day'], $arr['month'], $arr['year']));
	
	// Timestamp
	$arr['timestamp'] = $time->make(0, 0, 0, $arr['day'], $arr['month'], $arr['year']);
	
	global $pref;
	// Next hour
	if ($pref->getvalue('ttype') == '24hr') {
		if ($time->now['hour'] < 23) {
			$arr['nexthr'] = sprintf('%02d', $time->now['hour'] + 1);
		}
		else {
			$arr['nexthr'] = '00';
		}
		$arr['hour'] = $time->now['hour'];
	}
	else {
		$arr['xm'] = $time->date('a');
		if ($time->now['hour'] > 12) {
			$arr['hour'] = sprintf('%02d', $time->now['hour'] - 12);
		}
		if ($time->now['hour'] == 12) {
			$arr['thour'] = '01';
			$arr['hour'] = '12';
			if ($arr['xm'] == 'am') {
				$arr['txm'] = 'pm';
			}
			else {
				$arr['txm'] = 'am';
			}
		}
		else {
			$arr['txm'] = $arr['xm'];
			if (!isset($arr['hour'])) {
				$arr['hour'] = $time->now['hour'];
			}
			$arr['thour'] = sprintf('%02d', $arr['hour'] + 1);
		}
		if (!isset($arr['hour'])) {
			$arr['hour'] = $time->now['hour'];
		}
	}
	
	// Return value
	return $arr;
}

// Return events that match the timestamp
function get_events($timestamp1, $timestamp2) {
	global $db;
	global $time;
	$sql = "SELECT * FROM events WHERE fromtime >= '$timestamp1' AND totime <= '$timestamp2' OR repeat <> 'none'";
	$cols = array(
		'id',
		'summary',
		'flags',
		'fromtime',
		'totime',
		'status',
		'repeat',
		'alarm',
		'notes',
		'category'
	);
	
	// Get events
	$events = $db->fetch_rows_array($sql, $cols);
	
	foreach ($events as $key => $col) {
		if ($col['repeat'] != 'none') {
			// Split the characters into array
			$rep = explode(':', $col['repeat']);
			// Split 2
			$typ = explode('-', $rep[2]);
			
			// Take care of daily stuff
			if ($typ[0] == 'daily') {
				if ($rep[0] <= $timestamp1) {
					if ($rep[1] >= $timestamp2 || $rep[1] == '0') {
						continue;
					}
				}
				if ($col['fromtime'] >= $timestamp1 && $col['totime'] <= $timestamp2) {
					continue;
				}
			}
			
			// Take care of weekly stuff
			if ($typ[0] == 'weekly') {
				// Check day of week
				$dow = $time->date('w', $timestamp1);
				if ($dow == $typ[1]) {
					// Make sure we are within bounds
					if ($rep[0] <= $timestamp1) {
						if ($rep[1] >= $timestamp2 || $rep[1] == '0') {
							continue;
						}
					}
					if ($col['fromtime'] >= $timestamp1 && $col['totime'] <= $timestamp2) {
						continue;
					}
				}
			}
			
			// Take care of monthly stuff
			if ($typ[0] == 'monthly') {
				// Check day of month
				$dow = $time->date('d', $timestamp1);
				if ($dow == $typ[1]) {
					// Make sure we are within bounds
					if ($rep[0] <= $timestamp1) {
						if ($rep[1] >= $timestamp2 || $rep[1] == '0') {
							continue;
						}
					}
					if ($col['fromtime'] >= $timestamp1 && $col['totime'] <= $timestamp2) {
						continue;
					}
				}
			}
			
			// Take care of yearly stuff
			if ($typ[0] == 'yearly') {
				// Check day and month
				$dm = explode(',', $typ[1]);
				$day = $dm[0];
				$month = $dm[1];
				if ($time->date('d', $timestamp1) == $day && $time->date('m', $timestamp1) == $month) {
					// Make sure we are within bounds
					if ($rep[0] <= $timestamp1) {
						if ($rep[1] >= $timestamp2 || $rep[1] == '0') {
							continue;
						}
					}
					if ($col['fromtime'] >= $timestamp1 && $col['totime'] <= $timestamp2) {
						continue;
					}
				}
			}
		}
		else {
			continue;
		}
		// If not continued this event must be removed
		unset($events[$key]);
	}
	return $events;
}
?>