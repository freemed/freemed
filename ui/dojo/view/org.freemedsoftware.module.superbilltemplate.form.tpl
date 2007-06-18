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

<!--{assign var='module' value='superbilltemplate'}-->

<!--{assign_block var='moduleName'}-->
	<!--{t}-->Superbill Template<!--{/t}-->
<!--{/assign_block}-->

<!--{assign_block var='initialLoad'}-->
	document.getElementById( 'st_name' ).value = data.st_name;
	if ( data.st_dx ) { st_dx.onAssign( data.st_dx ); }
	if ( data.st_px ) { st_px.onAssign( data.st_px ); }
<!--{/assign_block}-->

<!--{assign_block var='collectDataArray'}-->
	st_name: document.getElementById('st_name').value,
	st_dx: st_dx.getValue(),
	st_px: st_px.getValue()
<!--{/assign_block}-->

<!--{assign_block var='moduleForm'}-->
<table border="0" style="width: auto;">

	<tr>
		<td align="right"><!--{t}-->Template Name<!--{/t}--></td>
		<td><input type="text" id="st_name" size="50" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Diagnosis Codes<!--{/t}--></td>
		<td><!--{include file="org.freemedsoftware.widget.multisupportpicklist.tpl" module="IcdCodes" varname="st_dx"}--></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Procedure Codes<!--{/t}--></td>
		<td><!--{include file="org.freemedsoftware.widget.multisupportpicklist.tpl" module="CptCodes" varname="st_px"}--></td>
	</tr>

</table>
<!--{/assign_block}-->

<!--{include file="org.freemedsoftware.module.supportmodule.form.tpl" module=$module moduleName=$moduleName moduleForm=$moduleForm collectDataArray=$collectDataArray initialLoad=$initialLoad validation=$validation}-->

