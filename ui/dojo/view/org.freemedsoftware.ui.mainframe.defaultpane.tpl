<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
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

<script language="javascript">
	dojo.require('dojo.widget.DropdownDatePicker');
</script>

<div align="center" style="size: 10pt; border: 1px solid #5555ff; padding: 5px; background-color: #aaaaff;">
<table border="0">
	<tr>
		<td><b>Today's Patients (<!--{$dailyAppointmentsDate}-->)</b></td>
		<td><input dojoType="DropdownDatePicker" date="<!--{$dailyAppointmentsDate}-->" id="dailyAppointmentsDate" onSetDate="freemedLoad('org.freemedsoftware.controller.mainframe?piece=defaultpane&dailyAppointmentsDate='+dojo.widget.byId('dailyAppointmentsDate').inputNode.value);"</td>
	</tr>
</table>
</div>

<!--{include file="org.freemedsoftware.ui.dailyappointmenttable.tpl"}-->

