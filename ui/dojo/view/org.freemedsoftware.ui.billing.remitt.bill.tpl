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

	var remitt = {
		// Aggregators
		patients: { },
		patientsEnabled: { },
		claimsEnabled: { },
		downloaded: { },
		expanded: { },
		destinationOverride: { },
		currentId: 0,

		//----- Functions
		loadPatients: function ( ) {
			dojo.io.bind({
				method: 'POST',
				content: { },
				url: "<!--{$relay}-->/org.freemedsoftware.module.RemittBillingTransport.PatientsToBill",
				load: function ( type, data, evt ) {
					if ( ! data.length ) {
						alert("<!--{t|escape:'javascript'}-->No patients to bill.<!--{/t}-->");
					} else {
						var pc = document.getElementById('remittPatientContainer');
						var t = document.createElement('table');
						var tBody = document.createElement('tbody');
						tBody.style.height = '95%';
						tBody.className = 'scrollContent';
						pc.appendChild( t );
						t.appendChild( tBody );
						var cellCount = 4;
						var alt = true;
						for (var i=0; i<data.length; i++) {
							alt = ! alt;
							var patient = data[i].patient_id;
							// Add to aggregators
							remitt.patients[ patient ] = data[i];
							remitt.patientsEnabled[ patient ] = true;
							remitt.downloaded[ patient ] = false;
							remitt.expanded[ patient ] = false;

							// Initial status, all claims enabled
							if ( data[i].claims.length ) {
								for (var cl=0; cl < data[i].claims.length; cl++ ) {
									remitt.claimsEnabled[ cl ] = true;
								}
							}

							var row = document.createElement('tr');
							var cells = new Array ( );
							row.id = 'remitt_bill_patient_' + patient.toString();
							row.valign = 'top';

							// Create blank rows
							for (var c=0; c<cellCount; c++) {
								cells[c] = document.createElement('td');
								cells[c].className = alt ? 'alternateRow' : '';
								cells[c].valign = 'top';
								row.appendChild( cells[c] );
							}

							// Create master patient checkbox
							var cb = document.createElement('input');
							cb.id = 'patient_check_' + patient;
							cb.type = 'checkbox';
							cb.checked = true;
							cb.onclick = remitt.OnPatientCheckbox;

							// Populate
							cells[0].appendChild( cb );
							var labelDiv = document.createElement('div');
							labelDiv.id = 'toggle_patient_' + patient.toString();
							labelDiv.className = 'clickable';
							labelDiv.onclick = remitt.OnTogglePatient;
							labelDiv.innerHTML = data[i].patient;
							cells[1].appendChild( labelDiv );
							cells[2].innerHTML = data[i].date_of_birth_mdy;
							cells[3].innerHTML = data[i].claim_count + " <!--{t|escape:'javascript'}-->claim(s)<!--{/t}-->";

							var divInner = document.createElement('div');
							divInner.id = 'patient_inner_div_' + patient.toString();
							divInner.style.display = 'none';
							divInner.style.border = '1px solid';
							cells[1].appendChild( divInner );

							// Tack on to the end
							tBody.appendChild( row );
						}
					}
				},
				mimetype: "text/json"
			});
		},
		//----- Callbacks
		OnClaimCheckbox: function ( evt ) {
			var id = this.id.replace( 'claim_check_', '' );
			//alert ( 'OnClaimCheckbox ' + id );
			remitt.claimsEnabled[ id ] = ! remitt.claimsEnabled[ id ];
		},
		OnDestinationOverride: function ( evt ) {
			var id = this.id.replace( 'destination_override_', '' );
			//alert ( 'OnDestinationOverride ' + id );
			remitt.destinationOverride[ id ] = document.getElementById( 'destination_override_' + id ).value;
			//alert ( 'DEBUG: ' + dojo.json.serialize( remitt.destinationOverride ) );
		},
		OnPatientCheckbox: function ( evt ) {
			var id = this.id.replace( 'patient_check_', '' );
			//alert ( 'OnPatientCheckbox ' + id );
			if ( remitt.patientsEnabled[ id ] && remitt.expanded[ id ] ) {
				//document.getElementById( 'patient_inner_div_' + id.toString() ).style.display = 'none';
				toggleDiv( 'patient_inner_div_' + id.toString() );
				remitt.expanded[ id ] = false;
			}
			remitt.patientsEnabled[ id ] = ! remitt.patientsEnabled[ id ];
		},
		OnProcessBilling: function ( ) {
			// Check to make sure we have *something* checked
			var anyPatientsEnabled = false;
			for ( var i in remitt.patientsEnabled ) {
				if ( remitt.patientsEnabled[ i ] ) { anyPatientsEnabled = true; }
			}
			if ( ! anyPatientsEnabled ) {
				alert("<!--{t|escape:'javascript'}-->Please select one or more patients to bill.<!--{/t}-->");
				return false;
			}

			// TODO : Perhaps check to make sure some claims are checked?

			// Form the parameters we need to push in
			var pEnabled = [ ];
			var cEnabled = [ ];
			var fOverride = [ ];

			for ( var i in remitt.patientsEnabled ) {
				if ( remitt.patientsEnabled[ i ] ) {
					pEnabled.push( i );
				}
			}
			for ( var i in remitt.claimsEnabled ) {
				if ( remitt.claimsEnabled[ i ] ) {
					cEnabled.push( i );
				}
			}

			// Make IO call
			dojo.io.bind({
				method: "POST",
				content: {
					param0: pEnabled,
					param1: cEnabled,
					param2: fOverride
				},
				url: "<!--{$relay}-->/org.freemedsoftware.module.RemittBillingTransport.ProcessClaims",
				load: function ( type, data, evt ) {
					if ( data.length ) {
						// Store state
						freemedGlobal.state[ 'remittRunningBatches' ] = data;

						// View status ...
						freemedLoad( 'org.freemedsoftware.ui.billing.remitt.status' );
					} else {
						alert("<!--{t|escape:'javascript'}-->An error occurred processing the claims. Please contact your system administrator.<!--{/t}-->");
					}
				},
				mimetype: "text/json"
			});
		},
		OnSelectAll: function ( ) {
			for ( var i in remitt.patients ) {
				// Set to be enabled
				remitt.patientsEnabled[ i ] = true;
				try {
					document.getElementById('patient_check_' + i).checked = true;
				} catch (err) { }
			}
		},
		OnSelectNone: function ( ) {
			for ( var i in remitt.patients ) {
				// Set to be enabled
				remitt.patientsEnabled[ i ] = false;
				try {
					document.getElementById('patient_check_' + i).checked = false;
				} catch (err) { }
			}
		},
		OnTogglePatient: function ( evt ) {
			var id = this.id.replace( 'toggle_patient_', '' );
			remitt.currentId = id;
			//alert ( 'OnToggleParent ' + id );

			// Load if we have to
			if ( ! remitt.downloaded[ id ] ) {
				dojo.io.bind({
					method: "POST",
					content: {
						param0: dojo.json.serialize( remitt.patients[ id ].claims )
					},
					url: "<!--{$relay}-->/org.freemedsoftware.module.RemittBillingTransport.GetClaimInformation",
					load: function ( type, data, evt ) {
						if ( data.length ) {
							var div = document.getElementById( 'patient_inner_div_' + remitt.currentId );
							div.style.display = 'none';
							div.style.backgroundColor = '#ffffff';
							var table = document.createElement( 'table' );
							var headerRow = document.createElement( 'tr' );
							var headerCells = new Array ();

							// Header row
							headerCells[0] = document.createElement( 'th' );
							headerCells[0].innerHTML = "<!--{t|escape:'javascript'}-->Enabled<!--{/t}-->";
							headerRow.appendChild( headerCells[0] );
							headerCells[1] = document.createElement( 'th' );
							headerCells[1].innerHTML = "<!--{t|escape:'javascript'}-->Claim<!--{/t}-->";
							headerRow.appendChild( headerCells[1] );
							headerCells[2] = document.createElement( 'th' );
							headerCells[2].innerHTML = "<!--{t|escape:'javascript'}-->Format<!--{/t}-->";
							headerRow.appendChild( headerCells[2] );

							table.appendChild( headerRow );
							for ( var i=0 ; i < data.length ; i++ ) {
								var row = document.createElement( 'tr' );
								var cells = new Array ();
	
								// Create master patient checkbox
								var cb = document.createElement('input');
								cb.id = 'claim_check_' + data[i].claim;
								cb.type = 'checkbox';
								cb.checked = true;
								cb.onclick = remitt.OnClaimCheckbox;

								var selectBox = document.createElement('select');
								var op = new Array ();
								selectBox.id = 'destination_override_' + data[i].claim;
								selectBox.onchange = remitt.OnDestinationOverride;
								op[0] = document.createElement('option');
								op[0].value = 'electronic';
								op[0].innerHTML = "<!--{t|escape:'javascript'}-->Electronic<!--{/t}--> (" + data[i].electronic_format + ' / ' + data[i].electronic_target + ')';
								selectBox.appendChild(op[0]);
								op[1] = document.createElement('option');
								op[1].value = 'paper';
								op[1].innerHTML = "<!--{t|escape:'javascript'}-->Paper<!--{/t}--> (" + data[i].paper_format + ' / ' + data[i].paper_target + ')';
								selectBox.appendChild(op[1]);
								if ( data[i].output_format == 'paper' ) {
									selectBox.selectedIndex = 1;
								} else {
									selectBox.selectedIndex = 0;
								}

								cells[0] = document.createElement( 'td' );
								cells[0].appendChild( cb );
								row.appendChild( cells[0] );
								cells[1] = document.createElement( 'td' );
								cells[1].innerHTML = '<label for="claim_check_' + data[i].claim + '">' + data[i].cpt_code + ' ' + data[i].cpt_description + ' (' + data[i].claim_date_mdy + ')</label>';
								row.appendChild( cells[1] );
								cells[2] = document.createElement( 'td' );
								cells[2].appendChild( selectBox );
								row.appendChild( cells[2] );
								
								table.appendChild( row );
							}
							div.appendChild( table );
						}
					},
					mimetype: "text/json",
					sync: true
				});
			}

			// Show hidden row of table
			//document.getElementById( 'patient_inner_div_' + id.toString() ).style.display = remitt.expanded[ id ] ? 'none' : 'block';
			toggleDiv( 'patient_inner_div_' + id.toString() );

			// Reverse expanded status after everything else is done
			remitt.expanded[ id ] = ! remitt.expanded[ id ];
			remitt.downloaded[ id ] = true;
		}
	};

	_container_.addOnLoad(function(){
		remitt.loadPatients();
		dojo.event.connect( dojo.widget.byId('remittProcessBilling'), "onClick", remitt, "OnProcessBilling" );
		dojo.event.connect( dojo.widget.byId('remittBillSelectAll'), "onClick", remitt, "OnSelectAll" );
		dojo.event.connect( dojo.widget.byId('remittBillSelectNone'), "onClick", remitt, "OnSelectNone" );
	});
	_container_.addOnUnload(function(){
		dojo.event.disconnect( dojo.widget.byId('remittProcessBilling'), "onClick", remitt, "OnProcessBilling" );
		dojo.event.disconnect( dojo.widget.byId('remittBillSelectAll'), "onClick", remitt, "OnSelectAll" );
		dojo.event.disconnect( dojo.widget.byId('remittBillSelectNone'), "onClick", remitt, "OnSelectNone" );
	});

</script>

<h3><!--{t}-->REMITT Billing<!--{/t}-->: <!--{t}-->Perform Billing<!--{/t}--></h3>

<table border="0" style="width: auto;">
	<tr>
		<td>
			<div dojoType="button" id="remittProcessBilling" widgetId="remittProcessBilling">
				<!--{t}-->Process<!--{/t}-->
			</div>
		</td>
		<td>
			<div dojoType="button" id="remittBillSelectAll" widgetId="remittBillSelectAll">
				<!--{t}-->Select All<!--{/t}-->
			</div>
		</td>
		<td>
			<div dojoType="button" id="remittBillSelectNone" widgetId="remittBillSelectNone">
				<!--{t}-->Select None<!--{/t}-->
			</div>
		</td>
		<td>
			<div dojoType="button" id="remittBillToMenu" widgetId="remittBillToMenu" onClick="freemedLoad('org.freemedsoftware.ui.billing.remitt');">
				<!--{t}-->Return to Menu<!--{/t}-->
			</div>
		</td>
	</tr>
</table>

<div id="remittPatientContainer" style="overflow-y:scroll; height: 95%;">

</div>

