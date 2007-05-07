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

<script language="javascript">
	dojo.require('dojo.event.*');
	dojo.require('dojo.widget.FilteringTable');

	var supportData = {
		onAddClick: function ( ) {
			freemedLoad( 'org.freemedsoftware.module.<!--{$module|escape}-->.form' );
		},
		onModifyClick: function ( ) {
			alert('STUB: modify click');
		},
		onDeleteClick: function ( ) {
			alert('STUB: delete click');
		},
		onFilterClick: function ( ) {
			alert('STUB: filter click');
		},
		moduleData: null,
		createMaintenance: function ( ) {
			// Grab synchronously the structions
			dojo.io.bind({
				method: 'POST',
				url: '<!--{$relay}-->/org.freemedsoftware.module.<!--$module|escape}-->.GetMaintenanceStructure',
				content: { },
				load: function ( type, data, evt ) {
					supportData.moduleData = data;
				},
				mimetype: 'text/json',
				sync: true
			});

			// Try to destroy this, if it exists
			try {
				dojo.widget.byId('supportDataWidget').destroy();
			} catch (err) { }

			// Create new widget to hold the data...
			var w = dojo.widget.byId('supportDataHolder');

			var selectWidget = document.getElementById('supportFilterSelect');

			// Clear past filtering possibilities
			w.columns = [];
			selectWidget.options.length = 0;

			// Redistribute properties as columns
			for ( var c in this.moduleData ) {
				//alert ( c );

				// Populate columns
				w.columns.push(
					w.createMetaData({
						field: this.moduleData[c],
						format: 'String',
						label: c
					})
				);

				// Populate filtering widget
				selectWidget.options[ selectWidget.options.length + 1 ] = new Option ( c, this.moduleData[c] );
				//alert(dojo.json.serialize(selectWidget.options));
			}
			//alert(dojo.json.serialize(w.columns));
			w.init();

			// Load initial data set
			this.populateTable( module, w );
		},
		populateTable: function ( module, widget ) {
			dojo.io.bind({
				method: 'POST',
				url: '<!--{$relay}-->/org.freemedsoftware.module.<!--{$module|escape}-->.GetRecords',
				content: {
					param0: 1000
				},
				load: function ( type, data, event ) {
					widget.store.setData( data );
				},
				mimetype: "text/json"
			});
		}
	};

	_container_.addOnLoad(function(){
		supportData.createMaintenance();
		dojo.event.connect( dojo.widget.byId('supportDataSelector'), 'onSelect', supportData, 'createMaintenance' );
		dojo.event.connect( dojo.widget.byId('supportAddButton'), 'onClick', supportData, 'onAddClick' );
		dojo.event.connect( dojo.widget.byId('supportModifyButton'), 'onClick', supportData, 'onModifyClick' );
		dojo.event.connect( dojo.widget.byId('supportDeleteButton'), 'onClick', supportData, 'onDeleteClick' );
		dojo.event.connect( dojo.widget.byId('supportFilterButton'), 'onClick', supportData, 'onFilterClick' );
	});

	_container_.addOnUnload(function(){
		dojo.event.disconnect( dojo.widget.byId('supportDataSelector'), 'onSelect', supportData, 'createMaintenance' );
		dojo.event.disconnect( dojo.widget.byId('supportAddButton'), 'onClick', supportData, 'onAddClick' );
		dojo.event.disconnect( dojo.widget.byId('supportModifyButton'), 'onClick', supportData, 'onModifyClick' );
		dojo.event.disconnect( dojo.widget.byId('supportDeleteButton'), 'onClick', supportData, 'onDeleteClick' );
		dojo.event.disconnect( dojo.widget.byId('supportFilterButton'), 'onClick', supportData, 'onFilterClick' );
	});

</script>

<h3><!--{t}-->Support Data<!--{/t}--></h3>

<div>
	<table border="0">
		<tr>
			<td><button dojoType="Button" id="supportAddButton" widgetId="supportAddButton"><!--{t}-->Add<!--{/t}--></button></td>
			<td><button dojoType="Button" id="supportModifyButton" widgetId="supportModifyButton"><!--{t}-->Modify<!--{/t}--></button></td>
			<td><button dojoType="Button" id="supportDeleteButton" widgetId="supportDeleteButton"><!--{t}-->Delete<!--{/t}--></button></td>
			<td>
				<select id="supportFilterSelect" name="supportFilterSelect">
					<option></option>
				</select>
			</td>
			<td>
				<input type="text" id="supportFilterText" name="supportFilterText" value="" />
			</td>
			<td><button dojoType="Button" id="supportFilterButton" widgetId="supportFilterButton"><!--{t}-->Filter<!--{/t}--></button></td>
		</tr>
	</table>
</div>

<div class="tableContainer">
        <table dojoType="FilteringTable" id="supportDataHolder"
         widgetId="supportDataHolder" headClass="fixedHeader"
         tbodyClass="scrollContent" enableAlternateRows="true"
         valueField="module_class" border="0" multiple="false"
         maxSelect="1" rowAlternateClass="alternateRow" sizerWidth="2"
         style="height: 100%;">
	</table>
</div>

