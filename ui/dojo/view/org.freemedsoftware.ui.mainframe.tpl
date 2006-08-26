<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // Copyright (C) 1999-2006 FreeMED Software Foundation
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

<link rel="stylesheet" type="text/css" src="<!--{$htdocs}-->/stylesheet.css" />
<script type="text/javascript" src="<!--{$base_uri}-->/lib/dojo/dojo.js"></script>
<script language="JavaScript" type="text/javascript">
	dojo.require("dojo.widget.LayoutContainer");
	dojo.require("dojo.widget.ContentPane");
	dojo.require("dojo.widget.LinkPane");
	dojo.require("dojo.widget.SplitContainer");
	dojo.require("dojo.widget.Tooltip");
	dojo.require("dojo.widget.Select");
	dojo.require("dojo.widget.Dialog");
	dojo.hostenv.writeIncludes();

	//
	//	Common FreeMED UI Functions
	//

	function freemedLoad ( uri ) {
		var contentPane = dojo.widget.getWidgetById('freemedContent');
		contentPane.setUrl( "<!--{$base_uri}-->/controller.php/<!--{$ui}-->/" + uri );
//		alert("DEBUG: setting URL to <!--{$base_uri}-->/controller.php/<!--{$ui}-->/" + uri );
		return true;
	}
	function freemedPatientLoad ( patient ) {
		var contentPane = dojo.widget.getWidgetById('freemedContent');
		contentPane.setUrl( "<!--{$base_uri}-->/controller.php/<!--{$ui}-->/org.freemedsoftware.controller.patient.overview?patient=" + patient );
		return true;
	}
	function freemedLogout ( ) {
		var logoutDialog = dojo.widget.getWidgetById('freemedLogoutDialog');
		logoutDialog.show();
		dojo.io.bind({
			method : 'POST',
			url: '<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.public.Login.Logout',
			error: function(type, data, evt) {
				alert('FreeMED has encountered an error. Please try again.');
			},
			load: function(type, data, evt) {
				if (data) {
					logoutDialog.hide();
					location.href = '<!--{$base_uri}-->/controller.php/<!--{$ui}-->/org.freemedsoftware.ui.login';	
				} else {
					logoutDialog.hide();
				}
			},
			mimetype: "text/json"
		});
		freemedLoad( "org.freemedsoftware.view.login" );
		return true;
	}
</script>
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
	.dojoSplitPane { margin: 5px; }
	.dojoToolTip { background: #ccccff; padding: 2px; }
	.dojoDialog { 
		width: 30%;
		border: 3px solid #555555;
		-moz-border-radius-topleft: 10px;
		-moz-border-radius-bottomright: 10px;
		background-color: #ffffff;
		padding: 2em;
	}
	#rightPane { margin: 0; }
</style>
</head>
<body>
<div dojoType="LayoutContainer" layoutChildPriority="top-bottom" style="width: 100%; height: 100%;">
	<div dojoType="ContentPane" layoutAlign="top" style="background-image: url(<!--{$htdocs}-->/images/brushed.gif); color: white;">

		<!-- Top of the screen bar -->

		<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:0;">
			<tr>
				<td align="left">
       					<img src="<!--{$htdocs}-->/images/UMIheaderbutton.png" alt="" height="56" width="236" border="0" />
				</td>
				<td align="right">
	                                <img src="<!--{$htdocs}-->/appframeimages/activeoffice.png" alt="" height="51" width="216" border="0" />
				</td>
		</table>
	</div>
	<div dojoType="ContentPane" layoutAlign="bottom" style="background-image: url(<!--{$htdocs}-->/images/brushed.gif); color: #000000;">

		<!-- Bottom of screen bar -->

		<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:0;">
			<tr>
				<td align="left" width="130">
					<img src="<!--{$htdocs}-->/images/FreeMEDLogoTransparent.png" alt="" height="37" width="120" border="0" />
				</td>
				<td align="middle" width="33%">
					<font size="2" face="Trebuchet MS, Geneva, Arial, Helvetica, SunSans-Regular, sans-serif">Version <!--{$VERSION}--></font>
				</td>
				<td align="middle" width="33%">
					<font size="1" face="Trebuchet MS, Geneva, Arial, Helvetica, SunSans-Regular, sans-serif">&copy; 1999-2006 by the FreeMED Software Foundation</font>
				</td>
				<td align="right" nowrap="nowrap" valign="middle">
					<img src="<!--{$htdocs}-->/images/techsupport.png" alt="" width="73" height="30" border="0" id="supportButton"/><img src="<!--{$htdocs}-->/images/usermanual.png" alt="" width="73" height="30" border="0" id="manualButton" /><img src="<!--{$htdocs}-->/images/logoff.png" alt="" width="73" height="30" border="0" id="logoffButton" onClick="freemedLogout();" />
					<!-- Tooltips -->
					<span dojoType="tooltip" connectId="supportButton" toggle="explode" toggleDuration="100">Access technical support</span>
					<span dojoType="tooltip" connectId="manualButton" toggle="explode" toggleDuration="100">View online FreeMED documentation</span>
					<span dojoType="tooltip" connectId="logoffButton" toggle="explode" toggleDuration="100">Terminate your current FreeMED session</span>
				</td>
			</tr>
		</table>
	</div>
	<div dojoType="SplitContainer"
		orientation="horizontal"
		sizerWidth="5"
		activeSizing="0"
		layoutAlign="client"
	>
		<div dojoType="SplitContainer"
			id="leftPane"
			orientation="vertical"
			sizerWidth="5"
			activeSizing="0"
			sizeMin="50" sizeShare="80"
		>
			<!-- this pane contains the actual application -->
			<div id="freemedContent" dojoType="ContentPane" sizeMin="20" sizeShare="70" href="<!--{$base_uri}-->/controller.php/<!--{$ui}-->/org.freemedsoftware.controller.mainframe?piece=defaultpane"></div>
			<div dojoType="ContentPane" sizeMin="5" sizeShare="5">
				<div id="statusBar" style="background: #ccccff; border: 1px solid #555555; -moz-border-radius: 5px;" align="center">
					<b>User</b> : <!--{method namespace=org.freemedsoftware.api.UserInterface.GetCurrentUsername}--> |
					<b>Messages</b> : <span onClick="freemedLoad('org.freemedsoftware.messages');"><!--{method namespace=org.freemedsoftware.api.UserInterface.GetNewMessages}--></span>
				</div>
			</div>
		</div>
		<div dojoType="LinkPane" id="rightPane" style="width: 250px; background-image: url(<!--{$htdocs}-->/images/stipedbg.png); overflow: auto;" href="<!--{$base_uri}-->/controller.php/<!--{$ui}-->/org.freemedsoftware.controller.mainframe?piece=links" layoutAlign="right"></div>
	</div>
</div>
<div dojoType="dialog" id="freemedLogoutDialog" bgOpacity="0.5" toggle="fade" toggleDuration="250" blockDuration="2000">
	<table border="0" cellpadding="5">
		<tr>
			<td valign="middle"><img src="<!--{$htdocs}-->/images/loading.gif" border="0" /></td>
			<td valign="middle"><b> Logging out of FreeMED </b></td>
		</tr>
	</table>
</div>
</body>
</html>
