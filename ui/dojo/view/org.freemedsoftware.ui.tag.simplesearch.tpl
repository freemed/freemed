<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2006 FreeMED Software Foundation
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

<!--{include file="org.freemedsoftware.ui.framework.tpl"}-->

<script type="text/javascript">
	dojo.require("dojo.event.*");
	dojo.require("dojo.widget.FilteringTable");
</script>

<style type="text/css">
	table {
		width: 100%;
		}

	* html div.tableContainer {	/* IE only hack */
		width:95%;
		/* border:1px solid #ccc; */
		height: 285px;
		overflow-x:hidden;
		overflow-y: auto;
		}

	* html div.tableContainer table {
		width:100%; border:1px solid #ccc; cursor:default;
		}

	div.tableContainer table td,
	div.tableContainer table th{
		border-right:1px solid #999;
		padding:2px;
		font-weight:normal;
		}
	table thead td, table thead th {
		background:#94BEFF;
		}
		
	* html div.tableContainer table thead tr td,
	* html div.tableContainer table thead tr th{
		/* IE Only hacks */
		position:relative;
		top:expression(dojo.html.getFirstAncestorByTag(this,'table').parentNode.scrollTop-2);
		}
		
	html>body tbody.scrollContent {
		height: 262px;
		overflow-x:hidden;
		overflow-y: auto;
		}

	tbody.scrollContent td, tbody.scrollContent tr td {
		background: #FFF;
		padding: 2px;
		}

	tbody.scrollContent tr.alternateRow td {
		background: #e3edfa;
		padding: 2px;
		}

	tbody.scrollContent tr.selected td {
		background: yellow;
		padding: 2px;
		}
	tbody.scrollContent tr:hover td {
		background: #a6c2e7;
		padding: 2px;
		}
	tbody.scrollContent tr.selected:hover td {
		background: #ffff33;
		padding: 2px;
		}

	.searchHeader {
		width: 100%;
		border: 1px solid #000000;
		background: #ccccff;
		padding: 5px;
		text-decoration: small-caps;
		}
</style>

<script language="javascript">
	dojo.addOnLoad(function() {
		dojo.io.bind({
			method : 'POST',
			url: '<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.module.PatientTag.SimpleTagSearch?param0=<!--{$tag}-->',
			error: function() { },
			load: function(type, data, evt) {
				if (data) {
					dojo.widget.byId('tagSimpleTable').store.setData(data);
				}
			},
			mimetype: "text/json"
		});
	});
</script>

<div class="searchHeader">
	<table border="0" cellpadding="0" cellspacing="0" width="98%"><tr>
	<td align="left"><b><!--{t}-->Tags found for<!--{/t}--></b>: "<!--{$tag|escape}-->"</td>
	<td align="right" style="padding-right: 10px;"><img src="<!--{$htdocs}-->/images/magnifying_glass.png" border="0" onClick="window.location='<!--{$base_uri}-->/controller.php/<!--{$ui}-->/org.freemedsoftware.ui.tag.searchform'; return true;" /></td>
	</tr></table>
</div>

<div class="tableContainer">
	<table dojoType="FilteringTable" widgetId="tagSimpleTable" headClass="fixedHeader"
	 tbodyClass="scrollContent" enableAlternateRows="true" rowAlternateClass="alternateRow"
	 border="0" onSelect="window.location='<!--{$base_uri}-->/controller.php/<!--{$ui}-->/org.freemedsoftware.controller.patient.overview?patient=' + dojo.widget.byId('tagSimpleTable').getSelectedData().patient_record; return true;">
	<thead>
		<tr>
			<th field="patient_record" dataType="Number"></th>
			<th field="patient_id" dataType="String">Record</th>
			<th field="last_name" dataType="String">Last</th>
			<th field="first_name" dataType="String">First</th>
			<th field="middle_name" dataType="String">Middle</th>
			<th field="date_of_birth" dataType="String">DOB</th>
			<th field="last_seen" dataType="String">Last Seen</th>
		</tr>
	</thead>
	<tbody>
<!--{*
        //      * patient_record - Patient record id
        //      * patient_id - Practice ID for patient
        //      * last_seen - Date last seen/next appointment
        //      * first_name - First name of patient
        //      * last_name - Last name of patient
        //      * middle_name - Middle name of patient
        //      * date_of_birth - Patient's date of birth
*}-->
	</tbody>
	</table>
</div>

