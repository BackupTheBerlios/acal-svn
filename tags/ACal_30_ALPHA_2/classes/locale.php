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

// Localization class
class Locale {
	public $locale = 'English';
	private $xml;
	public $langs = array();

	function __construct() {
		if (isset($_POST['language'])) {
			// Set a session variable with the users selection
			$_SESSION['USER_LANGUAGE'] = $_POST['language'];
		}
		
		// Get available locales
		$langs = scandir('languages');
		foreach ($langs as $lang) {
			if (preg_match("|\.xml|", $lang)) {
				$this->langs[] = str_replace('.xml', '', $lang);
			}
		}
		
		// What locale to load?
		global $pref;
		if (!isset($_SESSION['USER_LANGUAGE'])) {
			$this->locale = $pref->getvalue('locale');
		}
		else {
			$this->locale = $_SESSION['USER_LANGUAGE'];
		}
		
		// Load localization
		$this->xml = simplexml_load_file('languages/' . $this->locale . '.xml');
		
		// setlocale() is ready to run but we can't rely on it
		// currently it only works for integer dates
		setlocale(LC_ALL, $this->xml->lang);
		
		// Create constants from localization file
		foreach ($this->xml->str as $string) {
			// Convert any entities
			if (preg_match("|(AND)(.*?)(SC)|", (string) $string)) {
				$lStr = str_replace("AND", '&', (string) $string);
				$lStr = str_replace("SC", ';', $lStr);
			}
			else {
				$lStr = (string) $string;
			}
			// Define string
			define((string) $string['name'], $lStr);
		}
	}
}
?>