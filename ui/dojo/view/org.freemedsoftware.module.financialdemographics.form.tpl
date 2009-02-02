<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
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
	dojo.require( 'dojo.event.*' );

	var financialDemographics = {
		handleResponse: function ( data ) {
			if (data) {
				freemedMessage( "<!--{t|escape:'javascript'}-->Added demographics.<!--{/t}-->", "INFO" );
				freemedPatientContentLoad( 'org.freemedsoftware.ui.patient.overview.default?patient=<!--{$patient}-->' );
			} else {
				dojo.widget.byId('ModuleFormCommitChangesButton').enable();
			}
		},
		validate: function ( content ) {
			var r = true;
			var m = "";
			// TODO: validation goes here
			if ( m.length > 1 ) { alert( m ); }
			return r;
		},
		submit: function ( ) {
			try {
				dojo.widget.byId('ModuleFormCommitChangesButton').disable();
			} catch ( err ) { }
			var myContent = {
				fdincome: document.getElementById('financialDemographics.yearlyIncome').value,
				fdidtype: dojo.widget.byId('financialDemographics.identificationType').getValue(),
				fdidnumber: document.getElementById('financialDemographics.identificationNumber').value,
				fdidissuer: document.getElementById('financialDemographics.identificationIssuer').value,
				fdidexpire: document.getElementById('financialDemographics.identificationExpiration').value,
				fdhousehold: parseInt( document.getElementById('financialDemographics.householdSize').value ),
				fdspouse: parseInt( dojo.widget.byId('financialDemographics.spouse').getValue() ),
				fdchild: parseInt( document.getElementById('financialDemographics.dependentChildren').value ),
				fdother: parseInt( document.getElementById('financialDemographics.dependentOthers').value ),
				fdfreetext: document.getElementById('financialDemographics.otherInformation').value,
				fdpatient: '<!--{$patient|escape}-->'
			};
			if (financialDemographics.validate( myContent )) {
				dojo.io.bind({
					method: "POST",
					content: {
						param0: myContent
					},
					url: "<!--{$relay}-->/org.freemedsoftware.module.FinancialDemographics.add",
					load: function ( type, data, evt ) {
						financialDemographics.handleResponse( data );
					},
					mimetype: "text/json"
				});
			}
		}
	};

	_container_.addOnLoad(function() {
		dojo.event.connect( dojo.widget.byId('ModuleFormCommitChangesButton'), 'onClick', financialDemographics, 'submit' );
	});
	_container_.addOnUnload(function() {
		dojo.event.disconnect( dojo.widget.byId('ModuleFormCommitChangesButton'), 'onClick', financialDemographics, 'submit' );
	});

</script>

<h3><!--{t}-->Financial Demographics<!--{/t}--></h3>

<table border="0" style="width: auto;">

	<tr>
		<td align="right"><!--{t}-->Yearly Income<!--{/t}--></td>
		<td><input type="text" id="financialDemographics.yearlyIncome" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Identification<!--{/t}--></td>
		<td>
		<select dojoType="ComboBox" id="financialDemographics.identificationType" widgetId="financialDemographics.identificationType">
			<option value="driver's license"><!--{t}-->driver's license<!--{/t}--></option>
			<option value="passport"><!--{t}-->passport<!--{/t}--></option>
			<option value="baptismal certificate"><!--{t}-->baptismal certificate<!--{/t}--></option>
			<option value="green card"><!--{t}-->green card<!--{/t}--></option>
			<option value="birth certificate"><!--{t}-->birth certificate<!--{/t}--></option>
		</select>
		</td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Identification Number<!--{/t}--></td>
		<td><input type="text" id="financialDemographics.identificationNumber" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Identification Issuer<!--{/t}--></td>
		<td><input type="text" id="financialDemographics.identificationIssuer" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Identification Expiration<!--{/t}--></td>
		<td><input type="text" id="financialDemographics.identificationExpiration" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Household Size<!--{/t}--></td>
		<td><input type="text" id="financialDemographics.householdSize" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Has a Spouse?<!--{/t}--></td>
		<td>
		<select dojoType="ComboBox" id="financialDemographics.spouse" widgetId="financialDemographics.spouse">
			<option value="0"><!--{t}-->No<!--{/t}--></option>
			<option value="1"><!--{t}-->Yes<!--{/t}--></option>
		</select>
		</td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Number of Dependent Children<!--{/t}--></td>
		<td><input type="text" id="financialDemographics.dependentChildren" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Number of Other Dependents<!--{/t}--></td>
		<td><input type="text" id="financialDemographics.dependentOthers" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Other Information<!--{/t}--></td>
		<td><textarea id="financialDemographics.otherInformation" rows="4" cols="40" wrap="virtual"></textarea></td>
	</tr>

</table>

<div align="center">
        <table border="0" style="width:200px;">
        <tr><td align="center">
	        <button dojoType="Button" id="ModuleFormCommitChangesButton" widgetId="ModuleFormCommitChangesButton">
	                <div><!--{t}-->Commit Changes<!--{/t}--></div>
	        </button>
        </td><td align="left">
        	<button dojoType="Button" id="ModuleFormCancelButton" widgetId="ModuleFormCancelButton" onClick="freemedPatientContentLoad( 'org.freemedsoftware.ui.patient.overview.default?patient=<!--{$patient}-->' );">
        	        <div><!--{t}-->Cancel<!--{/t}--></div>
        	</button>
        </td></tr></table>
</div>

