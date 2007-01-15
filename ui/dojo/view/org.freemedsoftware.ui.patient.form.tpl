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

<!--{if $MODE ne 'widget'}-->
<!--{include file="org.freemedsoftware.ui.framework.tpl"}-->
<!--{/if}-->

<style type="text/css">

	/* Force dojo buttons to have some padding */
	.dojoButtonContents div { padding: 5px; }

</style>

<script type="text/javascript">

	dojo.require("dojo.widget.Form");
	dojo.require("dojo.widget.TabContainer");
//	dojo.require("dojo.widget.Tooltip");
	dojo.require("dojo.widget.ContentPane");
	dojo.require("dojo.widget.Button");
	dojo.require("dojo.widget.InternetTextbox");
	dojo.require("dojo.widget.UsTextbox");

	function patientFormTabTo( tabpane ) {
		if ( event.keyCode == 9 ) {
			dojo.widget.byId('mainTabContainer').selectTab( tabpane );
		}
	} // end patientFormTabTo

	function patientFormCommitChanges ( ) {
		// Verify form
		var message = '';
		if (document.getElementById('ptfname').value.length <= 3) {
			message += "No first name\n";
		}

		if (message.length > 0) {
			alert(message);
			return false;
		}

		// Determine action
		var action = 'add'
		try {
			if (document.getElementById('id').value) {
				action = 'mod';
			}
		} catch (err) { }

		dojo.io.bind({
			method : 'POST',
			url: '<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.module.PatientModule.' + action,
			content: {
				param0: dojo.widget.byId('patientForm').getValues()
			},
			error: function(type, data, evt) {
				alert('FreeMED has encountered an error. Please try again.');
			},
			load: function(type, data, evt) {
				if (data) {
					if ((data + 0) > 0) {
						freemed
					}
				} else {
					alert('<!--{t}-->The transaction has failed. Please try again or contact your system administrator.<!--{/t}-->');
				}
			},
			mimetype: "text/json"
		});
	} // end patientFormCommitChanges

</script>

<form dojoType="Form" id="patientForm" style="height: auto;">

<div dojoType="TabContainer" id="mainTabContainer" style="width: 100%; height: 20em;">
	<div dojoType="ContentPane" id="patientDemographicsPane" label="Demographics">
	<table style="border: 0; padding: 1em;">

	<tr>
		<td><!--{t}-->Name (Last, First Middle)<!--{/t}--></td>
		<td>
			<select dojoType="Select" id="ptsalut" name="ptsalut" style="width: 100px;" autocomplete="false" name="ptsalut">
				<option value="">--</option>
				<option value="Mr" <!--{if $record.ptsalut == 'Mr'}-->selected<!--{/if}-->>Mr</option>
				<option value="Mrs" <!--{if $record.ptsalut == 'Mrs'}-->selected<!--{/if}-->>Mrs</option>
				<option value="Ms" <!--{if $record.ptsalut == 'Ms'}-->selected<!--{/if}-->>Ms</option>
				<option value="Dr" <!--{if $record.ptsalut == 'Dr'}-->selected<!--{/if}-->>Dr</option>
				<option value="Fr" <!--{if $record.ptsalut == 'Fr'}-->selected<!--{/if}-->>Fr</option>
			</select>
			<input type="text" id="ptlname" name="ptlname" value="<!--{$record.ptlname|escape}-->" size="20" maxlength="50" /> <b>,</b>
			<input type="text" id="ptfname" name="ptfname" value="<!--{$record.ptfname|escape}-->" size="20" maxlength="50" />
			<input type="text" id="ptmname" name="ptmname" value="<!--{$record.ptmname|escape}-->" size="10" />
		</td>
	</tr>
	
	<tr>
		<td valign="top"><!--{t}-->Address<!--{/t}--></td>
		<td valign="top">
			<input type="text" id="ptaddr1" name="ptaddr1" value="<!--{$record.ptaddr1|escape}-->" size="50" maxlength="50" /><br/>
			<input type="text" id="ptaddr2" name="ptaddr2" value="<!--{$record.ptaddr2|escape}-->" size="50" maxlength="50" /><br/>
		</td>
	</tr>

	<tr>
		<td valign="top"><!--{t}-->City, State Zip<!--{/t}--></td>
		<td>
			<input dojoType="Select"
			autocomplete="false"
			id="ptcsz_widget"
			style="width: 300px;"
			dataUrl="<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.module.Zipcodes.CityStateZipPicklist?param0=%{searchString}"
			setValue="document.getElementById('ptcsz').value = arguments[0];"
			mode="remote" />
			<input type="hidden" id="ptcsz" name="ptcsz" value="<!--{$record.ptcity|escape}-->, <!--{$record.ptstate|escape}--> <!--{$record.ptzip|escape}-->" />
		</td>
	</tr>

	<tr>
		<td><!--{t}-->Gender<!--{/t}--></td>
		<td>
			<select dojoType="Select" style="width: 100px;" autocomplete="false" id="ptsex" name="ptsex" widgetId="ptsex">
				<option value=""></option>
				<option value="f" <!--{if $record.ptsex == 'f'}-->selected<!--{/if}-->>Female</option>
				<option value="m" <!--{if $record.ptsex == 'm'}-->selected<!--{/if}-->>Male</option>
			</select>
		</td>
	</tr>

	<tr>
		<td><!--{t}-->Date of Birth<!--{/t}--></td>
		<td><div dojoType="DropdownDatePicker" id="ptdob" widgetId="ptdob" name="ptdob" date="<!--{$record.ptdob}-->" containerToggle="wipe"></div></td>
	</tr>

	</table>
	</div>

	<div dojoType="ContentPane" id="patientContactPane" label="Contact">
	<table style="border: 0; padding: 1em;">
	<tr>
		<td><!--{t}-->Home Phone<!--{/t}--></td>
		<td><input dojoType="UsPhoneNumberTextbox" type="text" name="pthphone" id="pthphone" size="16" maxlength="16" value="<!--{$record.pthphone|escape}-->" /></td>
	</tr>
	<tr>
		<td><!--{t}-->Work Phone<!--{/t}--></td>
		<td><input dojoType="UsPhoneNumberTextbox" type="text" name="ptwphone" id="ptwphone" size="16" maxlength="16" value="<!--{$record.ptwphone|escape}-->" /></td>
	</tr>
	<tr>
		<td><!--{t}-->Fax Phone<!--{/t}--></td>
		<td><input dojoType="UsPhoneNumberTextbox" type="text" name="ptfax" id="ptfax" size="16" maxlength="16" value="<!--{$record.ptfax|escape}-->" /></td>
	</tr>
	<tr>
		<td><!--{t}-->Mobile Phone<!--{/t}--></td>
		<td><input dojoType="UsPhoneNumberTextbox" type="text" name="ptmphone" id="ptmphone" size="16" maxlength="16" value="<!--{$record.ptmphone|escape}-->" /></td>
	</tr>
	<tr>
		<td><!--{t}-->Email Address<!--{/t}--></td>
		<td><input dojoType="EmailTextbox" type="text" name="ptemail" id="ptemail" size="50" maxlength="50" value="<!--{$record.ptemail|escape}-->" /></td>
	</tr>
	</table>
	</div>

	<div dojoType="ContentPane" id="patientPersonalPane" label="Personal">
	<table style="border: 0; padding: 1em;">
	<tr>
		<td><!--{t}-->Marital Status<!--{/t}--></td>
		<td>
			<select dojoType="Select" style="width: 100px;" autocomplete="false" name="ptmarital">
				<option value="single" <!--{if $record.ptmarital == 'single'}-->selected<!--{/if}-->><!--{t}-->Single<!--{/t}--></option>
				<option value="married" <!--{if $record.ptmarital == 'married'}-->selected<!--{/if}-->><!--{t}-->Married<!--{/t}--></option>
				<option value="divorced" <!--{if $record.ptmarital == 'divorced'}-->selected<!--{/if}-->><!--{t}-->Divorced<!--{/t}--></option>
				<option value="separated" <!--{if $record.ptmarital == 'separated'}-->selected<!--{/if}-->><!--{t}-->Separated<!--{/t}--></option>
				<option value="windowed" <!--{if $record.ptmarital == 'windowed'}-->selected<!--{/if}-->><!--{t}-->Windowed<!--{/t}--></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><!--{t}-->Employment Status<!--{/t}--></td>
		<td>
			<select dojoType="Select" style="width: 100px;" autocomplete="false" name="ptempl">
				<option value="u" <!--{if $record.ptempl == 'u'}-->selected<!--{/if}-->><!--{t}-->Unknown<!--{/t}--></option>
				<option value="y" <!--{if $record.ptempl == 'y'}-->selected<!--{/if}-->><!--{t}-->Yes<!--{/t}--></option>
				<option value="n" <!--{if $record.ptempl == 'n'}-->selected<!--{/if}-->><!--{t}-->No<!--{/t}--></option>
				<option value="p" <!--{if $record.ptempl == 'p'}-->selected<!--{/if}-->><!--{t}-->Part<!--{/t}--></option>
				<option value="s" <!--{if $record.ptempl == 's'}-->selected<!--{/if}-->><!--{t}-->Self<!--{/t}--></option>
				<option value="r" <!--{if $record.ptempl == 'r'}-->selected<!--{/if}-->><!--{t}-->Retired<!--{/t}--></option>
				<option value="m" <!--{if $record.ptempl == 'm'}-->selected<!--{/if}-->><!--{t}-->Military<!--{/t}--></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><!--{t}-->Social Security Number<!--{/t}--></td>
		<td><input dojoType="UsSocialSecurityNumberTextbox" type="text" name="ptssn" id="ptssn" size="10" maxlength="9" value="<!--{$record.ptssn|escape}-->" /></td>
	</tr>
	<tr>
		<td><!--{t}-->Drivers License<!--{/t}--></td>
		<td><input type="text" name="ptdmv" id="ptdmv" size="10" maxlength="9" value="<!--{$record.ptdmv|escape}-->" /></td>
	</tr>
	<tr>
		<td><!--{t}--><!--{/t}--></td>
		<td></td>
	</tr>
	<tr>
		<td><!--{t}--><!--{/t}--></td>
		<td></td>
	</tr>
	</table>
	</div>

	<div dojoType="ContentPane" id="patientProviderPane" label="Provider">
	<table style="border: 0; padding: 1em;">
	<tr>
		<td><!--{t}-->In House Provider<!--{/t}--></td>
		<td>
			<input dojoType="Select"
			autocomplete="false"
			id="ptdoc_widget"
			style="width: 300px;"
			dataUrl="<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.module.ProviderModule.picklist?param0=%{searchString}"
			setValue="document.getElementById('ptdoc').value = arguments[0];"
			mode="remote" />
			<input type="hidden" id="ptdoc" name="ptdoc" value="<!--{$record.ptdoc|escape}-->" />
		</td>
	</tr>
	<tr>
		<td><!--{t}-->Referring Provider<!--{/t}--></td>
		<td>
			<input dojoType="Select"
			autocomplete="false"
			id="ptref_widget"
			style="width: 300px;"
			dataUrl="<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.module.ProviderModule.picklist?param0=%{searchString}"
			setValue="document.getElementById('ptref').value = arguments[0];"
			mode="remote" />
			<input type="hidden" id="ptref" name="ptref" value="<!--{$record.ptref|escape}-->" />
		</td>
	</tr>
	<tr>
		<td><!--{t}-->Primary Care Provider<!--{/t}--></td>
		<td>
			<input dojoType="Select"
			autocomplete="false"
			id="ptpcp_widget"
			style="width: 300px;"
			dataUrl="<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.module.ProviderModule.picklist?param0=%{searchString}"
			setValue="document.getElementById('ptpcp').value = arguments[0];"
			mode="remote" />
			<input type="hidden" id="ptpcp" name="ptpcp" value="<!--{$record.ptpcp|escape}-->" />
		</td>
	</tr>
	<tr>
		<td><!--{t}--><!--{/t}--></td>
		<td></td>
	</tr>
	<tr>
		<td><!--{t}--><!--{/t}--></td>
		<td></td>
	</tr>
	</table>
	</div>

</div>
<br clear="all" />
<div align="center">
	<button dojoType="Button" type="button" onClick="patientFormCommitChanges(); return true;">
		<div><!--{t}-->Commit Changes<!--{/t}--></div>
	</button>
</div>

</form>

<!--{if $MODE ne 'widget'}-->
<!--{include file="org.freemedsoftware.ui.footer.tpl"}-->
<!--{/if}-->

