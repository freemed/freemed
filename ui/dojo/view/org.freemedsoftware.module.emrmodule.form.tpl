<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // Copyright (C) 1999-2008 FreeMED Software Foundation
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

<!--{*

	File: org.freemedsoftware.module.emrmodule.form.tpl

		EMR module form template

	Parameters:

		$module - Module class name

		$moduleName - Textual module name

		$moduleForm - DHTML / Dojo fragment containing form

		$patientVariable - Variable name of patient field

		$dateWidget - Name of date field used for previous records

		$collectDataArray - JS array fragment

		$initialLoad - JS fragment for assigning values from data structure

		$validation - (optional) JS / Dojo validation fragment

*}-->

<script type="text/javascript">

	var m = {
		handleResponse: function ( data ) {
			if (data) {
				<!--{if $id}-->
				freemedMessage( "<!--{t|escape:'javascript'}-->Updated record.<!--{/t}-->", "INFO" );
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
			<!--{$validation}-->
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
				<!--{$collectDataArray}-->
				<!--{$patientVariable}-->: '<!--{$patient|escape}-->'
			};
			var mForm = dojo.widget.byId( 'moduleForm' ).getValues( );
			try {
				for ( var mFormElement in mForm ) {
					myContent[ mFormElement ] = mForm[ mFormElement ];
				}
			} catch ( err ) { }
			if (m.validate( myContent )) {
				dojo.io.bind({
					method: "POST",
					content: {
						param0: myContent
					},
					url: "<!--{$relay}-->/org.freemedsoftware.module.<!--{$module}-->.<!--{if $id}-->mod<!--{else}-->add<!--{/if}-->",
					load: function ( type, data, evt ) {
						m.handleResponse( data );
					},
					mimetype: "text/json"
				});
			}
		},
		loadData: function ( data ) {
			try {
				dojo.widget.byId( 'moduleForm' ).setValues( data );
			} catch ( err ) { }
			<!--{$initialLoad}-->
		},
		initialLoad: function ( ) {
			<!--{if $id}-->
			dojo.io.bind({
				method: "POST",
				content: {
					param0: "<!--{$id|escape}-->"
				},
				url: "<!--{$relay}-->/org.freemedsoftware.module.<!--{$module}-->.GetRecord",
				load: function ( type, data, evt ) {
					m.loadData( data );
				},
				mimetype: "text/json"
			});
			<!--{/if}-->
		},
		OnLoadRecent: function ( ) {
			var prev = document.getElementById( '<!--{$dateWidget}-->' ).value;
			dojo.io.bind({
				method: "POST",
				content: {
					param0: "<!--{$patient|escape}-->",
					param1: prev
				},
				url: '<!--{$relay}-->/org.freemedsoftware.module.<!--{$module}-->.GetRecentRecord',
				load: function( type, data, evt ) {
					try {
						m.loadData( data );
					} catch (e) { }
				},
				mimetype: "text/json"
			});
		},
		OnSelectTab: function( id ) {
			var myId = id.widgetId.replace('Pane', '');
			try {
				document.getElementById( myId ).focus();
			} catch (e) { }
		}
	};

	_container_.addOnLoad(function() {
		dojo.event.connect( dojo.widget.byId('ModuleFormCommitChangesButton'), 'onClick', m, 'submit' );
		try { dojo.event.topic.subscribe ( 'noteTabContainer-selectChild', m, "OnSelectTab" ); } catch ( err ) { }
		<!--{if $id}-->m.initialLoad();<!--{/if}-->
		<!--{if not $id}-->
		try { dojo.event.connect( dojo.widget.byId( 'importPrevious' ), 'onClick', m, 'OnLoadRecent' ); } catch ( err ) { }
		<!--{/if}-->
	});
	_container_.addOnUnload(function() {
		dojo.event.disconnect( dojo.widget.byId('ModuleFormCommitChangesButton'), 'onClick', m, 'submit' );
		try { dojo.event.topic.unsubscribe ( 'noteTabContainer-selectChild', m, "OnSelectTab" ); } catch ( err ) { }
		<!--{if not $id}-->
		try { dojo.event.disconnect( dojo.widget.byId( 'importPrevious' ), 'onClick', m, 'OnLoadRecent' ); } catch ( err ) { }
		<!--{/if}-->
	});

</script>

<h3><!--{$moduleName}--></h3>

<form dojoType="Form" id="moduleForm" style="height: auto;">
<!--{$moduleForm}-->
</form>

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

