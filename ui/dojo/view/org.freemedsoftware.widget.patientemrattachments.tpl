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

<script type="text/javascript">
	dojo.require("dojo.event.*");
	dojo.require("dojo.widget.FilteringTable");
	dojo.require('dojo.widget.DropdownDatePicker');

	function patientEmrAction ( action, id ) {
		alert( "TODO: " + action + " " + id );
	} // end patientEmrAction

	function patientLoadEmrAttachments ( ) {
		// Initial data load
		dojo.io.bind({
			method: 'POST',
			content: {
				param0: '<!--{$patient|escape}-->'

			},
			url: '<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.api.PatientInterface.EmrAttachmentsbyPatient',
			error: function() { },
			load: function( type, data, evt ) {
				if (typeof(data) == 'object') {
					for (i=0; i<data.length; i++) {	
						data[i]['actions'] = '';
						data[i]['actions'] += "<a onClick=\"patientEmrAction('modify', " + data[i]['id'] + ");\"><img src=\"<!--{$htdocs}-->/images/summary_modify.png\" border\"0\" /></a>";
						data[i]['actions'] += "<a onClick=\"patientEmrAction('print', " + data[i]['id'] + ");\"><img src=\"<!--{$htdocs}-->/images/summary_print.png\" border\"0\" /></a>";
					}
					dojo.widget.byId('patientEmrAttachments').store.setData( data );
				}
			},
			mimetype: "text/json"
		});
	}

	dojo.addOnLoad(patientLoadEmrAttachments);

</script>

<div class="tableContainer">
	<table dojoType="FilteringTable" id="patientEmrAttachments" widgetId="patientEmrAttachments" headClass="fixedHeader"
	 tbodyClass="scrollContent" enableAlternateRows="true" rowAlternateClass="alternateRow"
	 valueField="id" border="0" multiple="yes">
	<thead>
		<tr>
			<th field="stamp" dataType="String">Date/Time</th>
			<th field="summary" dataType="String">Summary</th>
			<th field="type" dataType="String">Type</th>
			<th field="notes" dataType="Html">Notes</th>
			<th field="actions" dataType="Html">Actions</th>
		</tr>
	</thead>
	<tbody></tbody>
	</table>
</div>

