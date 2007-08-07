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

<!--{*

	File: org.freemedsoftware.module.supportmodule.form.tpl

		Support module form template.

	Parameters:

		$module - Module class name

		$moduleName - Textual module name

		$moduleForm - DHTML / Dojo fragment containing form

		$collectDataArray - JS array fragment

		$initialLoad - JS fragment for assigning values from data structure

		$validation - (optional) JS / Dojo validation fragment

*}-->

<script type="text/javascript">
	dojo.require( 'dojo.widget.Form' );

	var m = {
		handleResponse: function ( data ) {
			if (data) {
				<!--{if $id}-->
				freemedMessage( "<!--{t}-->Committed changes.<!--{/t}-->", "INFO" );
				<!--{else}-->
				freemedMessage( "<!--{t}-->Added record.<!--{/t}-->", "INFO" );
				<!--{/if}-->
				freemedLoad( 'org.freemedsoftware.ui.supportdata.list?module=<!--{$module}-->' );
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
		initialLoad: function ( ) {
			<!--{if $id}-->
			dojo.io.bind({
				method: "POST",
				content: {
					param0: "<!--{$id|escape}-->"
				},
				url: "<!--{$relay}-->/org.freemedsoftware.module.<!--{$module}-->.GetRecord",
				load: function ( type, data, evt ) {
					try {
						dojo.widget.byId( 'moduleForm' ).setValues( data );
					} catch ( err ) { }
					<!--{$initialLoad}-->
				},
				mimetype: "text/json"
			});
			<!--{/if}-->
		},
		submit: function ( ) {
			try {
				dojo.widget.byId('ModuleFormCommitChangesButton').disable();
			} catch ( err ) { }
			var myContent = {
				<!--{if $id}-->id: "<!--{$id|escape}-->"<!--{if $collectDataArray}-->,<!--{/if}--><!--{/if}-->
				<!--{$collectDataArray}-->
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
		}
	};

	_container_.addOnLoad(function() {
		m.initialLoad();
		dojo.event.connect( dojo.widget.byId('ModuleFormCommitChangesButton'), 'onClick', m, 'submit' );
	});
	_container_.addOnUnload(function() {
		dojo.event.disconnect( dojo.widget.byId('ModuleFormCommitChangesButton'), 'onClick', m, 'submit' );
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
        	<button dojoType="Button" id="ModuleFormCancelButton" widgetId="ModuleFormCancelButton" onClick="freemedLoad( 'org.freemedsoftware.ui.supportdata.list?module=<!--{$module}-->' );">
        	        <div><img src="<!--{$htdocs}-->/images/teak/x_stop.16x16.png" border="0" width="16" height="16" /> <!--{t}-->Cancel<!--{/t}--></div>
        	</button>
        </td></tr></table>
</div>

