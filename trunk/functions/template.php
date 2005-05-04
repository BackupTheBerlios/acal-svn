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

// Create popup link. Will return attribute with value.
function popup($url) {
	$link = 'href="javascript:void(0)" onclick="popUp(\'' . $url . '\')"';
	return $link;
}

// Put all the date variables into an array (Set C)
function cset() {
	$arr = array();
	
	// Year
	if (isset($_GET['year'])) {
		$arr['year'] = $_GET['year'];
	}
	elseif (isset($_POST['year'])) {
		$arr['year'] = $_POST['year'];
	}
	else {
		$arr['year'] = date('Y');
	}
	
	// Month
	if (isset($_GET['month'])) {
		$arr['month'] = $_GET['month'];
	}
	elseif (isset($_POST['month'])) {
		$arr['month'] = $_POST['month'];
	}
	else {
		$arr['month'] = date('m');
	}
	
	// Day
	if (isset($_GET['day'])) {
		$arr['day'] = $_GET['day'];
	}
	elseif (isset($_POST['day'])) {
		$arr['day'] = $_POST['day'];
	}
	else {
		$arr['day'] = date('d');
	}
	
	// Amount of days in month
	$arr['days'] = date('t', mktime(0, 0, 0, $arr['month'], $arr['day'], $arr['year']));
	
	// Timestamp
	$arr['timestamp'] = mktime(0, 0, 0, $arr['month'], $arr['day'], $arr['year']);
	
	global $pref;
	// Next hour
	if ($pref->getvalue('ttype') == 'world') {
		$hr = date('H');
		$arr['hour'] = $hr;
		if ($hr == 23) {
			$arr['nexthr'] = 00;
		}
		else {
			$hr++;
			$arr['nexthr'] = sprintf('%02d', $hr);
		}
	}
	else {
		$hr = date('h');
		$arr['hour'] = $hr;
		if ($hr == 12) {
			$arr['nexthr'] = 01;
		}
		else {
			$hr++;
			$arr['nexthr'] = sprintf('%02d', $hr);
		}
		
		// AM or PM
		$arr['xm'] = date('a', $arr['timestamp']);
		$arr['txm'] = date('a', mktime($arr['nexthr'], 0, 0, $arr['month'], $arr['day'], $arr['year']));
	}
	
	// Return value
	return $arr;
}

// Return events that match the timestamp
function get_events($timestamp1, $timestamp2) {
	global $db;
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
		'notes'
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
				$dow = date('w', $timestamp1);
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
				$dow = date('d', $timestamp1);
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
				if (date('d', $timestamp1) == $day && date('m', $timestamp1) == $month) {
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