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

<!--{assign var='module' value='scanneddocuments'}-->

<!--{assign_block var='moduleName'}-->
	<!--{t}-->Scanned Document<!--{/t}-->
<!--{/assign_block}-->

<!--{assign_block var='validation'}-->
	/*
	if ( content.var.length < 2 ) {
		m += "<!--{t|escape:'javascript'}-->You must enter a name.<!--{/t}-->\n";
		r = false;
	}
	*/
<!--{/assign_block}-->

<!--{assign_block var='initialLoad'}-->
	imagetype.onAssign( data.imagetype );
	imagephy.onAssign( data.imagephy );
	dojo.widget.byId( 'imagedt' ).setValue( data.imagedt );
<!--{/assign_block}-->

<!--{assign_block var='collectDataArray'}-->
	imagedt: dojo.widget.byId( 'imagedt' ).getValue(),
<!--{/assign_block}-->

<!--{assign_block var='moduleForm'}-->

<table border="0" style="width: auto;">

	<tr>
		<td align="right"><!--{t}-->Date<!--{/t}--></td>
		<td><input dojoType="DropdownDatePicker" id="imagedt" name="imagedt" value="today" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Provider<!--{/t}--></td>
		<td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="ProviderModule" varname="imagephy"}--></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Category<!--{/t}--></td> 
		<td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="DocumentCategory" varname="imagetype"}--></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Description<!--{/t}--></td>
		<td><input type="text" id="imagedesc" name="imagedesc" size="50" maxlength="250" /></td>
	</tr>

</table>

<!--{/assign_block}-->

<!--{include file="org.freemedsoftware.module.emrmodule.form.tpl" module=$module moduleName=$moduleName moduleForm=$moduleForm collectDataArray=$collectDataArray initialLoad=$initialLoad validation=$validation patientVariable='imagepat'}-->

