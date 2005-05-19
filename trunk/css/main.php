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

// Send correct content type
header('Content-Type: text/css; charset=utf-8');

// Stylesheet Starts Here!
echo '
img.help:hover {
	cursor: help;
}

a {
	color: blue;
	text-decoration: underline;
}

a:hover {
	text-decoration: none;
	color: #071F9E;
}

*.pref_block {
	padding: 6px;
	border: 2px solid silver;
}

legend {
	font-weight: bold;
}

*.message {
	font-style: italic;
	color: #660000;
}

#Prefs {
	clear: left;
}

#Prefs form {
	clear: both;
	border: 1px solid silver;
	background-image: url("../images/layer_bg.png");
	padding: 0 7px;
	margin-right: 15px;
	min-width: 560px;
	position: relative;
	margin-bottom: 15px;
}

div.pref_tabs {
	clear: both;
	position: relative;
	z-index: 11;
	font-size: 16px;
}

div.pref_tabs ul {
	list-style: none;
	margin: 0px;
	padding: 0px;
}

div.pref_tabs ul>li {
	float: left;
	margin: 0px;
	padding-left: 6px;
	background: url("../images/tab-l.png") no-repeat left top;
}

div.pref_tabs ul>li#current-tab {
	background: url("../images/tab-l_on.png") no-repeat left top;
}

div.pref_tabs ul>li>a {
	display: block;
	background: url("../images/tab-r.png") no-repeat right top;
	padding: 4px 7px 3px 0px;
	color: white;
	text-decoration: none;
}

div.pref_tabs ul>li#current-tab>a {
	background: url("../images/tab-r_on.png") no-repeat right top;
	color: black;
}

div.closed {
	display: none;
}

div.open {
	position: absolute;
	top: 7%;
	left: 10%;
	z-index: 10;
}

div.shadow {
	background: url("../images/drop_shadow.png") no-repeat bottom right;
	position: relative;
	z-index: 9;
	clear: both;
	border-bottom: 1px solid transparent;
}

*.txt_btn {
	padding: 2px;
	margin: 6px;
	border: 1px solid #707171;
	width: 110px;
	text-align: center;
	font-size: 16px;
	background-color: #E8E6E7;
}

.txt_btn a {
	display: block;
	color: #0A19A7;
	text-decoration: none;
}

.txt_btn:hover {
	background-color: #3E8E87;
}

#status_none span.event_sum {
	background-color: #b7cef1;
}

#status_tentative span.event_sum {
	background-color: #B8E2AA;
}

#status_confirmed span.event_sum {
	background-color: #49A3DC;
}

#status_cancelled span.event_sum {
	background-color: #EEFB01;
}

#today {
	background-color: #ebeff6;
}

span.menu {
	display: block;
	border: 1px solid #aebcd5;
	background-image: url("../images/menu-trans.png");
	position: absolute;
}

span.menu a {
	display: block;
	padding: 3px;
	color: blue;
	text-decoration: none;
}

span.menu a:hover {
	background-color: white;
}

div.event {
	border: 1px solid white;
	background-image: url("../images/eventbg.png");
	padding: 3px;
	margin: 2px;
}

div.event span {
	display: block;
	margin-left: -3px;
	margin-right: -3px;
	margin-top: -3px;
}

div.event span.menu {
	display: none;
}

div.event span:hover>span.menu {
	display: block;
}

.ex {
	display: none;
}

.in {
	display: block;
	margin: 5px;
	padding: 3px;
	background-image: url("../images/in-trans.png");
	border: 1px solid white;
	border-radius: 10px;
}

.icons {
	float: right;
}

.iconsl {
	float: left;
}

#sidebar label {
	font-size: 13px;
	display: block;
	float: left;
	margin-bottom: 6px;
	margin-top: 3px;
}

#sidebar input:focus {
	background-color: #c0d9fa;
}

#sidebar input {
	font-size: 11px;
}

#sidebar textarea:focus {
	background-color: #c0d9fa;
}

#sidebar textarea {
	font-size: 11px;
}

#sidebar select {
	font-size: 11px;
}

#sidebar span {
	display: block;
	margin-bottom: 6px;
}

.form_box {
	font-size: 13px;
	clear: both;
	margin-left: 14px;
}

.side_panel {
	height: 100%;
	width: 200px;
	clear: both;
	position: fixed;
	right: 0px;
	top: 0px;
	background-image: url("../images/sidebar-trans.png");
	overflow: auto;
}

.nav {
	clear: both;
}

.nav fieldset {
	background-image: url("../images/nav.png");
}

.nav fieldset>legend {
	font-weight: bold;
}

.nav label {
	margin-left: 10px;
}

.note {
	background-color: #fffaac;
	width: 300px;
	padding: 4px;
	border: 1px solid #fffaac;
	margin: 4px;
	float: left;
}

table.month {
	width: 100%;
	border-top: 1px solid #cdcdcd;
	border-left: 1px solid #cdcdcd;
	clear: both;
}

td.month {
	border-right: 1px solid #cdcdcd;
	border-bottom: 1px solid #cdcdcd;
	height: 100px;
	vertical-align: top;
	text-align: left;
}

td.weekday {
	border-right: 1px solid #cdcdcd;
	border-bottom: 1px solid #cdcdcd;
	text-align: center;
	width: 14.285714285714%;
	background-image: url("../images/weekday.png");
	color: white;
}

.title {
	background-image: url("../images/title.png");
	height: 40px;
	border-left: 1px solid #e0e0e0;
	border-right: 1px solid #e0e0e0;
}

.theader {
	text-align: center;
	font-size: 36px;
	font-style: oblique;
}

.tnext {
	clear: right;
	float: right;
}

.tback {
	clear: both;
	float: left;
}

img {
	border: none 0px;
}

.topbar {
	background-color: #e8e8e8;
	border: 1px solid #e8e8e8;
}

.vtoggle {
	float: right;
}

.vtoggle a {
	padding-left: 3px;
}

.topbar form {
	display: inline;
}

#bartime {
	margin-right: 20px;
	color: #103963;
	font-style: italic;
}

.notice {
	border: 1px solid #eaeaea;
	padding: 2px;
	background-image: url("../images/notice.png");
}

.notice span {
	font-weight: bold;
}

.alarm {
	border: 1px solid #f3b1b1;
	padding: 2px;
	background-image: url("../images/alarm.png");
}

.alarm span {
	font-weight: bold;
	color: white;
}

*.toolbar {
	width: 100%;
	background-image: url("../images/toolbar.png");
	background-repeat: repeat-x;
}

.toolbar a {
	padding-left: 2px;
	padding-right: 2px;
	text-decoration: none;
	color: #364580;
}

.toolbar a:hover {
	background-color: white;
	color: black;
}

.pref_active {
	background-color: #e1e1e1;
	color: blue;
}

body {
	background-image: url("../images/body.png");
}
';
?>