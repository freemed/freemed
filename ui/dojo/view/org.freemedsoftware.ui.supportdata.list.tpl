<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2010 FreeMED Software Foundation
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
	var supportData = {
		onAddClick: function ( ) {
			freemedLoad( 'org.freemedsoftware.module.<!--{$module|lower|escape}-->.form?module=<!--{$module|escape}-->' );
		},
		onModifyClick: function ( ) {
			var v;
			try {
				v = dojo.widget.byId( 'supportDataHolder' ).getSelectedData().id;
			} catch (e) {
				alert("<!--{t|escape:'javascript'}-->Please select a record.<!--{/t}-->");
				return false;
			}
			freemedLoad( 'org.freemedsoftware.module.<!--{$module|lower|escape}-->.form?module=<!--{$module|escape}-->&id=' + v );
		},
		onDeleteClick: function ( ) {
			var v;
			try {
				v = dojo.widget.byId( 'supportDataHolder' ).getSelectedData().id;
			} catch (e) {
				alert("<!--{t|escape:'javascript'}-->Please select a record.<!--{/t}-->");
				return false;
			}
			if ( confirm("<!--{t|escape:'javascript'}-->Are you sure you want to delete this record?<!--{/t}-->") ) {
				dojo.io.bind({
					method: 'POST',
					content: {
						param0: v
					},
					url: '<!--{$relay}-->/org.freemedsoftware.module.<!--{$module}-->.del',
					load: function ( type, data, evt ) {
						freemedMessage( "<!--{t|escape:'javascript'}-->Record successfully removed.<!--{/t}-->", 'INFO' );
						// Refresh data display
						supportData.loadData();
					},
					mimetype: 'text/json'
				});
			}
		},
		onFilterClick: function ( ) {
			supportData.loadData();
		},
		onBackClick: function ( ) {
			freemedLoad( 'org.freemedsoftware.ui.supportdata' );
		},
		moduleData: null,
		currentFilter: 0,
		loadData: function ( ) {
			// Load initial data set
			var w = dojo.widget.byId('supportDataHolder');
			var c; var currentFilter;

			// See if we're loaded
			if ( document.getElementById( 'supportFilterText' ).value.length ) {
				var x = document.getElementById( 'supportFilterSelect' ).value + ',' + document.getElementById( 'supportFilterText' ).value;
				cFilter = x.length;
			} else {
				cFilter = 0;
			}

			supportData.currentFilter = cFilter;

			dojo.io.bind({
				method: 'POST',
				content: {
					param0: 100,
					param1: document.getElementById( 'supportFilterSelect' ).value ? document.getElementById( 'supportFilterSelect' ).value : '',
					param2: document.getElementById( 'supportFilterText' ).value
				},
				url: '<!--{$relay}-->/org.freemedsoftware.module.<!--{$module|escape}-->.GetRecords',
				load: function ( type, data, event ) {
					w.store.setData( data );
					try {
						var x = dojo.widget.byId( 'freemedContent' );
						var node = x.containerNode || x.domNode;
						var h = parseInt( node.offsetHeight ) - ( document.getElementById( 'supportDataHeader' ).style.height + document.getElementById( 'supportDataControls' ).style.height + 100 );
						document.getElementById( 'supportDataHolderBody' ).style.height = h + 'px';
					} catch ( e ) { }
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
		dojo.event.connect( dojo.widget.byId('supportBackButton'), 'onClick', supportData, 'onBackClick' );
		document.getElementById( 'supportFilterSelect' ).onchange = supportData.loadData;
		document.getElementById( 'supportFilterText' ).onkeyup = supportData.loadData;
	});

	_container_.addOnUnload(function(){
		dojo.event.disconnect( dojo.widget.byId('supportAddButton'), 'onClick', supportData, 'onAddClick' );
		dojo.event.disconnect( dojo.widget.byId('supportModifyButton'), 'onClick', supportData, 'onModifyClick' );
		dojo.event.disconnect( dojo.widget.byId('supportDeleteButton'), 'onClick', supportData, 'onDeleteClick' );
		dojo.event.disconnect( dojo.widget.byId('supportBackButton'), 'onClick', supportData, 'onBackClick' );
	});

</script>

<h3 id="supportDataHeader"><!--{t}-->Support Data<!--{/t}-->: <!--{$moduleName}--></h3>

<div id="supportDataControls">
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
			<td><button dojoType="Button" id="supportBackButton" widgetId="supportBackButton"><!--{t}-->Back<!--{/t}--></button></td>
		</tr>
	</table>
</div>

<div class="tableContainer">
        <table dojoType="FilteringTable" id="supportDataHolder"
         widgetId="supportDataHolder" headClass="fixedHeader"
         tbodyClass="scrollContent" enableAlternateRows="true"
         valueField="id" border="0" multiple="false"
         rowAlternateClass="alternateRow" style="height: 100%;">
	<thead>
		<tr>
		<!--{foreach from=$moduleStructure key='key' item='val'}-->
		<th field="<!--{$val|escape}-->" dataType="String"><!--{$key|escape}--></th>
		<!--{/foreach}-->
		</tr>
	</thead>
	<tbody id="supportDataHolderBody"></tbody>
	</table>
</div>

