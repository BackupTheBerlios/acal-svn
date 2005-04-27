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

// Database functions and rules for MySQL
class DBLayer {
	private $name = '';
	private $link = '';

	// Connect to database
	function connect($name) {
		$this->name = $name;
		global $cfg;
		
		$link = new mysqli($cfg->mysqli_host, $cfg->mysqli_username, $cfg->mysqli_password);

		// Make sure database exists
		$res = $link->query("SHOW DATABASES");
		while ($row = $res->fetch_row()) {
			if ($row[0] == $name) {
				$do = true;
			}
		}
		if (!isset($do)) {
			$do = false;
		}
		
		if (!$do) {
			$link->query("CREATE DATABASE $name");
		}

		$link->select_db($name);
		
		// check connection
		if (mysqli_connect_errno()) {
			printf("Connect failed: %s\n", mysqli_connect_error());
			exit();
		}
		
		$this->link = $link;
		return $link;
	}
	
	// Execute SQL
	function sql($sql) {
		$link = $this->link->query($sql);
		if (!$link) {
			printf("MySQLi Error: %s\n", $this->link->error);
		}
		return $link;
	}
	
	// Create table(s)
	function mktable($tables) {
		foreach ($tables as $table) {
			// If table doesn't exist, create it
			if (!$this->table_exists($table[0])) {
				// Create table
				$sql = "CREATE TABLE $table[0] ($table[1])";
				$this->sql($sql);
			}
		}
	}
	
	// Check if row exists
	function row_exists($table, $column, $row) {
		if ($result = $this->link->query("SELECT $column FROM $table WHERE $column = '$row'")) {
			if ($result->num_rows > 0) {
				return true;
			}
		}
		return false;
	}
	
	// Checks if a MySQL 4.1 table exists
	function table_exists($table) {
		$result = $this->link->query("SHOW TABLES");

		while ($row = $result->fetch_row()) {
			if ($row[0] == $table) {
				return true;
			}
		}
		return false;
	}
	
	// Select rows and return as array
	function fetch_rows_array($sql, $cols) {
		$res = $this->sql($sql);
		if ($res) {
			$rows = array();
			$r = 0;
			while ($row = $res->fetch_assoc()) {
				foreach ($cols as $col) {
					$rows[$r][$col] = $row[$col];
				}
				$r++;
			}
			return $rows;
		}
		return false;
	}
	
	// Escape string for use in SQL query
	function escape_sql($sql) {
		return mysqli_real_escape_string($this->link, $sql);
	}
	
	// Save rows in table
	function save_rows($rows, $action = 'create') {
		if ($action == 'create') {
			$sql = "INSERT INTO ";
			$sql .= $rows['table'];
			$sql .= ' VALUES (';
			unset($rows['table']);
			$c = count($rows) - 1;
			foreach ($rows as $key => $value) {
				if ($key == $c) {
					$sql .= "'$value'";
				}
				else {
					$sql .= "'$value',";
				}
			}
			$sql .= ')';
			$this->sql($sql);
		}
		elseif ($action == 'update') {
			$sql = 'UPDATE ' . $rows['table'] . ' SET';
			unset($rows['table']);
			$sqlWhere = $rows['sqlWhere'];
			unset($rows['sqlWhere']);
			$i = count($rows);
			$n = 1;
			foreach ($rows as $key => $value) {
				$sql .= ' ' . $key . " = '$value'";
				if ($i != $n) {
					$sql .= ',';
				}
				$n++;
			}
			$sql .= ' WHERE ' . $sqlWhere[0] . " = '" . $sqlWhere[1] . "'";
			$this->sql($sql);
		}
		elseif ($action == 'delete') {
			$sql = 'DELETE FROM ' . $rows['table'] . ' WHERE ' . $rows['sqlWhere'][0] . ' = \'' . $rows['sqlWhere'][1] . "'";
			$this->sql($sql);
		}
	}
}
?>