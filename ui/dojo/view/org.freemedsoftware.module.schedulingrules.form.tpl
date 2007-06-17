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
			if ( content.reason.length < 2 ) {
				m += "<!--{t}-->You must enter a description.<!--{/t}-->\n";
				r = false;
			}
			// TODO: flesh this out more, better handling of rules
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
				url: "<!--{$relay}-->/org.freemedsoftware.module.schedulingrules.GetRecord",
				load: function ( type, data, evt ) {
					if ( data.provider ) {
						provider.onAssign( data.provider );
					}
					document.getElementById( 'reason' ).value = data.reason;
					if ( parseInt( data.dowbegin ) > 0 ) {
						document.getElementById( 'dowbegin' ).selectedIndex = parseInt( data.dowbegin );
					}
					if ( parseInt( data.dowend ) > 0 ) {
						document.getElementById( 'dowend' ).selectedIndex = parseInt( data.dowend );
					}
					if ( data.newpatient != null ) {
						document.getElementById( 'newpatient' ).selectedIndex = parseInt( data.newpatient ) + 1;
					}
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
				provider: provider.getValue(),
				reason: document.getElementById('reason').value,
				dowbegin: document.getElementById('dowbegin').value,
				dowend: document.getElementById('dowend').value,
				datebegin: dojo.widget.byId('datebegin').getValue,
				dateend: dojo.widget.byId('datebegin').getValue,
				newpatient: document.getElementById('newpatient').value
			};
			if (m.validate( myContent )) {
				dojo.io.bind({
					method: "POST",
					content: {
						param0: myContent
					},
					url: "<!--{$relay}-->/org.freemedsoftware.module.schedulingrules.<!--{if $id}-->mod<!--{else}-->add<!--{/if}-->",
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

<h3><!--{t}-->Scheduling Rule<!--{/t}--></h3>

<table border="0" style="width: auto;">

	<tr>
		<td align="right"><!--{t}-->Provider<!--{/t}--></td>
		<td><!--{include file="org.freemedsoftware.widget.multisupportpicklist.tpl" module="ProviderModule" varname="provider"}--></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Reason / Description<!--{/t}--></td>
		<td><input type="text" id="reason" size="50" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Day of Week Beginning<!--{/t}--></td>
		<td><select id="dowbegin">
			<option value=""></option>
			<option value="1"><!--{t}-->Sunday<!--{/t}--></option>
			<option value="2"><!--{t}-->Monday<!--{/t}--></option>
			<option value="3"><!--{t}-->Tuesday<!--{/t}--></option>
			<option value="4"><!--{t}-->Wednesday<!--{/t}--></option>
			<option value="5"><!--{t}-->Thursday<!--{/t}--></option>
			<option value="6"><!--{t}-->Friday<!--{/t}--></option>
			<option value="7"><!--{t}-->Saturday<!--{/t}--></option>
		</select></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Day of Week Ending<!--{/t}--></td>
		<td><select id="dowend">
			<option value=""></option>
			<option value="1"><!--{t}-->Sunday<!--{/t}--></option>
			<option value="2"><!--{t}-->Monday<!--{/t}--></option>
			<option value="3"><!--{t}-->Tuesday<!--{/t}--></option>
			<option value="4"><!--{t}-->Wednesday<!--{/t}--></option>
			<option value="5"><!--{t}-->Thursday<!--{/t}--></option>
			<option value="6"><!--{t}-->Friday<!--{/t}--></option>
			<option value="7"><!--{t}-->Saturday<!--{/t}--></option>
		</select></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Date Begin<!--{/t}--></td>
		<td><input dojoType="DropdownDatePicker" id="datebegin" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Date End<!--{/t}--></td>
		<td><input dojoType="DropdownDatePicker" id="dateend" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Patient Status<!--{/t}--></td>
		<td><select id="newpatient">
			<option value=""></option>
			<option value="1"><!--{t}-->New Patients Only<!--{/t}--></option>
			<option value="0"><!--{t}-->Established Patients<!--{/t}--></option>
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
        	<button dojoType="Button" id="ModuleFormCancelButton" widgetId="ModuleFormCancelButton" onClick="freemedLoad( 'org.freemedsoftware.ui.supportdata.list?module=<!--{$module}-->' );">
        	        <div><!--{t}-->Cancel<!--{/t}--></div>
        	</button>
        </td></tr></table>
</div>

