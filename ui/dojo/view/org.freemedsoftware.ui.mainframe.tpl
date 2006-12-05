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

<!--{include file="org.freemedsoftware.ui.framework.tpl"}-->

<script language="javascript">
	function freemedPatientLoad ( patient ) {
		var contentPane = dojo.widget.getWidgetById('freemedContent');
		contentPane.setUrl( "<!--{$base_uri}-->/controller.php/<!--{$ui}-->/org.freemedsoftware.controller.patient.overview?patient=" + patient );
		return true;
	}
</script>
<!-- Include dock -->
<script language="javascript" src="<!--{$htdocs}-->/euDock/euDock.2.0.js"></script>
<script language="javascript" src="<!--{$htdocs}-->/euDock/euDock.Image.js"></script>
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
					dock.setIconsOffset(2);
dock.addIcon(new Array({euImage:{image:"<!--{$htdocs}-->/iconsEuDock/cube.png"}}),
{code:"freemedLoad('org.freemedsoftware.ui.user.form');"});
dock.addIcon(new Array({euImage:{image:"<!--{$htdocs}-->/iconsEuDock/cube.png"}}),
{link:"http://eudock.jules.it"});
dock.addIcon(new Array({euImage:{image:"<!--{$htdocs}-->/iconsEuDock/cube.png"}}),
{link:"http://eudock.jules.it"});
dock.addIcon(new Array({euImage:{image:"<!--{$htdocs}-->/iconsEuDock/cube.png"}}),
{link:"http://eudock.jules.it"});
dock.addIcon(new Array({euImage:{image:"<!--{$htdocs}-->/iconsEuDock/cube.png"}}),
{link:"http://eudock.jules.it"});
dock.addIcon(new Array({euImage:{image:"<!--{$htdocs}-->/iconsEuDock/cube.png"}}),
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
	<div dojoType="SplitContainer"
		orientation="horizontal"
		sizerWidth="5"
		activeSizing="0"
		layoutAlign="client"
	>
		<!-- this pane contains the actual application -->
		<div id="freemedContent" dojoType="ContentPane" executeScripts="true" sizeMin="20" sizeShare="70" href="<!--{$base_uri}-->/controller.php/<!--{$ui}-->/org.freemedsoftware.controller.mainframe?piece=defaultpane"></div>
		<div dojoType="LinkPane" id="rightPane" style="width: 250px; background-image: url(<!--{$htdocs}-->/images/stipedbg.png); overflow: auto;" href="<!--{$base_uri}-->/controller.php/<!--{$ui}-->/org.freemedsoftware.controller.mainframe?piece=links" layoutAlign="right"></div>
	</div>
</div>

<!--{include file="org.freemedsoftware.ui.footer.tpl"}-->

