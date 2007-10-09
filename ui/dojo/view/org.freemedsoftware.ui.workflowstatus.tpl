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
	dojo.require( 'dojo.date.format' );

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
		onGoClick: function ( evt ) {
			var hash = this.id.replace('workflow_status_go_', '' ).split('_');
			freemedLoad( '<!--{$controller}-->/org.freemedsoftware.module.' + hash[2] + '.workflow?patient=' + encodeURIComponent( hash[0] ) );
		},
		onImageClick: function ( evt ) {
			var hash = this.id.replace('workflow_status_img_', '' ).split('_');

			dojo.io.bind({
				method: 'POST',
				content: {
					param0: hash[0],
					param1: hash[1],
					param2: hash[2],
					param3: true
				},
				url: "<!--{$relay}-->/org.freemedsoftware.module.workflowstatus.SetStatus",
				load: function ( type, evt, data ) {
					if ( data ) {
						freemedMessage( "<!--{t|escape:'javascript'}-->Workflow updated.<!--{/t}-->", 'INFO' );
						var bImg = document.getElementById( 'workflow_status_img_' + hash[0] + '_' + hash[1] + '_' + hash[2] );
						bImg.src = "<!--{$htdocs}-->/images/teak/check_go.16x16.png";
					}
				},
				mimetype: 'text/json'
			});
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
					case 'date_of': break;
					default: cols.push( h ); break;
				}
			}

			var t = dojo.byId( 'workflowStatusTable' );

			// Create header row
			var hRow = dojo.byId( 'workflowStatusTableHeader' );
			var hPatient = document.createElement( 'th' );
			hPatient.innerHTML = "<!--{t|escape:'javascript'}-->Patient<!--{/t}-->";
			hRow.appendChild( hPatient );
			var hDate = document.createElement( 'th' );
			hDate.innerHTML = "<!--{t|escape:'javascript'}-->Date<!--{/t}-->";
			hRow.appendChild( hDate );
			for ( var i=0; i<cols.length; i++ ) {
				var hElement = document.createElement( 'th' );
				hElement.innerHTML = cols[i];
				hRow.appendChild( hElement );
			}

			var hBody = dojo.byId( 'workflowStatusTableBody' );
			for ( var i=0; i<d.length; i++ ) {
				if ( d[i].patient ) {
					var bRow = document.createElement( 'tr' );
					if ( i & 1 ) {
						bRow.className = 'alternateRow';
					}
					var bPatient = document.createElement( 'td' );
					bPatient.innerHTML = d[i].patient;
					bPatient.id = 'workflowstatus_patient_id_' + d[i].patient_id;
					bPatient.onclick = w.onPatientClick;
					bRow.appendChild( bPatient );

					var bDate = document.createElement( 'td' );
					bDate.innerHTML = d[i].date_of;
					bRow.appendChild( bDate );

					for ( var j=0; j<cols.length; j++ ) {
						var bElement = document.createElement( 'td' );
						bElement.style.textAlign = 'center';
						if ( d[i][ cols[j] ] == 1 ) {
							// Completed
							bElement.innerHTML = '<img src="<!--{$htdocs}-->/images/teak/check_go.16x16.png" border="0" />';
						} else {
							// Not completed
							var bLink = document.createElement( 'a' );
							bLink.innerHTML = "<!--{t|escape:'javascript'}-->Go<!--{/t}-->";
							bLink.onclick = w.onGoClick;
							bLink.className = 'clickable';
							bLink.id = 'workflow_status_go_' + d[i].patient_id + '_' + d[i].date_of + '_' + cols[j];
							bElement.appendChild( bLink );

							var bImg = document.createElement( 'img' );
							bImg.id = 'workflow_status_img_' + d[i].patient_id + '_' + d[i].date_of + '_' + cols[j];
							bImg.onclick = w.onImageClick;
							bImg.src = "<!--{$htdocs}-->/images/teak/x_stop.16x16.png";
							bImg.border = 0;
							bElement.appendChild( bImg );
						}
						bRow.appendChild( bElement );
					}
					hBody.appendChild( bRow );
				}
			}
		}
	};

	_container_.addOnLoad(function() {
		try {
			var x = dojo.widget.byId( 'freemedContent' );
			var node = x.containerNode || x.domNode;
			var h = parseInt( node.offsetHeight ) - ( document.getElementById( 'workflowStatusTableHeader' ).offsetHeight + 80 );
			document.getElementById( 'workflowStatusTableBody' ).style.height = h + 'px';
		} catch ( e ) { }
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

