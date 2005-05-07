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


// This code here will take care of sidebar form events
require_once('classes/events.php');
$events = new Events();

// C{}
$cset = cset();

global $time;
global $users;

// The title and other head information
$html['head'] = '<title>' . Calendar . ': ' . constant($time->date('F', $time->timestamp)) . ' ' . $time->date('Y', $time->timestamp) . '</title>';

// Start output buffer
ob_start();

global $pref;

// Make sure client has read access
if (!$users->client_check('read')) {
	echo '<div class="shadow" style="width: 400px; margin-right: auto; margin-left: auto; padding: 15px;"><p class="message">Sorry but you do not have access to this calendar. Please login below.</p>';
	echo '<form method="post" action="' . $_SERVER['REQUEST_URI'] . '">
	<p>Username: <input type="text" name="username" size="20" /></p>
	<p>Password: <input type="password" name="password" size="20" /></p>
	<p><input type="submit" value="Login" /></p>
	<input type="hidden" name="login" value="true" />
	</form></div>';
	$html['c'][0] = ob_get_contents();
	ob_end_clean();
}
else {

// Display any activated alarms
if ($alarms = active_alarms()) {
	foreach ($alarms as $alarm) {
		echo '<div class="alarm"><span>Alarm!</span> ' . $alarm['msg'] . '</div>';
	}
}

// Info and option bar
echo '<div class="topbar">';
// Toggle view buttons
echo '<div class="vtoggle">';

// Maybe display logout link here
if ($users->status == 'in') {
	echo '<a href="' . pend_requests(array('logout' => 'true')) . '">' . Logout . '</a>';
}
else {
	echo '<a href="'. pend_requests(array('layer' => 'preferences')) . '">' . Login . '</a>';
}

echo '<a href="#"><img src="images/day_view_btn.png" alt="Day View" /></a>';
echo '<a href="#"><img src="images/week_view_btn.png" alt="Week View" /></a>';
echo '<a href="' . $_SERVER['REQUEST_URI'] . '"><img src="images/month_view_btn.png" alt="Month View" /></a>';
echo '</div>';

// Print the data&time
echo '<span id="bartime">' . It_is_now . ' ' . $time->date('Y-m-d', $time->timestamp) . ' [' . $time->date('H:ia', $time->timestamp) . ']</span>';

// Language menu
if ($pref->prefs['showLangMenu'] == 'true') {
	echo '<form method="post" id="langSwitch" name="langSwitch" action="' . str_replace('&', '&amp;', $_SERVER['REQUEST_URI']) . '">';
	echo Language . ' <select name="language" onchange="document.langSwitch.submit()">';
	global $locale;
	foreach ($locale->langs as $lang) {
		echo '<option value="' . $lang . '"';
		if ($locale->locale == $lang) {
			echo ' selected="selected"';
		}
		echo '>' . $lang . '</option>';
	}
	echo '</select>';
	echo '</form>';
}

// End info and option bar
echo '</div>';

// Event management sidebar panel
if (isset($_GET['sidebar']) && $_GET['sidebar'] != 'false') {
	$sidebar = true;
}
else {
	$sidebar = false;
}

// If $sidebar is true, show it the correct one
if ($sidebar && isset($_GET['rmevent']) && $_GET['rmevent'] == 'true') {
	if ($users->client_check('editor')) {
	$sidebar = false;
	
	// Ask if this event should really be removed
	echo '<div class="side_panel">';
	$sidebarLink = edit_requests('sidebar', 'false');
	$sidebarLink = str_replace('&', '&amp;', $sidebarLink);
	$sidebarLink = str_replace("rmevent=true", "rmevent=false", $sidebarLink);
	echo '<span class="iconsl"><a href="' . $sidebarLink . '" title="Close"><img src="images/close.png" alt="-"></a></span>
	<div class="in">
	<p>Are you sure that you want to delete this event?</p>';
	echo '<form method="post" action="' . $sidebarLink . '" name="sidebar" id="sidebar">
	<p><input type="submit" value="Yes" /></p>
	<input type="hidden" name="rmevent" value="true" />
	<input type="hidden" name="form_event" id="form_event" value="true">
	</form>
	</div>
	</div>
	';
	}// End protection layer
}
if ($sidebar) {
	echo '<div class="side_panel">';
	echo '<span class="iconsl"><a href="' . str_replace('&', '&amp;', edit_requests('sidebar', 'false')) . '" title="Close"><img src="images/close.png" alt="-"></a></span>';
	
	if (!$users->client_check('editor')) {
		echo '<p class="message">Sorry but you cannot manage events. Please login below.</p>';
		echo '<form method="post" action="' . $_SERVER['REQUEST_URI'] . '">
		<p>Username: <input type="text" name="username" size="20" /></p>
		<p>Password: <input type="password" name="password" size="20" /></p>
		<p><input type="submit" value="Login" /></p>
		<input type="hidden" name="login" value="true" />
		</form>
		</div>';
	}
	else {
	
	// Shall we load an event instead of create a new one?
	if (isset($_GET['event'])) {
		$event = $events->loadEvent();
	}
	else {
		$event = false;
	}
	
	// If there is a message available display it
	echo '<span style="font-weight: bold; color: red;">' . $events->message . '</span><br>';
	
	// Start form
	echo '<form method="post" action="' . str_replace('&', '&amp;', $_SERVER['REQUEST_URI']) . '" name="sidebar" id="sidebar">';
	
	echo '<label for="summary">Summary: </label>
		<span><input type="text" size="17" name="summary" id="summary"';
		if ($event) {
			echo ' value="' . $event['summary'] . '"';
		}
		echo '></span>';
	
	echo '<label for="all-day">All Day: </label>
		<span><input type="checkbox" name="all-day" id="all-day" value="true" onclick="alldaytoggle()"';
		if ($event['all-day']) {
			echo ' checked="checked"';
		}
		echo '></span>';
	
	// Time
	echo '<label for="time">Time: </label>
		<span id="time">';
		if ($pref->getvalue('ttype') == '24hr') {
			echo '<select name="hour" id="hour">';
			if ($pref->getvalue('ttype') == '24hr') {
				$hrs = 23;
			}
			else {
				$hrs = 12;
			}
			$i = 0;
			while ($i <= $hrs) {
				$i = sprintf('%02d', $i);
				echo "<option value=\"$i\"";
				if ($event) {
					$chour = $event['hour'];
				}
				else {
					$chour = $cset['hour'];
				}
				if ($i == $chour) {
					echo ' selected="selected"';
				}
				echo ">$i</option>";
				$i++;
			}
			echo '</select>';
			echo '<select name="minute" id="minute">';
			$i = 0;
			while ($i < 60) {
				$i = sprintf('%02d', $i);
				echo "<option value=\"$i\"";
				if ($event) {
					if ($event['minute'] == $i) {
						echo ' selected="selected"';
					}
				}
				echo ">$i</option>";
				$i++;
			}
			echo '</select>';
		
			echo '<br>'; // *****************************  START END TIME ROW !!!!!!!!!!
			
			echo '<select name="thour" id="thour">';
			if ($pref->getvalue('ttype') == '24hr') {
				$hrs = 23;
			}
			else {
				$hrs = 12;
			}
			$i = 0;
			while ($i <= $hrs) {
				$i = sprintf('%02d', $i);
				echo "<option value=\"$i\"";
				if ($event) {
					$cnexthr = $event['thour'];
				}
				else {
					$cnexthr = $cset['nexthr'];
				}
				if ($i == $cnexthr) {
					echo ' selected="selected"';
				}
				echo ">$i</option>";
				$i++;
			}
			echo '</select>';
			echo '<select name="tminute" id="tminute">';
			$i = 0;
			while ($i < 60) {
				$i = sprintf('%02d', $i);
				echo "<option value=\"$i\"";
				if ($event) {
					if ($event['tminute'] == $i) {
						echo ' selected="selected"';
					}
				}
				echo ">$i</option>";
				$i++;
			}
			echo '</select>';
		}
		else {
			// -------------------- 12 HOUR TIME !!!!!!!!
			echo '<select name="hour" id="hour">';
			if ($pref->getvalue('ttype') == '24hr') {
				$hrs = 23;
			}
			else {
				$hrs = 12;
			}
			$i = 1;
			while ($i <= $hrs) {
				$i = sprintf('%02d', $i);
				echo "<option value=\"$i\"";
				if ($event) {
					$chour = $event['hour'];
				}
				else {
					$chour = $cset['hour'];
				}
				if ($i == $chour) {
					echo ' selected="selected"';
				}
				echo ">$i</option>";
				$i++;
			}
			echo '</select>';
			echo '<select name="minute" id="minute">';
			$i = 0;
			while ($i < 60) {
				$i = sprintf('%02d', $i);
				echo "<option value=\"$i\"";
				if ($event) {
					if ($event['minute'] == $i) {
						echo ' selected="selected"';
					}
				}
				echo ">$i</option>";
				$i++;
			}
			echo '</select>';
			echo '<select name="meridiem" id="meridiem">';
			if ($event) {
				$xm = $event['xm'];
			}
			else {
				$xm = $cset['xm'];
			}
			echo '<option value="am"';
			if ($xm == 'am') {
				echo ' selected="selected"';
			}
			echo '>AM</option>';
			echo '<option value="pm"';
			if ($xm == 'pm') {
				echo ' selected="selected"';
			}
			echo '>PM</option>';
			echo '</select>';
		
			echo '<br>'; // ******************* START END TIME ROW !!!!!!!!!!!!!!!!!!
			
			echo '<select name="thour" id="thour">';
			if ($pref->getvalue('ttype') == '24hr') {
				$hrs = 23;
			}
			else {
				$hrs = 12;
			}
			$i = 1;
			while ($i <= $hrs) {
				$i = sprintf('%02d', $i);
				echo "<option value=\"$i\"";
				if ($event) {
					$cthour = $event['thour'];
				}
				else {
					$cthour = $cset['thour'];
				}
				if ($i == $cthour) {
					echo ' selected="selected"';
				}
				echo ">$i</option>";
				$i++;
			}
			echo '</select>';
			echo '<select name="tminute" id="tminute">';
			$i = 0;
			while ($i < 60) {
				$i = sprintf('%02d', $i);
				echo "<option value=\"$i\"";
				if ($event) {
					if ($event['tminute'] == $i) {
						echo ' selected="selected"';
					}
				}
				echo ">$i</option>";
				$i++;
			}
			echo '</select>';
			echo '<select name="tmeridiem" id="tmeridiem">';
			if ($event) {
				$xm = $event['txm'];
			}
			else {
				$xm = $cset['txm'];
			}
			echo '<option value="am"';
			if ($xm == 'am') {
				echo ' selected="selected"';
			}
			echo '>AM</option>';
			echo '<option value="pm"';
			if ($xm == 'pm') {
				echo ' selected="selected"';
			}
			echo '>PM</option>';
			echo '</select>';
		}
		echo '</span>';
		
	// Status
   	echo '<label for="status">Status: </label>
   		<span><select name="status" id="status">';
   	echo '<option value="none"';
	if ($event) {
		if ($event['status'] == 'none') {
			echo 'selected="selected"';
		}
	}
	echo '>None</option>
   	<option value="tentative"';
	if ($event) {
		if ($event['status'] == 'tentative') {
			echo 'selected="selected"';
		}
	}
	echo '>Tentative</option>
   	<option value="confirmed"';
	if ($event) {
		if ($event['status'] == 'confirmed') {
			echo 'selected="selected"';
		}
	}
	echo '>Confirmed</option>
   	<option value="cancelled"';
	if ($event) {
		if ($event['status'] == 'cancelled') {
			echo 'selected="selected"';
		}
	}
	echo '>Cancelled</option>';
   	echo '</select></span>';
   	
   	// Repeat
   	echo '<label for="repeat">Repeat: </label>
   		<span><select name="repeat" id="repeat" onchange="repeattoggle()">
   		<option value="none"';
		if ($event) {
			if ($event['recurrence'] == 'none') {
				echo ' selected="selected"';
			}
		}
		echo '>None</option>
   		<option value="daily"';
		if ($event) {
			if ($event['recurrence'] == 'daily') {
				echo ' selected="selected"';
			}
		}
		echo '>Daily</option>
   		<option value="weekly"';
		if ($event) {
			if ($event['recurrence'] == 'weekly') {
				echo ' selected="selected"';
			}
		}
		echo '>Weekly</option>
   		<option value="monthly"';
		if ($event) {
			if ($event['recurrence'] == 'monthly') {
				echo ' selected="selected"';
			}
		}
		echo '>Monthly</option>
   		<option value="yearly"';
		if ($event) {
			if ($event['recurrence'] == 'yearly') {
				echo ' selected="selected"';
			}
		}
		echo '>Yearly</option>
   	</select></span>';
	echo '<div class="';
	if ($event) {
		if ($event['repeat'] != 'none') {
			echo 'in';
		}
		else {
			echo 'ex';
		}
	}
	else {
		echo 'ex';
	}
	echo '" id="repeatpane">
	<label for="times">Number of Times: </label>
		<span><input type="text" name="times" id="times" size="3" maxlength="3" value="';
		if ($event) {
			echo $event['times'];
		}
		else {
			echo '0';
		}
		echo '"></span>
	</div>
	';
   	
   	// Alarm
	if ($event || $pref->prefs['enable_alarms'] == 'false') {
		echo '<div class="ex">';
	}
   	echo '<label for="alarm">Alarm: </label>
   		<span><select name="alarm" id="alarm" onchange="alarmtoggle()">
   			<option value="none">None</option>
   			<option value="email">Email</option>
   			<option value="html">Message</option>
   		</select>
   		</span>';
   	echo '<div class="ex" id="recipientpane">
   	<label for="recipient">Recipient: <img src="images/help-trans.png" alt="?"></label>
   		<span><input type="text" name="recipient" id="recipient" size="17"></span>
   	</div>
   	';
   	echo '<div class="ex" id="alarmpane">
   	<label for="message">Message: </label>
   		<span><textarea name="message" id="message" cols="20" rows="5"></textarea></span>
   	</div>';
	if ($event || $pref->prefs['enable_alarms'] == 'false') {
		echo '</div>';
	}
	
	// Notes
	echo '<label for="notes">Notes: </label>
		<span><textarea name="notes" id="notes" cols="20" rows="6">';
		if ($event) {
			echo $event['notes'];
		}
		echo '</textarea></span>';
	
	// Save button
	echo '<span class="form_box"><input type="submit" value="' . Save . '"></span>';
	
	// End the form
	echo '<input type="hidden" name="form_event" id="form_event" value="true">';
	
	// Smr Hidden Vars
	echo '<input type="hidden" name="year" id="year" value="' . $cset['year'] . '">';
	echo '<input type="hidden" name="month" id="month" value="' . $cset['month'] . '">';
	echo '<input type="hidden" name="day" id="day" value="' . $cset['day'] . '">';
	echo '</form>';
	// End the sidebar
	echo '</div>';
	}// End protection layer
}

// Start title and back/next buttons
echo '<div class="title">';
// Figure out the previous year and month
if ($cset['month'] == 01) {
	$pyear = $cset['year'] - 1;	
}
else {
	$pyear = $cset['year'];
}
if ($cset['month'] != 01) {
	$pmonth = $cset['month'] - 1;
	$pmonth = sprintf('%02d', $pmonth);
}
else {
	$pmonth = 12;
}
echo '<div class="tback"><a href="' . $_SERVER['SCRIPT_NAME'] . '?view=month&amp;year=' . $pyear . '&amp;month=' . $pmonth . '"><img src="images/l_back.png" alt="Back" /></a></div>';
// Figure out the next year and month
if ($cset['month'] == 12) {
	$nyear = $cset['year'] + 1;	
}
else {
	$nyear = $cset['year'];
}
if ($cset['month'] != 12) {
	$nmonth = $cset['month'] + 1;
	$nmonth = sprintf('%02d', $nmonth);
}
else {
	$nmonth = "01";
}
echo '<div class="tnext"><a href="' . $_SERVER['SCRIPT_NAME'] . '?view=month&amp;year=' . $nyear . '&amp;month=' . $nmonth . '"><img src="images/l_next.png" alt="Next" /></a></div>';
echo '<div class="theader">' . constant($time->date('F', $cset['timestamp'])) . $time->date(', Y', $cset['timestamp']) . '</div>';
echo '</div>';



// The month grid
echo '<table class="month" cellspacing="0">';
echo '<tr>';

// Week day columns
echo '<td class="weekday">' . Sunday . '</td>';
echo '<td class="weekday">' . Monday . '</td>';
echo '<td class="weekday">' . Tuesday . '</td>';
echo '<td class="weekday">' . Wednesday . '</td>';
echo '<td class="weekday">' . Thursday . '</td>';
echo '<td class="weekday">' . Friday . '</td>';
echo '<td class="weekday">' . Saturday . '</td>
</tr><tr>';

// Days
$firstday = $time->date('w', $time->make(0, 0, 0, 1, $cset['month'], $cset['year']));
if ($firstday > 0) {
	$i = 0;
	while ($i < $firstday) {
		echo '<td class="month"><img src="images/null.png" alt="0"></td>';
		$i++;
	}
}

// *************************** DAYS REALLY START HERE ******************************
$i = 1;
$w = $firstday + 1;
while ($i <= $cset['days']) {
	echo '<td class="month"';
	$requests = array(
		'sidebar' => 'true',
		'year' => $cset['year'],
		'month' => $cset['month'],
		'day' => sprintf('%02d', $i),
	);
	if ($time->date('d') == $requests['day'] && $time->date('Y') == $requests['year'] && $time->date('m') == $requests['month']) {
		echo ' id="today"';
	}
	
	// Get rid of event for awhile if it exists
	
	// Double click event
	$location = pend_requests($requests, false);
	$location = preg_replace("|(\&event\=)(\d*)|", '', $location);
	echo " ondblclick=\"window.location='$location'\"";
	
	echo '>';
	echo '<span class="icons"><a href="' . preg_replace("|(\&event\=)(\d*)|", '', pend_requests($requests)) . '" title="Open Sidebar"><img src="images/edit-trans.png" alt="+"></a></span>' . $i;
	
	// Take care of events
	$timestamp1 = $time->make(0, 0, 0, $requests['day'], $requests['month'], $requests['year']);
	$timestamp2 = $time->make(59, 59, 23, $requests['day'], $requests['month'], $requests['year']);
	$events = get_events($timestamp1, $timestamp2);
	foreach ($events as $event) {
		echo '<div class="event"';
		
		// Take care of event status stuff
		echo ' id="status_' . $event['status'] . '"';
		
		echo '><span class="event_sum">' . $event['summary'];
		
		echo '<span class="menu">';
		$requests['event'] = $event['id'];
		echo '<a href="' . pend_requests($requests) . '">Edit Event</a>';
		$requests['rmevent'] = 'true';
		echo '<a href="' . pend_requests($requests) . '">Delete Event</a>';
		echo '</span>';
		
		echo '</span>' . $event['notes'];
		echo '</div>';
		// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! DAY ENDS HERE FOREVER !!!!!!!!!!!!!!!!!!!!!!!! until next time
	}
	
	echo '</td>';
	
	if ($w == 7) {
		echo '</tr><tr>';
		$w = 0;
	}
	$w++;
	$i++;
}

$lastday = $time->date('w', $time->make(0, 0, 0, $cset['days'], $cset['month'], $cset['year']));
$i = 0;
if ($lastday != 6) {
	$daysleft = 6 - $lastday;
	while ($i < $daysleft) {
		echo '<td class="month"><img src="images/null.png" alt="0"></td>';
		$i++;
	}
}

echo '</tr>';
echo '</table>';

// Any notes are posted here
//echo '<div class="note">';
//echo '</div>';

// Navigation panel
echo '<div class="nav">';
echo '<form method="get" action="' . $_SERVER['SCRIPT_NAME'] . '">';
echo '<fieldset><legend>' . Navigation . '</legend>';

// Choose Year
echo '<label>' . Year . ': </label><select name="year">';
echo '<option value="2005"';
if ($time->date('Y') == 2005) {
	echo ' selected="selected"';
}	echo '>2005</option>';
echo '<option value="2006"';
if ($time->date('Y') == 2006) {
	echo ' selected="selected"';
}	echo '>2006</option>';
echo '<option value="2007"';
if ($time->date('Y') == 2007) {
	echo ' selected="selected"';
}	echo '>2007</option>';
echo '<option value="2008"';
if ($time->date('Y') == 2008) {
	echo ' selected="selected"';
}	echo '>2008</option>';
echo '<option value="2009"';
if ($time->date('Y') == 2009) {
	echo ' selected="selected"';
}	echo '>2009</option>';
echo '<option value="2010"';
if ($time->date('Y') == 2010) {
	echo ' selected="selected"';
}	echo '>2010</option>';
echo '<option value="2011"';
if ($time->date('Y') == 2011) {
	echo ' selected="selected"';
}	echo '>2011</option>';
echo '<option value="2012"';
if ($time->date('Y') == 2012) {
	echo ' selected="selected"';
}	echo '>2012</option>';
echo '</select>';

// Choose month
echo '<label>' . Month . ': </label><select name="month">';
$months = array(
	January,
	February,
	March,
	April,
	May,
	June,
	July,
	August,
	September,
	October,
	November,
	December
);
$i = 01;
foreach ($months as $month) {
	echo '<option value="' . sprintf("%02d", $i) . '"';
	if (sprintf("%02d", $i) == $time->date('m')) {
		echo ' selected="selected"';
	}
	echo '>' . $month . '</option>';
	$i++;
}
echo '</select>';

// GO
echo ' <input type="submit" value="' . Go . '">';

// Display Preferences button if logged in
if ($users->status == 'in') {
	echo '<div class="txt_btn"><a href="';
	if (!isset($_GET['layer'])) {
		echo pend_requests(array('layer' => 'preferences'));
	}
	else {
		echo 'javascript:openLayer(\'Prefs\');';
	}
	echo '">' . Preferences . '</a></div>';
}

// End navigation panel
echo '</fieldset>';
echo '</form>';
echo '</div>';

// Display active pane if selected
if (isset($_GET['layer'])) {
	load_layer($_GET['layer']);
}

// Save buffer to template variable and end buffer
$html['c'][0] = ob_get_contents();
ob_end_clean();

}// End protection layer
?>