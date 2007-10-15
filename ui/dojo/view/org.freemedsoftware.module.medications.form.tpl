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
	dojo.require( 'dojo.event.*' );

	var m = {
		handleResponse: function ( data ) {
			if (data) {
				<!--{if $id}-->
				freemedMessage( "<!--{t|escape:'javascript'}-->Committed changes.<!--{/t}-->", "INFO" );
				<!--{else}-->
				freemedMessage( "<!--{t|escape:'javascript'}-->Added record.<!--{/t}-->", "INFO" );
				<!--{/if}-->
				freemedPatientContentLoad( 'org.freemedsoftware.ui.patient.overview.default?patient=<!--{$patient}-->' );
			} else {
				dojo.widget.byId('ModuleFormCommitChangesButton').enable();
			}
		},
		validate: function ( content ) {
			var r = true;
			var m = "";
			// TODO: validation goes here
			if ( m.length > 1 ) { alert( m ); }
			return r;
		},
		initialLoad: function ( ) {
			<!--{if $id}-->
			dojo.io.bind({
				method: "POST",
				content: {
					param0: "<!--{$id|escape}-->"
				},
				url: "<!--{$relay}-->/org.freemedsoftware.module.Medications.GetAtoms",
				load: function ( type, data, evt ) {
					var w = dojo.widget.byId( 'medicationsAtomic' );
					w.store.setData( data );
				},
				mimetype: "text/json"
			});
			<!--{/if}-->
		},
		addAtom: function ( ) {
			var w = dojo.widget.byId( 'medicationsAtomic' );
			var d = {
				mdrug: document.getElementById( 'mdrug' ).value,
				mdosage: document.getElementById( 'mdosage' ).value,
				mroute: document.getElementById( 'mroute' ).value,
				id: m.atomCount
			};
			w.store.addData( d );
			m.atomCount = m.atomCount - 1;
			document.getElementById( 'mdrug' ).value = '';
			document.getElementById( 'mdosage' ).value = '';
			document.getElementById( 'mroute' ).value = '';
			document.getElementById( 'mdrug' ).focus();
			return true;
		},
		removeAtom: function ( ) {
			var w = dojo.widget.byId( 'medicationsAtomic' );
			w.store.removeData( w.getSelectedData() );
		},
		atomCount: -1,
		submit: function ( ) {
			try {
				dojo.widget.byId('ModuleFormCommitChangesButton').disable();
			} catch ( err ) { }
			var myContent = {
				<!--{if $id}-->id: "<!--{$id|escape}-->",<!--{/if}-->
				mpatient: '<!--{$patient|escape}-->'
			};
			if (m.validate( myContent )) {
				dojo.io.bind({
					method: "POST",
					content: {
						param0: myContent
					},
					url: "<!--{$relay}-->/org.freemedsoftware.module.Medications.<!--{if $id}-->mod<!--{else}-->add<!--{/if}-->",
					load: function ( type, data, evt ) {
						if ( data ) {
							// Send atoms
							m.submitAtoms( data );
						} else {
							alert('Failed!');
						}
					},
					mimetype: "text/json"
				});
			}
		},
		submitAtoms: function ( myId ) {
			<!--{if $id}-->
			var thisId = <!--{$id}-->;
			<!--{else}-->
			var thisId = myId;
			<!--{/if}-->

			// Get atoms
			var w = dojo.widget.byId( 'medicationsAtomic' );
			if ( w.store.get().length > 0 ) {
				var x = w.store.get();
				var y = [];
				for ( var i=0; i<x.length; i++ ) {
					if ( x[i].src.id < 0 ) { x[i].src.id = 0; }
					y.push( x[i].src );
				}

				dojo.io.bind({
					method: 'GET',
					content: {
						param0: "<!--{$patient|escape}-->",
						param1: thisId,
						param2: dojo.json.serialize( y )
					},
					url: "<!--{$relay}-->/org.freemedsoftware.module.Medications.SetAtoms",
					load: function( type, data, evt ) {
						m.handleResponse( data );
					},
					mimetype: 'text/json'
				});
			} else {
				m.handleResponse( false );
			}
		}
	};

	_container_.addOnLoad(function() {
		m.initialLoad();
		dojo.event.connect( dojo.widget.byId('ModuleFormCommitChangesButton'), 'onClick', m, 'submit' );
		dojo.event.connect( dojo.widget.byId( 'addAtomButton' ), 'onClick', m, 'addAtom' );
		dojo.event.connect( dojo.widget.byId( 'removeAtomButton' ), 'onClick', m, 'removeAtom' );
	});
	_container_.addOnUnload(function() {
		dojo.event.disconnect( dojo.widget.byId('ModuleFormCommitChangesButton'), 'onClick', m, 'submit' );
		dojo.event.disconnect( dojo.widget.byId( 'addAtomButton' ), 'onClick', m, 'addAtom' );
		dojo.event.disconnect( dojo.widget.byId( 'removeAtomButton' ), 'onClick', m, 'removeAtom' );
	});

</script>

<h3><!--{t}-->Medications<!--{/t}--></h3>

<table border="0" style="width: 100%;">

	<tr>
		<td align="right"><!--{t}-->Drug<!--{/t}--></td>
		<td align="left">
			<input type="text" id="mdrug" name="mdrug" />
		</td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Dosage<!--{/t}--></td>
		<td align="left">
			<input type="text" id="mdosage" name="mdosage" />
		</td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Route<!--{/t}--></td>
		<td align="left">
			<input type="text" id="mroute" name="mroute" />
		</td>
	</tr>

	<tr>
		<td colspan="2" align="center">
		<div align="center" style="padding: .5ex;">
			<table border="0" style="width:auto;"><tr>
			<td>
			<button dojoType="Button" id="addAtomButton" widgetId="addAtomButton">
				<!--{t}-->Add Medication<!--{/t}-->
			</button>
			</td><td>
			<button dojoType="Button" id="removeAtomButton" widgetId="removeAtomButton">
				<!--{t}-->Remove Medication<!--{/t}-->
			</button>
			</td>
			</tr></table>
		</div>
		<div class="tableContainer">
			<table dojoType="FilteringTable" id="medicationsAtomic" widgetId="medicationsAtomic"
			 headClass="fixedHeader" tbodyClass="scrollContent" enableAlternateRows="true" rowAlternateClass="alternateRow"
			 valueField="id" border="0" multiple="false" style="height: 100%; width: 80%;">
			<thead id="medicationsAtomicHead">
				<tr>
					<th field="mdrug" dataType="String"><!--{t}-->Drug<!--{/t}--></th>
					<th field="mdosage" dataType="String"><!--{t}-->Dosage<!--{/t}--></th>
					<th field="mroute" dataType="String"><!--{t}-->Route<!--{/t}--></th>
				</tr>
			</thead>
			<tbody></tbody>
			</table>
		</div>
		</td>
	</tr>

</table>

<div align="center">
        <table border="0" style="width:200px;">
        <tr><td align="center">
	        <button dojoType="Button" id="ModuleFormCommitChangesButton" widgetId="ModuleFormCommitChangesButton">
			<div><img src="<!--{$htdocs}-->/images/teak/check_go.16x16.png" border="0" width="16" height="16" /> <!--{t}-->Commit Changes<!--{/t}--></div>
	        </button>
        </td><td align="left">
        	<button dojoType="Button" id="ModuleFormCancelButton" widgetId="ModuleFormCancelButton" onClick="freemedPatientContentLoad( 'org.freemedsoftware.ui.patient.overview.default?patient=<!--{$patient}-->' );">
			<div><img src="<!--{$htdocs}-->/images/teak/x_stop.16x16.png" border="0" width="16" height="16" /> <!--{t}-->Cancel<!--{/t}--></div>
        	</button>
        </td></tr></table>
</div>

