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

/* This class pertains to all things time & date
It's main purpose is to allow timezones and a reliable way to switch between 24hr/12hr daylight
savings time and GMT. You must make your timestamps using this class.
*/
class Time {
	public $timestamp = NULL; // Current timestamp
	public $availableZones = array(); // Available time zones array
	public $now = array(); // Now variables
	
	function __construct() {
		global $pref;
		
		// Set time zone according to preferences
		ini_set('date.timezone', $pref->timezone);
		
		$this->timestamp = time();
		$this->availableZones = array('GMT', 'America/New_York');
		
		$this->now['second'] = date('s');
		$this->now['minute'] = date('i');
		$this->now['hour'] = date('H');
		$this->now['day'] = date('d');
		$this->now['month'] = date('m');
		$this->now['year'] = date('Y');
	}
	
	// Make and return a GMT timestamp
	function make($second, $minute, $hour, $day, $month, $year, $gmt = false) {
		if (!$gmt) {
			return mktime($hour, $minute, $second, $month, $day, $year);
		}
		else {
			return gmmktime($hour, $minute, $second, $month, $day, $year);
		}
	}
	
	// return a formatted date
	// expects a GMT timestamp and return date in time zone
	function date($format, $timestamp = NULL) {
		if ($timestamp == NULL) {
			$timestamp = $this->timestamp;
		}
		
		// Replace strings with strings
		return date($format, $timestamp);
	}
}
?>