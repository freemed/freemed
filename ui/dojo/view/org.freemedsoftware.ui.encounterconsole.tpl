<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
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
	//	Widgets used
	dojo.require( "dojo.widget.TabContainer" );
	dojo.require( "dojo.widget.ContentPane" );

	//	Functions
	var patientEncounter = {
		patientInfo: {},
		loadPatientInformation: function() {
			var patientId = "<!--{$patient}-->";
			dojo.io.bind({
				method: 'POST',
				content: {
					param0: patientId
				},
				url: '<!--{$relay}-->/org.freemedsoftware.api.PatientInterface.PatientInformation',
				load: function( type, data, evt ) {
					patientEncounter.patientInfo = data;
				},
				mimetype: "text/json",
				sync: true
			});
			this.populate('patientEncounterPatientName', 'patient_name');
			this.populate('patientEncounter.patientName', 'patient_name');
			this.populate('patientEncounter.dateOfBirth', 'date_of_birth_mdy');
			this.populate('patientEncounter.age', 'age');
			this.populate('patientEncounter.csz', 'csz');
		},
		populate: function ( domName, keyName ) {
			document.getElementById(domName).innerHTML = this.patientInfo[keyName];
		},
		loadSuperbill: function( ) {
			// Load superbill form
			dojo.io.bind({
				method: 'POST',
				content: {
					param0: document.getElementById('superbillTemplate').value
				},
				url: '<!--{$relay}-->/org.freemedsoftware.module.SuperbillTemplate.GetTemplate',
				load: function( type, data, evt ) {
					alert('loading template : FIXME');
				},
				mimetype: "text/json"
			});
		}
	};

	//	Initialization / Event Connection
	_container_.addOnLoad(function(){
		patientEncounter.loadPatientInformation( );
		dojo.event.connect( dojo.widget.byId('superbillTemplate_widget'), 'onSelect', patientEncounter, loadSuperbill );
	});
	_container_.addOnUnLoad(function(){
		dojo.event.disconnect( dojo.widget.byId('superbillTemplate_widget'), 'onSelect', patientEncounter, loadSuperbill );
	});

</script>

<style type="text/css">
	#patientEncounterPatientName {
		color: #555555;
		text-decoration: underline;
		}
	#patientEncounterPatientName:hover {
		color: #ff5555;
		cursor: pointer;
		}
</style>

<h3><!--{t}-->Patient Encounter Console<!--{/t}--> [ <a onClick="freemedLoad('<!--{$controller}-->/org.freemedsoftware.controller.patient.overview?patient=<!--{$patient}-->');"><span id="patientEncounterPatientName"></span></a> ]</h3>

<div dojoType="TabContainer" id="patientEncounterTabContainer" style="width: 100%; height: 100%;">

	<div dojoType="ContentPane" id="patientEncounterSummaryPane" label="<!--{t}-->Summary<!--{/t}-->">

		<table border="0" cellpadding="5">

			<tr>
				<th><!--{t}-->Name<!--{/t}--></th>
				<td><span id="patientEncounter.patientName" /></td>

				<th><!--{t}-->Date of Birth / Age<!--{/t}--></th>
				<td><span id="patientEncounter.dateOfBirth"></span> (<span id="patientEncounter.age"></span> <!--{t}-->years old<!--{/t}-->)</td>
			</tr>

			<tr>
				<th><!--{t}-->Location<!--{/t}--></th>
				<td><span id="patientEncounter.csz" /></td>

				<th><!--{t}--><!--{/t}--></th>
				<td></td>
			</tr>

		</table>

	</div>

	<div dojoType="ContentPane" id="patientEncounterSuperbillPane" label="<!--{t}-->Superbill<!--{/t}-->">
		<div>
			<table border="0">
				<tr>
					<th><!--{t}-->Superbill<!--{/t}--></th>
					<td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="SuperbillTemplate" varname="superbillTemplate"}--></td>
				</tr>
			</table>
		</div>
	</div>

	<div dojoType="ContentPane" id="patientEncounterApptPane" label="<!--{t}-->Appointments<!--{/t}-->">

	</div>

<!--
	<div dojoType="ContentPane" id="patientEncounterPane" label="<!--{t}--><!--{/t}-->">
	</div>
-->

</div>

