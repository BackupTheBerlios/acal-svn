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

/*
 * Users and groups class
 * The purpose of this class is to provide username and password protection
 * to the calendar using a users and groups method.
*/
class Users {
	private $status = 'out'; // Whether or not we are logged in
	
	function __construct() {
		// First check whether or not the user is logged
		session_start();
		if (isset($_SESSION['status']) && $_SESSION['status'] == 'in') {
			$this->status = 'in';
		}
		
		// If login form has been submitted attempt to login
		
	}
	
	// Try to login
	function login() {
		
	}
}
?>