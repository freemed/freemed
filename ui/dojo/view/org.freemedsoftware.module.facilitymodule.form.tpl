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
	dojo.require("dojo.widget.InternetTextbox");
	dojo.require("dojo.widget.UsTextbox");

	var m = {
		handleResponse: function ( data ) {
			if (data) {
				freemedMessage( "<!--{t}-->Added record.<!--{/t}-->", "INFO" );
				freemedLoad( 'org.freemedsoftware.ui.supportdata.list?module=<!--{$module}-->' );
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
				url: "<!--{$relay}-->/org.freemedsoftware.module.facilitymodule.GetRecord",
				load: function ( type, data, evt ) {
					m.handleResponse( data );
				},
				mimetype: "text/json",
				sync: true
			});
			<!--{/if}-->
		},
		submit: function ( ) {
			try {
				dojo.widget.byId('ModuleFormCommitChangesButton').disable();
			} catch ( err ) { }
			var myContent = {
				<!--{if $id}-->id: "<!--{$id|escape}-->",<!--{/if}-->
				atname: document.getElementById('atname').value,
				atduration: document.getElementById('atduration').value,
				atequipment: document.getElementById('atequipment').value
			};
			if (m.validate( myContent )) {
				dojo.io.bind({
					method: "POST",
					content: {
						param0: myContent
					},
					url: "<!--{$relay}-->/org.freemedsoftware.module.facilitymodule.<!--{if $id}-->mod<!--{else}-->add<!--{/if}-->",
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

<h3><!--{t}-->Facility<!--{/t}--></h3>

<div dojoType="TabContainer" id="facilityTabContainer" style="width: 100%; height: 20em;">

	<div dojoType="ContentPane" label="<!--{t}-->Primary Information<!--{/t}-->">
		<table border="0">

			<tr>
				<td align="right"><!--{t}-->Facility Name<!--{/t}--></td>
				<td align="left"><input type="text" id="psrname" size="30" maxlength="100" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Address (Line 1)<!--{/t}--></td>
				<td align="left"><input type="text" id="psraddr1" size="20" maxlength="50" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Address (Line 2)<!--{/t}--></td>
				<td align="left"><input type="text" id="psraddr2" size="20" maxlength="50" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->City, State, Zip<!--{/t}--></td>
				<td><input dojoType="Select"
				autocomplete="false"
				id="psrcsz_widget"
				style="width: 300px;"
				dataUrl="<!--{$relay}-->/org.freemedsoftware.module.Zipcodes.CityStateZipPicklist?param0='%{searchString}'"
				setValue="document.getElementById('psrcsz').value = arguments[0];"
				mode="remote" />
				<input type="hidden" id="psrcsz" value="" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Country<!--{/t}--></td>
				<td align="left"><input type="text" id="psrcountry" size="30" maxlength="100" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}--><!--{/t}--></td>
				<td align="left"></td>
			</tr>

		</table>
	</div>

	<div dojoType="ContentPane" label="<!--{t}-->Details<!--{/t}-->">

		<table border="0">

			<tr>
				<td align="right"><!--{t}-->Description<!--{/t}--></td>
				<td align="left"><input type="text" id="psrnote" size="20" maxlength="40" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Default Provider<!--{/t}--></td>
				<td align="left"><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="ProviderModule" varname="psrdefphy"}--></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Place of Service Code<!--{/t}--></td>
				<td align="left"><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="PlaceOfService" varname="psrpos"}--></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Employer Identification Number<!--{/t}--></td>
				<td align="left"><input type="text" id="psrein" size="10" maxlength="15" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Rendering NPI Number<!--{/t}--></td>
				<td align="left"><input type="text" id="psrnpi" size="10" maxlength="15" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Employer Taxonomy Number<!--{/t}--></td>
				<td align="left"><input type="text" id="psrtaxonomy" size="10" maxlength="15" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Internal or External Facility<!--{/t}--></td>
				<td align="left"><select id="psrintext">
					<option value="0"><!--{t}-->Internal<!--{/t}--></option>
					<option value="1"><!--{t}-->External<!--{/t}--></option>
				</select></td>
			</tr>

		</table>
	</div>

	<div dojoType="ContentPane" label="<!--{t}-->Contact<!--{/t}-->">
		<table border="0">

			<tr>
				<td align="right"><!--{t}-->Phone Number<!--{/t}--></td>
				<td align="left"><input dojoType="UsPhoneNumberTextbox" type="text" id="psrphone" size="16" maxlength="16" value="" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Fax Number<!--{/t}--></td>
				<td align="left"><input dojoType="UsPhoneNumberTextbox" type="text" id="psrfax" size="16" maxlength="16" value="" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Email Address<!--{/t}--></td>
				<td align="left"><input dojoType="EmailTextbox" type="text" id="psremail" size="50" maxlength="50" value="" /></td>
			</tr>

		</table>
	</div>

	<div dojoType="ContentPane" label="<!--{t}-->Electronic Billing<!--{/t}-->">
		<table border="0">

			<tr>
				<td align="right"><!--{t}-->X12 Identifier<!--{/t}--></td>
				<td align="left"><input type="text" id="psrname" size="30" maxlength="100" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->X12 Identifier Type<!--{/t}--></td>
				<td align="left"></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Email Address<!--{/t}--></td>
				<td align="left"></td>
			</tr>

		</table>
	</div>

</div> <!--{* TabContainer *}-->

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

