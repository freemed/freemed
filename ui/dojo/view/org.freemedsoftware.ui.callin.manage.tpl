<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2011 FreeMED Software Foundation
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

	var o = {	
		loadData: function ( ) {
			// Initial data load
			dojo.io.bind({
				method: 'POST',
				content: { },
				url: '<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.module.CallIn.GetAll',
				load: function( type, data, evt ) {
					if (data) {
						dojo.widget.byId('callinTable').store.setData( data );
					}
				},
				mimetype: "text/json"
			});
		}
	};

	_container_.addOnLoad(function () {
		o.loadData();
	});

	_container_.addOnUnload(function () {
	});

</script>

<h3><!--{t}-->Call-in Patient Management<!--{/t}--></h3>

<div class="tableContainer">
	<table dojoType="FilteringTable" id="callinTable" widgetId="callinTable" headClass="fixedHeader"
	 tbodyClass="scrollContent" enableAlternateRows="true" rowAlternateClass="alternateRow"
	 valueField="id" border="0" multiple="yes" style="height: 100%;">
	<thead>
		<tr>
			<th field="call_date_mdy" dataType="Date"><!--{t}-->Date<!--{/t}--></th>
			<th field="name" dataType="String"><!--{t}-->Name<!--{/t}--></th>
			<th field="phone_home" dataType="String"><!--{t}-->Home Phone<!--{/t}--></th>
			<th field="phone_work" dataType="String"><!--{t}-->Work Phone<!--{/t}--></th>
			<th field="complaint" dataType="Html"><!--{t}-->Complaint<!--{/t}--></th>
		</tr>
	</thead>
	<tbody></tbody>
	</table>
</div>

