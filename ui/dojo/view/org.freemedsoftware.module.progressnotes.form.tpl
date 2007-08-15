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

<style type="text/css">
	.dojoFloatingPaneClient {{
		background-color: #ffffff;
	}
</style>

<script type="text/javascript">
	djConfig.dojoRichTextFrameUrl = "<!--{$htdocs}-->/dojo/src/widget/templates/richtextframe.html";
	dojo.require( 'dojo.widget.Editor2' );

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

			// Make sure all text fields have been saved
			notes.SaveLastTab();

			var myContent = {
				<!--{if $id}-->
				id: "<!--{$id|escape}-->",
				<!--{/if}-->
				pnotesdt: dojo.widget.byId('note.dateOf').getValue(),
				pnotesdoc: parseInt( document.getElementById('note.provider').value ),
				pnotesdescrip: document.getElementById('note.descrip').value,
				pnotes_S: dojo.byId('note_S_value').innerHTML,
				pnotes_O: dojo.byId('note_O_value').innerHTML,
				pnotes_A: dojo.byId('note_A_value').innerHTML,
				pnotes_P: dojo.byId('note_P_value').innerHTML,
				pnotes_I: dojo.byId('note_I_value').innerHTML,
				pnotes_E: dojo.byId('note_E_value').innerHTML,
				pnotes_R: dojo.byId('note_R_value').innerHTML,
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
			dojo.byId('note.descrip').value = data.pnotesdescrip;
			dojo.byId('note_S_value').innerHTML = data['pnotes_S'];
			dojo.byId('note_O_value').innerHTML = data['pnotes_O'];
			dojo.byId('note_A_value').innerHTML = data['pnotes_A'];
			dojo.byId('note_P_value').innerHTML = data['pnotes_P'];
			dojo.byId('note_I_value').innerHTML = data['pnotes_I'];
			dojo.byId('note_E_value').innerHTML = data['pnotes_E'];
			dojo.byId('note_R_value').innerHTML = data['pnotes_R'];
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
		SaveLastTab: function( ) {
			try {
				var t = dojo.widget.byId( notes.currentTab );
				var old = notes.currentTab.replace( 'Pane', '' );
				var w = dojo.widget.byId( old + '_editor' );

				// Save, then kill editor
				dojo.debug( 'DEBUG: save process for ' + old );
				dojo.byId( w.contentSource ).innerHTML = w.getEditorContent();

				t.removeChild( old + '_editor' );
			} catch (err) { }
		},
		OnSelectTab: function( id ) {
			var myId = id.widgetId.replace('Pane', '');

			notes.SaveLastTab();

			if ( notes.currentTab != id.widgetId ) {
				try {
					// Create new one
					if ( myId.match('_') ) {
						var w = dojo.widget.createWidget( 'dojo:Editor2', {
							id: myId + '_editor',
							widgetId: myId + '_editor',
							height: '300',
							shareToolbar: false
						}, dojo.byId( myId + '_container' ));
						w.contentSource = myId + '_value';
						dojo.event.connect( w, "editorOnLoad", dojo.lang.hitch( w, function() {
							this.replaceValue( dojo.byId( this.contentSource ).innerHTML );
							dojo.event.connect(this, "save", dojo.lang.hitch(this, function() {
								updateContent( this.contentSource, this.getEditorContent() );
							}));
						}));
						dojo.event.connect(dj_global, notes, "updateContent",
							dojo.lang.hitch( w, function(contentId, content){
								if (contentId == this.contentSource) {
									this.replaceValue( content );
								}
							})
						);
						id.addChild( w.domNode );
					}
				} catch (err) { }
			}

			try {
				document.getElementById( myId ).focus();
			} catch (e) { }
			notes.currentTab = id.widgetId;
		},
		updateContent: function ( contentId, content ) {
			dojo.byId( contentId ).innerHTML = content;
		},
		currentTab: ''
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
			style="width: 300px;"
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
			<td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="ProviderModule" varname="note.provider" methodName="internalPicklist" defaultValue=$SESSION.authdata.user_record.userrealphy}--></td>
		</tr>

		<tr>
			<td align="right"><!--{t}-->Description<!--{/t}--></td>
			<td><input type="text" id="note.descrip" size="50" /></td>
		</tr>

		</table>

	</div>

        <div dojoType="ContentPane" id="notePane_S" label="<!--{t}-->Subjective<!--{/t}-->">
		<div id="note_S_value" style="display: none;"></div>
		<div id="note_S_container"></div>
	</div>

        <div dojoType="ContentPane" id="notePane_O" label="<!--{t}-->Objective<!--{/t}-->">
		<div id="note_O_value" style="display: none;"></div>
		<div id="note_O_container"></div>
	</div>

        <div dojoType="ContentPane" id="notePane_A" label="<!--{t}-->Assessment<!--{/t}-->">
		<div id="note_A_value" style="display: none;"></div>
		<div id="note_A_container"></div>
	</div>

        <div dojoType="ContentPane" id="notePane_P" label="<!--{t}-->Plan<!--{/t}-->">
		<div id="note_P_value" style="display: none;"></div>
		<div id="note_P_container"></div>
	</div>

        <div dojoType="ContentPane" id="notePane_I" label="<!--{t}-->Interval<!--{/t}-->">
		<div id="note_I_value" style="display: none;"></div>
		<div id="note_I_container"></div>
	</div>

        <div dojoType="ContentPane" id="notePane_E" label="<!--{t}-->Education<!--{/t}-->">
		<div id="note_E_value" style="display: none;"></div>
		<div id="note_E_container"></div>
	</div>

        <div dojoType="ContentPane" id="notePane_R" label="<!--{t}-->Rx<!--{/t}-->">
		<div id="note_R_value" style="display: none;"></div>
		<div id="note_R_container"></div>
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

