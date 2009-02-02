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

<!--{assign var='module' value='roommodule'}-->

<!--{assign_block var='moduleName'}-->
	<!--{t}-->Room<!--{/t}-->
<!--{/assign_block}-->

<!--{assign_block var='validation'}-->
	if ( content.roomname.length < 2 ) {
		m += "<!--{t|escape:'javascript'}-->You must enter a name.<!--{/t}-->\n";
		r = false;
	}
	if ( parseInt( content.roompos ) < 1 ) {
		m += "<!--{t|escape:'javascript'}-->You must select a place of service.<!--{/t}-->\n";
		r = false;
	}
<!--{/assign_block}-->

<!--{assign_block var='initialLoad'}-->
	roompos.onAssign( data.roompos );
	roomequipment.onAssign( data.roomequipment );
<!--{/assign_block}-->

<!--{assign_block var='collectDataArray'}-->
	roomequipment: roomequipment.getValue()
<!--{/assign_block}-->

<!--{assign_block var='moduleForm'}-->
<table border="0" style="width: auto;">

	<tr>
		<td align="right"><!--{t}-->Name<!--{/t}--></td>
		<td><input type="text" id="roomname" name="roomname" size="20" maxlength="20" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Place of Service<!--{/t}--></td>
		<td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="FacilityModule" varname="roompos"}--></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Description<!--{/t}--></td>
		<td><input type="text" id="roomdescrip" name="roomdescrip" size="40" maxlength="40" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Equipment<!--{/t}--></td>
		<td><!--{include file="org.freemedsoftware.widget.multisupportpicklist.tpl" module="RoomEquipment" varname="roomequipment"}--></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Surgery Enabled?<!--{/t}--></td>
		<td><select id="roomsurgery" name="roomsurgery">
			<option value="n"><!--{t}-->No<!--{/t}--></option>
			<option value="y"><!--{t}-->Yes<!--{/t}--></option>
		</select></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Booking Enabled?<!--{/t}--></td>
		<td><select id="roombooking" name="roombooking">
			<option value="y"><!--{t}-->Yes<!--{/t}--></option>
			<option value="n"><!--{t}-->No<!--{/t}--></option>
		</select></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->IP Address<!--{/t}--></td>
		<td><input type="text" id="roomipaddr" name="roomipaddr" size="20" maxlength="16" /></td>
	</tr>

</table>
<!--{/assign_block}-->

<!--{include file="org.freemedsoftware.module.supportmodule.form.tpl" module=$module moduleName=$moduleName moduleForm=$moduleForm collectDataArray=$collectDataArray initialLoad=$initialLoad validation=$validation}-->

