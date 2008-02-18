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

<!--{assign var='module' value='facilitymodule'}-->

<!--{assign_block var='moduleName'}-->
	<!--{t}-->Facility<!--{/t}-->
<!--{/assign_block}-->

<!--{assign_block var='validation'}-->
	if ( content.psrname.length < 2 ) {
		m += "<!--{t|escape:'javascript'}-->You must enter a name.<!--{/t}-->\n";
		r = false;
	}
	if ( content.psraddr1.length < 2 ) {
		m += "<!--{t|escape:'javascript'}-->You must enter an address.<!--{/t}-->\n";
		r = false;
	}
	if ( content.psrcsz.length < 5 ) {
		m += "<!--{t|escape:'javascript'}-->You must enter a city and postal code.<!--{/t}-->\n";
		r = false;
	}
	if ( content.psrnote.length < 2 ) {
		m += "<!--{t|escape:'javascript'}-->You must enter a description.<!--{/t}-->\n";
		r = false;
	}
	if ( parseInt( content.psrpos.length ) < 1 ) {
		m += "<!--{t|escape:'javascript'}-->You must enter a place of service code.<!--{/t}-->\n";
		r = false;
	}
<!--{/assign_block}-->

<!--{assign_block var='initialLoad'}-->
	dojo.widget.byId( 'psrcsz_widget' ).setLabel( data['psrcity'] + ', ' + data['psrstate'] + ' ' + data['psrzip'] );
	dojo.widget.byId( 'psrcsz_widget' ).setValue( data['psrcity'] + ', ' + data['psrstate'] + ' ' + data['psrzip'] );
	dojo.event.topic.publish( 'psrdefphy-assign', data['psrdefphy'] );
	dojo.event.topic.publish( 'psrpos-assign', data['psrpos'] );
	dojo.widget.byId( 'psrphone' ).setValue( data['psrphone'] );
	dojo.widget.byId( 'psrfax' ).setValue( data['psrfax'] );
<!--{/assign_block}-->

<!--{assign_block var='collectDataArray'}-->
	psrcsz: document.getElementById( 'psrcsz' ).value,
	psrdefphy: parseInt( document.getElementById( 'psrdefphy' ).value ),
	psrpos: parseInt( document.getElementById( 'psrpos' ).value ),
	psrphone: dojo.widget.byId( 'psrphone' ).getValue( ),
	psrfax: dojo.widget.byId( 'psrfax' ).getValue( )
<!--{/assign_block}-->

<!--{assign_block var='moduleForm'}-->
<div dojoType="TabContainer" id="facilityTabContainer" style="width: 100%; height: 20em;">

	<div dojoType="ContentPane" label="<!--{t|escape:'javascript'}-->Primary Information<!--{/t}-->">
		<table border="0">

			<tr>
				<td align="right"><!--{t}-->Facility Name<!--{/t}--></td>
				<td align="left"><input type="text" id="psrname" name="psrname" size="30" maxlength="100" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Address (Line 1)<!--{/t}--></td>
				<td align="left"><input type="text" id="psraddr1" name="psraddr1" size="50" maxlength="50" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Address (Line 2)<!--{/t}--></td>
				<td align="left"><input type="text" id="psraddr2" name="psraddr2" size="50" maxlength="50" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->City, State, Zip<!--{/t}--></td>
				<td><input dojoType="Select"
				autocomplete="false"
				id="psrcsz_widget"
				style="width: 300px;"
				dataUrl="<!--{$relay}-->/org.freemedsoftware.module.Zipcodes.CityStateZipPicklist?param0='%{searchString}'"
				setValue="document.getElementById('psrcsz').value = arguments[0];"
				mode="remote" />
				<input type="hidden" id="psrcsz" value="" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Country<!--{/t}--></td>
				<td align="left"><input type="text" id="psrcountry" name="psrcountry" size="30" maxlength="100" /></td>
			</tr>

		</table>
	</div>

	<div dojoType="ContentPane" label="<!--{t|escape:'javascript'}-->Details<!--{/t}-->">

		<table border="0">

			<tr>
				<td align="right"><!--{t}-->Description<!--{/t}--></td>
				<td align="left"><input type="text" id="psrnote" name="psrnote" size="20" maxlength="40" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Default Provider<!--{/t}--></td>
				<td align="left"><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="ProviderModule" varname="psrdefphy"}--></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Place of Service Code<!--{/t}--></td>
				<td align="left"><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="PlaceOfService" varname="psrpos"}--></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Employer Identification Number<!--{/t}--></td>
				<td align="left"><input type="text" id="psrein" name="psrein" size="10" maxlength="15" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Rendering NPI Number<!--{/t}--></td>
				<td align="left"><input type="text" id="psrnpi" name="psrnpi" size="10" maxlength="15" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Employer Taxonomy Number<!--{/t}--></td>
				<td align="left"><input type="text" id="psrtaxonomy" name="psrtaxonomy" size="10" maxlength="15" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Internal or External Facility<!--{/t}--></td>
				<td align="left"><select id="psrintext" name="psrintext">
					<option value="0"><!--{t}-->Internal<!--{/t}--></option>
					<option value="1"><!--{t}-->External<!--{/t}--></option>
				</select></td>
			</tr>

		</table>
	</div>

	<div dojoType="ContentPane" label="<!--{t|escape:'javascript'}-->Contact<!--{/t}-->">
		<table border="0">

			<tr>
				<td align="right"><!--{t}-->Phone Number<!--{/t}--></td>
				<td align="left"><input dojoType="UsPhoneNumberTextbox" type="text" id="psrphone" widgetId="psrphone" name="psrphone" size="16" maxlength="16" value="" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Fax Number<!--{/t}--></td>
				<td align="left"><input dojoType="UsPhoneNumberTextbox" type="text" id="psrfax" widgetId="psrfax" name="psrfax" size="16" maxlength="16" value="" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Email Address<!--{/t}--></td>
				<td align="left"><input dojoType="EmailTextbox" type="text" id="psremail" widgetId="psremail" name="psremail" size="50" maxlength="50" value="" /></td>
			</tr>

		</table>
	</div>

	<div dojoType="ContentPane" label="<!--{t|escape:'javascript'}-->Electronic Billing<!--{/t}-->">
		<table border="0">

			<tr>
				<td align="right"><!--{t}-->X12 Identifier<!--{/t}--></td>
				<td align="left"><input type="text" id="psrx12id" name="psrx12id" size="30" maxlength="100" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->X12 Identifier Type<!--{/t}--></td>
				<td align="left"><input type="text" id="psrx12idtype" name="psrx12idtype" size="30" maxlength="100" /></td>
			</tr>

		</table>
	</div>

</div> <!--{* TabContainer *}-->
<!--{/assign_block}-->

<!--{include file="org.freemedsoftware.module.supportmodule.form.tpl" module=$module moduleName=$moduleName moduleForm=$moduleForm collectDataArray=$collectDataArray initialLoad=$initialLoad validation=$validation}-->

