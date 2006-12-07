<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
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

<!--{* ***** Style Elements ***** *}-->

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
	.euDockBar { z-index: 1000; }
	#rightPane { margin: 0; }
</style>

<!-- Include dock -->
<script language="javascript" src="<!--{$htdocs}-->/euDock/euDock.2.0.js"></script>
<script language="javascript" src="<!--{$htdocs}-->/euDock/euDock.Image.js"></script>

</head>
<body>

	<!--{* ***** Hidden things ***** *}-->

<div dojoType="dialog" id="freemedLoadingDialog" bgOpacity="0.5" toggle="fade" toggleDuration="500" blockDuration="2000">
	<table border="0" cellpadding="5">
		<tr>
			<td valign="middle"><img src="<!--{$htdocs}-->/images/loading.gif" border="0" /></td>
			<td valign="middle"><b> Loading ... </b></td>
		</tr>
	</table>
</div>

<div dojoType="dialog" id="freemedLogoutDialog" bgOpacity="0.5" toggle="fade" toggleDuration="250" blockDuration="2000">
	<table border="0" cellpadding="5">
		<tr>
			<td valign="middle"><img src="<!--{$htdocs}-->/images/loading.gif" border="0" /></td>
			<td valign="middle"><b> Logging out of FreeMED </b></td>
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
					<font size="1" face="Trebuchet MS, Geneva, Arial, Helvetica, SunSans-Regular, sans-serif">&copy; 1999-2006 by the FreeMED Software Foundation</font>
				</td>
				<td align="middle" width="33%">
					<div id="euDockContainer">
					<script language="javascript">
//					euEnv.imageBasePath="<!--{$htdocs}-->/";
					var dock = new euDock ();
					dock.setBar({
						left      :{euImage:{image:"<!--{$htdocs}-->/barImages/dockBg-l.png"}},
						horizontal:{euImage:{image:"<!--{$htdocs}-->/barImages/dockBg-c-o.gif"}},
						right     :{euImage:{image:"<!--{$htdocs}-->/barImages/dockBg-r.png"}}

					});
					dock.setIconsOffset(5);
dock.addIcon(new Array({euImage:{image:"<!--{$htdocs}-->/images/Quick-Cal.png"}}),
{code:"freemedLoad('org.freemedsoftware.ui.user.form');"});
dock.addIcon(new Array({euImage:{image:"<!--{$htdocs}-->/images/Stocks.png"}}),
{link:"<!--{$base_uri}-->/controller.php/<!--{$ui}-->/org.freemedsoftware.ui.billing"});
dock.addIcon(new Array({euImage:{image:"<!--{$htdocs}-->/images/Rolodex.png"}}),
{link:"http://eudock.jules.it"});
dock.addIcon(new Array({euImage:{image:"<!--{$htdocs}-->/images/Stickies.png"}}),
{link:"http://eudock.jules.it"});
dock.addIcon(new Array({euImage:{image:"<!--{$htdocs}-->/images/Rolodex.png"}}),
{link:"http://eudock.jules.it"});
dock.addIcon(new Array({euImage:{image:"<!--{$htdocs}-->/images/Rolodex.png"}}),
{link:"http://eudock.jules.it"});
					dock.setScreenAlign(euTOP, 5);
					</script>
					</div>
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

<!--{* ***** Content will go in here ***** *}-->

