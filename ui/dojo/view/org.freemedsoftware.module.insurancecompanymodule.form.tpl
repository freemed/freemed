<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //      Horea Teodoru <teodoruh@gmail.com>
 //
 // FreeMED Electronic Medical Record and Practice Management System
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

<!--{assign var='module' value='insurancecompanymodule'}-->

<!--{assign_block var='moduleName'}-->
	<!--{t}-->Insurance Company<!--{/t}-->
<!--{/assign_block}-->

<!--{assign_block var='validation'}-->
	if ( content.insconame.length < 2 ) {
		m += "<!--{t|escape:'javascript'}-->You must enter a Company Name.<!--{/t}-->\n";
		r = false;
	}
<!--{/assign_block}-->

<!--{assign_block var='initialLoad'}-->
	dojo.widget.byId( 'inscocsz_widget' ).setLabel( data['inscocity'] + ', ' + data['inscostate'] + ' ' + data['inscozip'] );
	dojo.widget.byId( 'inscocsz_widget' ).setValue( data['inscocity'] + ', ' + data['inscostate'] + ' ' + data['inscozip'] );
<!--{/assign_block}-->

<!--{assign_block var='collectDataArray'}-->
	inscocsz: document.getElementById('inscocsz').value
<!--{/assign_block}-->

<!--{assign_block var='moduleForm'}-->
<div dojoType="TabContainer" id="insuranceCompanyTabContainer" style="width: 100%; height: 20em;">

	<div dojoType="ContentPane" label="<!--{t|escape:'javascript'}-->Contact Information<!--{/t}-->">
		<table border="0">
			<tr>
				<td align="right"><!--{t}-->Company Name (full)<!--{/t}--></td>
				<td align="left"><input type="text" id="insconame" name="insconame" size="50" maxlength="50" /></td>
			</tr>
			
			<tr>
				<td align="right"><!--{t}-->Company Name (on forms)<!--{/t}--></td>
				<td align="left"><input type="text" id="inscoalias" name="inscoalias" size="30" maxlength="30" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Address (Line 1)<!--{/t}--></td>
				<td align="left"><input type="text" id="inscoaddr1" name="inscoaddr1" size="30" maxlength="30" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Address (Line 2)<!--{/t}--></td>
				<td align="left"><input type="text" id="inscoaddr2" name="inscoaddr2" size="30" maxlength="30" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->City, State, Zip<!--{/t}--></td>
				<td><input dojoType="Select"
				autocomplete="false"
				id="inscocsz_widget"
				style="width: 300px;"
				dataUrl="<!--{$relay}-->/org.freemedsoftware.module.Zipcodes.CityStateZipPicklist?param0='%{searchString}'"
				setValue="document.getElementById('inscocsz').value = arguments[0];"
				mode="remote" />
				<input type="hidden" id="inscocsz" value="" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Contact Phone<!--{/t}--></td>
				<td align="left"><input dojoType="UsPhoneNumberTextbox" type="text" id="inscophone" widgetId="inscophone" name="inscophone" size="16" maxlength="16" value="" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Fax Number<!--{/t}--></td>
				<td align="left"><input dojoType="UsPhoneNumberTextbox" type="text" id="inscofax" widgetId="inscofax" name="inscofax" size="16" maxlength="16" value="" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Email Address<!--{/t}--></td>
				<td align="left"><input dojoType="EmailTextbox" type="text" id="inscoemail" widgetId="inscoemail" name="inscoemail" size="50" maxlength="50" value="" /></td>
			</tr>
			
			<tr>
				<td align="right"><!--{t}-->Web Site<!--{/t}--></td>
				<td align="left"><input type="text" id="inscowebsite" name="inscowebsite" size="100" maxlength="100" /></td>
			</tr>

		</table>
	</div>

	<div dojoType="ContentPane" label="<!--{t|escape:'javascript'}-->Internal Information<!--{/t}-->">

		<table border="0">
			<tr>
				<td align="right"><!--{t}-->NEIC ID<!--{/t}--></td>
				<td align="left"><input type="text" id="inscoid" name="inscoid" size="11" maxlength="10" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Insurance Group<!--{/t}--></td>
				<td align="left"><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="InsuranceCompanyGroup" varname="inscogroup"}--></td>
			</tr>
			
			<tr>
				<td align="right"><!--{t}-->Insurance Type<!--{/t}--></td>
				<td align="left"><input type="text" id="inscotype" name="inscotype" size="10" maxlength="30" /></td>
			</tr>
			
			<tr>
				<td align="right"><!--{t}-->Insurance Assign?<!--{/t}--></td>
				<td align="left"><input type="text" id="inscoassign" name="inscoassign" size="10" maxlength="12" /></td>
			</tr>
			
			<tr>
				<td align="right"><!--{t}-->X12 Payer Id Code<!--{/t}--></td>
				<td align="left"><input type="text" id="inscox12id" name="inscox12id" size="25" maxlength="25" /></td>
			</tr>


			<tr>
				<td align="right"><!--{t}-->Insurance Modifiers<!--{/t}--></td>
				<td align="left"><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="InsuranceModifiers" varname="inscomod"}--></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Default Output<!--{/t}--></td>
				<td align="left"><select id="inscodefoutput" name="inscodefoutput">
					<option value="Electronic"><!--{t}-->Electronic<!--{/t}--></option>
					<option value="Paper"><!--{t}-->Paper<!--{/t}--></option>
				</select></td>
			</tr>

		</table>
	</div>
</div> <!--{* TabContainer *}-->
<!--{/assign_block}-->

<!--{include file="org.freemedsoftware.module.supportmodule.form.tpl" module=$module moduleName=$moduleName moduleForm=$moduleForm collectDataArray=$collectDataArray initialLoad=$initialLoad validation=$validation}-->
