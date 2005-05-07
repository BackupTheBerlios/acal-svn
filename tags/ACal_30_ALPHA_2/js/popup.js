function openLayer(WinID) {
	if (document.getElementById(WinID).className == "closed") {
		document.getElementById(WinID).className = "open";
	}
	else {
		document.getElementById(WinID).className = "closed";
	}
}

function popUp(URL) {
var windowReference;
day = new Date();
id = day.getTime();
windowReference = window.open(URL,"", 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=500,height=450,left = 200,top = 50');
if (!windowReference.opener)
	windowReference.opener = self;
}