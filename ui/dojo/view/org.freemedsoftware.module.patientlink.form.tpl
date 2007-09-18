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

<!--{assign var='module' value='patientlink'}-->

<!--{assign_block var='moduleName'}-->
	<!--{t}-->Patient Link<!--{/t}-->
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
	destpatient.onAssign( data.destpatient );
<!--{/assign_block}-->

<!--{assign_block var='collectDataArray'}-->
<!--{/assign_block}-->

<!--{assign_block var='moduleForm'}-->

<table border="0" style="width: auto;">

	<tr>
		<td align="right"><!--{t}-->Link Type<!--{/t}--></td>
		<td>
			<!--{* HL7 v2.5 Lookup CE_0063: Relationship *}-->
			<select name="linktype" id="linktype">
				<option value="UNK"><!--{t}-->Unknown<!--{/t}--></option>
				<option value="ASC"><!--{t}-->Associate<!--{/t}--></option>
				<option value="BRO"><!--{t}-->Brother<!--{/t}--></option>
				<option value="CGV"><!--{t}-->Care giver<!--{/t}--></option>
				<option value="CHD"><!--{t}-->Child<!--{/t}--></option>
				<option value="DEP"><!--{t}-->Handicapped dependent<!--{/t}--></option>
				<option value="DOM"><!--{t}-->Life partner<!--{/t}--></option>
				<option value="EMC"><!--{t}-->Emergency contact<!--{/t}--></option>
				<option value="EME"><!--{t}-->Employee<!--{/t}--></option>
				<option value="EMR"><!--{t}-->Employer<!--{/t}--></option>
				<option value="EXF"><!--{t}-->Extended family<!--{/t}--></option>
				<option value="FCH"><!--{t}-->Foster child<!--{/t}--></option>
				<option value="FND"><!--{t}-->Friend<!--{/t}--></option>
				<option value="FTH"><!--{t}-->Father<!--{/t}--></option>
				<option value="GCH"><!--{t}-->Grandchild<!--{/t}--></option>
				<option value="GRD"><!--{t}-->Guardian<!--{/t}--></option>
				<option value="GRP"><!--{t}-->Grandparent<!--{/t}--></option>
				<option value="MGR"><!--{t}-->Manager<!--{/t}--></option>
				<option value="MTH"><!--{t}-->Mother<!--{/t}--></option>
				<option value="NCH"><!--{t}-->Natural child<!--{/t}--></option>
				<!-- <option value="NON"><!--{t}-->None<!--{/t}--></option> -->
				<option value="OAD"><!--{t}-->Other adult<!--{/t}--></option>
				<option value="OTH"><!--{t}-->Other<!--{/t}--></option>
				<option value="OWN"><!--{t}-->Owner<!--{/t}--></option>
				<option value="PAR"><!--{t}-->Parent<!--{/t}--></option>
				<option value="SCH"><!--{t}-->Stepchild<!--{/t}--></option>
				<!-- <option value="SEL"><!--{t}-->Self<!--{/t}--></option> -->
				<option value="SIB"><!--{t}-->Sibling<!--{/t}--></option>
				<option value="SIS"><!--{t}-->Sister<!--{/t}--></option>
				<option value="SPO"><!--{t}-->Spouse<!--{/t}--></option>
				<option value="TRA"><!--{t}-->Trainer<!--{/t}--></option>
				<option value="WRD"><!--{t}-->Ward of court<!--{/t}--></option>
			</select>
		</td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Link to Patient<!--{/t}--></td>
		<td><!--{include file="org.freemedsoftware.widget.patientpicklist.tpl" varname="destpatient"}--></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Details<!--{/t}--></td>
		<td><input type="text" id="linkdetails" name="linkdetails" size="50" maxlength="250" /></td>
	</tr>

</table>

<!--{/assign_block}-->

<!--{include file="org.freemedsoftware.module.emrmodule.form.tpl" module=$module moduleName=$moduleName moduleForm=$moduleForm collectDataArray=$collectDataArray initialLoad=$initialLoad validation=$validation patientVariable='srcpatient'}-->

