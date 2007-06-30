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
	dojo.require( 'dojo.event.*' );

	var notes = {
		handleResponse: function ( data ) {
			if (data) {
				freemedMessage( "<!--{t}-->Added progress note.<!--{/t}-->", "INFO" );
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
		submit: function ( ) {
			try {
				dojo.widget.byId('ModuleFormCommitChangesButton').disable();
			} catch ( err ) { }
			var myContent = {
				<!--{if $id}-->
				id: "<!--{$id|escape}-->",
				<!--{/if}-->
				pnotesdt: dojo.widget.byId('note.dateOf').getValue(),
				pnotesdoc: parseInt( document.getElementById('note.provider').value ),
				pnotesdescrip: document.getElementById('note.descrip').value,
				pnotes_S: document.getElementById('note_S').value,
				pnotes_O: document.getElementById('note_O').value,
				pnotes_A: document.getElementById('note_A').value,
				pnotes_P: document.getElementById('note_P').value,
				pnotes_I: document.getElementById('note_I').value,
				pnotes_E: document.getElementById('note_E').value,
				pnotes_R: document.getElementById('note_R').value,
				pnotespat: '<!--{$patient|escape}-->'
			};
			if (notes.validate( myContent )) {
				dojo.io.bind({
					method: "POST",
					content: {
						param0: myContent
					},
					url: "<!--{$relay}-->/org.freemedsoftware.module.ProgressNotes.<!--{if $id}-->mod<!--{else}-->add<!--{/if}-->",
					load: function ( type, data, evt ) {
						notes.handleResponse( data );
					},
					mimetype: "text/json"
				});
			}
		},
		loadData: function ( data ) {
			dojo.widget.byId('note.dateOf').setValue( data['pnotesdt'] );
			document.getElementById('note.descrip').value = data['pnotesdescrip'];
			document.getElementById('note_S').value = data['pnotes_S'];
			document.getElementById('note_O').value = data['pnotes_O'];
			document.getElementById('note_A').value = data['pnotes_A'];
			document.getElementById('note_P').value = data['pnotes_P'];
			document.getElementById('note_I').value = data['pnotes_I'];
			document.getElementById('note_E').value = data['pnotes_E'];
			document.getElementById('note_R').value = data['pnotes_R'];
			dojo.event.topic.publish( 'note.provider-assign', data['pnotesdoc'] );
		},
		initialLoad: function ( ) {
			<!--{if $id}-->
			dojo.io.bind({
				method: "POST",
				content: {
					param0: "<!--{$id|escape}-->"
				},
				url: "<!--{$relay}-->/org.freemedsoftware.module.ProgressNotes.GetRecord",
				load: function ( type, data, evt ) {
					notes.loadData( data );
				},
				mimetype: "text/json"
			});
			<!--{/if}-->
		},
		OnLoadRecent: function ( ) {
			var prev = document.getElementById( 'prevProgressNoteDate' ).value;
			dojo.io.bind({
				method: "POST",
				content: {
					param0: "<!--{$patient|escape}-->",
					param1: prev
				},
				url: '<!--{$relay}-->/org.freemedsoftware.module.ProgressNotes.GetRecentRecord',
				load: function( type, data, evt ) {
					try {
						notes.loadData( data );
					} catch (e) { }
				},
				mimetype: "text/json"
			});
		},
		OnSelectTab: function( id ) {
			var myId = id.widgetId.replace('Pane', '');
			try {
				document.getElementById(myId).focus();
			} catch (e) { }
		}
	};

	_container_.addOnLoad(function() {
		dojo.event.connect( dojo.widget.byId('ModuleFormCommitChangesButton'), 'onClick', notes, 'submit' );
		dojo.event.topic.subscribe ( 'noteTabContainer-selectChild', notes, "OnSelectTab" );
		<!--{if $id}-->notes.initialLoad();<!--{/if}-->
		<!--{if not $id}-->
		dojo.event.connect( dojo.widget.byId( 'importPreviousProgressNote' ), 'onClick', notes, 'OnLoadRecent' );
		<!--{/if}-->
	});
	_container_.addOnUnload(function() {
		dojo.event.disconnect( dojo.widget.byId('ModuleFormCommitChangesButton'), 'onClick', notes, 'submit' );
		dojo.event.topic.unsubscribe ( 'noteTabContainer-selectChild', notes, "OnSelectTab" );
		<!--{if not $id}-->
		dojo.event.disconnect( dojo.widget.byId( 'importPreviousProgressNote' ), 'onClick', notes, 'OnLoadRecent' );
		<!--{/if}-->
	});

</script>

<h3><!--{t}-->Progress Note<!--{/t}--></h3>

<div dojoType="TabContainer" id="noteTabContainer" style="width: 100%; height: 80%;">

        <div dojoType="ContentPane" id="noteSummaryPane" label="<!--{t}-->Summary<!--{/t}-->">

		<table border="0" style="width: auto;">

		<!--{if not $id}-->
		<tr>
			<td align="right"></td>
			<td><table border="0"><tr><td><button dojoType="Button" id="importPreviousProgressNote" widgetId="importPreviousProgressNote">
				<!--{t}-->Import Previous Notes for <!--{/t}-->
			</button></td><td>
			<input dojoType="Select"
			autocomplete="false"
			id="prevProgressNoteDate_widget" widgetId="prevProgressNoteDate_widget"
			setValue="if (arguments[0]) { document.getElementById('prevProgressNoteDate').value = arguments[0]; }"
			style="width: 100px;"
			dataUrl="<!--{$relay}-->/org.freemedsoftware.module.ProgressNotes.RecentDates?param0=<!--{$patient|escape}-->&param1=%{searchString}"
			mode="remote"
			/></td></tr></table></td>
			<input type="hidden" id="prevProgressNoteDate" />
		</tr>
		<!--{/if}-->

		<tr>
			<td align="right"><!--{t}-->Date<!--{/t}--></td>
			<td><input dojoType="DropdownDatePicker" id="note.dateOf" /></td>
		</tr>
	
		<tr>
			<td align="right"><!--{t}-->Provider<!--{/t}--></td>
			<td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="ProviderModule" varname="note.provider" methodName="internalPicklist"}--></td>
		</tr>

		<tr>
			<td align="right"><!--{t}-->Description<!--{/t}--></td>
			<td><input type="text" id="note.descrip" size="50" /></td>
		</tr>

		</table>

	</div>

        <div dojoType="ContentPane" id="notePane_S" label="<!--{t}-->Subjective<!--{/t}-->">
		<textarea id="note_S" style="width: 95%; height: 95%;"></textarea>
	</div>

        <div dojoType="ContentPane" id="notePane_O" label="<!--{t}-->Objective<!--{/t}-->">
		<textarea id="note_O" style="width: 95%; height: 95%;"></textarea>
	</div>

        <div dojoType="ContentPane" id="notePane_A" label="<!--{t}-->Assessment<!--{/t}-->">
		<textarea id="note_A" style="width: 95%; height: 95%;"></textarea>
	</div>

        <div dojoType="ContentPane" id="notePane_P" label="<!--{t}-->Plan<!--{/t}-->">
		<textarea id="note_P" style="width: 95%; height: 95%;"></textarea>
	</div>

        <div dojoType="ContentPane" id="notePane_I" label="<!--{t}-->Interval<!--{/t}-->">
		<textarea id="note_I" style="width: 95%; height: 95%;"></textarea>
	</div>

        <div dojoType="ContentPane" id="notePane_E" label="<!--{t}-->Education<!--{/t}-->">
		<textarea id="note_E" style="width: 95%; height: 95%;"></textarea>
	</div>

        <div dojoType="ContentPane" id="notePane_R" label="<!--{t}-->Rx<!--{/t}-->">
		<textarea id="note_R" style="width: 95%; height: 95%;"></textarea>
	</div>

</div> <!--{* Tab Container *}-->

<div align="center">
        <table border="0" style="width:200px;">
        <tr><td align="center">
	        <button dojoType="Button" id="ModuleFormCommitChangesButton" widgetId="ModuleFormCommitChangesButton">
	                <div><!--{t}-->Commit Changes<!--{/t}--></div>
	        </button>
        </td><td align="left">
        	<button dojoType="Button" id="ModuleFormCancelButton" widgetId="ModuleFormCancelButton" onClick="freemedPatientContentLoad( 'org.freemedsoftware.ui.patient.overview.default?patient=<!--{$patient}-->' );">
        	        <div><!--{t}-->Cancel<!--{/t}--></div>
        	</button>
        </td></tr></table>
</div>

