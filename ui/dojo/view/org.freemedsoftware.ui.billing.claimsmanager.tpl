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

<style type="text/css">

	/* Force dojo buttons to have some padding */
	.dojoButtonContents div { padding: 5px; }

</style>

<script type="text/javascript">
	dojo.require("dojo.event.*");
	dojo.require("dojo.widget.Form");
	dojo.require("dojo.widget.FilteringTable");
	dojo.require("dojo.widget.DropdownDatePicker");

	var claimsManager = {
		loadData: function ( ) {
			var haveCrit = 0;
			var crit = { };
			if ( document.getElementById('criteriaLastName').value != '' ) { crit.last_name = document.getElementById('criteriaLastName').value; haveCrit = 1; }
			if ( document.getElementById('criteriaFirstName').value != '' ) { crit.first_name = document.getElementById('criteriaFirstName').value; haveCrit = 1; }
			//alert( dojo.json.serialize(crit) );

			// Do not allow us to proceed if there are no qualifiers, otherwise we can really jam up the browser
			if ( ! haveCrit ) {
				alert ("<!--{t}-->Please select the criteria for the claims you are trying to work with.<!--{/t}-->");
				return false;
			}
			dojo.io.bind({
				method: 'POST',
				content: { param0: crit },
				url: "<!--{$relay}-->/org.freemedsoftware.api.ClaimLog.AgingReportQualified",
				load: function ( type, data, evt ) {
					dojo.widget.byId('claimsManagerTable').store.setData( data );
				},
				mimetype: "text/json"
			});
		}
	};

	_container_.addOnLoad(function(){
		dojo.event.connect( dojo.widget.byId('claimsManagerUpdateButton'), "onClick", claimsManager, "loadData" );
	});
	_container_.addOnUnLoad(function(){
		dojo.event.disconnect( dojo.widget.byId('claimsManagerUpdateButton'), "onClick", claimsManager, "loadData" );
	});

</script>

<div dojoType="SplitContainer" orientation="vertical" sizerWidth="5" activeSizing="0" layoutAlign="client">

	<div dojoType="ContentPane" id="claimsManagerFormPane" layoutAlign="top" sizeShare="40">

		<h3><!--{t}-->Claims Manager<!--{/t}--></h3>

		<form>
		<table border="0">

			<tr>
				<td align="right" valign="top"><b><!--{t}-->Last Name<!--{/t}--></b></td>
				<td align="left" valign="top"><input type="text" id="criteriaLastName" /></td>
				<td align="right" valign="top"><b><!--{t}-->Aging Period<!--{/t}--></b></td>
				<td align="left" valign="top">
					<input type="radio" name="criteriaAging" id="criteriaAging1" value="0-30" /><label for="criteriaAging1">0-30</label>
					<input type="radio" name="criteriaAging" id="criteriaAging2" value="31-60" /><label for="criteriaAging2">31-60</label>
					<input type="radio" name="criteriaAging" id="criteriaAging3" value="61-90" /><label for="criteriaAging3">61-90</label>
					<input type="radio" name="criteriaAging" id="criteriaAging4" value="91-120" /><label for="criteriaAging4">91-120</label>
					<input type="radio" name="criteriaAging" id="criteriaAging5" value="120+" /><label for="criteriaAging5">120+</label>
				</td>
			</tr>

			<tr>
				<td align="right" valign="top"><b><!--{t}-->First Name<!--{/t}--></b></td>
				<td align="left" valign="top"><input type="text" id="criteriaFirstName" /></td>
				<td align="right" valign="top"><b><!--{t}-->Billed?<!--{/t}--></b></td>
				<td align="left" valign="top">
					<input type="radio" name="criteriaBilled" id="criteriaBilledN" value="0" /><label for="criteriaBilledN"><!--{t}-->No<!--{/t}--></label>
					<input type="radio" name="criteriaBilled" id="criteriaBilledY" value="1" /><label for="criteriaBilledY"><!--{t}-->Yes<!--{/t}--></label>
				</td>
			</tr>

			<tr>
				<td align="right" valign="top"><b><!--{t}--><!--{/t}--></b></td>
				<td align="left" valign="top"></td>
				<td align="right" valign="top"><b><!--{t}--><!--{/t}--></b></td>
				<td align="left" valign="top"></td>
			</tr>

			<tr>
				<td colspan="4" align="center">
					<div dojoType="Button" id="claimsManagerUpdateButton">
						<!--{t}-->Update<!--{/t}-->
					</div>
				</td>
			</tr>

		</table>
		</form>

	</div>

	<div dojoType="ContentPane" sizeShare="60" layoutAlign="bottom">

		<div class="tableContainer">
	                <table dojoType="FilteringTable" id="claimsManagerTable" widgetId="claimsManagerTable" headClass="fixedHeader" tbodyClass="scrollContent" enableAlternateRows="true" rowAlterateClass="alternateRow" valueField="claim" border="0" multiple="false">
				<thead>
					<tr>
						<th field="date_of_mdy" dataType="Date"><!--{t}-->Date<!--{/t}--></th>
						<th field="patient" dataType="String"><!--{t}-->Patient<!--{/t}--></th>
						<th field="claim" dataType="Integer"><!--{t}-->Claim<!--{/t}--></th>
						<th field="provider" dataType="String"><!--{t}-->Provider<!--{/t}--></th>
						<th field="payer" dataType="String"><!--{t}-->Payer<!--{/t}--></th>
						<th field="paid" dataType="String"><!--{t}-->Paid<!--{/t}--></th>
						<th field="balance" dataType="String"><!--{t}-->Balance<!--{/t}--></th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</div>

</div>

