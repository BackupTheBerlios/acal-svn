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
	private $locale = "English.xml";
	private $xml = NULL;

	function __construct() {
		// Load localization
		$this->xml = simplexml_load_file('languages/' . $this->locale);
		
		// Create constants from localization file
		foreach ($this->xml->str as $string) {
			define((string) $string['name'], (string) $string);
		}
	}
}
?>