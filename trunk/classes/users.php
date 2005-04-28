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
	public $groups = array(); // Available Groups
	
	function __construct() {
		global $db;
		
		// First check whether or not the user is logged
		session_start();
		if (isset($_SESSION['status']) && $_SESSION['status'] == 'in') {
			$this->status = 'in';
		}
		
		// If preferences form has been submitted change stuff
		if (isset($_POST['edit_groups']) && $_POST['edit_groups'] == 'true') {
			// Should we create a new group?
			if ($_POST['newgroup'] != '') {
				// Put together rights
				$rights = array();
				if (isset($_POST['admin'])) {
					$rights[] = 'admin';
				}
				if (isset($_POST['canedit'])) {
					$rights[] = 'canedit';
				}
				// Format rights correctly
				$rights = implode(',', $rights);
				$this->newGroup($_POST['newgroup'], $rights);
				define('PREFS_MSG', 'Group has been added.');
			}
			// Or should we delete the group?
			elseif (isset($_POST['delgroup']) && $_POST['delgroup'] == 'yes') {
				$this->rmGroup($_POST['group']);
				define('PREFS_MSG', 'Group has been removed.');
			}
			// Or maybe we want to edit this group?
			elseif (isset($_POST['group'])) {
				// Put together rights
				$rights = array();
				if (isset($_POST['admin'])) {
					$rights[] = 'admin';
				}
				if (isset($_POST['canedit'])) {
					$rights[] = 'canedit';
				}
				$rights = implode(',', $rights);
				$this->saveGroup($_POST['group'], $rights);
			}
		}
		
		
		// If there are no groups than create a default group and user
		$groups = $db->fetch_rows_array("SELECT * FROM groups", array('name', 'rights'));
		// "Fix" array
		foreach ($groups as $group) {
			$this->groups[$group['name']] = $group['rights'];
		}
		
		if (count($groups) == 0) {
			// Make a default group
			$rights = $db->escape_sql('admin,canedit');
			$this->newGroup('admin', $rights);
			
			// Make a default admin user
			$this->newUser('admin', 'admin', 'admin');
		}
	}
	
	// Try to login
	function login() {
		
	}
	
	// Delete a group
	function rmGroup($name) {
		global $db;
		$rows = array(
			'table' => 'groups',
			'sqlWhere' => array('name', $name)
		);
		$db->save_rows($rows, 'delete');
	}
	
	// Create group
	function newGroup($name, $rights) {
		global $db;
		$rows = array(
			'table' => 'groups',
			$name,
			$rights
		);
		$db->save_rows($rows, 'create');
	}
	
	// Save group (update)
	function saveGroup($name, $rights) {
		global $db;
		$rows = array(
			'table' => 'groups',
			'sqlWhere' => array('name', $name),
			'name' => $name,
			'rights' => $rights
		);
		$db->save_rows($rows, 'update');
	}
	
	// Create user
	function newUser($name, $groups, $password) {
		global $db;
		$rows = array(
			'table' => 'users',
			$name,
			$password,
			$groups
		);
		$db->save_rows($rows, 'create');
	}
}
?>