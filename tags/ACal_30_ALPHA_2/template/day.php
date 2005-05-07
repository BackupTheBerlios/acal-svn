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

global $time;
echo '<p>In one hour it will be ' . $time->offset / 3600 . ' | ' . $time->now['hour'] . '<br />';
// Make a timestamp
$stamp = $time->make($time->now['second'], $time->now['minute'], $time->now['hour'], $time->now['day'], $time->now['month'], $time->now['year']);

echo "<br />GMT timestamp is $stamp which = " . $time->date('c', $stamp) . ' in this time zone.<br />';
if ($time->timeZone->inDaylightTime($time->time)) {
	echo 'We are in DST';
}
else {
	echo 'We are not in DST';
}
echo '<p>';

/*
echo '<pre>';
print_r($time->availableZones);
echo '</pre>';*/
?>