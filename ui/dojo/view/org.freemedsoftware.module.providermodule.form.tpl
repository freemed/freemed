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
	dojo.require("dojo.widget.UsTextbox");
</script>

<!--{assign var='module' value='providermodule'}-->

<!--{assign_block var='moduleName'}-->
	<!--{t}-->Provider<!--{/t}-->
<!--{/assign_block}-->

<!--{assign_block var='validation'}-->
	if ( content.phylname.length < 2 || content.phyfname.length < 2 ) {
		m += "<!--{t|escape:'javascript'}-->You must enter a valid name.<!--{/t}-->\n";
		r = false;
	}
<!--{/assign_block}-->

<!--{assign_block var='initialLoad'}-->
	phystatus.onAssign( data.phystatus );
	physpecialties.onAssign( data.physpecialties );
	phydegrees.onAssign( data.phydegrees );
<!--{/assign_block}-->

<!--{assign_block var='collectDataArray'}-->
	phydegrees: phydegrees.getValue( ),
	physpecialties: physpecialties.getValue( )
<!--{/assign_block}-->

<!--{assign_block var='moduleForm'}-->
<div dojoType="TabContainer" id="mainTabContainer" style="width: 100%; height: 30em; overflow-y: scroll;">

	<div dojoType="ContentPane" id="providerMainPane" label="<!--{t|escape:'javascript'}-->Primary Information<!--{/t}-->">

		<table style="border: 0; padding: 1em;">

			<tr>
				<td><!--{t}-->First Name<!--{/t}--></td>
				<td><input type="text" id="phyfname" name="phyfname" size="50" /></td>
			</tr>

			<tr>
				<td><!--{t}-->Last Name<!--{/t}--></td>
				<td><input type="text" id="phylname" name="phylname" size="50" /></td>
			</tr>

			<tr>
				<td><!--{t}-->Middle Name<!--{/t}--></td>
				<td><input type="text" id="phymname" name="phymname" size="25" /></td>
			</tr>

			<tr>
				<td><!--{t}-->Practice Name<!--{/t}--></td>
				<td><input type="text" id="phypracname" name="phypracname" size="45" /></td>
			</tr>

			<tr>
				<td><!--{t}-->Status<!--{/t}--></td>
				<td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="ProviderStatus" varname="phystatus"}--></td>
			</tr>

			<tr>
				<td><!--{t}-->Provider Internal / External<!--{/t}--></td>
				<td>
					<select id="phyref" name="phyref">
						<option value="yes"><!--{t}-->In-House<!--{/t}--></option>
						<option value="no"><!--{t}-->Referring<!--{/t}--></option>
					</select>
				</td>
			</tr>

			<tr>
				<td><!--{t}-->Degrees / Certifications<!--{/t}--></td>
				<td><!--{include file="org.freemedsoftware.widget.multisupportpicklist.tpl" module="ProviderCertifications" varname="phydegrees"}--></td>
			</tr>

			<tr>
				<td><!--{t}-->Specialties<!--{/t}--></td>
				<td><!--{include file="org.freemedsoftware.widget.multisupportpicklist.tpl" module="ProviderSpecialties" varname="physpecialties"}--></td>
			</tr>

		</table>

	</div>

	<div dojoType="ContentPane" id="providerContactPane" label="<!--{t|escape:'javascript'}-->Contact<!--{/t}-->">

		<table style="border: 0; padding: 1em; width: auto;">

			<tr>
				<td><!--{t}-->Email<!--{/t}--></td>
				<td><input dojoType="EmailTextbox" type="text" id="phyemail" name="phyemail" size="45" /></td>
			</tr>

			<tr>
				<td><!--{t}-->Cellular Phone #<!--{/t}--></td>
				<td><input type="text" id="phycellular" name="phycellular" /></td>
			</tr>

			<tr>
				<td><!--{t}-->Beeper / Pager #<!--{/t}--></td>
				<td><input type="text" id="phypager" name="phypager" /></td>
			</tr>

		</table>

	</div>

	<div dojoType="ContentPane" id="providerIdPane" label="<!--{t|escape:'javascript'}-->Identifiers<!--{/t}-->">

		<table style="border: 0; padding: 1em; width: auto;">

			<tr>
				<td><!--{t}-->NPI<!--{/t}--></td>
				<td><input type="text" id="phynpi" name="phynpi" size="30" /></td>
			</tr>

			<tr>
				<td><!--{t}-->CLIA<!--{/t}--></td>
				<td><input type="text" id="phyclia" name="phyclia" size="30" /></td>
			</tr>

			<tr>
				<td><!--{t}-->DEA Number<!--{/t}--></td>
				<td><input type="text" id="phydea" name="phydea" size="30" /></td>
			</tr>

			<tr>
				<td><!--{t}-->HL7 Identification<!--{/t}--></td>
				<td><input type="text" id="phyhl7id" name="phyhl7id" size="30" /></td>
			</tr>

		</table>

	</div>

</div>
</form>
<!--{/assign_block}-->

<!--{include file="org.freemedsoftware.module.supportmodule.form.tpl" module=$module moduleName=$moduleName moduleForm=$moduleForm collectDataArray=$collectDataArray initialLoad=$initialLoad validation=$validation}-->

