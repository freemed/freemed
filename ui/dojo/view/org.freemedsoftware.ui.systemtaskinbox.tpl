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
	dojo.require("dojo.event.*");

	var o = {	
		loadData: function ( ) {
			// Initial data load
			dojo.io.bind({
				method: 'POST',
				content: { },
				url: '<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.module.SystemNotifications.GetSystemTaskUserInbox',
				load: function( type, data, evt ) {
					if (data) {
						dojo.widget.byId( 'systemTaskInboxTable' ).store.setData( data );
					}
				},
				mimetype: "text/json"
			});
		},
		onSelect: function ( ) {
			// Callback for selecting an item
			var w = dojo.widget.byId( 'systemTaskInboxTable' );
			var val = w.getSelectedData();

			// Push this to the appropriate system hook
			var d = 'org.freemedsoftware.module.' + val.module + '.systemtask?patient=' + encodeURIComponent( val.patient ) + '&id=' + encodeURIComponent( val.oid );
			freemedLoad( d );
		}
	};

	_container_.addOnLoad(function () {
		o.loadData();
		dojo.event.connect( dojo.widget.byId( 'systemTaskInboxTable' ), 'onSelect', o, 'onSelect' );
	});

	_container_.addOnUnload(function () {
		dojo.event.disconnect( dojo.widget.byId( 'systemTaskInboxTable' ), 'onSelect', o, 'onSelect' );
	});

</script>

<h3><!--{t}-->System Task Inbox<!--{/t}--></h3>

<div class="tableContainer">
	<table dojoType="FilteringTable" id="systemTaskInboxTable" widgetId="systemTaskInboxTable" headClass="fixedHeader"
	 tbodyClass="scrollContent" enableAlternateRows="true" rowAlternateClass="alternateRow"
	 valueField="id" border="0" multiple="false" style="height: 100%;">
	<thead>
		<tr>
			<th field="stamp_mdy" dataType="Date"><!--{t}-->Date<!--{/t}--></th>
			<th field="patient_name" dataType="String"><!--{t}-->Patient<!--{/t}--></th>
			<th field="module_name" dataType="String"><!--{t}-->Module<!--{/t}--></th>
			<th field="summary" dataType="String"><!--{t}-->Summary<!--{/t}--></th>
		</tr>
	</thead>
	<tbody></tbody>
	</table>
</div>

