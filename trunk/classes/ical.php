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

class iCal {
    private $raw = NULL; // The raw iCalendar file
    private $debug = false; // Debug mode toggle
    public $arr = array(); // Holds the parsed data
    
    // Contructor function
    function __construct($file = false, $debug = false) {
        // Load file if appropriate
        if ($file) {
            $this->load($file);
        }
        $this->debug = $debug;
    }
    
    // Load file
    function load($file) {
        $this->raw = file_get_contents($file);
    }
    
    // Parse the file
    function parse() {
        // Make sure there is a file
        if ($this->raw == NULL) {
            exit("Error: Cannot parse NULL value.");
            return false;
        }
        
        // Explode the stuff by line
        $this->raw = str_replace("\r\n", "\n", $this->raw);
        $this->raw = str_replace("\r", "\n", $this->raw);
        $lines = explode("\n", $this->raw);
        
        $arr = array();
        define('GREP', "|(.*)(:)(.*)|");
        $path = '';
        $loop = 0;
        
        foreach ($lines as $line) {
            // If row is empty continue
            if ($line == '') {
                continue;
            }
            preg_match(GREP, $line, $m);
            
            // Check what kind of tag this is and build path
            switch ($m[1]) {
                case 'BEGIN':
                    if ($path != '') {
                        $p = explode('/', $path);
                    }
                    else {
                        $p = array();
                    }
                    $p[] = $m[3];
                    $path = implode('/', $p);
                    $loop++;
                    break;
                case 'END':
                    $p = explode('/', $path);
                    unset($p[count($p) -1]);
                    $path = implode('/', $p);
                    break;
                default:
                    // Build information
                    $p = explode('/', $path);
                    if (count(array_keys($lines, 'BEGIN:' . $p[count($p) - 1])) > 1) {
                        $arr[$path][$loop][$m[1]] = $m[3];
                    }
                    else {
                        $arr[$path][$m[1]] = $m[3];
                    }
            }
        }
        
        if ($this->debug) {
            echo '<pre>';
            print_r($arr);
            echo '</pre>';
        }
        
        $this->arr = $arr;
    }
    
    /* Get data
       In array form
       string $path = data path
       Example: $events = $obj->get('VCALENDAR/VEVENT');
       foreach ($events as $event) {
         echo '<p>' . $event['SUMMARY'] . '</p>';
       }
    */
    function get($path) {
        if (!isset($this->arr[$path])) {
            return false;
        }
        else {
            return ($this->arr[$path]);
        }
    }
}
?>