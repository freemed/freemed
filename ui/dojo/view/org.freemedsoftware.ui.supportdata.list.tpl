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

<!--{* *** Smarty "preprocessing *** *}-->

<!--{method var='moduleName' namespace="org.freemedsoftware.module.$module.GetModuleName"}-->
<!--{method var='moduleStructure' namespace="org.freemedsoftware.module.$module.GetMaintenanceStructure"}-->

<script language="javascript">
	dojo.require('dojo.event.*');
	dojo.require('dojo.widget.FilteringTable');

	var supportData = {
		onAddClick: function ( ) {
			freemedLoad( 'org.freemedsoftware.module.<!--{$module|escape}-->.form' );
		},
		onModifyClick: function ( ) {
			var v;
			try {
				v = dojo.widget.byId( 'supportDataHolder' ).getSelectedData().id;
			} catch (e) {
				alert("<!--{t}-->Please select a record.<!--{/t}-->");
				return false;
			}
			freemedLoad( 'org.freemedsoftware.module.<!--{$module|escape}-->.form?id=' + v );
		},
		onDeleteClick: function ( ) {
			var v;
			try {
				v = dojo.widget.byId( 'supportDataHolder' ).getSelectedData().id;
			} catch (e) {
				alert("<!--{t}-->Please select a record.<!--{/t}-->");
				return false;
			}
			if ( confirm("<!--{t}-->Are you sure you want to delete this record?<!--{/t}-->") ) {
				dojo.io.bind({
					method: 'POST',
					content: {
						id: v
					},
					url: '<!--{$relay}-->/org.freemedsoftware.module.<!--{$module}-->.del',
					load: function ( type, data, evt ) {
						freemedMessage( "<!--{t}-->Record successfully removed.<!--{/t}-->", 'INFO' );
						// Refresh data display
						supportData.loadData();
					},
					mimetype: 'text/json'
				});
			}
		},
		onFilterClick: function ( ) {
			var filterField = document.getElementById( 'supportFilterSelect' ).value;
			dojo.widget.byId( 'supportDataHolder' ).setFilter( filterField, supportData.filterCallback );
		},
		onBackClick: function ( ) {
			freemedLoad( 'org.freemedsoftware.ui.supportdata' );
		},
		filterCallback: function ( v ) {
			var a = document.getElementById( 'supportFilterText' ).value;
			if ( ! a.length ) { return true; }
			return v.toLowerCase().match( a.toLowerCase() );
		},
		moduleData: null,
		loadData: function ( ) {
			// Load initial data set
			var w = dojo.widget.byId('supportDataHolder');
			dojo.io.bind({
				method: 'POST',
				url: '<!--{$relay}-->/org.freemedsoftware.module.<!--{$module|escape}-->.GetRecords',
				content: {
					param0: 1000
				},
				load: function ( type, data, event ) {
					w.store.setData( data );
				},
				mimetype: "text/json"
			});
		}
	};

	_container_.addOnLoad(function(){
		supportData.loadData();
		dojo.event.connect( dojo.widget.byId('supportAddButton'), 'onClick', supportData, 'onAddClick' );
		dojo.event.connect( dojo.widget.byId('supportModifyButton'), 'onClick', supportData, 'onModifyClick' );
		dojo.event.connect( dojo.widget.byId('supportDeleteButton'), 'onClick', supportData, 'onDeleteClick' );
		dojo.event.connect( dojo.widget.byId('supportFilterButton'), 'onClick', supportData, 'onFilterClick' );
		dojo.event.connect( dojo.widget.byId('supportBackButton'), 'onClick', supportData, 'onBackClick' );
	});

	_container_.addOnUnload(function(){
		dojo.event.disconnect( dojo.widget.byId('supportAddButton'), 'onClick', supportData, 'onAddClick' );
		dojo.event.disconnect( dojo.widget.byId('supportModifyButton'), 'onClick', supportData, 'onModifyClick' );
		dojo.event.disconnect( dojo.widget.byId('supportDeleteButton'), 'onClick', supportData, 'onDeleteClick' );
		dojo.event.disconnect( dojo.widget.byId('supportFilterButton'), 'onClick', supportData, 'onFilterClick' );
		dojo.event.disconnect( dojo.widget.byId('supportBackButton'), 'onClick', supportData, 'onBackClick' );
	});

</script>

<h3><!--{t}-->Support Data<!--{/t}-->: <!--{$moduleName}--></h3>

<div>
	<table border="0">
		<tr>
			<td><button dojoType="Button" id="supportAddButton" widgetId="supportAddButton"><!--{t}-->Add<!--{/t}--></button></td>
			<td><button dojoType="Button" id="supportModifyButton" widgetId="supportModifyButton"><!--{t}-->Modify<!--{/t}--></button></td>
			<td><button dojoType="Button" id="supportDeleteButton" widgetId="supportDeleteButton"><!--{t}-->Delete<!--{/t}--></button></td>
			<td>
				<select id="supportFilterSelect" name="supportFilterSelect">
					<!--{foreach from=$moduleStructure key='key' item='val'}-->
					<option value="<!--{$val|escape}-->"><!--{$key|escape}--></option>
					<!--{/foreach}-->
				</select>
			</td>
			<td>
				<input type="text" id="supportFilterText" name="supportFilterText" value="" />
			</td>
			<td><button dojoType="Button" id="supportFilterButton" widgetId="supportFilterButton"><!--{t}-->Filter<!--{/t}--></button></td>
			<td><button dojoType="Button" id="supportBackButton" widgetId="supportBackButton"><!--{t}-->Back<!--{/t}--></button></td>
		</tr>
	</table>
</div>

<div class="tableContainer">
        <table dojoType="FilteringTable" id="supportDataHolder"
         widgetId="supportDataHolder" headClass="fixedHeader"
         tbodyClass="scrollContent" enableAlternateRows="true"
         valueField="module_class" border="0" multiple="false"
         rowAlternateClass="alternateRow" style="height: 100%;">
	<thead>
		<tr>
		<!--{foreach from=$moduleStructure key='key' item='val'}-->
		<th field="<!--{$val|escape}-->" dataType="String"><!--{$key|escape}--></th>
		<!--{/foreach}-->
		</tr>
	</thead>
	<tbody></tbody>
	</table>
</div>

