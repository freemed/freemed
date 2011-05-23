<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // Copyright (C) 1999-2011 FreeMED Software Foundation
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

<!--{assign var='module' value='immunizations'}-->

<!--{assign_block var='moduleName'}-->
	<!--{t}-->Immunization<!--{/t}-->
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
	provider.onAssign( data.provider );
	immunization.onAssign( data.immunization );
	route.onAssign( data.route );
	body_site.onAssign( data.body_site );
	dojo.widget.byId( 'dateof' ).setValue( data.dateof );
<!--{/assign_block}-->

<!--{assign_block var='collectDataArray'}-->
	dateof: dojo.widget.byId('dateof').getValue(),
<!--{/assign_block}-->

<!--{assign_block var='moduleForm'}-->

<table border="0" style="width: auto;">

	<tr>
		<td align="right"><!--{t}-->Date of Immunization<!--{/t}--></td>
		<td><input dojoType="DropdownDatePicker" id="dateof" name="dateof" value="today" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Administering Provider<!--{/t}--></td>
		<td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="ProviderModule" varname="provider"}--></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Immunization<!--{/t}--></td> 
		<td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="Bccdc" varname="immunization"}--></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Route<!--{/t}--></td> 
		<td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="RouteOfAdministration" varname="route"}--></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Body Site<!--{/t}--></td> 
		<td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="BodySite" varname="body_site"}--></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Previous Doses<!--{/t}--></td>
		<td><input dojoType="IntegerTextbox" trim="true" type="text" id="previous_doses" name="previous_doses" size="10" maxlength="10" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Manufacturer<!--{/t}--> / <!--{t}-->Lot Number<!--{/t}--></td>
		<td>
			<input type="text" id="manufacturer" name="manufacturer" size="30" maxlength="100" />
			<b>/</b>
			<input type="text" id="lot_number" name="lot_number" size="15" maxlength="20" />
		</td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Recovery Status<!--{/t}--></td>
		<td>
			<select name="recovery" id="recovery">
				<option value="1"><!--{t}-->Recovered<!--{/t}--></option>
				<option value="0"><!--{t}-->Did not recover<!--{/t}--></option>
			</select>
		</td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Notes<!--{/t}--></td>
		<td><input type="text" id="notes" name="notes" size="50" maxlength="250" /></td>
	</tr>

</table>

<!--{/assign_block}-->

<!--{include file="org.freemedsoftware.module.emrmodule.form.tpl" module=$module moduleName=$moduleName moduleForm=$moduleForm collectDataArray=$collectDataArray initialLoad=$initialLoad validation=$validation patientVariable='patient'}-->

