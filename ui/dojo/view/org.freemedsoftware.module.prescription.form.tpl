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

<!--{assign var='module' value='prescription'}-->

<!--{assign_block var='moduleName'}-->
	<!--{t}-->Prescription<!--{/t}-->
<!--{/assign_block}-->

<!--{assign_block var='validation'}-->
	/*
	if ( content.var.length < 2 ) {
		m += "<!--{t}-->You must enter a name.<!--{/t}-->\n";
		r = false;
	}
	*/
<!--{/assign_block}-->

<!--{assign_block var='initialLoad'}-->
	rxdrugmultum.onAssign( data.rxdrugmultum );
	rxphy.onAssign( data.rxphy );
	rxform.onAssign( data.rxform );
	rxquantityqual.onAssign( data.rxquantityqual );
	rxunit.onAssign( data.rxunit );
	dojo.widget.byId( 'rxdtfrom' ).setValue( data.rxdtfrom );
<!--{/assign_block}-->

<!--{assign_block var='collectDataArray'}-->
	rxdtfrom: dojo.widget.byId('rxdtfrom').getValue(),
<!--{/assign_block}-->

<!--{assign_block var='moduleForm'}-->

<table border="0" style="width: auto;">

	<tr>
		<td align="right"><!--{t}-->Date of Prescription<!--{/t}--></td>
		<td><input dojoType="DropdownDatePicker" id="rxdtfrom" name="rxdtfrom" value="today" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Prescribing Provider<!--{/t}--></td>
		<td>
			<!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="ProviderModule" varname="rxphy" methodName="internalPicklist" defaultValue=$SESSION.authdata.user_record.userrealphy}-->
		</td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Drug<!--{/t}--></td>
		<td>
			<!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="MultumDrugLexicon" varname="rxdrugmultum"}-->
		</td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Drug Form<!--{/t}--></td>
		<td>
			<!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="DrugForms" varname="rxform"}-->
		</td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Dosage<!--{/t}--></td>
		<td>
			<table style="width: auto;"><tr><td>
				<input type="text" id="rxdosage" name="rxdosage" size="30" maxlength="100" />
			</td><td>
				<!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="DrugQuantityQualifiers" varname="rxunit"}-->
			</td></tr></table>
		</td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Quantity<!--{/t}--></td>
		<td>
			<table style="width: auto;"><tr><td>
				<input type="text" id="rxquantity" name="rxquantity" size="30" maxlength="100" />
			</td><td>
				<!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="DrugQuantityQualifiers" varname="rxquantityqual"}-->
			</td></tr></table>
		</td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Interval<!--{/t}--></td>
		<td>
			<select name="rxinterval" id="rxinterval">
				<option>b.i.d.</option>
				<option>t.i.d.</option>
				<option>q.i.d.</option>
				<option>q. 3h</option>
				<option>q. 4h</option>
				<option>q. 5h</option>
				<option>q. 6h</option>
				<option>q. 8h</option>
				<option>q.d.</option>
				<option>h.s.</option>
				<option>q.h.s.</option>
				<option>q.A.M.</option>
				<option>q.P.M.</option>
				<option>a.c.</option>
				<option>p.c.</option>
				<option>p.r.n.</option>
			</select>
		</td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Substitutions<!--{/t}--></td>
		<td>
			<select name="rxsubstitute" id="rxsubstitute">
				<option value="0"><!--{t}-->No Product selection Indicated<!--{/t}--></option>
				<option value="1"><!--{t}-->Substitution Not Allowed By Prescriber<!--{/t}--></option>
				<option value="2"><!--{t}-->Substitution Allowed - Patient Requested Product Dispensed<!--{/t}--></option>
				<option value="3"><!--{t}-->Substitution Allowed - Pharmacist Selected Product Dispensed<!--{/t}--></option>
				<option value="4"><!--{t}-->Substitution Allowed - Generic Drug Not In Stock<!--{/t}--></option>
				<option value="5"><!--{t}-->Substitution Allowed - Brand Drug Dispensed as a Generic<!--{/t}--></option>
				<option value="8"><!--{t}-->Substitution Allowed - Generic Drug Not Available in Marketplace<!--{/t}--></option>
			</select>
		</td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Coverage Status<!--{/t}--></td>
		<td>
			<select name="rxcovstatus" id="rxcovstatus">
				<option value="UN"><!--{t}-->Unknown<!--{/t}--></option>
				<option value="PR"><!--{t}-->Preferred<!--{/t}--></option>
				<option value="AP"><!--{t}-->Approved<!--{/t}--></option>
				<option value="PA"><!--{t}-->Prior Authorization Required<!--{/t}--></option>
				<option value="NF"><!--{t}-->Non Formulary<!--{/t}--></option>
				<option value="NR"><!--{t}-->Not Reimbursed<!--{/t}--></option>
				<option value="DC"><!--{t}-->Differential Co-Pay<!--{/t}--></option>
				<option value="ST"><!--{t}-->Step Therapy Required<!--{/t}--></option>
			</select>
		</td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Refills<!--{/t}--></td>
		<td>
			<table style="width: auto;"><tr>
			<td>
				<input type="text" name="rxrefills" id="rxrefills" value="0" />
			</td>
			<td>
			<select name="rxrefillinterval" id="rxrefillinterval">
				<option value="PRN"><!--{t}-->As Needed<!--{/t}--> (PRN)</option>
				<option value="Y"><!--{t}-->Refill for n Year(s)<!--{/t}--></option>
				<option value="M"><!--{t}-->Refill for n Month(s)<!--{/t}--></option>
				<option value="W"><!--{t}-->Refill for n Week(s)<!--{/t}--></option>
				<option value="D"><!--{t}-->Refill for n Day(s)<!--{/t}--></option>
				<option value="P"><!--{t}-->Pharmacy Requested Refills<!--{/t}--></option>
				<option value="A"><!--{t}-->Additional Refills Authorized<!--{/t}--></option>
				<option value="R"><!--{t}-->Number of Refills<!--{/t}--></option>
			</select>
			</td>
			</tr></table>
		</td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Sig<!--{/t}--></td>
		<td><input type="text" id="rxsig" name="rxsig" size="50" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Note<!--{/t}--></td>
		<td><input type="text" id="rxnote" name="rxnote" size="50" maxlength="250" /></td>
	</tr>

</table>

<!--{/assign_block}-->

<!--{include file="org.freemedsoftware.module.emrmodule.form.tpl" module=$module moduleName=$moduleName moduleForm=$moduleForm collectDataArray=$collectDataArray initialLoad=$initialLoad validation=$validation patientVariable='patient'}-->

