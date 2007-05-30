<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2007 FreeMED Software Foundation
 //
 // This program is free software; you can redistribute it and/or modify
 // it under the terms of the GNU General Public License as published by
 // the Free Software Foundation; either version 2 of the License, or
 // (at your option) any later version.
 //
 // This program is distributed in the hope that it will be useful,
 // but WITHOUT ANY WARRANTY; without even the implied warranty of
 // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 // GNU General Public License for more details.
 //
 // You should have received a copy of the GNU General Public License
 // along with this program; if not, write to the Free Software
 // Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
*}-->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>FreeMED v<!--{$VERSION}--></title>

<!--{* ***** Style Elements ***** *}-->

<link rel="stylesheet" type="text/css" href="<!--{$htdocs}-->/stylesheet.css" />
<!--{if $DEBUG}-->
<script language="JavaScript" type="text/javascript">
var djConfig = { isDebug: true }; //, debugContainerId : "dojoDebugOutput" };
</script>
<!--{/if}-->
<script type="text/javascript" src="<!--{$htdocs}-->/dojo/dojo.js"></script>
<script language="JavaScript" type="text/javascript">
	<!--{if $DEBUG}-->
	dojo.require( 'dojo.debug.Firebug' );
	<!--{/if}-->
	dojo.require("dojo.widget.LayoutContainer");
	dojo.require("dojo.widget.ContentPane");
	dojo.require("dojo.widget.LinkPane");
	dojo.require("dojo.widget.SplitContainer");
	dojo.require("dojo.widget.Tooltip");
	dojo.require("dojo.widget.Toaster");
	dojo.require("dojo.widget.Select");
	dojo.require("dojo.widget.Dialog");
	dojo.hostenv.writeIncludes();

	//
	//	Common FreeMED UI Functions
	//

	function openHelpPage ( ) {
		var popup = window.open('<!--{$controller}-->/org.freemedsoftware.ui.chtmlbrowser', 'chtmlBrowser', 'height=600,width=480,resizable=yes,alwaysRaised=yes');
	}

	function freemedMessage( message, type ) {
		var duration;
		switch (type) {
			case 'ERROR':
			duration = 3000;
			break;

			case 'WARNING':
			default:
			duration = 1000;
			break;
		}
		dojo.event.topic.publish(
			"freemedMessage",
			{message: message, type: type, duration: duration}
		);
	}

	function freemedLogout ( ) {
		var logoutDialog = dojo.widget.getWidgetById('freemedLogoutDialog');
		logoutDialog.show();
		dojo.io.bind({
			method : 'POST',
			url: '<!--{$relay}-->/org.freemedsoftware.public.Login.Logout',
			error: function(type, data, evt) {
				alert( "<!--{t}-->FreeMED has encountered an error. Please try again.<!--{/t}-->" );
			},
			load: function(type, data, evt) {
				if (data) {
					location.href = '<!--{$controller}-->/org.freemedsoftware.ui.login';	
				} else {
					logoutDialog.hide();
				}
			},
			mimetype: "text/json",
			sync: true
		});
		return true;
	}

	function freemedLoad ( url ) {
		// Set the URL for the contentPane to load the appropriate content
		dojo.widget.byId( 'freemedContent' ).setUrl( url );

		// Push current help topic value
		freemedGlobal.currentHelpTopic = url;

		// Add page to history
		freemedGlobal.pageHistory.push( url );

		return true;
	} // end function freemedLoad

	function freemedPatientContentLoad ( url ) {
		try {
			// Set the URL for the contentPane to load the appropriate content
			dojo.widget.byId( 'freemedPatientContent' ).setUrl( url );

			// Push current help topic value
			freemedGlobal.currentHelpTopic = url;
		} catch (err) {
			dojo.debug('Caught error in freemedPatientContentLoad() for ' + url);
		}

		return true;
	} // end function freemedLoad

	// "Global Namespace" functions and settings
	freemedGlobal = {
		currentHelpTopic: undefined,
		interval: 600, // seconds between polls
		intervalCallback: function ( ) {
			return '';
			dojo.io.bind({
				method: "POST",
				content: {
					param0: freemedGlobal.intervalStamp
				},
				url: "<!--{$relay}-->/org.freemedsoftware.module.SystemNotifications.GetFromTimestamp",
				load: function(type, data, evt) {
					if (data) {
						// Save interval passed back
						freemedGlobal.intervalStamp = data.timestamp;
					}
				},
				mimetype: "text/json"
			});
		},
		addPatientToHistory: function ( id, patient_name ) {
			try {
				// Avoid dupes
				if ( freemedGlobal.patientHistory.length > 0 ) {
					for (var i=0; i<freemedGlobal.patientHistory.length; i++) {
						if (freemedGlobal.patientHistory[i][0] == id) {
							// Already there
							return true;
						}
					}
				}
				freemedGlobal.patientHistory.push([ id, patient_name ]);
			} catch (err) { }
		},
		//---- Catch all "state" namespace for storing state variables
		pageHistory: [ ],
		patientHistory: [ ],
		state: { }
	};

	// Initialization
	dojo.addOnLoad(function(){
		return true;
		dojo.io.bind({
			method: "POST",
			content: { },
			url: "<!--{$relay}-->/org.freemedsoftware.module.SystemNotifications.GetTimestamp",
			load: function(type, data, evt) {
				if (data) {
					// Save interval passed back
					freemedGlobal.intervalStamp = data;
				}
			},
			sync: true,
			mimetype: "text/json"
		});
		window.setInterval( freemedGlobal.intervalCallback, 1000 * freemedGlobal.interval );
	});

</script>

	<!--{* ***** Style Elements ***** *}-->

<style type="text/css">
	html, body {
		width: 100%;	/* make the body expand to fill the visible window */
		height: 100%;
		font-family: sans-serif;
		size: 8pt;
		overflow: hidden;	/* erase window level scrollbars */
		padding: 0 0 0 0;
		margin: 0 0 0 0;
		background-image: url(<!--{$htdocs}-->/images/stipedbg.png);
		}

	.basicPane {
		background-image: url(<!--{$htdocs}-->/images/stipedbg.png);
		}

	.dashboardWidgetContainer {
		background-image: url(<!--{$htdocs}-->/images/brushed.gif);
		}
</style>

<!-- Include dock -->
<script language="javascript" src="<!--{$htdocs}-->/euDock/euDock.2.0.js"></script>
<script language="javascript" src="<!--{$htdocs}-->/euDock/euDock.Image.js"></script>

</head>
<body>

	<!--{* ***** Hidden things ***** *}-->

<div dojoType="dialog" id="freemedLoadingDialog" bgOpacity="0.5" toggle="fade" toggleDuration="500" blockDuration="2000" style="display:none;">
	<table border="0" cellpadding="5">
		<tr>
			<td valign="middle"><img src="<!--{$htdocs}-->/images/loading.gif" border="0" /></td>
			<td valign="middle"><b> <!--{t}-->Loading ...<!--{/t}--> </b></td>
		</tr>
	</table>
</div>

<div dojoType="dialog" id="freemedLogoutDialog" bgOpacity="0.5" toggle="fade" toggleDuration="250" blockDuration="2000" style="display:none;">
	<table border="0" cellpadding="5">
		<tr>
			<td valign="middle"><img src="<!--{$htdocs}-->/images/loading.gif" border="0" /></td>
			<td valign="middle"><b> <!--{t}-->Logging out of FreeMED<!--{/t}--> </b></td>
		</tr>
	</table>
</div>

<div dojoType="LayoutContainer" layoutChildPriority="top-bottom" style="width: 100%; height: 100%;">
	<div dojoType="ContentPane" layoutAlign="bottom" style="background-image: url(<!--{$htdocs}-->/images/brushed.gif); color: #000000;">

		<!-- Bottom of screen bar -->

		<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:0;">
			<tr>
				<td align="left" width="130">
					<img src="<!--{$htdocs}-->/images/FreeMEDLogoTransparent.png" alt="" height="37" width="120" border="0" />
				</td>
				<td align="middle" width="33%">
					<font size="2" face="Trebuchet MS, Geneva, Arial, Helvetica, SunSans-Regular, sans-serif">Version <!--{$VERSION}--></font><br/>
					<font size="1" face="Trebuchet MS, Geneva, Arial, Helvetica, SunSans-Regular, sans-serif">&copy; 1999-2007 by the FreeMED Software Foundation</font>
				</td>
				<td align="middle" width="33%">
					<div id="euDockContainer">
<script language="javascript">
//	euEnv.imageBasePath="<!--{$htdocs}-->/";
	var dock = new euDock ();
	dock.setBar({
		left      :{euImage:{image:"<!--{$htdocs}-->/euDock/barImages/dockBg-l.png"}},
		horizontal:{euImage:{image:"<!--{$htdocs}-->/euDock/barImages/dockBg-c-o.gif"}},
		right     :{euImage:{image:"<!--{$htdocs}-->/euDock/barImages/dockBg-r.png"}}
	});
	dock.setIconsOffset(5);
	dock.addIcon(
		new Array({ euImage:{ image:"<!--{$htdocs}-->/images/Quick-Cal.png" } } ),
		{ code:"freemedLoad('org.freemedsoftware.ui.scheduler.dailyappointments');" }
	);
	dock.addIcon(
		new Array( { euImage:{ image:"<!--{$htdocs}-->/images/Stocks.png" } } ),
		{ code:"freemedLoad('org.freemedsoftware.ui.billing');"}
	);
	dock.addIcon(
		new Array( { euImage:{ image:"<!--{$htdocs}-->/images/Rolodex.png" } } ),
		{ link:"http://eudock.jules.it" }
	);
	dock.addIcon(
		new Array( { euImage:{ image:"<!--{$htdocs}-->/images/Stickies.png" } } ),
		{ link:"http://eudock.jules.it" }
	);
	dock.addIcon(
		new Array( { euImage:{ image:"<!--{$htdocs}-->/images/Yellow-Pages.png" } } ),
		{ code:"freemedLoad('org.freemedsoftware.controller.mainframe');" }
	);
//	dock.setScreenAlign(euDOWN, 5);
</script>
					</div>
				</td>
				<td align="right" nowrap="nowrap" valign="middle">
					<img src="<!--{$htdocs}-->/images/notes.png" alt="" width="73" height="30" border="0" id="notesButton" />
					<img src="<!--{$htdocs}-->/images/techsupport.png" alt="" width="73" height="30" border="0" id="supportButton" />
					<img src="<!--{$htdocs}-->/images/usermanual.png" alt="" width="73" height="30" border="0" id="manualButton" onClick="openHelpPage(); return true;" />
					<img src="<!--{$htdocs}-->/images/logoff.png" alt="" width="73" height="30" border="0" id="logoffButton" onClick="freemedLogout();" />
					<!-- Tooltips -->
					<span dojoType="tooltip" connectId="supportButton" toggle="explode" toggleDuration="100"><!--{t}-->Access technical support<!--{/t}--></span>
					<span dojoType="tooltip" connectId="manualButton" toggle="explode" toggleDuration="100"><!--{t}-->View online FreeMED documentation<!--{/t}--></span>
					<span dojoType="tooltip" connectId="logoffButton" toggle="explode" toggleDuration="100"><!--{t}-->Terminate your current FreeMED session<!--{/t}--></span>
				</td>
			</tr>
		</table>
	</div>

<!--{* ***** Content will go in here ***** *}-->

<!--{if $DEBUG}--><div id="dojoDebugOutput" /><!--{/if}-->
