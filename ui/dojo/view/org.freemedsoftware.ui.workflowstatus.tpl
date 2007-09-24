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

	var w = {
		init: function ( ) {
			dojo.io.bind({
				method: 'POST',
				content: {
					param0: 'today'
				},
				url: '<!--{$relay}-->/org.freemedsoftware.module.workflowstatus.StatusMapForDate',
				load: function( type, data, evt ) {
					w.buildWorkflowStatus( data );
				},
				mimetype: 'text/json'
			});
		},
		onPatientClick: function ( evt ) {
			freemedLoad('<!--{$controller}-->/org.freemedsoftware.controller.patient.overview?patient=' + this.id.replace('workflowstatus_patient_id_', '' ) );
		},
		buildWorkflowStatus: function ( d ) {
			if ( d.length < 1 ) {
				return false;
			}

			// Calculate columns
			var cols = [];
			for ( var h in d[0] ) {
				switch ( h ) {
					case 'patient': break;
					case 'patient_id': break;
					default: cols.push( h ); break;
				}
			}

			var t = dojo.byId( 'workflowStatusTable' );

			// Create header row
			var hRow = dojo.byId( 'workflowStatusTableHeader' );
			var hPatient = document.createElement( 'th' );
			hPatient.innerHTML = "<!--{t}-->Patient<!--{/t}-->";
			hRow.appendChild( hPatient );
			for ( var i=0; i<cols.length; i++ ) {
				var hElement = document.createElement( 'th' );
				hElement.innerHTML = cols[i];
				hRow.appendChild( hElement );
			}

			var hBody = dojo.byId( 'workflowStatusTableBody' );
			for ( var i=0; i<d.length; i++ ) {
				var bRow = document.createElement( 'tr' );
				if ( i & 1 ) {
					bRow.className = 'alternateRow';
				}
				var bPatient = document.createElement( 'td' );
				bPatient.innerHTML = d[i].patient;
				bPatient.id = 'workflowstatus_patient_id_' + d[i].patient_id;
				bPatient.onclick = w.onPatientClick;
				bRow.appendChild( bPatient );
				for ( var j=0; j<cols.length; j++ ) {
					var bElement = document.createElement( 'td' );
					bElement.style.textAlign = 'center';
					if ( d[i][ cols[j] ] == 1 ) {
						// Completed
						bElement.innerHTML = '<img src="<!--{$htdocs}-->/images/teak/check_go.16x16.png" border="0" />';
					} else {
						// Not completed
						bElement.innerHTML = '<img onClick="freemedLoad(\'org.freemedsoftware.module.' + cols[j] + '.workflow\');" src="<!--{$htdocs}-->/images/teak/x_stop.16x16.png" border="0" />';
					}
					bRow.appendChild( bElement );
				}
				hBody.appendChild( bRow );
			}
		}
	};

	_container_.addOnLoad(function() {
		w.init();
	});

	_container_.addOnUnload(function() {
	});

</script>

<h3><!--{t}-->Workflow Status<!--{/t}--></h3>

<table id="workflowStatusTable">
	<thead>
		<tr id="workflowStatusTableHeader">
		</tr>
	</thead>
	<tbody id="workflowStatusTableBody" class="scrollContent">
	</tbody>
</table>

