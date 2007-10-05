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

<!--{assign var='module' value='typeofservice'}-->

<!--{assign_block var='moduleName'}-->
	<!--{t}-->Type of Service<!--{/t}-->
<!--{/assign_block}-->

<!--{assign_block var='validation'}-->
	if ( content.tosname.length < 2 ) {
		m += "<!--{t|escape:'javascript'}-->You must enter a type.<!--{/t}-->\n";
		r = false;
	}
	if ( content.tosdescrip.length < 2 ) {
		m += "<!--{t|escape:'javascript'}-->You must enter a description.<!--{/t}-->\n";
		r = false;
	}
<!--{/assign_block}-->

<!--{assign_block var='initialLoad'}-->
	document.getElementById( 'tosname' ).value = data.tosname;
	document.getElementById( 'tosdescrip' ).value = data.tosdescrip;
<!--{/assign_block}-->

<!--{assign_block var='collectDataArray'}-->
	tosname: document.getElementById( 'tosname' ).value,
	tosdescrip: document.getElementById( 'tosdescrip' ).value
<!--{/assign_block}-->

<!--{assign_block var='moduleForm'}-->
<table border="0" style="width: auto;">

	<tr>
		<td align="right"><!--{t}-->Type of Service<!--{/t}--></td>
		<td><input type="text" id="tosname" size="20" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Description<!--{/t}--></td>
		<td><input type="text" id="tosdescrip" size="50" /></td>
	</tr>

</table>
<!--{/assign_block}-->

<!--{include file="org.freemedsoftware.module.supportmodule.form.tpl" module=$module moduleName=$moduleName moduleForm=$moduleForm collectDataArray=$collectDataArray initialLoad=$initialLoad validation=$validation}-->

