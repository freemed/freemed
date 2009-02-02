<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
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

<!--{assign_block var='preSubmitCode'}-->
	var practiceValue = 0;
	if ( document.getElementById( 'practiceType' ).value == 'new' ) {
		var hash = {
			pracname: document.getElementById( 'pracname' ).value,
			ein: document.getElementById( 'ein' ).value,
			pracname: document.getElementById( 'pracname' ).value,
			addr1a: document.getElementById( 'addr1a' ).value,
			addr2a: document.getElementById( 'addr2a' ).value,
			citya: document.getElementById( 'citya' ).value,
			statea: document.getElementById( 'statea' ).value,
			zipa: document.getElementById( 'zipa' ).value,
			phonea: document.getElementById( 'phonea' ).value,
			faxa: document.getElementById( 'faxa' ).value,
			email: document.getElementById( 'email' ).value,
			cellular: document.getElementById( 'cellular' ).value,
			pager: document.getElementById( 'pager' ).value
		};
		dojo.io.bind({
			method: 'POST',
			content: {
				param0: hash
			},
			url: "<!--{$relay}-->/org.freemedsoftware.module.Practices.add",
			load: function ( type, data, evt ) {
				if ( data ) { practiceValue = data; }
			},
			mimetype: 'text/json',
			sync: true
		});
	}
<!--{/assign_block}-->

<!--{assign_block var='collectDataArray'}-->
	phypractice: ( document.getElementById('practiceType').value == 'old' ? dojo.widget.byId( 'phypractice_widget' ).getValue() : practiceValue ),
	phydegrees: phydegrees.getValue( ),
	physpecialties: physpecialties.getValue( )
<!--{/assign_block}-->

<!--{assign_block var='moduleForm'}-->
<script language="javascript">
	var npi_<!--{$unique}--> = {
		stateValue: '',
		lookup: function ( ) {
			npi_<!--{$unique}-->.stateValue = document.getElementById( 'statea' ).value;
			if ( document.getElementById( 'practiceType' ).value == 'old' ) {
				dojo.io.bind({
					method: 'POST',
					content: {
						param0: dojo.widget.byId( 'phypractice_widget' ).getValue()
					},
					url: "<!--{$relay}-->/org.freemedsoftware.module.Practices.GetRecord",
					load: function ( type, data, evt ) {
						if ( data ) {
							npi_<!--{$unique}-->.stateValue = data.statea;
						}
					},
					mimetype: 'text/json',
					sync: true
				});
			}
			dojo.io.bind({
				method: 'POST',
				url: "<!--{$relay}-->/org.freemedsoftware.module.ProviderModule.LookupNPI",
				content: {
					param0: document.getElementById( 'phyfname' ).value,
					param1: document.getElementById( 'phylname' ).value,
					param2: npi_<!--{$unique}-->.stateValue
				},
				load: function ( type, data, evt ) {
					if ( data ) {
						document.getElementById( 'phynpi' ).value = data;
					}
				},
				mimetype: 'text/json'
			});
		}
	};
	_container_.addOnLoad(function(){
		dojo.event.connect( dojo.widget.byId( 'lookupNPI<!--{$unique}-->' ), 'onClick', npi_<!--{$unique}-->, 'lookup' );
	});
	_container_.addOnUnload(function(){
		dojo.event.disconnect( dojo.widget.byId( 'lookupNPI<!--{$unique}-->' ), 'onClick', npi_<!--{$unique}-->, 'lookup' );
	});
</script>
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
				<td><!--{t}-->Status<!--{/t}--></td>
				<td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="ProviderStatus" varname="phystatus"}--></td>
			</tr>

			<tr>
				<td><!--{t}-->Provider Internal / External<!--{/t}--></td>
				<td>
					<select id="phyref" name="phyref">
						<option value="no"><!--{t}-->In-House<!--{/t}--></option>
						<option value="yes"><!--{t}-->Referring<!--{/t}--></option>
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

	<div dojoType="ContentPane" id="providerPracticePane" label="<!--{t|escape:'javascript'}-->Practice<!--{/t}-->">

		<div style="padding: 1em;">

		<div id="practiceTypeSelector">
			<!--{t}-->Practice Type<!--{/t}-->:
			<select id="practiceType" onChange="toggleDiv( 'practiceTypeOld' ); toggleDiv( 'practiceTypeNew' );">
				<option value="old"><!--{t}-->Existing<!--{/t}--></option>
				<option value="new"><!--{t}-->New<!--{/t}--></option>
			</select>
		</div>

		<div id="practiceTypeOld">
		<table style="border: 0; padding: 1em;">

			<tr>
				<td><!--{t}-->Practice<!--{/t}--></td>
				<td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="Practices" varname="phypractice"}--></td>
			</tr>

		</table>
		</div>
		
		<div id="practiceTypeNew" style="display: none;">
		<table style="border: 0; padding: 1em;">

			<tr>
				<td><!--{t}-->Practice Name<!--{/t}--></td>
				<td><input type="text" id="pracname" name="pracname" size="45" /></td>
			</tr>

			<tr>
				<td><!--{t}-->Address<!--{/t}--></td>
				<td><input type="text" id="addr1a" name="addr1a" size="40" /><br/>
				<input type="text" id="addr2a" name="addr2a" size="40" /></td>
			</tr>

			<tr>
				<td><!--{t}-->City<!--{/t}-->, <!--{t}-->State / Province<!--{/t}-->, <!--{t}-->Postal Code<!--{/t}--></td>
				<td><input type="text" id="citya" name="citya" size="20" /><input type="text" id="statea" name="statea" size="3" />, <input type="text" id="zipa" name="zipa" size="10" maxlength="10" /></td>
			</tr>

			<tr>
				<td><!--{t}-->Employer Identification Number<!--{/t}--></td>
				<td><input type="text" id="ein" name="ein" size="45" /></td>
			</tr>

			<tr>
				<td><!--{t}-->Email<!--{/t}--></td>
				<td><input dojoType="EmailTextbox" type="text" id="email" name="email" size="45" /></td>
			</tr>

			<tr>
				<td><!--{t}-->Phone #<!--{/t}--></td>
				<td><input type="text" id="phonea" name="phonea" /></td>
			</tr>

			<tr>
				<td><!--{t}-->Fax #<!--{/t}--></td>
				<td><input type="text" id="faxa" name="faxa" /></td>
			</tr>

			<tr>
				<td><!--{t}-->Cellular Phone #<!--{/t}--></td>
				<td><input type="text" id="cellular" name="cellular" /></td>
			</tr>

			<tr>
				<td><!--{t}-->Beeper / Pager #<!--{/t}--></td>
				<td><input type="text" id="pager" name="pager" /></td>
			</tr>

		</table>
		</div>

		</div>

	</div>

	<div dojoType="ContentPane" id="providerIdPane" label="<!--{t|escape:'javascript'}-->Identifiers<!--{/t}-->">

		<table style="border: 0; padding: 1em; width: auto;">

			<tr>
				<td><!--{t}-->NPI<!--{/t}--></td>
				<td><input type="text" id="phynpi" name="phynpi" size="30" /></td>
				<td><button dojoType="Button" id="lookupNPI<!--{$unique}-->" widgetId="lookupNPI<!--{$unique}-->">
					<!--{t}-->Lookup NPI<!--{/t}-->
				</button></td>
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

