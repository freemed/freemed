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

<script language="javascript">
	var supportData = {
		onAddClick: function ( ) {
			freemedLoad( 'org.freemedsoftware.ui.user.form' );
		},
		onModifyClick: function ( ) {
			var v;
			try {
				v = dojo.widget.byId( 'supportDataHolder' ).getSelectedData().id;
			} catch (e) {
				alert("<!--{t|escape:'javascript'}-->Please select a record.<!--{/t}-->");
				return false;
			}
			freemedLoad( 'org.freemedsoftware.ui.user.form?id=' + v );
		},
		onFilterClick: function ( ) {
			supportData.loadData();
		},
		onBackClick: function ( ) {
			freemedLoad( 'org.freemedsoftware.ui.supportdata' );
		},
		onDeleteClick: function ( ) {
			var v;
			try {
				v = dojo.widget.byId( 'supportDataHolder' ).getSelectedData().id;
			} catch (e) {
				alert("<!--{t|escape:'javascript'}-->Please select a record.<!--{/t}-->");
				return false;
			}
			var x = confirm("<!--{t|escape:'javascript'}-->Are you sure you want to permanently remove this user?<!--{/t}-->");
			if (x) {
				dojo.io.bind({
					method: 'POST',
					url: '<!--{$relay}-->/org.freemedsoftware.api.UserInterface.del',
					content: {
						param0: v
					},
					error: function( type, data, event ) {
						alert("<!--{t|escape:'javascript'}-->The system was unable to complete your request at this time.<!--{/t}-->");
					},
					load: function( type, data, event ) {
						supportData.loadData();
					},
					mimetype: "text/json"
				});
			}
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
				url: '<!--{$relay}-->/org.freemedsoftware.api.UserInterface.GetRecords',
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
		dojo.event.connect( dojo.widget.byId('supportDeleteButton'), 'onClick', supportData, 'onDeleteClick' );
		dojo.event.connect( dojo.widget.byId('supportModifyButton'), 'onClick', supportData, 'onModifyClick' );
		dojo.event.connect( dojo.widget.byId('supportBackButton'), 'onClick', supportData, 'onBackClick' );
		document.getElementById( 'supportFilterSelect' ).onchange = supportData.loadData;
		document.getElementById( 'supportFilterText' ).onkeyup = supportData.loadData;
	});

	_container_.addOnUnload(function(){
		dojo.event.disconnect( dojo.widget.byId('supportAddButton'), 'onClick', supportData, 'onAddClick' );
		dojo.event.disconnect( dojo.widget.byId('supportDeleteButton'), 'onClick', supportData, 'onDeleteClick' );
		dojo.event.disconnect( dojo.widget.byId('supportModifyButton'), 'onClick', supportData, 'onModifyClick' );
		dojo.event.disconnect( dojo.widget.byId('supportBackButton'), 'onClick', supportData, 'onBackClick' );
	});

</script>

<h3><!--{t}-->Users<!--{/t}--></h3>

<div>
	<table border="0">
		<tr>
			<td><button dojoType="Button" id="supportAddButton" widgetId="supportAddButton"><!--{t}-->Add<!--{/t}--></button></td>
			<td><button dojoType="Button" id="supportModifyButton" widgetId="supportModifyButton"><!--{t}-->Modify<!--{/t}--></button></td>
			<td>
				<select id="supportFilterSelect" name="supportFilterSelect">
					<option value="username"><!--{t}-->Name<!--{/t}--></option>
					<option value="userdescrip"><!--{t}-->Description<!--{/t}--></option>
				</select>
			</td>
			<td>
				<input type="text" id="supportFilterText" name="supportFilterText" value="" />
			</td>
			<td><button dojoType="Button" id="supportDeleteButton" widgetId="supportDeleteButton"><!--{t}-->Delete<!--{/t}--></button></td>
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
		<th field="username" dataType="String"><!--{t}-->Name<!--{/t}--></th>
		<th field="userdescrip" dataType="String"><!--{t}-->Description<!--{/t}--></th>
		</tr>
	</thead>
	<tbody></tbody>
	</table>
</div>

