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

class StartDB {
	private $type = 'sqlite'; // Can be sqlite or mysqli
	private $layer = ''; // Abstraction layer object
	private $dbHandle = '';
	private $tables = array(); // Required tables for the db
	
	function __construct($type, $tables) {
		// Set the variables
		$this->type = $type;
		$this->tables = $tables;
		
		// Include the correct database layer file
		require_once('db/' . $this->type . '.php');
		
		// Create the layer object
		$this->layer = new DBLayer();
	}
	
	// Open database connection
	function open($name) {
		$this->dbHandle = $this->layer->connect($name);
		
		// Make sure all the tables exist
		$this->layer->mktable($this->tables);
	}
	
	// Execute some SQL
	function run($sql) {
		return $this->layer->sql($sql);
	}
	
	// Check if row exists
	function row_exists($table, $column, $row) {
		return $this->layer->row_exists($table, $column, $row);
	}
	
	// Select rows and return as array
	function fetch_rows_array($sql, $cols) {
		return $this->layer->fetch_rows_array($sql, $cols);
	}
	
	// Escape string for use in SQL statement
	function escape_sql($str) {
		return $this->layer->escape_sql($str);
	}
	
	// Create/update rows in table
	function save_rows($rows, $action) {
		return $this->layer->save_rows($rows, $action);
	}
}
?>