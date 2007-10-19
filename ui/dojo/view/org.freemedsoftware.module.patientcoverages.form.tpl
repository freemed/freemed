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

<!--{assign var='module' value='patientcoverage'}-->

<!--{assign_block var='moduleName'}-->
	<!--{t}-->Patient Coverage<!--{/t}-->
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
	covinstp.onAssign( data.covinstp );
	covinsco.onAssign( data.covinsco );
	dojo.widget.byId( 'covrelinfodt' ).setValue( data.covrelinfodt );
	dojo.widget.byId( 'coveffdt' ).setValue( data.coveffdt );
	dojo.widget.byId( 'covdob' ).setValue( data.covdob );
<!--{/assign_block}-->

<!--{assign_block var='collectDataArray'}-->
	covrelinfodt: dojo.widget.byId('covrelinfodt').getValue(),
	coveffdt: dojo.widget.byId('coveffdt').getValue(),
	covdob: dojo.widget.byId('covdob').getValue(),
<!--{/assign_block}-->

<!--{assign_block var='moduleForm'}-->

<div dojoType="TabContainer" id="coverageTabContainer<!--{$unique}-->" style="width: 100%; height: 30em; overflow-y: scroll;">

	<div dojoType="ContentPane" id="infoPane<!--{$unique}-->" label="<!--{t|escape:'javascript'}-->Coverage Information<!--{/t}-->">

		<table border="0" style="width: auto;">

			<tr>
				<td align="right"><!--{t}-->Coverage Insurance Type<!--{/t}--></td>
                                <td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="CoverageTypes" varname="covinstp"}--></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Provider Accepts Assignment<!--{/t}--></td>
				<td><select id="covprovasgn" name="covprovasgn">
					<option value="1"><!--{t}-->Yes<!--{/t}--></option>
					<option value="0"><!--{t}-->No<!--{/t}--></option>
				</select></td>
			</tr>
	
			<tr>
				<td align="right"><!--{t}-->Assignment of Benefits<!--{/t}--></td>
				<td><select id="covbenasgn" name="covbenasgn">
					<option value="1"><!--{t}-->Yes<!--{/t}--></option>
					<option value="0"><!--{t}-->No<!--{/t}--></option>
				</select></td>
			</tr>
	
			<tr>
				<td align="right"><!--{t}-->Release of Information<!--{/t}--></td>
				<td><select id="covrelinfo" name="covrelinfo">
					<option value="1"><!--{t}-->Yes<!--{/t}--></option>
					<option value="0"><!--{t}-->No<!--{/t}--></option>
					<option value="2"><!--{t}-->Limited<!--{/t}--></option>
				</select></td>
			</tr>
	
			<tr>
				<td align="right"><!--{t}-->Release Date Signed<!--{/t}--></td>
				<td><input dojoType="DropdownDatePicker" id="covrelinfodt" name="covrelinfodt" value="today" /></td>
			</tr>
	
			<tr>
				<td align="right"><!--{t}-->Group - Plan Name<!--{/t}--></td>
				<td><input type="text" id="covplanname" name="covplanname" size="30" maxlength="33" /></td>
			</tr>
	
		</table>

	</div>

	<div dojoType="ContentPane" id="insurancePane<!--{$unique}-->" label="<!--{t|escape:'javascript'}-->Insurance Information<!--{/t}-->">

		<table border="0" style="width: auto;">

			<tr>
				<td align="right"><!--{t}-->Start Date<!--{/t}--></td>
				<td><input dojoType="DropdownDatePicker" id="coveffdt" name="coveffdt" value="today" /></td>
			</tr>
	
			<tr>
				<td align="right"><!--{t}-->Insurance Type<!--{/t}--></td>
				<td><select id="covtype" name="covtype">
					<option value="1"><!--{t}-->Primary<!--{/t}--></option>
					<option value="2"><!--{t}-->Secondary<!--{/t}--></option>
					<option value="3"><!--{t}-->Tertiary<!--{/t}--></option>
					<option value="4"><!--{t}-->Workers' Compensation<!--{/t}--></option>
				</select></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Insurance Company<!--{/t}--></td>
                                <td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="InsuranceCompanyModule" varname="covinsco"}--></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Insurance ID Number<!--{/t}--></td>
				<td><input type="text" id="covpatinsno" name="covpatinsno" size="30" maxlength="30" /></td>
			</tr>
	
			<tr>
				<td align="right"><!--{t}-->Insurance Group Number<!--{/t}--></td>
				<td><input type="text" id="covpatgrpno" name="covpatgrpno" size="30" maxlength="30" /></td>
			</tr>
	
			<tr>
				<td align="right"><!--{t}-->Relationship to Insured<!--{/t}--></td>
				<td><select id="covrel" name="covrel">
					<option value="S"><!--{t}-->Self<!--{/t}--></option>
					<option value="C"><!--{t}-->Child<!--{/t}--></option>
					<option value="H"><!--{t}-->Husband<!--{/t}--></option>
					<option value="W"><!--{t}-->Wife<!--{/t}--></option>
					<option value="D"><!--{t}-->Child Not Fin<!--{/t}--></option>
					<option value="SC"><!--{t}-->Step Child<!--{/t}--></option>
					<option value="FC"><!--{t}-->Foster Child<!--{/t}--></option>
					<option value="WC"><!--{t}-->Ward of Court<!--{/t}--></option>
					<option value="HD"><!--{t}-->HC Dependent<!--{/t}--></option>
					<option value="SD"><!--{t}-->Sponsored Dependent<!--{/t}--></option>
					<option value="LR"><!--{t}-->Medicare Legal Representative<!--{/t}--></option>
					<option value="O"><!--{t}-->Other<!--{/t}--></option>
				</covrel></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Replace Like Coverage<!--{/t}--></td>
				<td><select id="covreplace" name="covreplace">
					<option value="1"><!--{t}-->Yes<!--{/t}--></option>
					<option value="0"><!--{t}-->No<!--{/t}--></option>
				</select></td>
			</tr>
	
		</table>

	</div>

	<div dojoType="ContentPane" id="insuredPane<!--{$unique}-->" label="<!--{t|escape:'javascript'}-->Insured Information<!--{/t}-->">

		<table border="0" style="width: auto;">

			<tr>
				<td align="right"><!--{t}-->Last Name<!--{/t}--></td>
				<td><input type="text" id="covlname" name="covlname" size="30" maxlength="50" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->First Name<!--{/t}--></td>
				<td><input type="text" id="covfname" name="covfname" size="30" maxlength="50" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Middle Name<!--{/t}--></td>
				<td><input type="text" id="covmname" name="covmname" size="30" maxlength="50" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Address Line 1<!--{/t}--></td>
				<td><input type="text" id="covaddr1" name="covaddr1" size="45" maxlength="45" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Address Line 2<!--{/t}--></td>
				<td><input type="text" id="covaddr2" name="covaddr2" size="45" maxlength="45" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Date of Birth<!--{/t}--></td>
				<td><input dojoType="DropdownDatePicker" id="covdob" name="covdob" value="today" /></td>
			</tr>
	
			<tr>
				<td align="right"><!--{t}-->Gender<!--{/t}--></td>
				<td><select id="covsex" name="covsex">
					<option value="m"><!--{t}-->Male<!--{/t}--></option>
					<option value="f"><!--{t}-->Female<!--{/t}--></option>
					<option value="t"><!--{t}-->Transgendered<!--{/t}--></option>
				</select></td>
			</tr>

		</table>

	</div>

</div>

<!--{/assign_block}-->

<!--{include file="org.freemedsoftware.module.emrmodule.form.tpl" module=$module moduleName=$moduleName moduleForm=$moduleForm collectDataArray=$collectDataArray initialLoad=$initialLoad validation=$validation patientVariable='covpatient'}-->

