<?php
// Preferences Layer
global $pref;
global $time;
global $users;

// Figure out what pref we should be showing
if (!isset($_GET['pref'])) {
	$showPref = 'general';
}
else {
	$showPref = $_GET['pref'];
}

// Print tabs
$availablePrefPanes = array(
	'general' => General,
	'datetime' => Date_and_Time,
	'users' => Users_and_Groups,
	'locale' => Localization,
	'template' => Template,
	'alarm' => Alarm,
);
echo '<div id="Prefs" class="open">';

// The tabs
echo '<div class="pref_tabs">
<ul>';
$closeLink = edit_requests('layer', false, REQUEST_URI, true);
$closeLink = edit_requests('pref', false, $closeLink, true);
//echo '<a href="' . $closeLink . '"><img src="images/close.png" alt="-"></a>';

foreach ($availablePrefPanes as $prefLink => $prefPane) {
	echo '<li';
	if ($prefLink == $showPref) {
		echo ' id="current-tab"';
	}
	echo '><a href="' . pend_requests(array('pref' => $prefLink)) . '">' . $prefPane . '</a></li>';
}
echo '</ul></div>';

// Start form
$formAction = edit_requests('editgroup', NULL, $_SERVER['REQUEST_URI'], true);
echo '<div class="shadow">
<form method="post" action="' . $formAction . '">';

// Print out the correct preference pane
switch ($showPref) {
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
		
		echo '<input type="hidden" name="pref_action" value="ttype:formt">';
		break;
	case "general":
		echo '<h2>' . General . ' ' . Preferences . '</h2>';
		
		echo '<p>Test 2: <input type="test" size="25" name="test2" value="' . $pref->getvalue('test2') . '" /></p>
		';
		
		echo '<input type="hidden" name="pref_action" value="test2">';
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
		<input type="hidden" name="pref_action" value="enable_alarms">
		';
		break;
	case "locale":
		echo '<h2>' . Localization . ' ' . Preferences . '</h2>';
		
		echo '<p>Show Language Menu: <input type="checkbox" name="showLangMenu" value="true"';
		if ($pref->prefs['showLangMenu'] == 'true') {
			echo ' checked="checked"';
		}
		echo ' /></p>';
		
		// Choose locale
		global $locale;
		echo '<p>Localization: <select name="locale">';
		foreach ($locale->langs as $lang) {
			echo '<option value="' . $lang . '"';
			if ($pref->prefs['locale'] == $lang) {
				echo ' selected="selected"';
			}
			echo '>' . $lang . '</option>';
		}
		echo '</select></p>';
		
		echo '<input type="hidden" name="pref_action" value="showLangMenu:locale" />';
		break;
	case 'datetime':
		echo '<h2>' .  Date_and_Time . ' ' . Preferences . '</h2>';
		echo '<p>Time Zone: <select name="timezone">';
		
		foreach ($time->availableZones as $zone) {
			echo '<option value="' . $zone . '"';
			if ($zone == $pref->prefs['timezone']) {
				echo ' selected="selected"';
			}
			echo '>' . $zone . '</option>';
		}
		echo '</select><p>';
		
		echo '<input type="hidden" name="pref_action" value="timezone" />';
		break;
	case 'users':
		echo '<h2>' . Users_and_Groups . ' ' . Preferences . '</h2>';
		
		// Groups manager
		echo '<fieldset><legend>Groups Manager</legend>';
		if (defined('PREFS_MSG')) {
			echo '<p class="message">' . PREFS_MSG . '</p>';
		}
		echo '
		<select multiple="true" name="groups" size="6" style="float: left" onchange="jumpTo(this);">';
		foreach ($users->groups as $group) {
			$req = array(
				'editgroup' => $group['name']
			);
			$uri = pend_requests($req);
			echo '<option value="' . $uri . '"';
			if (isset($_GET['editgroup']) && $_GET['editgroup'] == $group['name']) {
				echo ' selected="selected"';
			}
			echo '>' . $group['name'] . '</option>';
		}
		
		if (isset($_GET['editgroup'])) {
			// Get that group information
			foreach ($users->groups as $group) {
				if ($group['name'] == $_GET['editgroup']) {
					$groupInfo = $group['rights'];
					$groupInfo = explode(',', $groupInfo);
					break;
				}
			}
		}
		else {
			$groupInfo = array();
		}
		
		echo '
		</select>
		<div style="margin-left: 70px;">
		<p>New Group: <input type="text" size="20" name="newgroup" /></p>
		<p>Delete Selected Group: <input type="checkbox" name="delgroup" value="yes" /><br />
		<span style="font-style: italic; color: grey;">Warning: Users in those groups will no longer be able to login.</span></p>
		<p><b>';
		if (isset($_GET['editgroup'])) {
			echo ucwords($_GET['editgroup']);
		}
		else {
			echo 'New';
		}
		echo ' Group Settings:</b><br />
		Can Manage Events <input type="checkbox" name="canedit" value="true"';
		if (in_array('canedit', $groupInfo)) {
			echo ' checked="true"';
		}
		echo ' /><br />
		Has Administration Rights <input type="checkbox" name="admin" value="true"';
		if (in_array('admin', $groupInfo)) {
			echo ' checked="true"';
		}
		echo ' />
		</p>
		</div>
		</fieldset>';
		
		if (isset($_GET['editgroup'])) {
			echo '<input type="hidden" name="group" value="' . $_GET['editgroup'] . '" />';
		}
		echo '<input type="hidden" name="edit_groups" value="true" />';
		break;
}

// End form
echo '<p><input type="submit" value="' . Save . '"> <input type="button" name="' . edit_requests('layer', NULL, REQUEST_URI, true) . '" onclick="window.location.href = this.name" value="Cancel" /></p>';
echo '</form>';
echo '</div></div>';
?>