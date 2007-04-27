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

<script language="javascript">
	dojo.require("dojo.event.*");
	dojo.require("dojo.widget.Form");
	dojo.require("dojo.widget.FilteringTable");

	var patientSearch = {
		populatePatientSearch: function ( ) {
			dojo.io.bind({
				method: 'POST',
				content: {
					param0: dojo.widget.byId('patientSearchForm').getValues()
				},
				url: '<!--{$relay}-->/org.freemedsoftware.api.PatientInterface.Search',
				error: function () { },
				load: function(type, data, evt) {
					if (data) {
						dojo.widget.byId('patientSearch').store.setData( data );
					}
				},
				mimetype: "text/json"
			});
		},
		goToPatient: function () {
			var val = dojo.widget.byId('patientSearch').getSelectedData();
			if (val != 'undefined') {
				dojo.widget.byId('patientSearch').disable();
				freemedLoad('<!--{$controller}-->/org.freemedsoftware.controller.patient.overview?patient=' + val.id);
				return true;
			}
		},
		goToPatientHistorical: function () {
			var i = document.getElementById('patientSearchHistorical').selectedIndex;
			var val = document.getElementById('patientSearchHistorical').options[i].value;
			if (val != 'undefined') {
				if ( val == 0 ) { return false; }
				freemedLoad('<!--{$controller}-->/org.freemedsoftware.controller.patient.overview?patient=' + val);
				return true;
			}
		}
	};

	_container_.addOnLoad(function() {
		dojo.widget.byId('smartSearch').setValue('');
		dojo.widget.byId('patientTags').setValue('');
		dojo.widget.byId('smartSearch').textInputNode.focus();
		dojo.event.connect(dojo.widget.byId('patientSearch'), "onSelect", patientSearch, 'goToPatient');
		dojo.event.connect(dojo.widget.byId('populatePatientSearchButton'), "onClick", patientSearch, 'populatePatientSearch');

		// If ...
		if ( freemedGlobal.patientHistory.length ) {
			var sWidget = document.createElement( 'select' );
			sWidget.id = 'patientSearchHistorical';
			sWidget.onchange = patientSearch.goToPatientHistorical;
			var o = new Array ();
			var oOrig = document.createElement( 'option' );
			oOrig.value = 0;
			oOrig.innerHTML = '-----';
			sWidget.appendChild( oOrig );
			for ( var i=0; i < freemedGlobal.patientHistory.length; i++ ) {
				o[i] = document.createElement( 'option' );
				o[i].value = freemedGlobal.patientHistory[i][0];
				o[i].innerHTML = freemedGlobal.patientHistory[i][1];
				sWidget.appendChild( o[i] );
			}
			document.getElementById('patientHistorySpan').appendChild( sWidget );
			document.getElementById('patientHistoryDiv').style.display = 'block';
		}
	});

	_container_.addOnUnload(function() {
		dojo.event.disconnect(dojo.widget.byId('patientSearch'), "onSelect", patientSearch, 'goToPatient');
		dojo.event.disconnect(dojo.widget.byId('populatePatientSearchButton'), "onClick", patientSearch, 'populatePatientSearch');
		dojo.widget.byId('smartSearch').setValue('');
		dojo.widget.byId('patientTags').setValue('');
	});

</script>

<div dojoType="SplitContainer" orientation="vertical" activesizing="0" layoutAlign="client" sizerWidth="2" style="height: 100%;">

	<div dojoType="ContentPane" executeScripts="true" sizeShare="40" style="width: 100%; overflow: auto;">

		<h3><!--{t}-->Patient Search<!--{/t}-->: <!--{method namespace="org.freemedsoftware.api.PatientInterface.TotalInSystem"|escape}--> <!--{t}-->Patient(s) in the System<!--{/t}--></h3>

	<div class="infoBox" style="float:right;">
		<form dojoType="Form" id="patientSearchForm" widgetId="patientSearchForm">
		<table border="0">
		<tr>
			<td><!--{t}-->Age<!--{/t}--></td>
			<td><input type="text" id="age" name="age" value=""/></td>
		</tr>
		<tr>
			<td><!--{t}-->SSN #<!--{/t}--></td>
			<td><input type="text" id="ssn" name="ssn" value=""/></td>
		</tr>
		</table>
		<div align="center">
		<div type="button" dojoType="Button" id="populatePatientSearchButton" widgetId="populatePatientSearchButton"><!--{t}-->Search<!--{/t}--></div>
		</div>
		</form>
	</div>

	<div style="margin: .5em;">
		<!--{t}-->Smart Search<!--{/t}--> :
		<input dojoType="Select" value=""
		 autocomplete="false"
		 id="smartSearch" widgetId="smartSearch"
		 setValue="if (arguments[0]) { freemedLoad( '<!--{$controller}-->/org.freemedsoftware.ui.patient.overview?patient=' + arguments[0] ); }"
		 style="width: 300px;"
		 dataUrl="<!--{$relay}-->/org.freemedsoftware.api.PatientInterface.picklist?param0=%{searchString}"
		 mode="remote" />
	</div>

	<div style="margin: .5em;">
		<!--{t}-->Tag Search<!--{/t}--> :
		<input dojoType="Select" value=""
		 autocomplete="false"
		 id="patientTags" widgetId="patientTags"
		 setValue="if (arguments[0]) { freemedLoad( '<!--{$controller}-->/org.freemedsoftware.ui.tag.simplesearch?tag=' + arguments[0] ); }"
		 style="width: 300px;"
		 dataUrl="<!--{$relay}-->/org.freemedsoftware.module.PatientTag.ListTags?param0=%{searchString}"
		 mode="remote" />
	</div>

	<div style="margin: .5em; display: none;" id="patientHistoryDiv">
		<!--{t}-->Last Patients<!--{/t}--> :
		<span id="patientHistorySpan"></span>
	</div>

</div>

<br clear="all" />

<div dojoType="ContentPane" layoutAlign="bottom" sizeShare="60" style="width: 100%; overflow: auto;">

	<div class="tableContainer">
		<table dojoType="FilteringTable" id="patientSearch" widgetId="patientSearch" headClass="fixedHeader" tbodyClass="scrollContent" enableAlternateRows="true" rowAlterateClass="alternateRow" valueField="id" border="0" multiple="false">
			<thead>
				<tr>
					<th field="patient_id" dataType="String">Record</th>
					<th field="last_name" dataType="String">Last Name</th>
					<th field="first_name" dataType="String">First Name</th>
					<th field="middle_name" dataType="String">Middle Name</th>
					<th field="age" dataType="String">Age</th>
					<th field="date_of_birth" dataType="String">DOB</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>

</div> <!--{* ContentPane for FilteringTable *}-->

</div>

