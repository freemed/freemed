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
	dojo.require("dojo.widget.DropdownDatePicker");

	function loadUnfiledDocuments ( ) {
		// Initial data load
		dojo.io.bind({
			method: 'POST',
			content: { },
			url: '<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.module.UnfiledDocuments.GetAll',
			error: function() { },
			load: function( type, data, evt ) {
				dojo.widget.byId('unfiledDocuments').store.setData( data );
			},
			mimetype: "text/json"
		});
	}

	// Special scope variable because of ContentPane
	// see http://manual.dojotoolkit.org/WikiHome/DojoDotBook/Book30
	var o = {
		saveValue: 0,
		cancelDocument: function ( ) {
			// Hide form, unload djvu viewer
			document.getElementById('unfiledDocumentsFormDiv').style.display = 'none';
			dojo.widget.byId('unfiledDocumentViewPane').setUrl('<!--{$base_uri}-->/controller.php/<!--{$ui}-->/blank');

			// Unset all selections...
			dojo.widget.byId('unfiledDocuments').resetSelections();
			dojo.widget.byId('unfiledDocuments').renderSelections();
			this.saveValue = 0;
		},
		selectUnfiledDocument: function ( ) {
			var w = dojo.widget.byId('unfiledDocuments');
			var val = w.getSelectedData();
			if (val != 'undefined') {
				// Save the value
				this.saveValue = val.id;

				// Populate/display
				document.getElementById('unfiledDocumentsFormDiv').style.display = 'block';
				dojo.widget.byId('unfiledDocumentViewPane').setUrl('<!--{$base_uri}-->/controller.php/<!--{$ui}-->/org.freemedsoftware.widget.djvuviewer?MODE=widget&type=UnfiledDocuments&id=' + val.id);
				return true;
			}
		}
	};

	// Make sure we load this upon page load
	dojo.addOnLoad(loadUnfiledDocuments);

	// Handle in context loading for these widgets
	_container_.addOnLoad(function(){
		dojo.event.connect(dojo.widget.byId('unfiledDocuments'), "onSelect", o, "selectUnfiledDocument");
		dojo.event.connect(dojo.widget.byId('cancelButton'), "onClick", o, "cancelDocument");
	});
	_container_.addOnUnLoad(function(){
		dojo.event.disconnect(dojo.widget.byId('unfiledDocuments'), "onSelect", o, "selectUnfiledDocument");
		dojo.event.disconnect(dojo.widget.byId('cancelButton'), "onClick", o, "cancelDocument");
	});

</script>

<div dojoType="SplitContainer" orientation="horizontal" sizerWidth="5" activeSizing="0" layoutAlign="client" style="height: 100%;">

	<div dojoType="ContentPane" executeScripts="true" sizeMin="30" sizeShare="50" style="height: 100%;">

		<div class="tableContainer">
			<table dojoType="FilteringTable" id="unfiledDocuments" widgetId="unfiledDocuments" headClass="fixedHeader"
			 tbodyClass="scrollContent" enableAlternateRows="true" rowAlternateClass="alternateRow"
			 valueField="id" border="0" multiple="false" maxSelect="1">
			<thead>
				<tr>
					<th field="uffdate_mdy" dataType="Date"><!--{t}-->Date<!--{/t}--></th>
					<th field="ufffilename" dataType="String"><!--{t}-->Filename<!--{/t}--></th>
				</tr>
			</thead>
			<tbody></tbody>
			</table>
		</div>

		<div id="unfiledDocumentsFormDiv" style="display: none;">
		<table border="0">
			<tr>
				<td><!--{t}-->Date<!--{/t}--></td>
				<td>
					<input dojoType="DropdownDatePicker" value="today" id="uffdate_widget" widgetId="uffdate_widget" onValueChanged="document.getElementById('uffdate').value = dojo.widget.byId('uffdate_widget').inputNode.value;" />
					<input type="hidden" id="uffdate" date="uffdate" />
				</td>
			</tr>
			<tr>
				<td><!--{t}-->Patient<!--{/t}--></td>
				<td><!--{include file="org.freemedsoftware.widget.patientpicklist.tpl" varname="uffpatient"}--></td>
			</tr>
			<tr>
				<td><!--{t}-->Provider<!--{/t}--></td>
				<td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="ProviderModule" varname="uffprovider"}--></td>
			</tr>
			<tr>
				<td><!--{t}--><!--{/t}--></td>
				<td></td>
			</tr>
			<tr>
				<td><!--{t}--><!--{/t}--></td>
				<td></td>
			</tr>
		</table>

		<div align="center">
		<table border="0">
			<tr>
				<td><button dojoType="Button"><!--{t}-->Send to Provider<!--{/t}--></button></td>
				<td><button dojoType="Button"><!--{t}-->Send to Provider<!--{/t}--><br/><!--{t}-->(w/o first page)<!--{/t}--></button></td>
				<td><button dojoType="Button"><!--{t}-->File Directly<!--{/t}--></button></td>
				<td><button dojoType="Button"><!--{t}-->File Directly<!--{/t}--><br/><!--{t}-->(w/o first page)<!--{/t}--></button></td>
				<td><button dojoType="Button"><!--{t}-->Split Batch<!--{/t}--></button></td>
				<td><button dojoType="Button" id="cancelButton"><!--{t}-->Cancel<!--{/t}--></button></td>
				<td><button dojoType="Button"><!--{t}-->Delete Document<!--{/t}--></button></td>
			</tr>
		</table>
		</div>

		</div>

	</div>

	<div dojoType="ContentPane" executeScripts="true" sizeMin="30" sizeShare="50" id="unfiledDocumentViewPane">
	</div>
</div>

