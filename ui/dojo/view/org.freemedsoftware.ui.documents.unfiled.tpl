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

	// see http://manual.dojotoolkit.org/WikiHome/DojoDotBook/Book30
	var o = {
		// Special scope variable because of ContentPane
		saveValue: 0,
		loadUnfiledDocuments: function ( ) {
			// Initial data load
			dojo.io.bind({
				method: 'POST',
				content: { },
				url: '<!--{$relay}-->/org.freemedsoftware.module.UnfiledDocuments.GetAll',
				error: function() { },
				load: function( type, data, evt ) {
					dojo.widget.byId('unfiledDocuments').store.setData( data );
				},
				mimetype: "text/json"
			});
		},
		cancelDocument: function ( ) {
			// Hide form, unload djvu viewer
			document.getElementById('unfiledDocumentsFormDiv').style.display = 'none';
			dojo.widget.byId('unfiledDocumentViewPane').setUrl('<!--{$controller}-->/blank');

			// Unset all selections...
			dojo.widget.byId('unfiledDocuments').resetSelections();
			dojo.widget.byId('unfiledDocuments').renderSelections();
			o.saveValue = 0;
		},
		deleteDocument: function ( ) {
			var x = confirm("<!--{t}-->Are you sure you want to permanently remove this document?<!--{/t}-->");
			if (x) {
				dojo.io.bind({
					method: 'POST',
					url: '<!--{$relay}-->/org.freemedsoftware.module.UnfiledDocuments.del',
					content: {
						param0: o.saveValue
					},
					load: function( type, data, event ) {
						if (data) {
							freemedMessage( "<!--{t}-->Document removed successfully.<!--{/t}-->", "INFO" );
						}
						o.resetForm();
					},
					mimetype: "text/json"
				});
			}
		},
		modifyDocument: function ( review, dropfirst ) {
			var p = {
				id: o.saveValue,
				date: document.getElementById('uffdate').value,
				category: document.getElementById('uffcategory').value,
				patient: document.getElementById('uffpatient').value,
				physician: document.getElementById('uffprovider').value,
				note: document.getElementById('uffnote').value,
				withoutfirstpage: dropfirst ? 1 : 0,
				filedirectly: review ? 0 : 1,
				flip: document.getElementById('uffflip').checked ? 1 : 0
			};

			// Some validation
			var messages = '';
			if ( ! p.date ) { messages += "<!--{t}-->No date has been selected.<!--{/t}-->\n"; }
			if ( p.category == 0 ) { messages += "<!--{t}-->No category has been chosen.<!--{/t}-->\n"; }
			if ( p.patient == 0 ) { messages += "<!--{t}-->No patient has been selected.<!--{/t}-->\n"; }
			if ( p.physician == 0 ) { messages += "<!--{t}-->No provider has been selected.<!--{/t}-->\n"; }
			if ( ! p.note ) { messages += "<!--{t}-->No note has been entered.<!--{/t}-->\n"; }
			if ( messages != '' ) {
				alert( messages );
				return false;
			}

			dojo.io.bind({
				method: 'POST',
				url: '<!--{$relay}-->/org.freemedsoftware.module.UnfiledDocuments.mod',
				content: {
					param0: p
				},
				load: function( type, data, event ) {
					freemedMessage( "<!--{t}-->Document handled successfully.<!--{/t}-->", "INFO" );
					o.resetForm();
				},
				mimetype: "text/json"
			});
		},
		// specialty button actions here:
		modifyToProvider: function ( ) { o.modifyDocument( true, false ); },
		modifyToProviderNoCover: function ( ) { o.modifyDocument( true, true ); },
		modifyDirectly: function ( ) { o.modifyDocument( false, false ); },
		modifyDirectlyNoCover: function ( ) { o.modifyDocument( false, true ); },
		resetForm: function ( ) {
			o.loadUnfiledDocuments();
			o.saveValue = 0;

			// Hide form, unload djvu viewer
			document.getElementById('unfiledDocumentsFormDiv').style.display = 'none';
			dojo.widget.byId('unfiledDocumentViewPane').setUrl('<!--{$controller}-->/blank');
		},
		selectUnfiledDocument: function ( ) {
			var w = dojo.widget.byId('unfiledDocuments');
			var val = w.getSelectedData();
			if (val != 'undefined') {
				// Save the value
				o.saveValue = val.id;

				// Populate/display
				document.getElementById('unfiledDocumentsFormDiv').style.display = 'block';
				dojo.widget.byId('unfiledDocumentViewPane').setUrl('<!--{$controller}-->/org.freemedsoftware.widget.djvuviewer?MODE=widget&type=UnfiledDocuments&id=' + val.id);
				return true;
			}
		}
	};

	// Make sure we load this upon page load
	_container_.addOnLoad(o.loadUnfiledDocuments);

	// Handle in context loading for these widgets
	_container_.addOnLoad(function(){
		dojo.event.connect(dojo.widget.byId('unfiledDocuments'), "onSelect", o, "selectUnfiledDocument");
		dojo.event.connect(dojo.widget.byId('cancelButton'), "onClick", o, "cancelDocument");
		dojo.event.connect(dojo.widget.byId('deleteButton'), "onClick", o, "deleteDocument");
		dojo.event.connect(dojo.widget.byId('modifyToProviderButton'), "onClick", o, "modifyToProvider");
		dojo.event.connect(dojo.widget.byId('modifyToProviderNoCoverButton'), "onClick", o, "modifyToProviderNoCover");
		dojo.event.connect(dojo.widget.byId('modifyDirectlyButton'), "onClick", o, "modifyDirectly");
		dojo.event.connect(dojo.widget.byId('modifyDirectlyNoCoverButton'), "onClick", o, "modifyDirectlyNoCover");
	});
	_container_.addOnUnload(function(){
		dojo.event.disconnect(dojo.widget.byId('unfiledDocuments'), "onSelect", o, "selectUnfiledDocument");
		dojo.event.disconnect(dojo.widget.byId('cancelButton'), "onClick", o, "cancelDocument");
		dojo.event.disconnect(dojo.widget.byId('deleteButton'), "onClick", o, "deleteDocument");
		dojo.event.disconnect(dojo.widget.byId('modifyToProviderButton'), "onClick", o, "modifyToProvider");
		dojo.event.disconnect(dojo.widget.byId('modifyToProviderNoCoverButton'), "onClick", o, "modifyToProviderNoCover");
		dojo.event.disconnect(dojo.widget.byId('modifyDirectlyButton'), "onClick", o, "modifyDirectly");
		dojo.event.disconnect(dojo.widget.byId('modifyDirectlyNoCoverButton'), "onClick", o, "modifyDirectlyNoCover");
	});

</script>

<h3><!--{t}-->Unfiled Documents<!--{/t}--></h3>

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
				<td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="ProviderModule" varname="uffprovider" methodName="internalPicklist" defaultValue=$SESSION.authdata.user_record.userrealphy}--></td>
			</tr>
			<tr>
				<td><!--{t}-->Category<!--{/t}--></td>
				<td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="DocumentCategory" varname="uffcategory"}--></td>
			</tr>
			<tr>
				<td><!--{t}-->Note<!--{/t}--></td>
				<td><input type="text" id="uffnote" name="uffnote" value="" /></td>
			</tr>
			<tr>
				<td><label for="uffflip"><!--{t}-->Flip Document?<!--{/t}--></label></td>
				<td><input type="checkbox" id="uffflip" name="uffflip" value="1" /></td>
			</tr>
			<tr>
				<td><!--{t}--><!--{/t}--></td>
				<td></td>
			</tr>
		</table>

		<div align="center">
		<table border="0">
			<tr>
				<td><button dojoType="Button" id="modifyToProviderButton" widgetId="modifyToProviderButton"><!--{t}-->Send to Provider<!--{/t}--></button></td>
				<td><button dojoType="Button" id="modifyToProviderNoCoverButton" widgetId="modifyToProviderNoCoverButton"><!--{t}-->Send to Provider<!--{/t}--><br/><!--{t}-->(w/o first page)<!--{/t}--></button></td>
				<td><button dojoType="Button" id="modifyDirectlyButton" widgetId="modifyDirectlyButton"><!--{t}-->File Directly<!--{/t}--></button></td>
				<td><button dojoType="Button" id="modifyDirectlyNoCoverButton" widgetId="modifyDirectlyNoCoverButton"><!--{t}-->File Directly<!--{/t}--><br/><!--{t}-->(w/o first page)<!--{/t}--></button></td>
			</tr><tr>
				<td><button dojoType="Button"><!--{t}-->Split Batch<!--{/t}--></button></td>
				<td><button dojoType="Button" id="cancelButton"><!--{t}-->Cancel<!--{/t}--></button></td>
				<td><button dojoType="Button" id="deleteButton"><!--{t}-->Delete Document<!--{/t}--></button></td>
			</tr>
		</table>
		</div>

		</div>

	</div>

	<div dojoType="ContentPane" executeScripts="true" sizeMin="30" sizeShare="50" id="unfiledDocumentViewPane" style="overflow-x: scroll; overflow-y: scroll; max-height: 92%; max-width: 92%;">
	</div>
</div>

