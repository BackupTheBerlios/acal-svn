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
	public $time = NULL; // Time object
	public $availableZones = array(); // Available time zones array
	public $now = array(); // Now variables
	public $offset = NULL; // Time zone offset in seconds
	public $timeZone = NULL; // DateTimeZone object
	
	function __construct() {
		require_once('Date.php');
		global $pref;
		$this->time = new Date();
		
		// Convert this original time to UTC so we can get an accurate conversion
		$localtimeOffset = idate('Z');
		// Subtract or add the perfect amount of hours
		$s = $localtimeOffset / 3600;
		$hour = $this->time->hour - $s;
		$this->time->setHour($hour);
		
		$this->time->convertTZbyID($pref->prefs['timezone']);
		$this->timeZone = $this->time->tz;
		$this->offset = $this->timeZone->offset / 1000;
		
		// Set available time zones
		$this->availableZones = $this->timeZone->getAvailableIDs();
		
		// Set now
		$this->now['second'] = $this->time->getSecond();
		$this->now['minute'] = $this->time->getMinute();
		$this->now['hour'] = $this->time->getHour();
		$this->now['day'] = $this->time->getDay();
		$this->now['month'] = $this->time->getMonth();
		$this->now['year'] = $this->time->getYear();
		
		// Set current GMT timestamp
		$this->timestamp = $this->make($this->now['second'], $this->now['minute'], $this->now['hour'], $this->now['day'], $this->now['month'], $this->now['year']);
	}
	
	// Make and return a GMT timestamp
	function make($second, $minute, $hour, $day, $month, $year, $gmt = false) {
		if (!$gmt) {
			// Make the timestamp
			$stamp = gmmktime($hour, $minute, $second, $month, $day, $year);
			
			// Apply offset
			return $stamp - $this->offset;
		}
		else {
			// Make the timestamp
			return gmmktime($this->time->hour, $this->time->minute, $this->time->second, $this->time->month, $this->time->day, $this->time->year);
		}
	}
	
	// return a formatted date
	// expects a GMT timestamp and return date in time zone
	function date($format, $timestamp = NULL) {
		if ($timestamp == NULL) {
			$timestamp = $this->timestamp;
		}
		
		// Apply offset
		$timestamp = $timestamp + $this->offset;
		
		// Replace strings with strings
		return gmdate($format, $timestamp);
	}
}
?>