<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // Copyright (C) 1999-2010 FreeMED Software Foundation
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

<!--{assign var='module' value='authorizations'}-->

<!--{assign_block var='moduleName'}-->
	<!--{t}-->Insurance Authorization<!--{/t}-->
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
	authprov.onAssign( data.authprov );
	authinsco.onAssign( data.authinsco );
	dojo.widget.byId( 'authdtbegin' ).setValue( data.authdtbegin );
	dojo.widget.byId( 'authdtend' ).setValue( data.authdtend );
<!--{/assign_block}-->

<!--{assign_block var='collectDataArray'}-->
	authdtbegin: dojo.widget.byId( 'authdtbegin' ).getValue(),
	authdtend: dojo.widget.byId( 'authdtend' ).getValue(),
<!--{/assign_block}-->

<!--{assign_block var='moduleForm'}-->

<table border="0" style="width: auto;">

	<tr>
		<td align="right"><!--{t}-->Starting Date<!--{/t}--></td>
		<td><input dojoType="DropdownDatePicker" id="authdtbegin" name="authdtbegin" value="today" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Ending Date<!--{/t}--></td>
		<td><input dojoType="DropdownDatePicker" id="authdtend" name="authdtend" value="today" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Authorization Number<!--{/t}--></td>
		<td><input type="text" id="authnum" name="authnum" size="50" maxlength="250" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Authorization Type<!--{/t}--></td>
		<td>
			<select name="authtype" id="authtype">
				<option value="0"><!--{t}-->NONE SELECTED<!--{/t}--></option>
				<option value="1"><!--{t}-->Provider<!--{/t}--></option>
				<option value="2"><!--{t}-->Insurance Company<!--{/t}--></option>
				<option value="3"><!--{t}-->Certificate of Medical Necessity<!--{/t}--></option>
				<option value="4"><!--{t}-->Surgical Authorization<!--{/t}--></option>
				<option value="5"><!--{t}-->Worker's Compensation<!--{/t}--></option>
				<option value="6"><!--{t}-->Consultation<!--{/t}--></option>
			</select>
		</td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Authorizing Provider<!--{/t}--></td>
		<td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="ProviderModule" varname="authprov" defaultValue=$SESSION.authdata.user_record.userrealphy}--></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Authorizing Insurance Company<!--{/t}--></td>
		<td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="InsuranceCompanyModule" varname="authinsco"}--></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Number of Visits<!--{/t}--></td>
		<td><input dojoType="IntegerTextbox" trim="true" type="text" id="authvisits" name="authvisits" size="10" maxlength="10" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Comment<!--{/t}--></td>
		<td><input type="text" id="authcomment" name="authcomment" size="50" maxlength="250" /></td>
	</tr>

</table>

<!--{/assign_block}-->

<!--{include file="org.freemedsoftware.module.emrmodule.form.tpl" module=$module moduleName=$moduleName moduleForm=$moduleForm collectDataArray=$collectDataArray initialLoad=$initialLoad validation=$validation patientVariable='authpatient'}-->

