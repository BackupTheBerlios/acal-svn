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

// Template class
class Tpl {
	private $view = 'month'; // View, month is default later we can add a default view preference
	
	// Constructer
	function __construct() {
		// Figure out the view
		if (isset($_GET['view'])) {
			$this->view = $_GET['view'];
		}
	}
	
	// Print the template
	function output() {
		// Include template files
		require_once('template/common.php');
		require_once('template/' . $this->view . '.php');
		
		// Generate the HTML
		// Start with DOCTYPE
		echo $html['doctype'];
		
		// Anything that goes inside the head
		echo $html['commonhead'];
		if (isset($html['head'])) {
			echo $html['head'];
		}
		
		// End the head and start the body
		echo $html['bodystart'];
		
		// The main content
		if (isset($html['c'])) {
			foreach ($html['c'] as $content) {
				echo $content;
			}
		}
		
		// End the body and HTML
		echo $html['bodyend'];
	}
}
?>