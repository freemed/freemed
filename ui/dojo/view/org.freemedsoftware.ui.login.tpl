{* Smarty *}
{*
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
*}
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title>FreeMED {$VERSION} :: Login</title>
	<script type="text/javascript" src="{$base_uri}/lib/dojo/dojo.js"></script>
	<script type="text/javascript">
		dojo.require("dojo.io.*");
		dojo.require("dojo.widget.Button");
		dojo.require("dojo.widget.Dialog");
	</script>

	<script type="text/javascript">
	var dlg;
	function initLogin(e) {literal} { {/literal}
		dlg = dojo.widget.byId("DialogContent");
		dlg.show();
	{literal} } {/literal}

	function doFreemedLogin ( ) {literal} { {/literal}
		var params = new Array ( );
		dojo.io.bind({literal} { {/literal}
			method : 'POST',
			content : {literal} { {/literal}
				param0: dojo.byId('username').value,
				param1: dojo.byId('password').value
			{literal} } {/literal},
			url: '{$base_uri}/relay.php/json/org.freemedsoftware.public.Login.Validate',
			error: function(type, data, evt) {literal} { {/literal}
				alert('FreeMED has encountered an error. Please try again.');
			{literal} } {/literal},
			load: function(type, data, evt) {literal} { {/literal}
				if (data) {literal} { {/literal}
					dlg = dojo.widget.byId("DialogContent");
					dlg.hide();
					location.href = '{$base_uri}/controller.php/{$ui}/org.freemedsoftware.controller.mainframe';	
				{literal} } else { {/literal}
					alert('You have entered an incorrect username or password, please try again.');
				{literal} } {/literal}
			{literal} } {/literal},
			mimetype: "text/json"
		{literal} } {/literal} );
	{literal} } {/literal}

	dojo.addOnLoad(initLogin);
	</script>

	<style type="text/css">
		{literal} body { {/literal}
			background: url({$htdocs}/images/brushed.gif);
			font-family : sans-serif;
			font-size: 10pt;
		{literal} } {/literal}
		{literal} .dojoButton { {/literal}
			font-size: 10pt;
			padding : 4px;
		{literal} } {/literal}
		{literal} .dojoDialog { {/literal}
			background : #ffffff;
			border : 3px solid #999999;
			-moz-border-radius-topleft : 10px;
			-moz-border-radius-bottomright : 10px;
			padding : 1em;
		{literal} } {/literal}
		{literal} .submitLogin { {/literal}
			background-color: #ffffff;
			border: 1px solid #000000;
			padding: 3px;
			margin: 3px;
		{literal} } {/literal}
		{literal} form { {/literal}
			margin-bottom : 0;
		{literal} } {/literal}
	</style>
</head>
<body>

<div dojoType="dialog" id="DialogContent" bgColor="#cccccc" bgOpacity="0.5" toggle="fade" toggleDuration="250">
	<form onsubmit="return false;">
		<table>
			<tr>
				<td colspan="2" align="center" valign="top">
					<a href="http://www.freemedsoftware.org/"
					><img src="{$htdocs}/images/freemed_logo_small.png" border="0" /></a>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center" valign="top">
					<small><strong>{$INSTALLATION}</strong></small>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center" valign="top">
					<small>version {$VERSION}</small>
				</td>
			</tr>
			<tr>
				<td><b>Login</b></td>
				<td><input type="text" id="username" name="username" /></td>
			</tr>
			<tr>
				<td><b>Password</b></td>
				<td><input type="password" id="password" name="password" /></td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					<img src="{$htdocs}/images/login.png" id="loginImage" border="0" onMouseOver="document.getElementById('loginImage').src='{$htdocs}/images/login_over.png';" onMouseOut="document.getElementById('loginImage').src='{$htdocs}/images/login.png';" onClick="doFreemedLogin();" />
				</td>
			</tr>
		</table>
	</form>
</div>

</body>
</html>
