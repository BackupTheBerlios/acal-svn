function repeattoggle() {
	if (document.sidebar.repeat.value == "none") {
		document.getElementById('repeatpane').className = "ex";
	}
	else {
		document.getElementById('repeatpane').className = "in";
	}
}

function alarmtoggle() {
	if (document.sidebar.alarm.value == "none") {
		document.getElementById('alarmpane').className = "ex";
		document.getElementById('recipientpane').className = "ex";
	}
	else {
		if (document.sidebar.alarm.value == "email") {
			document.getElementById('recipientpane').className = "in";
			document.getElementById('alarmpane').className = "in";
		}
		else {
			document.getElementById('alarmpane').className = "in";
			document.getElementById('recipientpane').className = "ex";
		}
	}
}

function alldaytoggle()	{
	if (document.sidebar.hour.disabled == true) {
		document.sidebar.hour.disabled=false;
	}
	else {
		document.sidebar.hour.disabled=true;
	}
	if (document.sidebar.minute.disabled == true) {
		document.sidebar.minute.disabled=false;
	}
	else {
		document.sidebar.minute.disabled=true;
	}
	if (document.sidebar.thour.disabled == true) {
		document.sidebar.thour.disabled=false;
	}
	else {
		document.sidebar.thour.disabled=true;
	}
	if (document.sidebar.tminute.disabled == true) {
		document.sidebar.tminute.disabled=false;
	}
	else {
		document.sidebar.tminute.disabled=true;
	}
	if (document.sidebar.meridiem.disabled == true) {
		document.sidebar.meridiem.disabled=false;
	}
	else {
		document.sidebar.meridiem.disabled=true;
	}
	if (document.sidebar.tmeridiem.disabled == true) {
		document.sidebar.tmeridiem.disabled=false;
	}
	else {
		document.sidebar.tmeridiem.disabled=true;
	}
}