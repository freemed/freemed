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
<html>
<head>
	<title><!--{t}-->FreeMED Installation Wizard<!--{/t}--></title>
	<script type="text/javascript" src="<!--{$base_uri}-->/lib/dojo/dojo.js"></script>
	<script type="text/javascript">
		dojo.require("dojo.widget.Wizard");
		dojo.require("dojo.widget.Tooltip");
		dojo.require("dojo.widget.Button");
	</script>

	<style type="text/css">
		body {
			background-image: url(<!--{$htdocs}-->/images/stipedbg.png);
		}
	</style>
</head>
<body>

<img src="<!--{$htdocs}-->/images/FreeMEDLogoTransparent.png" border="0" />

<br/>

<script language="javascript">

	function verifyDatabasePane ( ) {
		var okay = true;
		var messages = '';

		if (! document.getElementById('test').checked ) {
			okay = false;
			messages += "Failed to set test\n";
		}

		if (!okay) { return messages; }
	} // end function verifyDatabasePane

</script>

<div id="installWizard" dojoType="WizardContainer" style="width: 95%; height: 80%;" nextButtonLabel="Next &gt;&gt;" previousButtonLabel="&lt;&lt; Previous" cancelButtonLabel="Cancel" cancelFunction="cancel" >
	<div dojoType="WizardPane" label="Welcome to FreeMED">
		<h1>Welcome to FreeMED!</h1>

		<p>
		Thank you for choosing <b>FreeMED</b> as your electronic medical record /
		practice management system. <b>FreeMED</b> is an opensource program, and
		is located on the web at <a href="http://www.freemedsoftware.org/"
		>http://www.freemedsoftware.org/</a>.
		</p>

		<p>
		This wizard will help you set up your initial <b>FreeMED</b> install by
		asking you questions. Please make sure that your <b>FreeMED</b>
		installation is writeable by your webserver user. ( We think that your
		webserver root is "<b><!--{$webroot}--></b>" and your webserver user is
		"<b><!--{$webuser}--></b>". )
		</p>
	</div>

	<div dojoType="WizardPane" label="Database Configuration" passFunction="verifyDatabasePane">
		<h1>Database Configuration</h1>
		Database config pane contents
		<input type="checkbox" name="test" id="test" value="1" />
	</div>

	<div dojoType="WizardPane" label="Confirm Configuration" doneFunction="done">
		Confirm config pane contents
	</div>
</div>

</body>
</html>
