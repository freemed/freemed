<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2010 FreeMED Software Foundation
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
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title>FreeMED <!--{$VERSION}--> Login</title>
	<script type="text/javascript" src="<!--{$htdocs}-->/dojo/dojo.js"></script>
	<script type="text/javascript">
		dojo.require("dojo.io.*");
		dojo.require("dojo.widget.Button");
		dojo.require("dojo.widget.Dialog");
	</script>

	<script type="text/javascript">

	function doFreemedLogin ( ) {
		dojo.io.bind({
			method : 'POST',
			content : {
				param0: dojo.byId('username').value,
				param1: dojo.byId('password').value,
				_l: dojo.widget.byId( 'language_widget' ).getValue( ),
				_f: dojo.widget.byId( 'facility_widget' ).getValue( )
			},
			url: '<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.public.Login.Validate',
			error: function(type, data, evt) {
				alert('FreeMED has encountered an error. Please try again.');
			},
			load: function(type, data, evt) {
				if (data) {
					var dlg = dojo.widget.byId("DialogContent");
					dlg.hide();
					location.href = '<!--{$base_uri}-->/controller.php/<!--{$ui}-->/org.freemedsoftware.controller.mainframe';
				} else {
					alert('You have entered an incorrect username or password, please try again.');
				}
			},
			mimetype: "text/json"
		} );
	}

	dojo.addOnLoad(function() {
		dojo.widget.byId("DialogContent").show();
		// Hack around dealing with focus issue
		window.setTimeout( 'dojo.byId("username").focus();', 500 );
		dojo.widget.byId( 'language_widget' ).setLabel( 'English' );
		dojo.widget.byId( 'language_widget' ).setValue( 'en_US' );
	});

	</script>

	<style type="text/css">
<!--{if $LOGIN_IMAGE}-->
		body {
			background-color: transparent;
			font-family : sans-serif;
			font-size: 9pt;
			overflow-x: none;
			overflow-y: none;
		}
		.backgroundImage {
			z-index: -1;
		}
		.backgroundImageDiv {
			overflow-x: none;
			overflow-y: none;
		}
<!--{else}-->
		body {
			background: url(<!--{$htdocs}-->/images/brushed.gif);
			font-family : sans-serif;
			font-size: 9pt;
		}
<!--{/if}-->
		.dojoButton {
			font-size: 10pt;
			padding : 4px;
		}
		.dojoDialog {
			background : #ffffff;
			border : 3px solid #999999;
			-moz-border-radius-topleft : 10px;
			-moz-border-radius-bottomright : 10px;
			padding : 1em;
		}
		.submitLogin {
			background-color: #ffffff;
			border: 1px solid #000000;
			padding: 3px;
			margin: 3px;
		}
		#legalNotice {
			position: absolute;
			align: center;
			left: 10%;
			text-align: center;
			bottom: 3em;
			background: #ffffff;
			color: #000000;
			width: 80%;
			border: 3px solid #aaaaaa;
			padding: 2em;
			font-size: 8pt;
		}
		form {
			margin-bottom : 0;
		}
		input {
			background-color: #ccccff;
		}
	</style>
</head>

<!--{if $LOGIN_IMAGE}-->
<body style="overflow-x: none; overflow-y: none;">
<div id="backgroundImageDiv" style="height: 98%; width: 98%;" align="center">
	<img id="backgroundImage" src="<!--{$htdocs}-->/images/<!--{$LOGIN_IMAGE}-->" border="0" alt="" style="height: 98%; width: 98%;" />
</div>
<!--{else}-->
<body>
<!--{/if}-->

<div id="legalNotice">
	<b>FreeMED</b> &copy; 1999-2010 (GPL) FreeMED Software Foundation
	( <b>http://www.freemedsoftware.org/</b> )
	<br />
    	<small><i>FreeMED comes with ABSOLUTELY NO WARRANTY. This is free
	software, and you are welcome to redistribute it under certain
	conditions; please consult a copy of the GNU Public License for
	more information.</i></small>
	<hr />
	The Multum Lexicon database from Cerner Multum, Inc has been modified for
	this application, and there is no warranty explicit or implied from either
	Cerner Multum, Inc or the FreeMED Software Foundation. More information
	is available at <b>http://www.multum.com/license.htm</b> [ Multum Lexicon
	version <b><!--{include file="../../../data/multum/multum_version"}--></b> ]
</div>

<div dojoType="dialog" id="DialogContent" bgColor="#cccccc" bgOpacity="0.5" toggle="fade" toggleDuration="250" executeScripts="true" focusElement="username">
	<form onsubmit="return false;">
		<table>
			<tr>
				<td colspan="2" align="center" valign="top">
					<a href="http://www.freemedsoftware.org/"
					><img src="<!--{$htdocs}-->/images/freemed_logo_small.png" border="0" /></a>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center" valign="top">
					<small><strong><!--{$INSTALLATION}--></strong></small>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center" valign="top">
					<small>version <!--{$VERSION}--></small>
				</td>
			</tr>
			<tr>
				<td><b><!--{t}-->Login<!--{/t}--></b></td>
				<td><input type="text" id="username" name="username" widgetId="username" style="width: 200px;" /></td>
			</tr>
			<tr>
				<td><b><!--{t}-->Password<!--{/t}--></b></td>
				<td><input type="password" id="password" name="password" style="width: 200px;" /></td>
			</tr>
			<tr>
				<td><b><!--{t}-->Location<!--{/t}--></b></td>
				<td><input dojoType="Select" value=""
					autocomplete="false"
					id="facility_widget" widgetId="facility_widget"
					setValue="if (arguments[0]) { document.getElementById('facility').value = arguments[0]; }"
					style="width: 200px;"
					dataUrl="<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.public.Login.GetLocations?param0=%{searchString}"
					mode="remote" />
				<input type="hidden" id="facility" name="facility" value="0" />
				</td>
			</tr>
			<tr>
				<td><b><!--{t}-->Language<!--{/t}--></b></td>
				<td><input dojoType="Select" value=""
					autocomplete="false"
					id="language_widget" widgetId="language_widget"
					setValue="if (arguments[0]) { document.getElementById('language').value = arguments[0]; }"
					style="width: 200px;"
					dataUrl="<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.public.Login.GetLanguages?param0=%{searchString}"
					mode="remote" />
				<input type="hidden" id="language" name="language" value="0" />
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					<input type="image" style="background: #ffffff;" src="<!--{$htdocs}-->/images/login.png" id="loginImage" border="0" onMouseOver="document.getElementById('loginImage').src='<!--{$htdocs}-->/images/login_over.png';" onMouseOut="try {document.getElementById('loginImage').src='<!--{$htdocs}-->/images/login.png'; } catch (e) { }" onClick="doFreemedLogin();" />
				</td>
			</tr>
		</table>
	</form>
</div>

</body>
</html>
