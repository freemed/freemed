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

<!--{if $MODE ne 'widget'}-->
<!--{include file="org.freemedsoftware.ui.framework.tpl"}-->
<!--{/if}-->

<style type="text/css">

	/* Force dojo buttons to have some padding */
	.dojoButtonContents div { padding: 5px; }

</style>

<script type="text/javascript">
	dojo.require("dojo.event.*");
	dojo.require("dojo.widget.Form");
	dojo.require("dojo.widget.FilteringTable");
	dojo.require("dojo.widget.DropdownDatePicker");

	var chargeEntryBeginDate = null;
	var chargeEntryEndDate = null;

	function chargeEntryPopulate ( ) {
		dojo.io.bind({
			method : 'POST',
			url: '<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.api.Scheduler.GetDailyAppointmentsRange',
			content: {
				param0: chargeEntryBeginDate,
				param1: chargeEntryEndDate
			},
			error: function( ) { },
			load: function(type, data, evt) {
				if (data) {
					dojo.widget.byId('chargeEntrySearch').store.setData( data );
				}
			},
			mimetype: "text/json"
		});
	} // end chargeEntryPopulate

	function chargeEntryPopulateForm ( id ) {
		if ( ! id ) { return false; }
		var rec = dojo.widget.byId('chargeEntrySearch').store.getDataByKey( id );
		document.getElementById('spanPatientName').innerHTML = rec.patient;
		document.getElementById('spanProviderName').innerHTML = rec.provider;
		document.getElementById('spanDate').innerHTML = rec.date_of;

		// Diagnosis codes
		dojo.widget.byId('dxCode1').enable();
		dojo.widget.byId('dxCode2').enable();
		dojo.widget.byId('dxCode3').enable();
		dojo.widget.byId('dxCode4').enable();

		// CPT/Procedural codes
		dojo.widget.byId('cptCode1').enable();
		dojo.widget.byId('cptCode2').enable();
		dojo.widget.byId('cptCode3').enable();
		dojo.widget.byId('cptCode4').enable();

		// CPT/Procedural codes
		dojo.widget.byId('cptCodeMod1').enable();
		dojo.widget.byId('cptCodeMod2').enable();
		dojo.widget.byId('cptCodeMod3').enable();
		dojo.widget.byId('cptCodeMod4').enable();

		// Units
		document.getElementById('cptUnits1').disabled = false;
		document.getElementById('cptUnits2').disabled = false;
		document.getElementById('cptUnits3').disabled = false;
		document.getElementById('cptUnits4').disabled = false;
	} // end chargeEntryPopulateForm

	function setChargeEntryBeginDate( x ) {
		chargeEntryBeginDate = x;
		chargeEntryPopulate( );
	}
	function setChargeEntryEndDate( x ) {
		chargeEntryEndDate = x;
		chargeEntryPopulate( );
	}

	dojo.addOnLoad(function(){
		dojo.event.connect( dojo.widget.byId('chargeEntrySearch'), 'onSelect', function () {
			var w = dojo.widget.byId('chargeEntrySearch');
			var val;
			if (w.getSelectedData().length > 0) {
				dojo.debug("found getSelectedData()");
				val = w.getSelectedData()[0].scheduler_id;
			}
			if (val) {
				chargeEntryPopulateForm( val );
			}
		});
		chargeEntryPopulate( );
	});
</script>

<table>
<tr>
	<td>Date Range : </td>
	<td><input dojoType="DropdownDatePicker" widgetId="chargeEntryBegin" id="chargeEntryBegin" value="" onValueChanged="setChargeEntryBeginDate(dojo.widget.byId('chargeEntryBegin').inputNode.value);" /></td>
	<td><input dojoType="DropdownDatePicker" widgetId="chargeEntryEnd" id="chargeEntryEnd" value="" onValueChanged="setChargeEntryEndDate(dojo.widget.byId('chargeEntryEnd').inputNode.value);" /></td>
</tr>
<tr>
	<td colspan="3">
		<div class="tableContainer">
                <table dojoType="FilteringTable" id="chargeEntrySearch" widgetId="chargeEntrySearch" headClass="fixedHeader" tbodyClass="scrollContent" enableAlternateRows="true" rowAlterateClass="alternateRow" valueField="scheduler_id" border="0" multiple="false">
			<thead>
				<tr>
					<th field="date_of_mdy" dataType="Date"><!--{t}-->Date<!--{/t}--></th>
					<th field="patient" dataType="String"><!--{t}-->Patient<!--{/t}--></th>
					<th field="provider" dataType="String"><!--{t}-->Provider<!--{/t}--></th>
					<th field="note" dataType="String"><!--{t}-->Note<!--{/t}--></th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
		</div>
	</td>
</tr>
</table>

<form dojoType="Form" id="chargeEntryForm" style="height: auto;">

<table border="0">

<tr>
	<td><b><!--{t}-->Patient<!--{/t}--></b>: <span id="spanPatientName" /></td>
	<td><b><!--{t}-->Provider<!--{/t}--></b>: <span id="spanProviderName" /></td>
	<td><b><!--{t}-->Date<!--{/t}--></b>: <span id="spanDate" /></td>
</tr>

<tr>
	<td><b><!--{t}-->ICD<!--{/t}--> 1</b>:
		<input dojoType="Select"
			autocomplete="true"
			id="dxCode1" widgetId="dxCode1"
			style="width:200px;"
			dataUrl="<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.module.IcdCodes.picklist?param0=%{searchString}"
			setValue="document.getElementById('dxCode1_real').value = arguments[0];"
			disabled="true"
			mode="remote" />
		<input type="hidden" id="dxCode1_real" value="" />
	</td>
	<td><b><!--{t}-->ICD<!--{/t}--> 2</b>:
		<input dojoType="Select"
			autocomplete="true"
			id="dxCode2" widgetId="dxCode2"
			style="width:200px;"
			dataUrl="<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.module.IcdCodes.picklist?param0=%{searchString}"
			setValue="document.getElementById('dxCode2_real').value = arguments[0];"
			disabled="true"
			mode="remote" />
		<input type="hidden" id="dxCode2_real" value="" />
	</td>
	<td><b><!--{t}-->ICD<!--{/t}--> 3</b>:
		<input dojoType="Select"
			autocomplete="true"
			id="dxCode3" widgetId="dxCode3"
			style="width:200px;"
			dataUrl="<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.module.IcdCodes.picklist?param0=%{searchString}"
			setValue="document.getElementById('dxCode3_real').value = arguments[0];"
			disabled="true"
			mode="remote" />
		<input type="hidden" id="dxCode3_real" value="" />
	</td>
	<td><b><!--{t}-->ICD<!--{/t}--> 4</b>:
		<input dojoType="Select"
			autocomplete="true"
			id="dxCode4" widgetId="dxCode4"
			style="width:200px;"
			dataUrl="<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.module.IcdCodes.picklist?param0=%{searchString}"
			setValue="document.getElementById('dxCode4_real').value = arguments[0];"
			disabled="true"
			mode="remote" />
		<input type="hidden" id="dxCode4_real" value="" />
	</td>
</tr>

<tr>
	<td><b><!--{t}-->CPT<!--{/t}--></b>:
		<input dojoType="Select"
			autocomplete="true"
			id="cptCode1" widgetId="cptCode1"
			style="width:300px;"
			dataUrl="<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.module.CptCodes.picklist?param0=%{searchString}"
			setValue="document.getElementById('cptCode1_real').value = arguments[0];"
			disabled="true"
			mode="remote" />
		<input type="hidden" id="cptCode1_real" value="" />
		<input dojoType="Select"
			autocomplete="true"
			id="cptCodeMod1" widgetId="cptCodeMod1"
			style="width:100px;"
			dataUrl="<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.module.CptModifiers.picklist?param0=%{searchString}"
			setValue="document.getElementById('cptCodeMod1_real').value = arguments[0];"
			disabled="true"
			mode="remote" />
		<input type="hidden" id="cptCodeMod1_real" value="" />
	</td>
	<td><b><!--{t}-->Units<!--{/t}--></b>: <input type="text" id="cptUnits1" value="1" disabled="true" /></td>
</tr>
<tr>
	<td><b><!--{t}-->CPT<!--{/t}--></b>:
		<input dojoType="Select"
			autocomplete="true"
			id="cptCode2" widgetId="cptCode2"
			style="width:300px;"
			dataUrl="<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.module.CptCodes.picklist?param0=%{searchString}"
			setValue="document.getElementById('cptCode2_real').value = arguments[0];"
			disabled="true"
			mode="remote" />
		<input type="hidden" id="cptCode2_real" value="" />
		<input dojoType="Select"
			autocomplete="true"
			id="cptCodeMod2" widgetId="cptCodeMod2"
			style="width:100px;"
			dataUrl="<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.module.CptModifiers.picklist?param0=%{searchString}"
			setValue="document.getElementById('cptCodeMod2_real').value = arguments[0];"
			disabled="true"
			mode="remote" />
		<input type="hidden" id="cptCodeMod2_real" value="" />
	</td>
	<td><b><!--{t}-->Units<!--{/t}--></b>: <input type="text" id="cptUnits2" value="1" disabled="true" /></td>
</tr>
<tr>
	<td><b><!--{t}-->CPT<!--{/t}--></b>:
		<input dojoType="Select"
			autocomplete="true"
			id="cptCode3" widgetId="cptCode3"
			style="width:300px;"
			dataUrl="<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.module.CptCodes.picklist?param0=%{searchString}"
			setValue="document.getElementById('cptCode3_real').value = arguments[0];"
			disabled="true"
			mode="remote" />
		<input type="hidden" id="cptCode3_real" value="" />
		<input dojoType="Select"
			autocomplete="true"
			id="cptCodeMod3" widgetId="cptCodeMod3"
			style="width:100px;"
			dataUrl="<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.module.CptModifiers.picklist?param0=%{searchString}"
			setValue="document.getElementById('cptCodeMod3_real').value = arguments[0];"
			disabled="true"
			mode="remote" />
		<input type="hidden" id="cptCodeMod3_real" value="" />
	</td>
	<td><b><!--{t}-->Units<!--{/t}--></b>: <input type="text" id="cptUnits3" value="1" disabled="true" /></td>
</tr>
<tr>
	<td><b><!--{t}-->CPT<!--{/t}--></b>:
		<input dojoType="Select"
			autocomplete="true"
			id="cptCode4" widgetId="cptCode4"
			style="width:300px;"
			dataUrl="<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.module.CptCodes.picklist?param0=%{searchString}"
			setValue="document.getElementById('cptCode4_real').value = arguments[0];"
			disabled="true"
			mode="remote" />
		<input type="hidden" id="cptCode4_real" value="" />
		<input dojoType="Select"
			autocomplete="true"
			id="cptCodeMod4" widgetId="cptCodeMod4"
			style="width:100px;"
			dataUrl="<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.module.CptModifiers.picklist?param0=%{searchString}"
			setValue="document.getElementById('cptCodeMod4_real').value = arguments[0];"
			disabled="true"
			mode="remote" />
		<input type="hidden" id="cptCodeMod4_real" value="" />
	</td>
	<td><b><!--{t}-->Units<!--{/t}--></b>: <input type="text" id="cptUnits4" value="1" disabled="true" /></td>
</tr>

</table>

</form>

<!--{if $MODE ne 'widget'}-->
<!--{include file="org.freemedsoftware.ui.footer.tpl"}-->
<!--{/if}-->

