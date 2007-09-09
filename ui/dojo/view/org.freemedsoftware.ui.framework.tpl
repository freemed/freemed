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
<title>FreeMED v<!--{$VERSION}--><!--{if $svnVersion}--> (build <!--{$svnVersion}-->)<!--{/if}--></title>

<!--{* ***** Style Elements ***** *}-->

<link rel="stylesheet" type="text/css" href="<!--{$htdocs}-->/stylesheet.css" />
<script language="JavaScript" type="text/javascript">
var djConfig = {
<!--{if $DEBUG}-->
	isDebug: true,
	debugContainerId : "dojoDebugOutput",
<!--{/if}-->
	dojoRichTextFrameUrl: "<!--{$htdocs}-->/dojo/src/widget/templates/richtextframe.html"
};
</script>
<script type="text/javascript" src="<!--{$htdocs}-->/dojo/dojo.js"></script>
<script language="JavaScript" type="text/javascript">
	<!--{if $DEBUG}-->
	dojo.require( 'dojo.debug.Firebug' );
	<!--{/if}-->
	dojo.require("dojo.date");
	dojo.require("dojo.event.*");
	dojo.require("dojo.lfx.*");
	dojo.require("dojo.widget.LayoutContainer");
	dojo.require("dojo.widget.ContentPane");
	dojo.require("dojo.widget.LinkPane");
	dojo.require("dojo.widget.SplitContainer");
	dojo.require("dojo.widget.Tooltip");
	dojo.require("dojo.widget.Toaster");
	dojo.require("dojo.widget.Select");
	dojo.require("dojo.widget.Dialog");
	dojo.require("dojo.widget.IntegerTextbox");
	dojo.hostenv.writeIncludes();

	//
	//	Common FreeMED UI Functions
	//

	function openHelpPage ( ) {
		var topic = freemedGlobal.currentHelpTopic;
		var popup = window.open('<!--{$controller}-->/org.freemedsoftware.ui.chtmlbrowser?topic=' + topic, 'chtmlBrowser', 'height=600,width=480,resizable=yes,alwaysRaised=yes');
	}

	function openNotesDialog ( ) {
		var notesDialog = dojo.widget.getWidgetById('freemedNotesDialog');
		notesDialog.show();
	}

	function freemedMessage( message, type ) {
		var duration;
		switch (type) {
			case 'MESSAGE':
			duration = 10000;
			break;

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
			{message: message, type: type, delay: duration}
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

	function freemedLoad ( url, e, forcenew ) {
		forcenew = forcenew || 0;

		try {
			if ( e.ctrlKey ) { forcenew = 1; }
		} catch (err) { }

		// Decide where this goes
		var t = dojo.widget.byId( 'freemedTabContainer' );

		if ( forcenew ) {
			dojo.debug( 'Forcing new tab creation' );
			freemedGlobal.tabCount += 1;	
			var newTab = dojo.widget.createWidget( 'dojo:ContentPane', {
				label: "<!--{t}-->Workspace<!--{/t}--> " + freemedGlobal.tabCount,
				cacheContent: false,
				closable: true,
				adjustPaths: false,
				executeScripts: true,
				loadingMessage: "<!--{$paneLoading|replace:'"':'\\"'}-->",
				href: url
			});
			t.addChild( newTab );
			t.selectChild( newTab ); 
		} else {
			try {
				var tab = dojo.widget.byId( t.selectedChild );
				tab.setUrl( url );
			} catch ( err ) {
				dojo.debug( 'CAUGHT ERROR, OPENING NEW TAB' );
				freemedGlobal.tabCount += 1;	
				var newTab = dojo.widget.createWidget( 'dojo:ContentPane', {
					label: "<!--{t}-->Workspace<!--{/t}--> " + freemedGlobal.tabCount,
					cacheContent: false,
					closable: true,
					adjustPaths: false,
					executeScripts: true,
					loadingMessage: "<!--{$paneLoading|replace:'"':'\\"'}-->",
					href: url
				});
				t.addChild( newTab );
				t.selectChild( newTab );
			}
		}

		// Push current help topic value
		var x = url.replace( "<!--{$controller}-->/", '' );
		if ( x.match('=') ) {
			x = x.slice( 0, x.indexOf( '?' ) );
		}
		freemedGlobal.currentHelpTopic = x;

		// Add page to history
		freemedGlobal.pageHistory.push( url );

		return true;
	} // end function freemedLoad

	function freemedPatientContentLoad ( url ) {
		try {
			// Set the URL for the contentPane to load the appropriate content
			dojo.widget.byId( 'freemedPatientContent' ).setUrl( url );

			// Push current help topic value
			var x = url.replace( "<!--{$controller}-->/", '' );
			if ( x.match('=') ) {
				x = x.slice( 0, x.indexOf( '?' ) );
			}
			freemedGlobal.currentHelpTopic = x;
		} catch (err) {
			dojo.debug('Caught error in freemedPatientContentLoad() for ' + url);
		}

		return true;
	} // end function freemedLoad

	function toggleDiv ( d ) {
		// Get current setting
		if ( document.getElementById( d ).offsetHeight > 0 ) {
			dojo.lfx.wipeOut( d, 300 ).play();
		} else {
			dojo.lfx.wipeIn( d, 300 ).play();
		}
	} // end function toggleDiv

	// "Global Namespace" functions and settings
	freemedGlobal = {
		currentHelpTopic: undefined,
		tabCount: 1,
		noteSave: "<!--{$SESSION.authdata.user_record.usermanageopt.notePad|escape:'javascript'}-->",
		interval: 60, // seconds between polls
		intervalCallback: function ( ) {
			var p = [
				{
					method: "org.freemedsoftware.module.SystemNotifications.GetFromTimestamp",
					parameters: [ freemedGlobal.intervalStamp ]
				},
				{
					method: "org.freemedsoftware.module.UnfiledDocuments.GetCount"
				},
				{
					method: "org.freemedsoftware.module.UnreadDocuments.GetCount"
				}
				]
			dojo.io.bind({
				method: "POST",
				content: {
					param0: dojo.json.serialize( p )
				},
				url: "<!--{$relay}-->/org.freemedsoftware.api.UserInterface.Multicall",
				load: function(type, data, evt) {
					if (data[0]) {
						// Handle everything
						if ( data[0].count > 0 ) {
							freemedGlobal.onSystemNotifications( data[0].items );
						}

						// Save interval passed back
						freemedGlobal.intervalStamp = data.timestamp;
					}

					// Show toasters if new documents have arrived.
					if ( parseInt( document.getElementById( 'taskPaneUnfiledCount' ).innerHTML ) < parseInt( data[1] ) ) {
						freemedMessage( "<!--{t}-->New unfiled document(s)<!--{/t}-->", 'INFO' );
					}
					if ( parseInt( document.getElementById( 'taskPaneUnreadCount' ).innerHTML ) < parseInt( data[2] ) ) {
						freemedMessage( "<!--{t}-->New unread document(s).<!--{/t}-->", 'INFO' );
					}
					// Update display
					document.getElementById( 'taskPaneUnfiledCount' ).innerHTML = data[1];
					document.getElementById( 'taskPaneUnreadCount' ).innerHTML = data[2];
				},
				mimetype: "text/json"
			});
		},
		onUpdateNotepad: function ( ) {
			// Handle changes to the notepad
			var curNote = document.getElementById( 'notePad' ).value;
			if ( curNote != freemedGlobal.noteSave ) {
				dojo.io.bind({
					method: "POST",
					content: {
						param0: 'notePad',
						param1: curNote
					},
					url: "<!--{$relay}-->/org.freemedsoftware.api.UserInterface.SetConfigValue",
					load: function(type, data, evt) {
						if (data) {
							freemedMessage( "<!--{t}-->Notepad contents saved.<!--{/t}-->", 'INFO' );
						}
					},
					mimetype: "text/json"
				});
			}
		},
		onSystemNotifications: function ( data ) {
			for (var i=0; i<data.length; i++) {
				freemedMessage( "<a class=\"clickable\" onclick=\"freemedLoad('org.freemedsoftware.controller.patient.overview?patient=" + data[i].npatient + "');\">" + data[i].ntext + "</a>", 'MESSAGE' );
			}
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
		removePatientFromHistory: function ( id ) {
			try {
				if ( freemedGlobal.patientHistory.length > 0 ) {
					for (var i=0; i<freemedGlobal.patientHistory.length; i++) {
						if (freemedGlobal.patientHistory[i][0] == id) {
							freemedGlobal.patientHistory[i] = null;
						}
					}
				}
			} catch (err) { }
		},
		//---- Catch all "state" namespace for storing state variables
		pageHistory: [ ],
		patientHistory: [ ],
		state: { }
	};

	// Initialization
	dojo.addOnLoad(function(){
		dojo.io.bind({
			method: "POST",
			content: { },
			url: "<!--{$relay}-->/org.freemedsoftware.module.SystemNotifications.GetTimestamp",
			load: function(type, data, evt) {
				if (data) {
					// Save interval passed back
					freemedGlobal.intervalStamp = data;
					// Set window interval
					window.setInterval( freemedGlobal.intervalCallback, 1000 * freemedGlobal.interval );
				}
			},
			mimetype: "text/json"
		});
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

<div dojoType="dialog" id="freemedNotesDialog" bgOpacity="0.5" toggle="fade" toggleDuration="250" blockDuration="2000" style="display:none;" closeOnBackgroundClick="true">
	<div align="center">
	<table border="0" cellpadding="2">
		<tr>
			<td align="center" valign="middle"><h3><!--{t}-->Notes<!--{/t}--></h3></td>
		</tr>
		<tr>
			<td align="center" valign="middle"><small><i><!--{t}-->Click outside this dialog box to close the notepad.<!--{/t}--></i></small></td>
		</tr>
		<tr>
			<td valign="middle" align="center"><textarea id="notePad" rows="20" cols="80" wrap="virtual" onBlur="freemedGlobal.onUpdateNotepad();"><!--{$SESSION.authdata.user_record.usermanageopt.notePad|escape:'html'}--></textarea></td>
		</tr>
	</table>
	</div>
</div>

<div dojoType="LayoutContainer" layoutChildPriority="top-bottom" style="width: 100%; height: 100%;">
	<div dojoType="ContentPane" layoutAlign="bottom" style="background-image: url(<!--{$htdocs}-->/images/brushed.gif); color: #000000; border-top: 2px solid #000000;">

		<!-- Bottom of screen bar -->

		<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:0;">
			<tr>
				<td align="left" width="130">
					<img src="<!--{$htdocs}-->/images/FreeMEDLogoTransparent.png" alt="" height="37" width="120" border="0" />
				</td>
				<td align="middle" width="33%">
					<font size="2" face="Trebuchet MS, Geneva, Arial, Helvetica, SunSans-Regular, sans-serif">Version <!--{$VERSION}--><!--{if $svnVersion}--> (build <!--{$svnVersion}-->)<!--{/if}--></font><br/>
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
		new Array({ euImage:{ image:"<!--{$htdocs}-->/images/teak/scheduler.64x64.png" } } ),
		{ code:"freemedLoad('org.freemedsoftware.ui.scheduler');" }
	);
	dock.addIcon(
		new Array( { euImage:{ image:"<!--{$htdocs}-->/images/teak/patient_entry.64x64.png" } } ),
		{ code:"freemedLoad('org.freemedsoftware.ui.patient.form');"}
	);
	dock.addIcon(
		new Array( { euImage:{ image:"<!--{$htdocs}-->/images/Stickies.png" } } ),
		{ code:"openNotesDialog();" }
	);
	dock.addIcon(
		new Array( { euImage:{ image:"<!--{$htdocs}-->/images/teak/dashboard.64x64.png" } } ),
		{ code:"freemedLoad('org.freemedsoftware.ui.mainframe.default');" }
	);
//	dock.setScreenAlign(euDOWN, 5);
</script>
					</div>
				</td>
				<td align="right" nowrap="nowrap" valign="middle">
					<img src="<!--{$htdocs}-->/images/notes.png" alt="" width="73" height="30" border="0" id="notesButton" onClick="openNotesDialog();" />
					<img src="<!--{$htdocs}-->/images/techsupport.png" alt="" width="73" height="30" border="0" id="supportButton" />
					<img src="<!--{$htdocs}-->/images/usermanual.png" alt="" width="73" height="30" border="0" id="manualButton" onClick="openHelpPage(); return true;" />
					<img src="<!--{$htdocs}-->/images/logoff.png" alt="" width="73" height="30" border="0" id="logoffButton" onClick="freemedLogout();" />
				</td>
			</tr>
		</table>
	</div>

<!--{* ***** Content will go in here ***** *}-->

<!--{if $DEBUG}--><div id="dojoDebugOutput" /><!--{/if}-->
