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
<title>FreeMED v<!--{$VERSION}--> : <!--{t}-->Help Browser<!--{/t}--></title>

<!--{* ***** Style Elements ***** *}-->

<link rel="stylesheet" type="text/css" src="<!--{$htdocs}-->/stylesheet.css" />
<script type="text/javascript" src="<!--{$base_uri}-->/lib/dojo/dojo.js"></script>
<script language="JavaScript" type="text/javascript">
	dojo.require("dojo.widget.LayoutContainer");
	dojo.require("dojo.widget.ContentPane");
	dojo.require("dojo.widget.Tooltip");
	dojo.hostenv.writeIncludes();
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

</head>
<body>

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
				<td align="right" nowrap="nowrap" valign="middle">
					<img src="<!--{$htdocs}-->/images/close.png" alt="" border="0" id="closeButton" onClick="window.close(); return true;" />
					<!-- Tooltips -->
					<span dojoType="tooltip" connectId="closeButton" toggle="explode" toggleDuration="100"><!--{t}-->Close Help Browser<!--{/t}--></span>
				</td>
			</tr>
		</table>
	</div>
	<div dojoType="ContentPane" layoutAlign="top" style="color: #ffffff;">
		<iframe width="100%" height="100%">
		</iframe>
	</div>
</div>

</body>
</html>

