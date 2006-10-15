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
<html>
<head>
	<title><!--{t}-->FreeMED Installation Wizard<!--{/t}--></title>
	<script type="text/javascript" src="<!--{$base_uri}-->/lib/dojo/dojo.js"></script>
	<script type="text/javascript">
		dojo.require("dojo.io.*");
		dojo.require("dojo.widget.Wizard");
		dojo.require("dojo.widget.Tooltip");
		dojo.require("dojo.widget.Select");
		dojo.require("dojo.widget.Button");
		dojo.require("dojo.widget.TimePicker");
	</script>

	<style type="text/css">

		/* Special display stuff */
		body {
			background-image: url(<!--{$htdocs}-->/images/stipedbg.png);
			margin: 2em;
			}
		h1 {
			font-variant: small-caps;
			}
		.description {
			font-style: italic;
			padding-bottom: 2em;
			}

		/* Hide minutes for time selection and "any" container */
		.minutes, .minutesHeading, .anyTimeContainer { display: none; }

	</style>
</head>
<body>

<img src="<!--{$htdocs}-->/images/FreeMEDLogoTransparent.png" border="0" />

<br/>

<script language="javascript">

	function verifyDatabasePane ( ) {
		var okay = true;
		var messages = '';

		if ( ! document.getElementById('name').value ) {
			okay = false;
			messages += "<!--{t}-->Database name has not been set.<!--{/t}-->\n";
		}
		if ( ! document.getElementById('username').value ) {
			okay = false;
			messages += "<!--{t}-->Username has not been set.<!--{/t}-->\n";
		}
		if ( ! document.getElementById('password').value ) {
			okay = false;
			messages += "<!--{t}-->Password has not been set.<!--{/t}-->\n";
		}
		if ( document.getElementById('password').value != document.getElementById('password_confirm').value ) {
			okay = false;
			messages += "<!--{t}-->Password do not match.<!--{/t}-->\n";
		}

		// If something is wrong already, don't run check.
		if ( okay ) {
			// Functional check; needs to run synchronous, otherwise we
			// won't be able to wait for the status of 'okay' variable.
			dojo.io.bind({
				method : 'POST',
				sync : true,
				url: '<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.public.Installation.CheckDbCredentials',
				content: {
					param0: document.getElementById('host').value,
					param1: document.getElementById('name').value,
					param2: document.getElementById('username').value,
					param3: document.getElementById('password').value
				},
				error: function(type, data, evt) {
					alert("<!--{t}-->Please try again, data error.<!--{/t}-->");
				},
				load: function(type, data, evt) {
					if (!data) {
						okay = false;
						messages += "<!--{t}-->Credentials are not valid.<!--{/t}-->\n";
					}
				},
				mimetype: "text/json"
			});
		}

		if (!okay) { return messages; }
	} // end function verifyDatabasePane

	function done ( ) {
		alert('done');
	} // end function done

</script>

<div id="installWizard" dojoType="WizardContainer" style="width: 95%; height: 80%;" nextButtonLabel="<!--{t}-->Next<!--{/t}--> &gt;&gt;" previousButtonLabel="&lt;&lt; <!--{t}-->Previous<!--{/t}-->" cancelButtonLabel="<!--{t}-->Cancel<!--{/t}-->" cancelFunction="cancel" >
	<div dojoType="WizardPane" label="Welcome to FreeMED" passFunction="initialCheck">
		<h1><!--{t}-->Welcome to FreeMED!<!--{/t}--></h1>

		<p>
		<!--{t escape="no"}-->Thank you for choosing <b>FreeMED</b> as your electronic medical record / practice management system. <b>FreeMED</b> is an opensource program, and is located on the web at <a href="http://www.freemedsoftware.org/" >http://www.freemedsoftware.org/</a>.<!--{/t}-->
		</p>

		<p>
		<!--{t escape="no"}-->This wizard will help you set up your initial <b>FreeMED</b> install by asking you questions. Please make sure that your <b>FreeMED</b> installation is writeable by your webserver user.<!--{/t}-->
		<!--{t escape="no" 1=$webroot 2=$webuser}-->( We think that your webserver root is "<b>%1</b>" and your webserver user is "<b>%2</b>". )<!--{/t}-->
		</p>

		<p>
		<!--{if $configwrite}-->
		<!--{t escape="no"}-->We have detected that your <b>lib/settings.php</b> file is writable, and that the installation may continue.<!--{/t}-->
		<script language="javascript">
			// Config file exists, leave null stub to keep from funny errors.
			function initialCheck ( ) { }
		</script>
		<!--{else}-->
		<span style="color: #ff0000;">
		<!--{t escape="no"}-->We have detected that your <b>lib/settings.php</b> file is NOT writable, and that the installation cannot continue. Please change the permissions on this file by issuing the following commands as root:<!--{/t}-->
		</span>
		<pre>
	touch <!--{$webroot}-->/lib/settings.php
	chown <!--{$webuser}--> <!--{$webroot}-->/lib/settings.php
	chmod +w <!--{$webroot}-->/lib/settings.php
		</pre>
		<script language="javascript">
			function initialCheck ( ) {
				return "<!--{t}-->Please follow the instructions on this pane and reload the wizard to proceed.<!--{/t}-->";
			} // end function initialCheck
		</script>
		<!--{/if}-->
		</p>
	</div>

	<div dojoType="WizardPane" label="Database Configuration" passFunction="verifyDatabasePane">
		<h1><!--{t}-->Database Configuration<!--{/t}--></h1>

		<div class="description">
		<!--{t}-->Please configure these options to reflect your SQL server configuration.<!--{/t}-->
		</div>

		<table border="0">
			<tr>
				<td align="right"><!--{t}-->Database Engine<!--{/t}--></td>
				<td align="left">
					<select dojoType="Select" id="engine" name="engine">
						<option value="mysql" selected>mysql</option>
					</select>
				</td>
			</tr>
			<tr>
				<td align="right"><!--{t}-->Host<!--{/t}--></td>
				<td align="left"><input type="input" id="host" name="host" value="localhost" /></td>
			</tr>
			<tr>
				<td align="right"><!--{t}-->Username<!--{/t}--></td>
				<td align="left"><input type="input" id="username" name="username" /></td>
			</tr>
			<tr>
				<td align="right"><!--{t}-->Password<!--{/t}--></td>
				<td align="left"><input type="password" id="password" name="password" /></td>
			</tr>
			<tr>
				<td align="right"><!--{t}-->Password (confirm)<!--{/t}--></td>
				<td align="left"><input type="password" id="password_confirm" name="password_confirm" /></td>
			</tr>
			<tr>
				<td align="right"><!--{t}-->Database Name<!--{/t}--></td>
				<td align="left"><input type="input" id="name" name="name" /></td>
			</tr>
			<tr>
				<td align="right"><!--{t}--><!--{/t}--></td>
				<td align="left"></td>
			</tr>
		</table>

		<!-- Tooltips for database pane -->

		<span dojoType="tooltip" connectId="engine" toggle="explode" toggleDuration="100"><!--{t escape="no"}-->Select the database engine which is to be used by <b>FreeMED</b>. <b>mysql</b> is the default.<!--{/t}--></span>
		<span dojoType="tooltip" connectId="host" toggle="explode" toggleDuration="100"><!--{t escape="no"}-->Hostname for the SQL server. This is usually "<b>localhost</b>" for most installs.<!--{/t}--></span>
		<span dojoType="tooltip" connectId="username" toggle="explode" toggleDuration="100"><!--{t escape="no"}--><!--{/t}--></span>
		<span dojoType="tooltip" connectId="password" toggle="explode" toggleDuration="100"><!--{t escape="no"}--><!--{/t}--></span>
		<span dojoType="tooltip" connectId="password_confirm" toggle="explode" toggleDuration="100"><!--{t}--><!--{/t}--></span>

		<script language="javascript">
		dojo.addOnLoad( function () {
				// Set defaults
			dojo.widget.byId('engine').setLabel('mysql');
			dojo.widget.byId('engine').setValue('mysql');
		} );
		</script>
	</div>

	<div dojoType="WizardPane" label="System Configuration" passFunction="verifySystemPane">
		<h1><!--{t}-->System Configuration<!--{/t}--></h1>

		<div>
		<!--{t}-->Please choose the configuration options which best fit your system needs.<!--{/t}-->	
		</div>

		<table border="0">
			<tr>
				<td align="right"><!--{t}-->Scheduler Start Time<!--{/t}--></td>
				<td align="left"><div name="starttime" dojoType="TimePicker"></div></td>
			</tr>
			<tr>
				<td align="right"><!--{t}-->Scheduler End Time<!--{/t}--></td>
				<td align="left"><div name="endtime" dojoType="TimePicker"></div></td>
			</tr>
			<tr>
				<td align="right"><!--{t}--><!--{/t}--></td>
				<td align="left"></td>
			</tr>
		</table>
	</div>

	<div dojoType="WizardPane" label="Confirm Configuration" doneFunction="done">
		Confirm config pane contents
	</div>
</div>

</body>
</html>
