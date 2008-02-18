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

<!--{assign var='module' value='smsprovider'}-->

<!--{assign_block var='moduleName'}-->
	<!--{t}-->SMS Provider<!--{/t}-->
<!--{/assign_block}-->

<!--{assign_block var='validation'}-->
	if ( content.providername.length < 2 ) {
		m += "<!--{t|escape:'javascript'}-->You must enter a name.<!--{/t}-->\n";
		r = false;
	}
	if ( content.mailgwaddr.length < 2 ) {
		m += "<!--{t|escape:'javascript'}-->You must enter a SMS-gateway.<!--{/t}-->\n";
		r = false;
	}
<!--{/assign_block}-->

<!--{assign_block var='initialLoad'}-->
	document.getElementById( 'providername' ).value = data.providername;
	document.getElementById( 'numberlength' ).value = parseInt( data.numberlength );
	document.getElementById( 'mailgwaddr' ).value = data.mailgwaddr;
	document.getElementById( 'countrycode' ).selectedIndex = data.countrycode;
<!--{/assign_block}-->

<!--{assign_block var='collectDataArray'}-->
	providername: document.getElementById('providername').value,
	mailgwaddr: document.getElementById('mailgwaddr').value,
	numberlength: parseInt( document.getElementById('numberlength').value ),
	countrycode: document.getElementById('countrycode').value
<!--{/assign_block}-->

<!--{assign_block var='moduleForm'}-->
<table border="0" style="width: auto;">

	<tr>
		<td align="right"><!--{t}-->Provider Name<!--{/t}--></td>
		<td><input type="text" id="providername" size="50" maxlength="250" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->SMS-Gateway<!--{/t}--></td>
		<td><input type="text" id="mailgwaddr" size="50" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Number Length<!--{/t}--></td>
		<td><input type="text" id="numberlength" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Use Country Code?<!--{/t}--></td>
		<td><select id="countrycode">
			<option value="0"><!--{t}-->No<!--{/t}--></option>
			<option value="1"><!--{t}-->Yes<!--{/t}--></option>
		</select></td>
	</tr>

</table>
<!--{/assign_block}-->

<!--{include file="org.freemedsoftware.module.supportmodule.form.tpl" module=$module moduleName=$moduleName moduleForm=$moduleForm collectDataArray=$collectDataArray initialLoad=$initialLoad validation=$validation}-->

