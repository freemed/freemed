<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // Copyright (C) 1999-2012 FreeMED Software Foundation
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

<!--{assign var='module' value='schedulerstatustype'}-->

<!--{assign_block var='moduleName'}-->
	<!--{t}-->Scheduler Status Type<!--{/t}-->
<!--{/assign_block}-->

<!--{assign_block var='validation'}-->
	if ( content.sname.length < 1 ) {
		m += "<!--{t|escape:'javascript'}-->You must enter a type.<!--{/t}-->\n";
		r = false;
	}
	if ( content.sdescrip.length < 1 ) {
		m += "<!--{t|escape:'javascript'}-->You must enter a description.<!--{/t}-->\n";
		r = false;
	}
<!--{/assign_block}-->

<!--{assign_block var='initialLoad'}-->
	document.getElementById( 'sname' ).value = data.sname;
	document.getElementById( 'sdescrip' ).value = data.sdescrip;
	document.getElementById( 'sage' ).value = data.sage;
	dojo.widget.byId( 'scolor' ).currentValue = data.scolor;
<!--{/assign_block}-->

<!--{assign_block var='collectDataArray'}-->
	sname: document.getElementById('sname').value,
	sdescrip: document.getElementById('sdescrip').value,
	sage: document.getElementById('sage').value,
	scolor: dojo.widget.byId( 'scolor' ).currentValue
<!--{/assign_block}-->

<!--{assign_block var='moduleForm'}-->
<table border="0" style="width: auto;">

	<tr>
		<td align="right"><!--{t}-->Name<!--{/t}--></td>
		<td><input type="text" id="sname" size="7" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Description<!--{/t}--></td>
		<td><input type="text" id="sdescrip" size="50" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Age<!--{/t}--></td>
		<td><input type="text" id="sage" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Color<!--{/t}--></td>
		<td><div dojoType="ColorPalette" id="scolor" widgetId="scolor" /></td>
	</tr>

</table>
<!--{/assign_block}-->

<!--{include file="org.freemedsoftware.module.supportmodule.form.tpl" module=$module moduleName=$moduleName moduleForm=$moduleForm collectDataArray=$collectDataArray initialLoad=$initialLoad validation=$validation}-->

