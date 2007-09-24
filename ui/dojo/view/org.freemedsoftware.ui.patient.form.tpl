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

<!--{config_value option='patient_form' var='patient_form'}-->

<script type="text/javascript">
	dojo.require("dojo.widget.Form");
	dojo.require("dojo.widget.TabContainer");
	dojo.require("dojo.widget.ContentPane");
	dojo.require("dojo.widget.InternetTextbox");
	dojo.require("dojo.widget.UsTextbox");

	var pForm = {
		addrCount: -1,
		commitChanges: function ( ) {
			// Verify form
			var message = '';
			if (document.getElementById('ptfname').value.length < 3) {
				message += "<!--{t}-->No first name.<!--{/t}-->\n";
			}
	
			if (document.getElementById('ptlname').value.length < 2) {
				message += "<!--{t}-->No last name.<!--{/t}-->\n";
			}
	
			if (message.length > 0) {
				alert(message);
				return false;
			}

			// Disable submit button
			dojo.widget.byId('patientFormCommitChangesButton').disable();
	
			// Determine action
			var action = 'add';
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
				load: function(type, data, evt) {
					if (data) {
						freemedMessage( "<!--{t}-->Patient database updated.<!--{/t}-->", 'INFO' );
						if ( parseInt( data ) > 0 ) {
							pForm.commitAddressChanges( data );
						}
					} else {
						alert('<!--{t}-->The transaction has failed. Please try again or contact your system administrator.<!--{/t}-->');
						dojo.widget.byId('patientFormCommitChangesButton').enable();
					}
				},
				mimetype: "text/json"
			});
		},
		commitAddressChanges: function ( patientData ) {
			var w = dojo.widget.byId( 'patientAddress' );
			// Set all others to be inactive if this one is active
			if ( w.store.get().length > 0 ) {
				var x = w.store.get();
				var y = [];
				// Adjust all "new" values to be 0 now
				for ( var i=0; i<x.length; i++ ) {
					if ( x[i].src.id < 0 ) { x[i].src.id = 0; }
					y.push( x[i].src );
				}
				//alert(dojo.json.serialize(y));
				dojo.io.bind({
					method: 'POST',
					url: '<!--{$relay}-->/org.freemedsoftware.module.PatientModule.SetAddresses',
					content: {
						param0: parseInt( patientData ),
						param1: dojo.json.serialize( y )
					},
					load: function( type, data, evt ) {
						freemedMessage( "<!--{t}-->Patient addresses updated.<!--{/t}-->", 'INFO' );
						freemedLoad( '<!--{$controller}-->/org.freemedsoftware.ui.patient.overview?patient=' + parseInt( patientData ) );
					},
					mimetype: "text/json"
				});
			} else {
				freemedLoad( '<!--{$controller}-->/org.freemedsoftware.ui.patient.overview?patient=' + parseInt( patientData ) );
			}
		},
		Populate: function ( id ) {
			dojo.io.bind({
				method : 'POST',
				url: '<!--{$relay}-->/org.freemedsoftware.module.PatientModule.GetRecord',
				content: { param0: id },
				load: function(type, data, evt) {
					if (data) {
						// Catch all for populating form data
						dojo.widget.byId('patientForm').setValues(data);

						// Picklists
						dojo.event.topic.publish( 'ptref-assign', data.ptref );
						dojo.event.topic.publish( 'ptdoc-assign', data.ptdoc );
						dojo.event.topic.publish( 'ptpcp-assign', data.ptpcp );

						// DOB
						dojo.widget.byId( 'ptdob' ).setValue( data.ptdob );

						// Select boxes
						dojo.widget.byId( 'ptsex' ).setValue( data.ptsex );
						switch( data.ptsex ) {
							case 'm': dojo.widget.byId( 'ptsex' ).setLabel( "<!--{t}-->Male<!--{/t}-->" ); break;
							case 'f': dojo.widget.byId( 'ptsex' ).setLabel( "<!--{t}-->Female<!--{/t}-->" ); break;
							case 't': dojo.widget.byId( 'ptsex' ).setLabel( "<!--{t}-->Transgendered<!--{/t}-->" ); break;
							break;
							default: break;
						}
					} else {
						alert('<!--{t}-->The transaction has failed. Please try again or contact your system administrator.<!--{/t}-->');
					}
				},
				mimetype: "text/json"
			});
			// Populate addresses
			dojo.io.bind({
				method : 'POST',
				url: '<!--{$relay}-->/org.freemedsoftware.module.PatientModule.GetAddresses',
				content: { param0: id },
				load: function(type, data, evt) {
					if (data) {
						// Altered flag
						for ( var i=0; i < data.length; i++ ) {
							d[i]['altered'] = false;
						}

						// Catch all for populating form data
						dojo.widget.byId('addresses').setValues( data );
					}
				},
				mimetype: "text/json"
			});	
		},
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
		},
		dobP10: function () {
			var d = dojo.widget.byId( 'ptdob' );
			var nextDate = dojo.date.add( d.value, dojo.date.dateParts.YEAR, -10 );
			d.setValue( nextDate );
			d.value = nextDate;
		},
		dobN10: function () {
			var d = dojo.widget.byId( 'ptdob' );
			var nextDate = dojo.date.add( d.value, dojo.date.dateParts.YEAR, 10 );
			d.setValue( nextDate );
			d.value = nextDate;
		},
		addAddress: function () {
			var w = dojo.widget.byId( 'patientAddress' );
			// Set all others to be inactive if this one is active
			if ( parseInt( document.getElementById( 'addActive' ).value ) == 1 ) {
				if ( w.store.get().length > 0 ) {
					var x = w.store.get();
					for ( var i=0; i<x.length; i++ ) {
						if ( x[i].src.active ) { x[i].src.active = 0; }
					}
					w.store.setData( x );
				}
			}
			var d = {
				type: document.getElementById( 'addType' ).value,
				relate: document.getElementById( 'addRelate' ).value,
				line1: document.getElementById( 'addAddr1' ).value,
				line2: document.getElementById( 'addAddr2' ).value,
				csz: document.getElementById( 'addCsz' ).value,
				active: document.getElementById( 'addActive' ).value,
				id: pForm.addrCount
			};
			w.store.addData( d );
			pForm.addrCount = pForm.addrCount - 1;
			return true;
		}
	};

<!--{if $patient > 0}-->
	_container_.addOnLoad(function(){
		//TODO: make this work properly to load via "AJAX"
		pForm.Populate(<!--{$patient}-->);
		dojo.event.connect( dojo.widget.byId( 'addAddressButton' ), 'onClick', pForm, 'addAddress' );
	});
	_container_.addOnUnload(function(){
		dojo.event.disconnect( dojo.widget.byId( 'addAddressButton' ), 'onClick', pForm, 'addAddress' );
	});
<!--{/if}-->
	_container_.addOnLoad(function(){
		dojo.event.connect( dojo.widget.byId( 'dupeButton' ), 'onClick', pForm, 'checkForDupes' );
		dojo.event.connect( dojo.widget.byId( 'patientFormCommitChangesButton' ), 'onClick', pForm, 'commitChanges' );
		dojo.event.connect( dojo.widget.byId( 'addAddressButton' ), 'onClick', pForm, 'addAddress' );
		dojo.event.connect( dojo.widget.byId( 'dobP10' ), 'onClick', pForm, 'dobP10' );
		dojo.event.connect( dojo.widget.byId( 'dobN10' ), 'onClick', pForm, 'dobN10' );
	});
	_container_.addOnUnload(function(){
		dojo.event.disconnect( dojo.widget.byId( 'dupeButton' ), 'onClick', pForm, 'checkForDupes' );
		dojo.event.disconnect( dojo.widget.byId( 'patientFormCommitChangesButton' ), 'onClick', pForm, 'commitChanges' );
		dojo.event.disconnect( dojo.widget.byId( 'addAddressButton' ), 'onClick', pForm, 'addAddress' );
		dojo.event.disconnect( dojo.widget.byId( 'dobP10' ), 'onClick', pForm, 'dobP10' );
		dojo.event.disconnect( dojo.widget.byId( 'dobN10' ), 'onClick', pForm, 'dobN10' );
	});
</script>

<form dojoType="Form" id="patientForm" style="height: auto;">

<!--{if $patient > 0}-->
<h3><!--{t}-->Change Patient Details<!--{/t}--></h3>
<!--{else}-->
<h3><!--{t}-->Patient Entry<!--{/t}--></h3>
<!--{/if}-->


<!--{if $patient_form eq 'tab'}-->
<div dojoType="TabContainer" id="mainTabContainer" style="width: 100%; height: 30em;">
	<div dojoType="ContentPane" id="patientDemographicsPane" label="<!--{t}-->Demographics<!--{/t}-->">
<!--{else}-->
	<div style="height: 30em; overflow-y: scroll;">
	<h4><!--{t}-->Demographics<!--{/t}--></h4>
<!--{/if}-->
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
			<input type="text" id="ptlname" name="ptlname" size="20" maxlength="50" />
		</td>
	</tr>
	<tr>
		<td><!--{t}-->First Name<!--{/t}--></td>
		<td>
			<input type="text" id="ptfname" name="ptfname" size="20" maxlength="50" />
		</td>
	</tr>
	<tr>
		<td><!--{t}-->Middle Name<!--{/t}--></td>
		<td>
			<input type="text" id="ptmname" name="ptmname" size="10" />
		</td>
	</tr>
	<tr>
		<td><!--{t}-->Suffix<!--{/t}--></td>
		<td>
			<select dojoType="Select" id="ptsuffix" name="ptsuffix" style="width: 4em;" autocomplete="false">
				<option value=""></option>
				<option value="Sr">Sr</option>
				<option value="Jr">Jr</option>
				<option value="II">II</option>
				<option value="III">III</option>
				<option value="IV">IV</option>
			</select>
		</td>
	</tr>
	
	<tr>
		<td><!--{t}-->Gender<!--{/t}--></td>
		<td>
			<select dojoType="Select" style="width: 100px;" autocomplete="false" id="ptsex" name="ptsex" widgetId="ptsex">
				<option value=""></option>
				<option value="f"><!--{t}-->Female<!--{/t}--></option>
				<option value="m"><!--{t}-->Male<!--{/t}--></option>
				<option value="t"><!--{t}-->Transgendered<!--{/t}--></option>
			</select>
		</td>
	</tr>

	<tr>
		<td><!--{t}-->Date of Birth<!--{/t}--></td>
		<td><table style="width: auto;" cellspacing="0" cellpadding="0" border="0"><tr><td><button dojoType="Button" id="dobP10">-10</button></td><td><div dojoType="DropdownDatePicker" id="ptdob" widgetId="ptdob" name="ptdob" containerToggle="wipe" value="today" displayFormat="MM/dd/yyyy"></div></td><td><button dojoType="Button" id="dobN10">+10</button></td></tr></table></td>
	</tr>

	<tr>
		<td><!--{t}-->Patient Practice ID<!--{/t}--></td>
		<td><input type="text" id="ptid" name="ptid" /></td>
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

<!--{if $patient_form eq 'tab'}-->
	</div>
	<div dojoType="ContentPane" id="patientAddressPane" label="<!--{t}-->Address<!--{/t}-->">
<!--{else}-->
	<h4><!--{t}-->Address<!--{/t}--></h4>
<!--{/if}-->
	<table style="border: 0; padding: 1em;">

	<tr>
		<td><!--{t}-->Type of Address<!--{/t}--></td>
		<td>
			<select id="addType">
				<option value="H"><!--{t}-->Home<!--{/t}--></option>
				<option value="W"><!--{t}-->Work<!--{/t}--></option>
			</select>
		</td>
	</tr>

	<tr>
		<td><!--{t}-->Relationship to Patient<!--{/t}--></td>
		<td>
			<select id="addRelate">
				<option value="S"><!--{t}-->Self<!--{/t}--></option>
				<option value="P"><!--{t}-->Parents<!--{/t}--></option>
				<option value="C"><!--{t}-->Cousin<!--{/t}--></option>
				<option value="SH"><!--{t}-->Shelter<!--{/t}--></option>
				<option value="U"><!--{t}-->Unrelated<!--{/t}--></option>
			</select>
		</td>
	</tr>

	<tr>
		<td><!--{t}-->Address Line 1<!--{/t}--></td>
		<td><input type="text" id="addAddr1" size="50" maxlength="100" /></td>
	</tr>

	<tr>
		<td><!--{t}-->Address Line 2<!--{/t}--></td>
		<td><input type="text" id="addAddr2" size="50" maxlength="100" /></td>
	</tr>

	<tr>
		<td><!--{t}-->City, State Zip<!--{/t}--></td>
		<td>
			<input dojoType="Select"
			autocomplete="false"
			id="addCsz_widget"
			style="width: 300px;"
			dataUrl="<!--{$relay}-->/org.freemedsoftware.module.Zipcodes.CityStateZipPicklist?param0='%{searchString}'"
			setValue="document.getElementById('addCsz').value = arguments[0];"
			mode="remote" />
			<input type="hidden" id="addCsz" value="" />
		</td>
	</tr>

	<tr>
		<td><!--{t}-->Active Address<!--{/t}--></td>
		<td>
			<select id="addActive">
				<option value="1"><!--{t}-->Active<!--{/t}--></option>
				<option value="0"><!--{t}-->Inactive<!--{/t}--></option>
			</select>
		</td>
	</tr>

	<tr>
		<td colspan="2" align="center">
			<button dojoType="Button" id="addAddressButton" widgetId="addAddressButton">
				<!--{t}-->Add Address<!--{/t}-->
			</button>
		</td>
	</tr>

	<tr>
		<td colspan="2">
		<div class="tableContainer">
			<table dojoType="FilteringTable" id="patientAddress" widgetId="patientAddress"
			 headClass="fixedHeader" tbodyClass="scrollContent" enableAlternateRows="true" rowAlternateClass="alternateRow"
			 valueField="id" border="0" multiple="yes" style="height: 100%;">
			<thead id="patientAddressHead">
				<tr>
					<th field="type" dataType="String"><!--{t}-->Address Type<!--{/t}--></th>
					<th field="relate" dataType="String"><!--{t}-->Relation to Patient<!--{/t}--></th>
					<th field="line1" dataType="String"><!--{t}-->Address 1<!--{/t}--></th>
					<th field="line2" dataType="String"><!--{t}-->Address 2<!--{/t}--></th>
					<th field="csz" dataType="String"><!--{t}-->City, State Zip<!--{/t}--></th>
					<th field="active" dataType="String"><!--{t}-->Active<!--{/t}--></th>
				</tr>
			</thead>
			<tbody></tbody>
			</table>
		</div>
		</td>
	</tr>

	</table>

<!--{if $patient_form eq 'tab'}-->
	</div>
	<div dojoType="ContentPane" id="patientContactPane" label="<!--{t}-->Contact<!--{/t}-->">
<!--{else}-->
	<h4><!--{t}-->Contact<!--{/t}--></h4>
<!--{/if}-->

	<table style="border: 0; padding: 1em;">
	<tr>
		<td><!--{t}-->Preferred Contact<!--{/t}--></td>
		<td>
			<select dojoType="Select" style="width: 100px;" autocomplete="false" id="ptprefcontact" name="ptprefcontact">
				<option value="home"><!--{t}-->Home<!--{/t}--></option>
				<option value="work"><!--{t}-->Work<!--{/t}--></option>
				<option value="mobile"><!--{t}-->Mobile<!--{/t}--></option>
				<option value="email"><!--{t}-->Email<!--{/t}--></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><!--{t}-->Home Phone<!--{/t}--></td>
		<td><input dojoType="UsPhoneNumberTextbox" type="text" name="pthphone" id="pthphone" size="16" maxlength="16" /></td>
	</tr>
	<tr>
		<td><!--{t}-->Work Phone<!--{/t}--></td>
		<td><input dojoType="UsPhoneNumberTextbox" type="text" name="ptwphone" id="ptwphone" size="16" maxlength="16" /></td>
	</tr>
	<tr>
		<td><!--{t}-->Fax Phone<!--{/t}--></td>
		<td><input dojoType="UsPhoneNumberTextbox" type="text" name="ptfax" id="ptfax" size="16" maxlength="16" /></td>
	</tr>
	<tr>
		<td><!--{t}-->Mobile Phone<!--{/t}--></td>
		<td><input dojoType="UsPhoneNumberTextbox" type="text" name="ptmphone" id="ptmphone" size="16" maxlength="16" /></td>
	</tr>
	<tr>
		<td><!--{t}-->Email Address<!--{/t}--></td>
		<td><input dojoType="EmailTextbox" type="text" name="ptemail" id="ptemail" size="50" maxlength="50" /></td>
	</tr>
	</table>

<!--{if $patient_form eq 'tab'}-->
	</div>
	<div dojoType="ContentPane" id="patientPersonalPane" label="<!--{t}-->Personal<!--{/t}-->">
<!--{else}-->
	<h4><!--{t}-->Personal<!--{/t}--></h4>
<!--{/if}-->

	<table style="border: 0; padding: 1em;">
	<tr>
		<td><!--{t}-->Marital Status<!--{/t}--></td>
		<td>
			<select dojoType="Select" style="width: 100px;" autocomplete="false" id="ptmarital" name="ptmarital">
				<option value="single"><!--{t}-->Single<!--{/t}--></option>
				<option value="married"><!--{t}-->Married<!--{/t}--></option>
				<option value="divorced"><!--{t}-->Divorced<!--{/t}--></option>
				<option value="separated"><!--{t}-->Separated<!--{/t}--></option>
				<option value="widowed"><!--{t}-->Widowed<!--{/t}--></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><!--{t}-->Employment Status<!--{/t}--></td>
		<td>
			<select dojoType="Select" style="width: 100px;" autocomplete="false" id="ptempl" name="ptempl">
				<option value="u"><!--{t}-->Unknown<!--{/t}--></option>
				<option value="y"><!--{t}-->Yes<!--{/t}--></option>
				<option value="n"><!--{t}-->No<!--{/t}--></option>
				<option value="p"><!--{t}-->Part<!--{/t}--></option>
				<option value="s"><!--{t}-->Self<!--{/t}--></option>
				<option value="r"><!--{t}-->Retired<!--{/t}--></option>
				<option value="m"><!--{t}-->Military<!--{/t}--></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><!--{t}-->Social Security Number<!--{/t}--></td>
		<td><input dojoType="UsSocialSecurityNumberTextbox" type="text" name="ptssn" id="ptssn" size="10" maxlength="9" /></td>
	</tr>
	<tr>
		<td><!--{t}-->Drivers License<!--{/t}--></td>
		<td><input type="text" name="ptdmv" id="ptdmv" size="10" maxlength="9" /></td>
	</tr>
	</table>

<!--{if $patient_form eq 'tab'}-->
	</div>
	<div dojoType="ContentPane" id="patientProviderPane" label="<!--{t}-->Provider<!--{/t}-->">
<!--{else}-->
	<h4><!--{t}-->Provider<!--{/t}--></h4>
<!--{/if}-->

	<table style="border: 0; padding: 1em;">
	<tr>
		<td><!--{t}-->In House Provider<!--{/t}--></td>
		<td>
			<!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="ProviderModule" varname="ptdoc" methodName="internalPicklist"}-->
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
	</table>

<!--{if $patient_form eq 'tab'}-->
	</div>

</div>
<!--{else}-->
</div>
<!--{/if}-->

<br clear="all" />
<div align="center">
	<table border="0" style="width:200px;">
	<tr><td align="<!--{if $patient > 0}-->right<!--{else}-->center<!--{/if}-->">
	<button dojoType="Button" type="button" id="patientFormCommitChangesButton" widgetId="patientFormCommitChangesButton">
<!--{if $patient > 0}-->
		<div><img src="<!--{$htdocs}-->/images/teak/check_go.16x16.png" border="0" width="16" height="16" /> <!--{t}-->Commit Changes<!--{/t}--></div>
<!--{else}-->
		<div><img src="<!--{$htdocs}-->/images/teak/check_go.16x16.png" border="0" width="16" height="16" /> <!--{t}-->Create Patient<!--{/t}--></div>
<!--{/if}-->

	</button>
	<!--{if $patient > 0}-->
	</td><td align="left">
	<button dojoType="Button" type="button" id="patientFormCancelButton" widgetId="patientFormCancelButton" onClick="freemedLoad('<!--{$controller}-->/org.freemedsoftware.controller.patient.overview?patient=<!--{$patient}-->'); return true;">
		<div><img src="<!--{$htdocs}-->/images/teak/x_stop.16x16.png" border="0" width="16" height="16" /> <!--{t}-->Cancel<!--{/t}--></div>
	</button>
	<!--{/if}-->
	</td></tr></table>
</div>

</form>

