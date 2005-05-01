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
	public $users = array(); // Available users
	private $user = NULL;
	
	function __construct() {
		global $db;
		global $pref;
		
		// Start session
		session_start();
		
		// If groups manager form has been submitted change stuff
		if (isset($_POST['edit_stuff']) && $_POST['edit_stuff'] == 'true') {
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
			}
			// Or should we delete the group?
			elseif (isset($_POST['delgroup']) && $_POST['delgroup'] == 'yes') {
				$this->rmGroup($_POST['group']);
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
		
		// If groups manager form has been submitted change stuff
		if (isset($_POST['edit_stuff']) && $_POST['edit_stuff'] == 'true') {
			// Maybe we should add a user
			if (isset($_POST['newusername'])) {
				// Make sure a valid username and password were provided
				$grep = "|^[a-zA-Z0-9\_\.\-]+$|";
				if (!preg_match($grep, $_POST['newusername'])) {
					define('ERROR_MSG', 'Not a valid username. Username can only contain letters, number, dashes, and underscores.');
				}
				elseif (!preg_match($grep, $_POST['newpassword'])) {
					define('ERROR_MSG', 'Not a valid password. Passwords can only contain letters, number, dashes, and underscores.');
				}
				else {
					// Make sure passwords match
					if ($_POST['newpassword'] != $_POST['passconfirm']) {
						define('ERROR_MSG', 'Password and password confirmation do not match.');
					}
					// Also make sure user is member of at least one group
					elseif (!isset($_POST['membergroups'])) {
						define('ERROR_MSG', 'Error: User is not a member of any group(s).');
					}
					else {
						$groups = implode(',', $_POST['membergroups']);
						$this->newUser($_POST['newusername'], $groups, $_POST['newpassword']);
					}
				}
			}
			// Or maybe we should delete a user
			elseif (isset($_POST['deluser'])) {
				$this->rmUser($_POST['editusername']);
			}
			// Or maybe we should edit a user
			elseif (isset($_POST['editusername'])) {
				// Make sure a valid username and password were provided
				$grep = "|^[a-zA-Z0-9\_\.\-]+$|";
				if (!preg_match($grep, $_POST['editusername'])) {
					define('ERROR_MSG', 'Not a valid username. Username can only contain letters, number, dashes, and underscores.');
				}
				elseif (!preg_match($grep, $_POST['editpassword'])) {
					define('ERROR_MSG', 'Not a valid password. Passwords can only contain letters, number, dashes, and underscores.');
				}
				else {
					// Make sure passwords match
					if ($_POST['editpassword'] != $_POST['passconfirm']) {
						define('ERROR_MSG', 'Password and password confirmation do not match.');
					}
					// Also make sure user is member of at least one group
					elseif (!isset($_POST['membergroups'])) {
						define('ERROR_MSG', 'Error: User is not a member of any group(s).');
					}
					else {
						$groups = implode(',', $_POST['membergroups']);
						$this->editUser($_POST['editusername'], $groups, $_POST['editpassword']);
					}
				}
			}
		}
		
		// Define groups array
		$groups = $db->fetch_rows_array("SELECT * FROM groups", array('name', 'rights'));
		// "Fix" array
		foreach ($groups as $group) {
			$this->groups[$group['name']] = $group['rights'];
		}
		
		// Define users array
		$users = $db->fetch_rows_array("SELECT * FROM users", array('user', 'password', 'groups'));
		// "Fix" array
		foreach ($users as $user) {
			$this->users[$user['user']] = array('password' => $user['password'], 'groups' => explode(',', $user['groups']));
		}
		
		// If there are no groups than create a default group and user
		if (count($groups) == 0) {
			// Make a default group
			$rights = $db->escape_sql('admin,canedit');
			$this->newGroup('admin', $rights);
			
			// Make a default admin user
			$this->newUser('admin', 'admin', 'admin');
		}
		
		if (isset($_POST['login'])) {
			$this->login();
		}
		
		// Check whether or not the user is logged
		if (isset($_SESSION['status']) && $_SESSION['status'] == 'in') {
			$this->status = 'in';
		}
	}
	
	// Try to login
	function login() {
		// Validate input
		if (isset($this->users[$_POST['username']])) {
			if ($this->users[$_POST['username']]['password'] == $_POST['password']) {
				// Login
				$_SESSION['status'] = 'in';
				$_SESSION['username'] = $_POST['username'];
				return true;
			}
		}
		return false;
	}
	
	/* Determine if client has rights
	 * $requirement = [read],[editor],[admin]
	*/
	function client_check($requirement = 'read') {
		global $pref;
		// Take care of all cases when nobody is logged in
		if ($this->status == 'out') {
			switch ($pref->prefs['protectionLevel']) {
				case 'none':
					return false;
					break;
				case 'readonly':
					if ($requirement == 'editor' || $requirement == 'admin') {
						return false;
					}
					break;
				case 'readwrite':
					if ($requirement == 'admin') {
						return false;
					}
					break;
			}
			return true;
		}
		// Take care of cases when somebody is logged in
		else {
			// First checkout who we are dealing with
			$username = $_SESSION['username'];
			switch ($requirement) {
				case 'read':
					return true;
					break;
				case 'editor':
					if (in_array('canedit', $this->users[$username]['groups'])) {
						return true;
					}
					break;
				case 'admin':
					if (in_array('admin', $this->users[$username]['groups'])) {
						return true;
					}
					break;
			}
			return false;
		}
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
	
	// Delete a user
	function rmUser($user) {
		global $db;
		$rows = array(
			'table' => 'users',
			'sqlWhere' => array('user', $user)
		);
		$db->save_rows($rows, 'delete');
	}
	
	// Edit user
	function editUser($user, $groups, $password) {
		global $db;
		$rows = array(
			'table' => 'users',
			'sqlWhere' => array('user', $user),
			'user' => $user,
			'password' => $password,
			'groups' => $groups
		);
		$db->save_rows($rows, 'update');
	}
}
?>