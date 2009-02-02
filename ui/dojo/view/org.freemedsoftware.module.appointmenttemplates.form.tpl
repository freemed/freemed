<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
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

<!--{assign var='module' value='appointmenttemplates'}-->

<!--{assign_block var='moduleName'}-->
	<!--{t}-->Appointment Template<!--{/t}-->
<!--{/assign_block}-->

<!--{assign_block var='initialLoad'}-->
	document.getElementById( 'atname' ).value = data.atname;
	document.getElementById( 'atduration' ).value = data.atduration;
	if ( data.atequipment ) { atequipment.onAssign( data.atequipment ); }
	dojo.widget.byId( 'atcolor' ).currentValue = data.atcolor;
<!--{/assign_block}-->

<!--{assign_block var='collectDataArray'}-->
	atname: document.getElementById('atname').value,
	atduration: document.getElementById('atduration').value,
	atequipment: atequipment.getValue(),
	atcolor: dojo.widget.byId( 'atcolor' ).currentValue
<!--{/assign_block}-->

<!--{assign_block var='moduleForm'}-->
<table border="0" style="width: auto;">

	<tr>
		<td align="right"><!--{t}-->Template Name<!--{/t}--></td>
		<td><input type="text" id="atname" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Duration<!--{/t}--></td>
		<td><input type="text" id="atduration" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Required Equipment<!--{/t}--></td>
		<td><!--{include file="org.freemedsoftware.widget.multisupportpicklist.tpl" module="RoomEquipment" varname="atequipment"}--></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Color<!--{/t}--></td>
		<td><div dojoType="ColorPalette" id="atcolor" widgetId="atcolor" /></td>
	</tr>

</table>
<!--{/assign_block}-->

<!--{include file="org.freemedsoftware.module.supportmodule.form.tpl" module=$module moduleName=$moduleName moduleForm=$moduleForm collectDataArray=$collectDataArray initialLoad=$initialLoad validation=$validation}-->

