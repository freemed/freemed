<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // Copyright (C) 1999-2010 FreeMED Software Foundation
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

<!--{assign var='module' value='workflowstatustype'}-->

<!--{assign_block var='moduleName'}-->
	<!--{t}-->Workflow Status Type<!--{/t}-->
<!--{/assign_block}-->

<!--{assign_block var='validation'}-->
	if ( content.status_name.length < 2 ) {
		m += "<!--{t|escape:'javascript'}-->You must enter a name.<!--{/t}-->\n";
		r = false;
	}
	if ( content.status_module.length < 2 ) {
		m += "<!--{t|escape:'javascript'}-->You must enter a module.<!--{/t}-->\n";
		r = false;
	}
<!--{/assign_block}-->

<!--{assign_block var='initialLoad'}-->
	document.getElementById( 'status_name' ).value = data.status_name;
	document.getElementById( 'status_order' ).value = data.status_order;
	document.getElementById( 'status_module' ).value = data.status_module;
	document.getElementById( 'active' ).selectedIndex = parseInt( data.active ) + 0;
<!--{/assign_block}-->

<!--{assign_block var='collectDataArray'}-->
	status_name: document.getElementById('status_name').value,
	status_order: parseInt( document.getElementById('status_order').value ) + 0,
	status_module: document.getElementById('status_module').value,
	active: document.getElementById('active').value
<!--{/assign_block}-->

<!--{assign_block var='moduleForm'}-->
<table border="0" style="width: auto;">

	<tr>
		<td align="right"><!--{t}-->Name<!--{/t}--></td>
		<td><input type="text" id="status_name" size="30" maxlength="250" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Priority / Order<!--{/t}--></td>
		<td><input type="text" id="status_order" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Module<!--{/t}--></td>
		<td><input type="text" id="status_module" size="50" maxlength="250" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Active<!--{/t}--></td>
		<td><select id="active">
			<option value="0"><!--{t}-->Inactive<!--{/t}--></option>
			<option value="1"><!--{t}-->Active<!--{/t}--></option>
		</select></td>
	</tr>

</table>
<!--{/assign_block}-->

<!--{include file="org.freemedsoftware.module.supportmodule.form.tpl" module=$module moduleName=$moduleName moduleForm=$moduleForm collectDataArray=$collectDataArray initialLoad=$initialLoad validation=$validation}-->

