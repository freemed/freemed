{* Smarty *}
{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // Copyright (C) 1999-2006 FreeMED Software Foundation
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
*}
<script type="text/javascript" src="{$base_uri}/lib/dojo/dojo.js"></script>
<script language="javascript">
        dojo.require("dojo.widget.Button");
        dojo.require("dojo.widget.Checkbox");
        dojo.require("dojo.widget.DropdownDatePicker");
</script>

<div>
<table style="border: 0; padding: 1em;">

	<tr>
		<td>Name (Last, First Middle)</td>
		<td>
			<input type="text" name="ptlname" value="{$record.ptlname|escape}" size="20" maxlength="50" /> <b>,</b>
			<input type="text" name="ptfname" value="{$record.ptfname|escape}" size="20" maxlength="50" />
			<input type="text" name="ptmname" value="{$record.ptmname|escape}" size="10" />
		</td>
	</tr>

	<tr>
		<td>Date of Birth</td>
		<td><div dojoType="DropdownDatePicker" id="ptdob" date="{$record.ptdob}" containerToggle="wipe"></div></td>
	</tr>

	<tr>
		<td>Gender</td>
		<td>
			<select name="ptsex">
				<option value="f">Female</option>
				<option value="m">Male</option>
			</select>
		</td>
	</tr>

</table>
</div>

