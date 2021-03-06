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

// When a form is submitted change categories as needed
function acal_change_categories() {
	global $db;
	if (isset($_POST['change_cats']) && $_POST['change_cats'] == 'true') {
		// Add category is needed
		if ($_POST['newcatname'] != '') {
			if (!isset($_POST['newcatcolor'])) {
				$_POST['newcatcolor'] = 'white';
			}
			
			$rows = array(
				'table' => 'categories',
				$_POST['newcatname'],
				$_POST['newcatcolor']
			);
			$db->save_rows($rows, 'create');
		}
		
		// Delete category if needed
		if (isset($_POST['realdel'])) {
			$rows = array(
				'table' => 'categories',
				'sqlWhere' => array('category', $_POST['delcat'])
			);
			$db->save_rows($rows, 'delete');
		}
	}
}

// Will attempt to retrieve category info. If not available will return default values.
function acal_get_category($name) {
	global $db;
	$data = $db->fetch_rows_array("SELECT * FROM categories WHERE category = '$name'", array('category', 'color'));
	return $data[0];
}

// Return a list of categories
function acal_get_categories() {
	global $db;
	$data = $db->fetch_rows_array("SELECT * FROM categories", array('category', 'color'));
	return $data;
}
?>