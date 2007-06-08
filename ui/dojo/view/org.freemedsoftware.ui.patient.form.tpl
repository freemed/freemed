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

		// Disable submit button
		dojo.widget.byId('patientFormCommitChangesButton').disable();

		// Determine action
		var action = 'add'
		try {
			if (document.getElementById('id').value) {
				action = 'mod';
			}
		} catch (err) { }

		dojo.io.bind({
			method : 'POST',
			url: '<!--{$relay}-->/org.freemedsoftware.module.PatientModule.' + action,
			content: {
				param0: dojo.widget.byId('patientForm').getValues()
			},
			error: function(type, data, evt) {
				alert('FreeMED has encountered an error. Please try again.');
				dojo.widget.byId('patientFormCommitChangesButton').enable();
			},
			load: function(type, data, evt) {
				if (data) {
					if ((data + 0) > 0) {
						freemed
					}
				} else {
					alert('<!--{t}-->The transaction has failed. Please try again or contact your system administrator.<!--{/t}-->');
					dojo.widget.byId('patientFormCommitChangesButton').enable();
				}
			},
			mimetype: "text/json"
		});
	} // end patientFormCommitChanges

	function patientFormPopulate ( id ) {
		dojo.io.bind({
			method : 'POST',
			url: '<!--{$relay}-->/org.freemedsoftware.module.PatientModule.GetRecord',
			content: { param0: id },
			error: function(type, data, evt) {
				alert('<!--{t}-->FreeMED has encountered an error. Please try again.<!--{/t}-->');
				dojo.widget.byId('patientFormCommitChangesButton').enable();
			},
			load: function(type, data, evt) {
				if (data) {
					dojo.widget.byId('patientForm').setValues(data);
				} else {
					alert('<!--{t}-->The transaction has failed. Please try again or contact your system administrator.<!--{/t}-->');
				}
			},
			mimetype: "text/json"
		});
	} // end patientFormPopulate

	var pForm = {
		checkForDupes: function () {
			var d = {
				ptlname: document.getElementById('ptlname').value,
				ptfname: document.getElementById('ptfname').value,
				ptmname: document.getElementById('ptmname').value,
				ptdob: dojo.widget.byId( 'ptdob' ).getValue()
			};
			var dMsg = document.getElementById( 'dupeMessage' );
			dojo.io.bind({
				method: 'GET',
				content: {
					param0: d
				},
				url: "<!--{$relay}-->/org.freemedsoftware.api.PatientInterface.CheckForDuplicatePatient",
				load: function ( type, data, evt ) {
					if (data) {
						dMsg.innerHTML = "<!--{t}-->Patient exists in the system with ID<!--{/t}--> " + data;
						
					} else {
						dMsg.innerHTML = "<!--{t}-->No duplicate patients found.<!--{/t}-->";
					}
				},
				mimetype: 'text/json'
			});
		},
		clearDupe: function () {
			document.getElementById( 'dupeMessage' ).innerHTML = '';
		}
	};

<!--{if $patient > 0}-->
	_container_.addOnLoad(function(){
		//TODO: make this work properly to load via "AJAX"
		patientFormPopulate(<!--{$patient}-->);
	});
<!--{else}-->
	_container_.addOnLoad(function(){
		dojo.event.connect( dojo.widget.byId( 'dupeButton' ), 'onClick', pForm, 'checkForDupes' );
	});
	_container_.addOnUnload(function(){
		dojo.event.disconnect( dojo.widget.byId( 'dupeButton' ), 'onClick', pForm, 'checkForDupes' );
	});
<!--{/if}-->
</script>

<form dojoType="Form" id="patientForm" style="height: auto;">

<!--{if $patient > 0}-->
<h3><!--{t}-->Change Patient Details<!--{/t}--></h3>
<!--{else}-->
<h3><!--{t}-->Patient Entry<!--{/t}--></h3>
<!--{/if}-->


<div dojoType="TabContainer" id="mainTabContainer" style="width: 100%; height: 20em;">
	<div dojoType="ContentPane" id="patientDemographicsPane" label="Demographics">
	<table style="border: 0; padding: 1em;">

	<tr>
		<td><!--{t}-->Title<!--{/t}--></td>
		<td>
			<select dojoType="Select" id="ptsalut" name="ptsalut" style="width: 100px;" autocomplete="false">
				<option value="">--</option>
				<option value="Mr">Mr</option>
				<option value="Mrs">Mrs</option>
				<option value="Ms">Ms</option>
				<option value="Dr">Dr</option>
				<option value="Fr">Fr</option>
			</select>
		</td>
	</tr>
	<tr>
		<td><!--{t}-->Last Name<!--{/t}--></td>
		<td>
			<input type="text" id="ptlname" name="ptlname" value="<!--{$record.ptlname|escape}-->" size="20" maxlength="50" />
		</td>
	</tr>
	<tr>
		<td><!--{t}-->First Name<!--{/t}--></td>
		<td>
			<input type="text" id="ptfname" name="ptfname" value="<!--{$record.ptfname|escape}-->" size="20" maxlength="50" />
		</td>
	</tr>
	<tr>
		<td><!--{t}-->Middle Name<!--{/t}--></td>
		<td>
			<input type="text" id="ptmname" name="ptmname" value="<!--{$record.ptmname|escape}-->" size="10" />
		</td>
	</tr>
	<tr>
		<td><!--{t}-->Suffix<!--{/t}--></td>
		<td>
			<select dojoType="Select" id="ptsuffix" name="ptsuffix" style="width: 4em;" autocomplete="false">
				<option value=""></option>
				<option value="Sr" <!--{if $record.ptsuffix == 'Sr'}-->selected<!--{/if}-->>Sr</option>
				<option value="Jr" <!--{if $record.ptsuffix == 'Jr'}-->selected<!--{/if}-->>Jr</option>
				<option value="II" <!--{if $record.ptsuffix == 'II'}-->selected<!--{/if}-->>II</option>
				<option value="III" <!--{if $record.ptsuffix == 'III'}-->selected<!--{/if}-->>III</option>
				<option value="IV" <!--{if $record.ptsuffix == 'IV'}-->selected<!--{/if}-->>IV</option>
			</select>
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
			dataUrl="<!--{$relay}-->/org.freemedsoftware.module.Zipcodes.CityStateZipPicklist?param0='%{searchString}'"
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
				<option value="f" <!--{if $record.ptsex == 'f'}-->selected<!--{/if}-->><!--{t}-->Female<!--{/t}--></option>
				<option value="m" <!--{if $record.ptsex == 'm'}-->selected<!--{/if}-->><!--{t}-->Male<!--{/t}--></option>
				<option value="t" <!--{if $record.ptsex == 't'}-->selected<!--{/if}-->><!--{t}-->Transgendered<!--{/t}--></option>
			</select>
		</td>
	</tr>

	<tr>
		<td><!--{t}-->Date of Birth<!--{/t}--></td>
		<td><div dojoType="DropdownDatePicker" id="ptdob" widgetId="ptdob" name="ptdob" date="<!--{$record.ptdob}-->" containerToggle="wipe"></div></td>
	</tr>

<!--{* Verify if patient exists already based on L, F M and DOB *}-->
<!--{if not $patient}-->
	<tr>
		<td colspan="2"><button dojoType="Button" id="dupeButton" widgetId="dupeButton"><!--{t}-->Check for duplicate patient records<!--{/t}--></button></td>
	</tr>
	<tr>
		<td colspan="2"><div id="dupeMessage"></div></td>
	</tr>
<!--{/if}-->

	</table>
	</div>

	<div dojoType="ContentPane" id="patientContactPane" label="Contact">
	<table style="border: 0; padding: 1em;">
	<tr>
		<td><!--{t}-->Preferred Contact<!--{/t}--></td>
		<td>
			<select dojoType="Select" style="width: 100px;" autocomplete="false" id="ptprefcontact" name="ptprefcontact">
				<option value="home" <!--{if $record.ptprefcontact == 'home'}-->selected<!--{/if}-->><!--{t}-->Home<!--{/t}--></option>
				<option value="work" <!--{if $record.ptprefcontact == 'work'}-->selected<!--{/if}-->><!--{t}-->Work<!--{/t}--></option>
				<option value="mobile" <!--{if $record.ptprefcontact == 'mobile'}-->selected<!--{/if}-->><!--{t}-->Mobile<!--{/t}--></option>
				<option value="email" <!--{if $record.ptprefcontact == 'email'}-->selected<!--{/if}-->><!--{t}-->Email<!--{/t}--></option>
			</select>
		</td>
	</tr>
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
			<select dojoType="Select" style="width: 100px;" autocomplete="false" id="ptmarital" name="ptmarital">
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
			<select dojoType="Select" style="width: 100px;" autocomplete="false" id="ptempl" name="ptempl">
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
			<!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="ProviderModule" varname="ptdoc"}-->
		</td>
	</tr>
	<tr>
		<td><!--{t}-->Referring Provider<!--{/t}--></td>
		<td>
			<!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="ProviderModule" varname="ptref"}-->
		</td>
	</tr>
	<tr>
		<td><!--{t}-->Primary Care Provider<!--{/t}--></td>
		<td>
			<!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="ProviderModule" varname="ptpcp"}-->
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
	<table border="0" style="width:200px;">
	<tr><td align="<!--{if $patient > 0}-->right<!--{else}-->center<!--{/if}-->">
	<button dojoType="Button" type="button" id="patientFormCommitChangesButton" widgetId="patientFormCommitChangesButton" onClick="patientFormCommitChanges(); return true;">
<!--{if $patient > 0}-->
		<div><!--{t}-->Commit Changes<!--{/t}--></div>
<!--{else}-->
		<div><!--{t}-->Create Patient<!--{/t}--></div>
<!--{/if}-->

	</button>
	<!--{if $patient > 0}-->
	</td><td align="left">
	<button dojoType="Button" type="button" id="patientFormCancelButton" widgetId="patientFormCancelButton" onClick="freemedLoad('<!--{$controller}-->/org.freemedsoftware.controller.patient.overview?patient=<!--{$patient}-->'); return true;">
		<div><!--{t}-->Cancel<!--{/t}--></div>
	</button>
	<!--{/if}-->
	</td></tr></table>
</div>

</form>

