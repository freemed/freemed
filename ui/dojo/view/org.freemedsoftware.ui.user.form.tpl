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

<!--{method var='aclGroups' namespace='org.freemedsoftware.module.ACL.UserGroups'}-->

<script type="text/javascript">
	var m = {
		handleResponse: function ( data ) {
			if (data) {
				<!--{if $id}-->
				freemedMessage( "<!--{t|escape:'javascript'}-->Committed changes.<!--{/t}-->", "INFO" );
				<!--{else}-->
				freemedMessage( "<!--{t|escape:'javascript'}-->Added record.<!--{/t}-->", "INFO" );
				<!--{/if}-->
				freemedLoad( 'org.freemedsoftware.ui.user' );
			} else {
				dojo.widget.byId('ModuleFormCommitChangesButton').enable();
			}
		},
		validate: function ( content ) {
			var r = true;
			var m = "";
			if ( content.username.length < 2 ) {
				m += "<!--{t|escape:'javascript'}-->You must enter a name.<!--{/t}-->\n";
				r = false;
			}
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
				url: "<!--{$relay}-->/org.freemedsoftware.api.UserInterface.GetRecord",
				load: function ( type, data, evt ) {
					document.getElementById( 'username' ).value = data.username;
					document.getElementById( 'userpassword' ).value = data.userpassword;
					document.getElementById( 'userpasswordverify' ).value = data.userpassword;
					document.getElementById( 'userdescrip' ).value = data.userdescrip;
					<!--{foreach from=$aclGroups item='x'}-->
					<!--{assign var="grpTmp" value=$x.1}-->
					<!--{method var='y' namespace='org.freemedsoftware.module.ACL.UserInGroup' param0=$id param1=$grpTmp}-->
					<!--{if $y}-->document.getElementById( 'acl_<!--{$grpTmp}-->' ).checked = true;<!--{/if}-->
					<!--{/foreach}-->
					userrealphy.onAssign( data.userrealphy );
				},
				mimetype: "text/json"
			});
			<!--{/if}-->
		},
		submit: function ( ) {
			try {
				dojo.widget.byId('ModuleFormCommitChangesButton').disable();
			} catch ( err ) { }
			// Aggregate ACLs
			var useracl = [];
			<!--{foreach from=$aclGroups item='x'}-->
			if ( document.getElementById('acl_<!--{$x.1}-->').checked ) {
				useracl.push( <!--{$x.1}--> );
			}
			<!--{/foreach}-->
			var myContent = {
				<!--{if $id}-->id: "<!--{$id|escape}-->",<!--{/if}-->
				username: document.getElementById( 'username' ).value,
				userpassword: document.getElementById( 'userpassword' ).value,
				userdescrip: document.getElementById( 'userdescrip' ).value,
				usertype: dojo.widget.byId( 'usertype' ).getValue(),
				useracl: useracl
			};
			if (m.validate( myContent )) {
				dojo.io.bind({
					method: "GET",
					content: {
						param0: myContent
					},
					url: "<!--{$relay}-->/org.freemedsoftware.api.UserInterface.<!--{if $id}-->mod<!--{else}-->add<!--{/if}-->",
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

<h3><!--{t}-->User<!--{/t}--></h3>

<div dojoType="TabContainer" id="mainTabContainer" style="width: 100%; height: 85%;">

	<div dojoType="ContentPane" id="userMainPane" label="User">

		<table style="border: 0; padding: 1em;">

			<tr>
				<td><!--{t}-->Username<!--{/t}--></td>
				<td><input type="text" id="username" size="30" /></td>
			</tr>

			<tr>
				<td><!--{t}-->Password<!--{/t}--></td>
				<td><input type="password" id="userpassword" size="32" onFocus="document.getElementById( 'userpassword' ).value = '';" /></td>
			</tr>

			<tr>
				<td><!--{t}-->Password (verify)<!--{/t}--></td>
				<td><input type="password" id="userpasswordverify" size="32" onFocus="document.getElementById( 'userpasswordverify' ).value = '';" /></td>
			</tr>

			<tr>
				<td><!--{t}-->Description<!--{/t}--></td>
				<td><input type="text" id="userdescrip" size="30" /></td>
			</tr>

			<tr>
				<td><!--{t}-->User Type<!--{/t}--></td>
				<td>
					<select dojoType="Select" id="usertype" style="width:200px;" autocomplete="false">
						<option value="phy"><!--{t}-->Physician<!--{/t}--></option>
						<option value="misc"><!--{t}-->Miscellaneous<!--{/t}--></option>
					</select>
				</td>
			</tr>

			<tr>
				<td><!--{t}-->Actual Provider<!--{/t}--></td>
				<td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="ProviderModule" varname="userrealphy"}--></td>
			</tr>

		</table>

	</div>

	<div dojoType="ContentPane" id="userACLPane" label="ACL">

		<table style="border: 0; padding: 1em; width: auto;">

		<!--{foreach from=$aclGroups item='x'}-->
			<tr>
				<td width="50"><input type="checkbox" id="acl_<!--{$x.1}-->" value="<!--{$x.1}-->"></td>
				<td><label for="acl_<!--{$x.1}-->"><!--{$x.0}--></label></td>
			</tr>
		<!--{/foreach}-->

		</table>

	</div>

</div>

<div align="center">
        <table border="0" style="width:200px;">
        <tr><td align="center">
	        <button dojoType="Button" id="ModuleFormCommitChangesButton" widgetId="ModuleFormCommitChangesButton">
	                <div><!--{t}-->Commit Changes<!--{/t}--></div>
	        </button>
        </td><td align="left">
        	<button dojoType="Button" id="ModuleFormCancelButton" widgetId="ModuleFormCancelButton" onClick="freemedLoad( 'org.freemedsoftware.ui.user' );">
        	        <div><!--{t}-->Cancel<!--{/t}--></div>
        	</button>
        </td></tr></table>
</div>

