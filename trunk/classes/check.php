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

// Check to make sure the basic requirements are met

class checkReq {
	function check() {
		global $cfg;
		
		// Make sure PHP is new enough.
		if (!version_compare(phpversion(), "5.0.0", ">=")) {
			echo "<p><b>Error:</b> Your version of PHP is too old! PHP 5.0.0 or later is required and you are using PHP " . phpversion() . ".</p>";
			exit;
		}
		
		// Include the correct database check file
		include_once('db/' . $cfg->db . '-checkfile.php');
		
		// Do some stuff to make it work on Windows IIS
		if (!isset($_SERVER['REQUEST_URI'])) {
			foreach ($_GET as $gkey => $get) {
				$_SERVER['REQUEST_URI'][$gkey] = $get;
			}
		}
		define('REQUEST_URI', $_SERVER['REQUEST_URI']);
	}
}
?>