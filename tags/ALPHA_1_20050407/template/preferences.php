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
global $pref;

// If a preference pane has been updated, update db
if (isset($_POST['action'])) {
	$myprefs = explode(':', $_POST['action']);
	foreach ($myprefs as $mypref) {
		if (!isset($_POST[$mypref])) {
			$prefSetting = 'false';
		}
		else {
			$prefSetting = $_POST[$mypref];
		}
		$pref->update($mypref, $prefSetting);
	}
	// Reload preferences
	$pref->reload();
}

// Start output buffer
ob_start();

$html['head'] = '<title>' . ACal . ' ' . Preferences . '</title>';

// Get preference activity setting
if (!isset($_GET['pref'])) {
	$_GET['pref'] = 'general';
}
switch ($_GET['pref']) {
	case "alarm":
		define('PREF_ALARM_ACTIVITY', 'active');
		break;
	case "general":
		define('PREF_GENERAL_ACTIVITY', 'active');
		break;
	case "locale":
		define('PREF_LOCALE_ACTIVITY', 'active');
		break;
	case "template":
		define('PREF_TEMPLATE_ACTIVITY', 'active');
		break;
	default:
		define('PREF_GENERAL_ACTIVITY', 'active');
}
if (!defined('PREF_ALARM_ACTIVITY')) {
	define('PREF_ALARM_ACTIVITY', 'inactive');
}
if (!defined('PREF_GENERAL_ACTIVITY')) {
	define('PREF_GENERAL_ACTIVITY', 'inactive');
}
if (!defined('PREF_LOCALE_ACTIVITY')) {
	define('PREF_LOCALE_ACTIVITY', 'inactive');
}
if (!defined('PREF_TEMPLATE_ACTIVITY')) {
	define('PREF_TEMPLATE_ACTIVITY', 'inactive');
}

echo '<div class="toolbar">
<a href="' . $_SERVER['SCRIPT_NAME'] . '?view=preferences&pref=general" title="' . General . '" class="pref_' . PREF_GENERAL_ACTIVITY . '">' . General . '</a>
<a href="' . $_SERVER['SCRIPT_NAME'] . '?view=preferences&pref=alarm" title="' . Alarm . '" class="pref_' . PREF_ALARM_ACTIVITY . '">' . Alarm . '</a>
<a href="' . $_SERVER['SCRIPT_NAME'] . '?view=preferences&pref=locale" title="' . Localization . '" class="pref_' . PREF_LOCALE_ACTIVITY . '">' . Localization . '</a>
<a href="' . $_SERVER['SCRIPT_NAME'] . '?view=preferences&pref=template" title="' . Template . '" class="pref_' . PREF_TEMPLATE_ACTIVITY . '">' . Template . '</a>
</div>
';

// Start form
echo '<form method="post" action="' . $_SERVER['REQUEST_URI'] . '">';

// Now for the actual preference panes
switch ($_GET['pref']) {
	case "template":
		echo '<h2>' . Template . ' ' . Preferences . '</h2>';
		
		$ttype = $pref->getvalue('ttype');
		echo '<p>Time: <select name="ttype">';
		echo '<option value="daylight"';
		if ($ttype == 'daylight') {
			echo ' selected="selected"';
		}
		echo '>12 Hour</option>';
		echo '<option value="world"';
		if ($ttype == 'world') {
			echo ' selected="selected"';
		}
		echo '>24 Hour</option>';
		echo '</select></p>';
		
		echo '<p>Forms: <select name="formt">';
		$formt = $pref->getvalue('formt');
		echo '<option value="html"';
		if ($formt == 'html') {
			echo ' selected="selected"';
		}
		echo '>Use HTML</option>';
		echo '<option value="xforms"';
		if ($formt == 'xforms') {
			echo ' selected="selected"';
		}
		echo '>Use XForms</option>';
		echo '</select></p>';
		
		echo '<input type="hidden" name="action" value="ttype:formt">';
		break;
	case "general":
		echo '<h2>' . General . ' ' . Preferences . '</h2>';
		
		echo '<p>Test 2: <input type="test" size="25" name="test2" value="' . $pref->getvalue('test2') . '" /></p>
		';
		
		echo '<input type="hidden" name="action" value="test2">';
		break;

	case "alarm":
		echo '<h2>' . Alarm . ' ' . Preferences . '</h2>';
		
		echo '<p>Enable Alarms: <select name="enable_alarms">';
		$val = $pref->getvalue('enable_alarms');
		echo '<option value="true"';
		if ($val == 'true') {
			echo ' selected="selected"';
		}
		echo '>Yes</option>';
		echo '<option value="false"';
		if ($val == 'false') {
			echo ' selected="selected"';
		}
		echo '>No</option>
		</select></p>';
		echo '
		<input type="hidden" name="action" value="enable_alarms">
		';
		break;
	case "locale":
		echo '<h2>' . Localization . ' ' . Preferences . '</h2>';
		
		echo '<p>Show Language Menu: <input type="checkbox" name="showLangMenu" value="true"';
		if ($pref->prefs['showLangMenu'] == 'true') {
			echo ' checked="checked"';
		}
		echo ' /></p>';
		
		echo '<input type="hidden" name="action" value="showLangMenu" />';
		break;
}

// End form
echo '<p><input type="submit" value="' . Save . '"></p>';
echo '</form>';

// Save buffer to template variable and end buffer
$html['c'][0] = ob_get_contents();
ob_end_clean();
?>