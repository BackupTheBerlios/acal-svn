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

// Send out the correct content-type
header('Content-Type: text/html; charset=UTF-8');

$html['doctype'] = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
            "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
';

$html['commonhead'] = '	<link rel="stylesheet" type="text/css" href="css/main.php">
	<script type="text/javascript" src="js/popup.js"></script>
	<script type="text/javascript" src="js/functions.js"></script>
	<!-- compliance patch for microsoft browsers -->
	<!--[if lt IE 7]>
	<script src="js/ie7/ie7-standard.js" type="text/javascript">
	</script>
	<![endif]-->
	<script type="text/javascript">
    <!--
        function jumpTo(URL_List) {
            var URL = URL_List.options[URL_List.selectedIndex].value;
            window.location.href = URL;
		}
    -->
    </script>
';

$html['bodystart'] = '	</head>
	<body>
';

$html['bodyend'] = '	</body>
</html>
';
?>