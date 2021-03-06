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

// Preference system class
class Prefs {
	public $prefs = array(); // Holds all the preferences
	private $availablePrefs;
	
	function __construct() {
		// Make db global
		global $db;
		
		// Try to detect OS in order to enable/disable alarms. Cannot risk false positives.
		if (PHP_OS == "Darwin" || PHP_OS == "Linux" || substr(PHP_OS, 0, 3) == 'WIN') {
			$defval = 'true';
		}
		else {
			$defval = 'false';
		}
		
		// Define available prefs
		$prefs = array(
			array('ttype', '24hr'),
			array('enable_alarms', $defval),
			array('showLangMenu', 'false'),
			array('locale', 'English'),
			array('timezone', 'UTC'),
			array('protectionLevel', 'readonly')
		);
		
		$this->availablePrefs = $prefs;
		
		// Preload Preferences
		$this->preload($prefs);
		
		// Load all the preferences into $this->prefs so the database doesn't have to be queried each time I want one
		foreach ($prefs as $pref) {
			$name = $pref[0];
			$row = $db->fetch_rows_array("SELECT value FROM prefs WHERE name = '$name'", array('value'));
			$this->prefs[$name] = $row[0]['value'];
		}
		
		// If a preference has been updated, update db
		if (isset($_POST['pref_action'])) {
			$myprefs = explode(':', $_POST['pref_action']);
			foreach ($myprefs as $mypref) {
				if (!isset($_POST[$mypref])) {
					$prefSetting = 'false';
				}
				else {
					$prefSetting = $_POST[$mypref];
				}
				$this->update($mypref, $prefSetting);
			}
			// Reload preferences
			$this->reload();
		}
	}
	
	// Reload preferences
	function reload() {
		global $db;
		// Load all the preferences into $this->prefs so the database doesn't have to be queried each time I want one
		foreach ($this->availablePrefs as $pref) {
			$name = $pref[0];
			$row = $db->fetch_rows_array("SELECT value FROM prefs WHERE name = '$name'", array('value'));
			$this->prefs[$name] = $row[0]['value'];
		}
	}
	
	// Preload preferences
	function preload($prefs) {
		// Make db global
		global $db;
		// loop through them and if not in database add them
		foreach ($prefs as $pref) {
			if (!$db->row_exists('prefs', 'name', $pref[0])) {
				$rows = array(
							  'table' => 'prefs',
							  $pref[0],
							  $pref[1]
							  );
				$db->save_rows($rows);
			}
		}
	}
	
	// Get preference value
	function getvalue($name) {
		return $this->prefs[$name];
	}
	
	// Overloading version of getvalue()
	function __get($name) {
		return $this->prefs[$name];
	}
	
	// Update preference value
	function update($name, $value) {
		global $db;
		$rows = array(
					  'table' => 'prefs',
					  'sqlWhere' => array('name', $name),
					  'value' => $value
					  );
		$db->save_rows($rows, 'update');
	}
}
?>