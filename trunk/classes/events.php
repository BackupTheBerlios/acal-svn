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

// This class takes care of sidebar form events
class Events {
	private $what = 'nothing'; // What should do with POST data
	public $message = ''; // Error and success messages are stored here
	
	function __construct() {
		// First check if we should do anything at all
		if (isset($_POST['form_event'])) {
			// Now figure out what to do
			if (isset($_POST['rmevent']) && $_POST['rmevent'] == 'true') {
				// Bypass everything and just delete event
				$this->rmevent();
			}
			elseif (isset($_GET['event'])) {
				$this->what = 'update';
			}
			else {
				$this->what = 'create';
			}
			
			// Check form
			if (!$this->check()) {
				return false;
			}
			
			// Execute the save method
			$this->save();
		}
	}
	
	/* Save an event to database
	If it has not been saved already, create it. If this event already exists, update it.
	If the user wants it removed, delete it.
	*/
	function save() {
		// Make db global
		global $db;
		
		// Create alarm if needed
		if ($_POST['alarm'] != 'none') {
			// Create random ID
			$alarmID = time() + mt_rand(1, 128);
			
			$type = $_POST['alarm']; // Get type which is email or html
			$msg = $db->escape_sql($_POST['message']); //Alarm message
			
			// Create timestamp for when the alarm will be activated
			$timestamp = mktime($_POST['hour'], $_POST['minute'], 0, $_POST['month'], $_POST['day'], $_POST['year']);
			
			// Insert Alarm into DB
			$sql = "INSERT INTO alarms VALUES('$alarmID', '$msg', '$type', '$timestamp')";
			$db->run($sql);
			
			// Execute alarm
			exec("./bin/acal_alarm -n $alarmID -m $type -t $timestamp >/dev/null &");
		}
		
		// Check if event is all day
		if (isset($_POST['all-day'])) {
			$time1 = mktime(0, 0, 0, $_POST['month'], $_POST['day'], $_POST['year']);
			$time2 = mktime(23, 59, 59, $_POST['month'], $_POST['day'], $_POST['year']);
		}
		else {
			// Check for DST
			global $pref;
			if ($pref->prefs['ttype'] == 'daylight') {
				$dst = 1;
			}
			else {
				$dst = -1;
			}
			
			// Create the timestamps for this event
			$time1 = mktime($_POST['hour'], $_POST['minute'], 0, $_POST['month'], $_POST['day'], $_POST['year'], $dst);
			$time2 = mktime($_POST['thour'], $_POST['tminute'], 0, $_POST['month'], $_POST['day'], $_POST['year'], $dst);
		}
		
		// Take care of event recurrence
		// Syntax: starttimestamp:endtimestamp:type-options
		switch ($_POST['repeat']) {
			case 'daily':
				// Options
				$options = ''; // Later can be used to make exceptions
				// Start Timestamp
				$startstamp = $time1;
				// End Timestamp
				if ($_POST['times'] == '0') {
					$endstamp = 0;
				}
				else {
					$add = 86400 * $_POST['times'];
					$endstamp = $time2 + $add;
				}
				$repeat = "$startstamp:$endstamp:daily-$options";
				break;
			case 'weekly':
				// Day of week
				$dow = date('w', $time1);
				$options = "$dow";
				// Start Timestamp
				$startstamp = $time1;
				// End Timestamp
				if ($_POST['times'] == '0') {
					$endstamp = 0;
				}
				else {
					$add = 604800 * $_POST['times'];
					$endstamp = $time2 + $add;
				}
				$repeat = "$startstamp:$endstamp:weekly-$options";
				break;
			case 'monthly':
				// Day of month
				$dom = date('d', $time1);
				$options = "$dom";
				// Start Timestamp
				$startstamp = $time1;
				// End Timestamp
				if ($_POST['times'] == '0') {
					$endstamp = 0;
				}
				else {
					// Make end timestamp
					$month = $_POST['month'];
					$monthTimes = $month + $_POST['times'] - 1;
					$yearsToAdd = floor($monthTimes / 12);
					$month = $monthTimes - ($yearsToAdd * 12);
					$year = $_POST['year'] + $yearsToAdd;
					
					$endstamp = mktime(23, 59, 59, $month, $_POST['day'], $year);
				}
				$repeat = "$startstamp:$endstamp:monthly-$options";
				break;
			case 'yearly':
				// Set options
				$options = date('d', $time1) . ',' . date('m', $time1);
				// Start Timestamp
				$startstamp = $time1;
				// End Timestamp
				if ($_POST['times'] == '0') {
					$endstamp = 0;
				}
				else {
					// Make end timestamp
					$year = $_POST['year'] + $_POST['times'] - 1;
					$endstamp = mktime(23, 59, 59, $_POST['month'], $_POST['day'], $year);
				}
				$repeat = "$startstamp:$endstamp:yearly-$options";
				break;
			default:
				$repeat = 'none';
				break;
		}
		
		// Category not working yet
		$category = 'default';
		
		switch ($this->what) {
			case 'create':
				// Generate ID
				$id = time() + rand(1, 999);
				
				if (isset($alarmID)) {
					$alarm = $alarmID;
				}
				else {
					$alarm = 'none';
				}
				
				// Save event
				$rows = array(
					'table' => 'events',
					$id,
					$db->escape_sql($_POST['summary']),
					'null',
					$time1,
					$time2,
					$_POST['status'],
					$repeat,
					$alarm,
					$db->escape_sql($_POST['notes']) . ' ',
					$category
				);
				$db->save_rows($rows, 'create');
				break;
			case 'update':
				$id = $_GET['event'];
				
				// Load current state
				$currentEvent = $db->fetch_rows_array("SELECT alarm FROM events WHERE id ='$id'", array('alarm'));
				
				$rows = array(
					'table' => 'events',
					'id' => $id,
					'summary' => $db->escape_sql($_POST['summary']),
					'flags' => 'null',
					'fromtime' => $time1,
					'totime' => $time2,
					'status' => $_POST['status'],
					'repeat' => $repeat,
					'alarm' => $currentEvent[0]['alarm'],
					'notes' => $db->escape_sql($_POST['notes']) . ' ',
					'category' => $category,
					'sqlWhere' => array('id', $id)
				);
				$db->save_rows($rows, 'update');
				break;
		}
		
		// Say that the event has been saved
		$this->message = Event_has_been_saved;
		return true;
	}
	
	// Check if required fields were filled in. This code must be flexible
	function check($other = 'null') {
		global $pref;
		// Check system
		$vars = array(
			'summary',
			'day',
			'year',
			'month',
			'repeat',
			'alarm',
			'status'
		);
		if ($pref->getvalue('ttype') == 'daylight') {
			$vars[] = 'tmeridiem';
			$vars[] = 'meridiem';
		}
		if (!isset($_POST['all-day']) || $_POST['all-day'] != "true") {
			$vars[] = 'hour';
			$vars[] = 'minute';
			$vars[] = 'thour';
			$vars[] = 'tminute';
		}
		if ($_POST['alarm'] == 'email') {
			$vars[] = 'recipient';
		}
		if ($_POST['repeat'] != 'none') {
			$vars[] = 'times';
		}
		
		// Do the checking
		foreach ($vars as $var) {
			if (!isset($_POST[$var]) || $_POST[$var] == '') {
				$this->message = "$var is required.";
				return false;
			}
		}
		
		// Check types
		if ($_POST['times'] != 'none' && !is_numeric($_POST['times'])) {
			$this->message = "The amount of times this event repeats must be a number.";
			return false;
		}
		
		// If false has not yet been returned, return true
		return true;
	}
	
	// Load a current event into array
	function loadEvent() {
		global $db;
		
		// Event ID
		$id = $_GET['event'];
		
		// Fetch event
		$eventRow = $db->fetch_rows_array("SELECT * FROM events WHERE id = '$id'", array('id', 'summary', 'flags', 'fromtime', 'totime', 'status', 'repeat', 'alarm', 'notes', 'category'));
		
		// Put everything together nicely
		$event = array();
		foreach ($eventRow[0] as $key => $eventCol) {
			$event[$key] = $eventCol;
		}
		
		if (date('H:i', $event['totime']) == '23:59' && date('H:i', $event['fromtime']) == '00:00') {
			$event['all-day'] = true;
		}
		else {
			$event['all-day'] = false;
		}
		
		$event['hour'] = date('H', $event['fromtime']);
		$event['minute'] = date('i', $event['fromtime']);
		$event['thour'] = date('H', $event['totime']);
		$event['tminute'] = date('i', $event['totime']);
		$event['xm'] = date('a', $event['fromtime']);
		$event['txm'] = date('a', $event['totime']);
		
		$event['recurrence'] = explode(':', $event['repeat']);
		$event['rtime1'] = $event['recurrence'][0];
		$event['rtime2'] = $event['recurrence'][1];
		$event['recurrence'] = explode('-', $event['recurrence'][2]);
		$event['recurrence'] = $event['recurrence'][0];
		
		
		// Calculate times
		switch ($event['recurrence']) {
			case "daily":
				$event['times'] = ($event['rtime2'] - $event['rtime1']) / 86400;
				break;
			case "monthly":
				
				break;
			default:
				$event['times'] = 0;
				break;
		}
		
		return $event;
	}
	
	// Delete event
	function rmevent() {
		global $db;
		$id = $_GET['event'];
		
		$rows = array(
			'sqlWhere' => array('id', $id)
		);
		$db->save_rows($rows, 'delete');
	}
}
?>