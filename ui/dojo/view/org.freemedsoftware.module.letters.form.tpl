<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
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
	dojo.require( 'dojo.event.*' );
	dojo.require( 'dojo.widget.RichText' );

	var letters = {
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
				url: "<!--{$relay}-->/org.freemedsoftware.module.Letters.GetRecord",
				load: function ( type, data, evt ) {
					try {
					dojo.widget.byId('letters.dateOf').setValue( data.letterdt );
					dojo.widget.byId('letterText').setValue( data.lettertext );
					document.getElementById( 'lettersubject' ).value = data.lettersubject;
					lettersfromProvider.onAssign( data.letterfrom );
					letterstoProvider.onAssign( data.letterto );
					} catch (e) { alert(e); }
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
				<!--{if $id}-->id: "<!--{$id|escape}-->",<!--{/if}-->
				letterdt: dojo.widget.byId('letters.dateOf').getValue(),
				letterfrom: parseInt( document.getElementById('letters.fromProvider').value ),
				letterto: parseInt( document.getElementById('letters.toProvider').value ),
				lettertext: dojo.widget.byId('letterText').getValue(),
				lettersubject: document.getElementById( 'lettersubject' ).value,
				letterpatient: '<!--{$patient|escape}-->'
			};
			if (letters.validate( myContent )) {
				dojo.io.bind({
					method: "POST",
					content: {
						param0: myContent
					},
					url: "<!--{$relay}-->/org.freemedsoftware.module.Letters.<!--{if $id}-->mod<!--{else}-->add<!--{/if}-->",
					load: function ( type, data, evt ) {
						letters.handleResponse( data );
					},
					mimetype: "text/json"
				});
			}
		}
	};

	_container_.addOnLoad(function() {
		letters.initialLoad();
		dojo.event.connect( dojo.widget.byId('ModuleFormCommitChangesButton'), 'onClick', letters, 'submit' );
	});
	_container_.addOnUnload(function() {
		dojo.event.disconnect( dojo.widget.byId('ModuleFormCommitChangesButton'), 'onClick', letters, 'submit' );
	});

</script>

<h3><!--{t}-->Letters<!--{/t}--></h3>

<table border="0" style="width: auto;">

	<tr>
		<td align="right"><!--{t}-->Date<!--{/t}--></td>
		<td><input dojoType="DropdownDatePicker" id="letters.dateOf" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->From<!--{/t}--></td>
		<td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="ProviderModule" varname="letters.fromProvider" methodName="internalPicklist" defaultValue=$SESSION.authdata.user_record.userrealphy}--></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->To<!--{/t}--></td>
		<td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="ProviderModule" varname="letters.toProvider"}--></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Subject<!--{/t}--></td>
		<td><input type="text" id="lettersubject" size="50" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Template<!--{/t}--></td>
		<td><!--{include file="org.freemedsoftware.widget.letterstemplates.tpl" varname="ltemplate" inject="letterText"}--></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Letter<!--{/t}--></td>
		<td>
			<div dojoType="RichText" id="letterText" widgetId="letterText" style="border: 1px solid black; background-color: #ffffff; width: 30em;" height="15em" inheritWidth="true"></div>
		</td>
	</tr>

</table>

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

