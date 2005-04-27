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

// Check for SimpleXML support
if (!function_exists('simplexml_load_file')) {
	exit("<p><b>Error:</b> PHP does not have SimpleXML support. You probably are not using PHP 5.0.0 or later. Or PHP was compiled with --disable-simplexml");
}

// Let's see what we can all do with XML
$cfg = simplexml_load_file('configuration.xml');

// Do a requirements check!
include('classes/check.php');
$check = new checkReq();
$check->check(); // So it will still work with PHP 4

// Include needed files
require_once('classes/locale.php');
require_once('classes/template.php');
require_once('classes/db.php');
require_once('classes/preferences.php');
require_once('classes/time.php');
require_once('classes/users.php');
require_once('functions/all.php');

// Start database abstraction layer
$tables = array(
	array('events', 'id VARCHAR(2048), summary VARCHAR(7168), flags VARCHAR(7168), fromtime VARCHAR(2048), totime VARCHAR(2048), status VARCHAR(1024), repeat VARCHAR(5120), alarm VARCHAR(1024), notes VARCHAR(7168), category VARCHAR(2048)'),
	array('prefs', 'name VARCHAR(2048), value VARCHAR(800000)'),
	array('alarms', 'id VARCHAR(128), msg VARCHAR(700000), method VARCHAR(2048), status VARCHAR(1024)'),
	array('notes', 'id VARCHAR(256), note VARCHAR(555000), flags VARCHAR(5120)'),
	array('users', 'user VARCHAR(128), password VARCHAR(128), groups VARCHAR(128)'),
	array('groups', 'name VARCHAR(128), rights VARCHAR(128)')
);
$db = new StartDB($cfg->db, $tables);

// Open db connection
$db->open('acal3');

// Create preferences object
$pref = new Prefs();

// Start localization
$locale = new Locale();

// Create time object
$time = new Time();

// Create users object
$users = new Users();

// Start template engine
$tpl = new Tpl();

// Print template to output
$tpl->output();
?>