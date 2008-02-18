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

<!--{assign var='module' value='providergroups'}-->

<!--{assign_block var='moduleName'}-->
	<!--{t}-->Provider Groups<!--{/t}-->
<!--{/assign_block}-->

<!--{assign_block var='validation'}-->
	if ( content.phygroupname.length < 2 ) {
		m += "<!--{t|escape:'javascript'}-->You must enter a name.<!--{/t}-->\n";
		r = false;
	}
<!--{/assign_block}-->

<!--{assign_block var='initialLoad'}-->
	if ( data.phygroupdocs ) {
		phygroupdocs.onAssign( data.phygroupdocs );
	}
	if ( data.phygroupfac ) {
		phygroupfac.onAssign( data.phygroupfac );
	}
	document.getElementById( 'phygroupname' ).value = data.phygroupname;
<!--{/assign_block}-->

<!--{assign_block var='collectDataArray'}-->
	phygroupdocs: phygroupdocs.getValue(),
	phygroupname: document.getElementById('phygroupname').value
<!--{/assign_block}-->

<!--{assign_block var='moduleForm'}-->
<table border="0" style="width: auto;">

	<tr>
		<td align="right"><!--{t}-->Group Name<!--{/t}--></td>
		<td><input type="text" id="phygroupname" size="50" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Facility<!--{/t}--></td>
		<td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="FacilityModule" varname="phygroupfac"}--></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Providers<!--{/t}--></td>
		<td><!--{include file="org.freemedsoftware.widget.multisupportpicklist.tpl" module="ProviderModule" varname="phygroupdocs"}--></td>
	</tr>

</table>
<!--{/assign_block}-->

<!--{include file="org.freemedsoftware.module.supportmodule.form.tpl" module=$module moduleName=$moduleName moduleForm=$moduleForm collectDataArray=$collectDataArray initialLoad=$initialLoad validation=$validation}-->

