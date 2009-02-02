<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // Copyright (C) 1999-2009 FreeMED Software Foundation
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
	dojo.require( "dojo.widget.FilteringTable" );
	dojo.require( "dojo.widget.Select" );
	dojo.require( "dojo.widget.TabContainer" );
	dojo.require( "dojo.widget.ContentPane" );

	//	Functions
	var patientEncounter = {
		patientInfo: {},
		superbillDx: {},
		superbillPx: {},				
		loadSuperbillInformation: function () {
			var patientId = "<!--{$patient}-->";
			dojo.io.bind({
				method: 'POST',
				url: '<!--{$relay}-->/org.freemedsoftware.module.Superbill.GetForDates',
				content: {
					param0: '',
					param1: '',
					param2: '',
					param3: patientId
				},
				error: function() { },
				load: function( type, data, evt ) {
					w = dojo.widget.byId( 'superbillTable' );
					w.sortInformation = [];
					w.sortInformation.push({index:'provider_name', direction:1});
					w.store.setData( data );
				},
				mimetype: "text/json"
			});
		},
		printSuperbill: function ( ) {
			var w = dojo.widget.byId( 'superbillTable' );
			var val = w.getSelectedData();
			if ( ! val ) {
				alert ( "<!--{t|escape:'javascript'}-->No superbill was selected.<!--{/t}-->" );
				return false;
			}
			var uri = "<!--{$relay}-->/org.freemedsoftware.module.Superbill.printSuperbills?param0=<!--{$patient}-->&param1="+val.id+"&param2="+document.getElementById('sbnote').value;
			window.open( uri );
		},
		selectSuperbill: function ( ) {
			var w = dojo.widget.byId( 'superbillTable' );
			var val = w.getSelectedData();
			if ( val != 'undefined' ) {
				dojo.io.bind({
					method: 'POST',
					url: "<!--{$relay}-->/org.freemedsoftware.module.Superbill.GetSuperbill",
					content: {
						param0: val.id
					},
					load: function( type, data, evt ) {
						if ( data.provider != 0 ) {
							dojo.widget.byId( 'provider_widget' ).setValue(data.provider);
						
							var provider;
							dojo.io.bind({
								method: "POST",
								url: "<!--{$relay}-->/org.freemedsoftware.module.ProviderModule.picklist",
								load: function ( type, data1, evt ) {
									dojo.widget.byId( 'provider_widget' ).setLabel(data1[data.provider]);
							},
							mimetype: "text/json"
							});
						}
						
						// After everything is done loading, display
						document.getElementById('superbillPopulateDiv').style.display = 'block';
						
						document.getElementById( 'superbillDxCol0' ).innerHTML = '';
						document.getElementById( 'superbillDxCol1' ).innerHTML = '';
						document.getElementById( 'superbillDxCol2' ).innerHTML = '';

						// Populate diagnoses
						patientEncounter.superbillDxPopulate( data.dx, true );
						
						document.getElementById( 'superbillPxCol0' ).innerHTML = '';
						document.getElementById( 'superbillPxCol1' ).innerHTML = '';
						document.getElementById( 'superbillPxCol2' ).innerHTML = '';

						// Populate procedures
						patientEncounter.superbillPxPopulate( data.px, true );
					},
				mimetype: "text/json"
				});
			}
		},
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
			//this.populate('patientEncounterPatientName', 'patient_name');
			this.populate('patientEncounter.patientName', 'patient_name');
			this.populate('patientEncounter.dateOfBirth', 'date_of_birth_mdy');
			this.populate('patientEncounter.age', 'age');
			this.populate('patientEncounter.csz', 'csz');
		},
		populate: function ( domName, keyName ) {
			document.getElementById(domName).innerHTML = this.patientInfo[keyName];
		},
		superbillDxPopulate: function ( dx, checkDx ) {
			var t = document.getElementById( 'superbillDxTable' );
			patientEncounter.superbillDx = {};
			for ( var d=0; d < dx.length ; d++ ) {
				var col = d==0 ? 0 : d % 3;
				var colObj = document.getElementById('superbillDxCol' + col);

				var tDiv = document.createElement( 'div' );
				var tSpan = document.createElement( 'span' );
				var thisId = dx[d].id;

				tDiv.className = 'superbillElement';

				var cb = document.createElement( 'input' );
				cb.type = 'checkbox';
				cb.id = 'superbill<!--{$unique}-->_dx_' + thisId;
				cb.onclick = patientEncounter.onDxCheck;

				// Populate label, bolding previous dx's
				if ( parseInt(dx[d].previous) == 1 || checkDx) {
					var i = ' <label for="superbill<!--{$unique}-->_dx_' + thisId + '"><small>' + dx[d].code + ' ( ' + dx[d].descrip + ' )</small></label> ';
					tSpan.innerHTML = i;
					// Set internal counter to 1
					patientEncounter.superbillDx[ thisId ] = 1;

					// Previous dx should be selected by default
					cb.checked = true;
				} else {
					// Set internal counter to 0
					patientEncounter.superbillDx[ thisId ] = 0;
					
					var i = ' <label for="superbill<!--{$unique}-->_dx_' + thisId + '"><small>' + dx[d].code + ' ( ' + dx[d].descrip + ' )</small></label> ';
					tSpan.innerHTML = i;
				}

				// Assemble objects
				tDiv.appendChild( cb );
				tDiv.appendChild( tSpan );
				colObj.appendChild( tDiv );
			}
		},
		superbillPxPopulate: function ( px, checkPx ) {
			var t = document.getElementById( 'superbillPxTable' );
			patientEncounter.superbillPx = {};
			for ( var p=0; p < px.length ; p++ ) {
				var col = p==0 ? 0 : p % 3;
				var colObj = document.getElementById('superbillPxCol' + col);

				var tDiv = document.createElement( 'div' );
				var tSpan = document.createElement( 'span' );
				var thisId = px[p].id;

				tDiv.className = 'superbillElement';

				var tInnerDiv = document.createElement( 'div' );
				var cb = document.createElement( 'input' );
				cb.type = 'checkbox';
				cb.id = 'superbill<!--{$unique}-->_px_' + thisId;
				cb.onclick = patientEncounter.onPxCheck;

				// Populate label
				var i = ' <label for="superbill<!--{$unique}-->_px_' + thisId + '"><small>' + px[p].code + ' ( ' + px[p].descrip + ' )</small></label> ';
				tSpan.innerHTML = i;

				// Detailed inner div
				var tDetailDiv = document.createElement( 'div' );
				tDetailDiv.id = 'superbill<!--{$unique}-->_detaildiv_' + thisId;

				var tDetailTable = document.createElement( 'table' );
				var tDetailRow = document.createElement( 'tr' );

				var spanUnit = document.createElement( 'td' );
				spanUnit.innerHTML = "&nbsp;&nbsp;&nbsp; <small><!--{t|escape:'javascript'}-->Units<!--{/t}-->:</small> ";
				var inputUnitContainer = document.createElement( 'td' );
				var inputUnit = document.createElement( 'input' );
				inputUnit.type = 'text';
				inputUnit.size = 6;
				inputUnit.id = 'superbill<!--{$unique}-->_unit_' + thisId;
				
				if ( checkPx ) {
					tDetailDiv.style.display = 'block';
					cb.checked = true;
					inputUnit.value = 1;
					inputUnit.disabled = false;
					// Set internal counter to 1
					patientEncounter.superbillPx[ thisId ] = 1;
				} else {
					tDetailDiv.style.display = 'none';
					inputUnit.value = 0;
					inputUnit.disabled = true;
					// Set internal counter to 0
					patientEncounter.superbillPx[ thisId ] = 0;
				}
				inputUnitContainer.appendChild( inputUnit );

				var spanMod = document.createElement( 'td' );
				spanMod.innerHTML = "<small><!--{t|escape:'javascript'}-->Modifier<!--{/t}-->:</small> ";

				var tWidgetDiv = document.createElement( 'td' );
				var wid = dojo.widget.createWidget(
					'Select',
					{
						id: 'superbill<!--{$unique}-->_pxmod_' + thisId.toString() + '_widget',
						name: 'superbill<!--{$unique}-->_pxmod_' + thisId + '_widget',
						width: '100px',
						dataUrl: "<!--{$relay}-->/org.freemedsoftware.module.CptModifiers.picklist?param0=%{searchString}",
						mode: 'remote',
						autocomplete: false,
						setValue: function ( ) { if (arguments[0]) { document.getElementById('superbill<!--{$unique}-->_pxmod_' + this.iteration.toString() ).value = arguments[0]; } },
						iteration: thisId
					}
				);
				tWidgetDiv.appendChild( wid.domNode );
	
				// Keep track of the data here ...
				var tHidden = document.createElement( 'input' );
				tHidden.type = 'hidden';
				tHidden.id = "superbill<!--{$unique}-->_pxmod_" + thisId;
				tHidden.name = "superbill<!--{$unique}-->_pxmod_" + thisId;
				
				tDetailRow.appendChild( spanUnit );
				tDetailRow.appendChild( inputUnitContainer );
				tDetailRow.appendChild( spanMod );
				tDetailRow.appendChild( tWidgetDiv );
				tDetailTable.appendChild( tDetailRow );
				tDetailDiv.appendChild( tDetailTable );
				tDetailDiv.appendChild( tHidden );

				// Assemble objects
				tInnerDiv.appendChild( cb );
				tInnerDiv.appendChild( tSpan );
				tDiv.appendChild( tInnerDiv );
				tDiv.appendChild( tDetailDiv );
				colObj.appendChild( tDiv );
			}
		},
		saveSuperbill: function( ) {
			// Compile all px & dx
			var px = new Array ( );
			var dx = new Array ( );
			var detail = new Array ( );
			detail['units'] = new Array ( );
			for ( var i in patientEncounter.superbillDx ) {
				if ( patientEncounter.superbillDx[ i ] ) {
					dx.push( i );
				}
			}
			try {
				var x = document.getElementById( 'superbillDxCustom' ).value;
				if ( x > 0 ) { dx.push( x ); }
			} catch (e) { }
			for ( var i in patientEncounter.superbillPx ) {
				if ( patientEncounter.superbillPx[ i ] ) {
					px.push( i );
					detail['units'][i] = document.getElementById( 'superbill<!--{$unique}-->_unit_' + i ).value;
				}
			}
			try {
				var x = document.getElementById( 'superbillPxCustom' ).value;
				if ( x > 0 ) { px.push( x ); }
			} catch (e) { }
			if ( ! px.length || ! dx.length ) {
				alert ( "<!--{t|escape:'javascript'}-->Both procedures and diagnoses must be present to create a superbill.<!--{/t}-->" );
				return false;
			}
			dojo.io.bind({
				method: 'POST',
				content: {
					param0: {
						patient: "<!--{$patient}-->",
						procs: px,
						dx: dx,
						detail: detail,
						provider: dojo.widget.byId( 'provider_widget' ).getValue()
					}
				},
				url: '<!--{$relay}-->/org.freemedsoftware.module.Superbill.add',
				load: function( type, data, evt ) {
					dojo.widget.byId( 'superbillTemplateSave' ).disable();
					freemedMessage( "<!--{t|escape:'javascript'}-->Added superbill.<!--{/t}-->", "INFO" );
				},
				mimetype: 'text/json'
			});
			patientEncounter.loadSuperbillInformation( );
		},
		loadSuperbill: function( ) {
			var v = document.getElementById('superbillTemplate').value;

			// Disable button
			//dojo.widget.byId( 'superbillTemplateChoose' ).disable();

			// Load superbill form
			dojo.io.bind({
				method: 'POST',
				content: {
					param0: v,
					param1: "<!--{$patient}-->"
				},
				url: '<!--{$relay}-->/org.freemedsoftware.module.SuperbillTemplate.GetTemplate',
				load: function( type, data, evt ) {
					// After everything is done loading, display
					document.getElementById('superbillPopulateDiv').style.display = 'block';
					
					document.getElementById( 'superbillDxCol0' ).innerHTML = '';
					document.getElementById( 'superbillDxCol1' ).innerHTML = '';
					document.getElementById( 'superbillDxCol2' ).innerHTML = '';

					// Populate diagnoses
					patientEncounter.superbillDxPopulate( data.dx );
					
					document.getElementById( 'superbillPxCol0' ).innerHTML = '';
					document.getElementById( 'superbillPxCol1' ).innerHTML = '';
					document.getElementById( 'superbillPxCol2' ).innerHTML = '';

					// Populate procedures
					patientEncounter.superbillPxPopulate( data.px );
				},
				mimetype: "text/json"
			});
		},
		//----- Callbacks
		onDxCheck: function ( evt ) {
			var id = this.id.replace( 'superbill<!--{$unique}-->_dx_', '' );
			patientEncounter.superbillDx[ id ] = ! patientEncounter.superbillDx[ id ];
		},
		onPxCheck: function ( evt ) {
			var id = this.id.replace( 'superbill<!--{$unique}-->_px_', '' );
			patientEncounter.superbillPx[ id ] = ! patientEncounter.superbillPx[ id ];
			// Attempt to make sure we can write to this
			try {
				var pCount = document.getElementById( 'superbill<!--{$unique}-->_unit_' + id );
				if ( ! patientEncounter.superbillPx[ id ] ) {
					pCount.value = '0'
					pCount.disabled = true;
				} else {
					pCount.value = '1';
					pCount.disabled = false;
				}
				toggleDiv( 'superbill<!--{$unique}-->_detaildiv_' + id );
			} catch (err) { }
		}
	};

	//	Initialization / Event Connection
	_container_.addOnLoad(function(){
		patientEncounter.loadPatientInformation( );
		patientEncounter.loadSuperbillInformation( );
		dojo.event.connect( dojo.widget.byId('superbillTemplateChoose'), 'onClick', patientEncounter, 'loadSuperbill' );
		dojo.event.connect( dojo.widget.byId('superbillTemplateSave'), 'onClick', patientEncounter, 'saveSuperbill' );
		dojo.event.connect( dojo.widget.byId('printSuperbillButton'), 'onClick', patientEncounter, 'printSuperbill' );
		dojo.event.connect(dojo.widget.byId('superbillTable'), "onSelect", patientEncounter, "selectSuperbill");
	});
	_container_.addOnUnload(function(){
		dojo.event.disconnect( dojo.widget.byId('superbillTemplateChoose'), 'onClick', patientEncounter, 'loadSuperbill' );
		dojo.event.disconnect( dojo.widget.byId('superbillTemplateSave'), 'onClick', patientEncounter, 'saveSuperbill' );
		dojo.event.disconnect( dojo.widget.byId('printSuperbillButton'), 'onClick', patientEncounter, 'printSuperbill' );
		dojo.event.disconnect(dojo.widget.byId('superbillTable'), "onSelect", patientEncounter, "selectSuperbill");
	});

</script>

<style type="text/css">
	#patientEncounterClose {
		color: #555555;
		text-decoration: underline;
		}
	#patientEncounterClose:hover {
		color: #ff5555;
		cursor: pointer;
		}
	.superbillElement {
		border: 1px solid #000000;
		}
	.superbillElement:hover {
		background-color: #ffffff;
		}
</style>

<h3><!--{t}-->Patient Encounter Console<!--{/t}--> [ <a onClick="freemedPatientContentLoad('<!--{$controller}-->/org.freemedsoftware.ui.patient.overview.default?patient=<!--{$patient}-->');" id="patientEncounterClose">X</a> ]</h3>

<div dojoType="TabContainer" id="patientEncounterTabContainer" style="width: 100%; height: 100%;">

	<div dojoType="ContentPane" id="patientEncounterSummaryPane" label="&lt;img src='<!--{$htdocs}-->/images/teak/dashboard.16x16.png' border='0' width='16' height='16' /;&gt; <!--{t|escape:'javascript'}-->Summary<!--{/t}-->">

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

	<div dojoType="ContentPane" id="patientEncounterSuperbillPane" style="height: 100%; overflow: auto;" label="&lt;img src='<!--{$htdocs}-->/images/teak/superbill.16x16.png' border='0' width='16' height='16' /;&gt; <!--{t|escape:'javascript'}-->Superbill<!--{/t}-->">
		
		<div class="tableContainer" style="height: 130px; overflow-y: auto;">
			<table dojoType="FilteringTable" id="superbillTable" widgetId="superbillTable" headClass="fixedHeader"
			 tbodyClass="scrollContent" enableAlternateRows="true" rowAlternateClass="alternateRow" 
			 valueField="id" border="0" multiple="false" maxSelect="1">
			<thead>
				<tr>
					<th field="dateofservice_mdy" dataType="Date"><!--{t}-->Date<!--{/t}--></th>
					<th field="provider_name" dataType="String"><!--{t}-->Provider<!--{/t}--></th>
					<th field="cpt" dataType="String"><!--{t}-->Procedural Codes<!--{/t}--></th>
				</tr>
			</thead>
			<tbody></tbody>
			</table><br>
		</div>	
	
		<div>
			<table border="0">
				<tr>
					<th><!--{t}-->Note<!--{/t}--></th>
					<td><input type="text" id="sbnote" name="sbnote" size="50" value="" /></td>
					<td><button dojoType="Button" id="printSuperbillButton" widgetId="printSuperbillButton"><!--{t}-->View Superbill<!--{/t}--></button></td>
				</tr><br>
				<tr>
					<th><!--{t}-->Provider<!--{/t}--></th>
					<td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="ProviderModule" varname="provider" methodName="internalPicklist" defaultValue=$SESSION.authdata.user_record.userrealphy}--></td>
				</tr>
				<tr>
					<th><!--{t}-->Superbill<!--{/t}--></th>
					<td><table border="0" style="width: auto;"><tr><td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="SuperbillTemplate" varname="superbillTemplate"}--></td><td><button dojoType="Button" id="superbillTemplateChoose" widgetId="superbillTemplateChoose"><!--{t}-->Use<!--{/t}--></button></td></tr></table></td>
				</tr>
			</table>
		</div>

		<div id="superbillPopulateDiv" style="display: none;">

			<h4><!--{t}-->Diagnoses<!--{/t}--></h4>
			<table id="superbillDxTable">
				<tbody>
					<tr>
						<td width="33%" id="superbillDxCol0"></td>
						<td width="33%" id="superbillDxCol1"></td>
						<td width="33%" id="superbillDxCol2"></td>
					</tr>
					<tr>
						<td class="superbillElement"><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="IcdCodes" varname="superbillDxCustom"}--></td>
					</tr>
				</tbody>
			</table>

			<h4><!--{t}-->Procedures<!--{/t}--></h4>
			<table id="superbillPxTable">
				<tbody>
					<tr>
						<td width="33%" id="superbillPxCol0"></td>
						<td width="33%" id="superbillPxCol1"></td>
						<td width="33%" id="superbillPxCol2"></td>
					</tr>
					<tr>
						<td class="superbillElement"><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="CptCodes" varname="superbillPxCustom"}--></td>
					</tr>
				</tbody>
			</table>
			
			<div align="center">
				<button dojoType="Button" id="superbillTemplateSave" widgetId="superbillTemplateSave">
					<div><img src="<!--{$htdocs}-->/images/teak/check_go.16x16.png" border="0" /> <!--{t}-->Commit Superbill<!--{/t}--></div>
				</button>
			</div>
			<div style="width: 100%; height: 85px;"></div>
		</div>
	</div>

	<div dojoType="ContentPane" id="patientEncounterApptPane" label="&lt;img src='<!--{$htdocs}-->/images/teak/book_appt.16x16.png' border='0' width='16' height='16' /;&gt; <!--{t|escape:'javascript'}-->Appointments<!--{/t}-->">

	</div>

	<!--{method var='recentProgressNote' namespace='org.freemedsoftware.module.progressnotes.GetRecentRecord' param0=$patient}-->
	<!--{if $recentProgressNote.id}-->
	<div dojoType="ContentPane" id="patientEncounterProgressNotesPane" label="&lt;img src='<!--{$htdocs}-->/images/annotation_icon.png' border='0' width='16' height='16' /;&gt; <!--{t|escape:'javascript'}-->Recent Notes<!--{/t}-->" href="<!--{$controller}-->/org.freemedsoftware.module.progressnotes.view?id=<!--{$recentProgressNote.id}-->&patient=<!--{$patient}-->&embed=1" executeScripts="true" cacheContent="false" adjustPaths="false">
	</div>
	<!--{/if}-->

<!--
	<div dojoType="ContentPane" id="patientEncounterPane" label="<!--{t|escape:'javascript'}--><!--{/t}-->">
	</div>
-->

</div>

