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

<script type="text/javascript">
	dojo.require("dojo.widget.Form");
	dojo.require("dojo.widget.TabContainer");
	dojo.require("dojo.widget.ContentPane");
	dojo.require("dojo.widget.InternetTextbox");
</script>

<!--{assign var='module' value='cptcodes'}-->

<!--{assign_block var='moduleName'}-->
	<!--{t}-->CPT Codes<!--{/t}-->
<!--{/assign_block}-->

<!--{assign_block var='validation'}-->
	/*
	if ( content.phylname.length < 2 || content.phyfname.length ) {
		m += "<!--{t|escape:'javascript'}-->You must enter a valid name.<!--{/t}-->\n";
		r = false;
	}
	*/
<!--{/assign_block}-->

<!--{assign_block var='initialLoad'}-->
	cptdeftos.onAssign( data.cptdeftos );
	cpttype.onAssign(   data.cpttype   );
	cptreqcpt.onAssign( data.cptreqcpt );
	cptexccpt.onAssign( data.cptexccpt );
	cptreqicd.onAssign( data.cptreqicd );
	cptexcicd.onAssign( data.cptexcicd );
<!--{/assign_block}-->

<!--{assign_block var='collectDataArray'}-->
	cptreqicd: cptreqicd.getValue(),
	cptexcicd: cptexcicd.getValue(),
	cptreqcpt: cptreqcpt.getValue(),
	cptexccpt: cptexccpt.getValue()
<!--{/assign_block}-->

<!--{assign_block var='moduleForm'}-->
<div dojoType="TabContainer" id="mainTabContainer" style="width: 100%; height: 15em;">

	<div dojoType="ContentPane" id="cptMainPane" label="<!--{t|escape:'javascript'}-->Primary Information<!--{/t}-->">

		<table style="border: 0; padding: 1em;">

			<tr>
				<td><!--{t}-->Procedural Code<!--{/t}--></td>
				<td><input type="text" id="cptcode" name="cptcode" size="7" /></td>
			</tr>

			<tr>
				<td><!--{t}-->Internal Description<!--{/t}--></td>
				<td><input type="text" id="cptnameint" name="cptnameint" size="50" /></td>
			</tr>

			<tr>
				<td><!--{t}-->External Description<!--{/t}--></td>
				<td><input type="text" id="cptnameext" name="cptnameext" size="50" /></td>
			</tr>

			<tr>
				<td><!--{t}-->Gender Restriction<!--{/t}--></td>
				<td><select id="cptgender" name="cptgender">
					<option value="n"><!--{t}-->No Gender Restriction<!--{/t}--></option>
					<option value="m"><!--{t}-->Male Only<!--{/t}--></option>
					<option value="f"><!--{t}-->Female Only<!--{/t}--></option>
				</select></td>
			</tr>

			<tr>
				<td><!--{t}-->Taxed?<!--{/t}--></td>
				<td><select id="cpttaxed" name="cpttaxed">
					<option value="n"><!--{t}-->No<!--{/t}--></option>
					<option value="y"><!--{t}-->Yes<!--{/t}--></option>
				</select></td>
			</tr>

			<tr>
				<td><!--{t}-->Internal Service Type<!--{/t}--></td>
				<td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="InternalServiceTypes" varname="cpttype"}--></td>
			</tr>

		</table>

	</div>

	<div dojoType="ContentPane" id="cptBillingPane" label="<!--{t|escape:'javascript'}-->Billing Information<!--{/t}-->">

		<table style="border: 0; padding: 1em; width: auto;">

			<tr>
				<td><!--{t}-->Relative Value<!--{/t}--></td>
				<td><input type="text" id="cptrelval" name="cptrelval" value="1" /></td>
			</tr>

			<tr>
				<td><!--{t}-->Required Diagnoses<!--{/t}--></td>
				<td><!--{include file="org.freemedsoftware.widget.multisupportpicklist.tpl" module="IcdCodes" varname="cptreqicd"}--></td>
			</tr>

			<tr>
				<td><!--{t}-->Excluded Diagnoses<!--{/t}--></td>
				<td><!--{include file="org.freemedsoftware.widget.multisupportpicklist.tpl" module="IcdCodes" varname="cptexcicd"}--></td>
			</tr>

			<tr>
				<td><!--{t}-->Required Procedural Codes<!--{/t}--></td>
				<td><!--{include file="org.freemedsoftware.widget.multisupportpicklist.tpl" module="CptCodes" varname="cptreqcpt"}--></td>
			</tr>

			<tr>
				<td><!--{t}-->Excluded Procedural Codes<!--{/t}--></td>
				<td><!--{include file="org.freemedsoftware.widget.multisupportpicklist.tpl" module="CptCodes" varname="cptexccpt"}--></td>
			</tr>

		</table>

	</div>

	<div dojoType="ContentPane" id="cptFeeProfiles" label="<!--{t|escape:'javascript'}-->Fee Profiles<!--{/t}-->">

		<table style="border: 0; padding: 1em; width: auto;">

			<tr>
				<td><!--{t}-->Default Standard Fee<!--{/t}--></td>
				<td><input type="text" id="cptdefstdfee" name="cptdefstdfee" size="20" /></td>
			</tr>

			<tr>
				<td><!--{t}-->Default Type of Service<!--{/t}--></td>
				<td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="TypeOfService" varname="cptdeftos"}--></td>
			</tr>

		</table>
		</table>

	</div>

</div>
</form>
<!--{/assign_block}-->

<!--{include file="org.freemedsoftware.module.supportmodule.form.tpl" module=$module moduleName=$moduleName moduleForm=$moduleForm collectDataArray=$collectDataArray initialLoad=$initialLoad validation=$validation}-->

