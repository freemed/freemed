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

<h3><!--{t}-->REMITT Billing<!--{/t}--></h3>

<table border="0" cellpadding="5" cellspacing="0">

	<tr>
		<td><a class="clickable" onClick="freemedLoad('org.freemedsoftware.ui.billing.remitt.bill');"><!--{t}-->Perform Billing<!--{/t}--></a></td>
		<td><!--{t}-->Perform Remitt billing runs.<!--{/t}--></td>
	</tr>

	<tr>
		<td><a class="clickable" onClick="freemedLoad('org.freemedsoftware.ui.billing.remitt.rebill');"><!--{t}-->Rebill<!--{/t}--></a></td>
		<td><!--{t}-->Select a previous billing to rebill.<!--{/t}--></td>
	</tr>

	<tr>
		<td><a class="clickable" onClick="freemedLoad('org.freemedsoftware.ui.billing.remitt.reports');"><!--{t}-->Show Reports<!--{/t}--></a></td>
		<td><!--{t}-->View output files and logs from Remitt.<!--{/t}--></td>
	</tr>

</table>
