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
		populateList: function () {
			dojo.io.bind({
				method: 'POST',
				url: '<!--{$relay}-->/org.freemedsoftware.api.TableMaintenance.GetModules',
				content: {
					param0: 'SupportModule'
				},
				load: function ( type, data, event ) {
					dojo.widget.byId('supportDataSelector').store.setData( data );
				},
				mimetype: "text/json"
			});
		},
		moduleData: null,
		createMaintenance: function ( ) {
			var module = dojo.widget.byId('supportDataSelector').getSelectedData().module_class;
			freemedLoad( 'org.freemedsoftware.ui.supportdata.list?module=' + module );
		}
	};

	_container_.addOnLoad(function(){
		supportData.populateList();
		dojo.event.connect( dojo.widget.byId('supportDataSelector'), 'onSelect', supportData, 'createMaintenance' );
	});

	_container_.addOnUnload(function(){
		dojo.event.disconnect( dojo.widget.byId('supportDataSelector'), 'onSelect', supportData, 'createMaintenance' );
	});

</script>

<h3><!--{t}-->Support Data<!--{/t}--></h3>

<div class="tableContainer">

	<table dojoType="FilteringTable" id="supportDataSelector"
	 widgetId="supportDataSelector" headClass="fixedHeader"
	 tbodyClass="scrollContent" enableAlternateRows="true"
	 valueField="module_class" border="0" multiple="false"
	 maxSelect="1" rowAlternateClass="alternateRow" sizerWidth="2"
	 style="height: 100%;">
	<thead>
		<tr>
			<th field="module_name" dataType="String"><!--{t}-->Module<!--{/t}--></th>
			<th field="module_version" dataType="String"><!--{t}-->Version<!--{/t}--></th>
		</tr>
	</thead>
	<tbody style="height: 75ex;"></tbody>
	</table>

</div>

