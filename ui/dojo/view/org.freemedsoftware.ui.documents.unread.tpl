<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2008 FreeMED Software Foundation
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

	// Special scope variable because of ContentPane
	// see http://manual.dojotoolkit.org/WikiHome/DojoDotBook/Book30
	var o = {
		saveValue: 0,
		loadUnreadDocuments: function ( ) {
			// Initial data load
			dojo.io.bind({
				method: 'POST',
				content: { },
				url: '<!--{$relay}-->/org.freemedsoftware.module.UnreadDocuments.GetAll',
				error: function() { },
				load: function( type, data, evt ) {
					dojo.widget.byId('unreadDocuments').store.setData( data );
				},
				mimetype: "text/json"
			});
		},
		cancelDocument: function ( ) {
			// Hide form, unload djvu viewer
			document.getElementById('unreadDocumentsFormDiv').style.display = 'none';
			dojo.widget.byId('unreadDocumentViewPane').setUrl('<!--{$controller}-->/blank');

			// Unset all selections...
			dojo.widget.byId('unreadDocuments').resetSelections();
			dojo.widget.byId('unreadDocuments').renderSelections();
			o.saveValue = 0;
		},
		fileUnreadDocument: function ( ) {
			dojo.io.bind({
				method: 'POST',
				content: {
					param0: o.saveValue
				},
				url: '<!--{$relay}-->/org.freemedsoftware.module.UnreadDocuments.ReviewIntoRecord',
				load: function( type, data, evt ) {
					freemedMessage( "<!--{t|escape:'javascript'}-->Filed document in patient record.<!--{/t}-->", "INFO" );
					o.resetForm();
				},
				mimetype: 'text/json'	
			});
		},
		wrongUnreadDocument: function ( ) {
			dojo.widget.byId('sendToProviderDialog').show();
		},
		sendToAnotherProvider: function ( ) {
			dojo.io.bind({
				method: "POST",
				content: {
					param0: o.saveValue,
					param1: parseInt( document.getElementById('sendToProvider').value )
				},
				url: "<!--{$relay}-->/org.freemedsoftware.module.SendToAnotherProvider",
				load: function ( type, data, evt ) {
					freemedMessage( "<!--{t|escape:'javascript'}-->Document moved to another provider.<!--{/t}-->", 'INFO' );
					dojo.widget.byId('sendToProviderDialog').hide();
					o.resetForm();
				},
				mimetype: "text/json"
			});
		},
		deleteDocument: function ( ) {
			var x = confirm("<!--{t|escape:'javascript'}-->Are you sure you want to permanently remove this document?<!--{/t}-->");
			if (x) {
				dojo.io.bind({
					method: 'POST',
					url: '<!--{$relay}-->/org.freemedsoftware.module.UnreadDocuments.del',
					content: {
						param0: o.saveValue
					},
					error: function( type, data, event ) {
						alert("<!--{t|escape:'javascript'}-->The system was unable to complete your request at this time.<!--{/t}-->");
					},
					load: function( type, data, event ) {
						o.resetForm();
					},
					mimetype: "text/json"
				});
			}
		},
		resetForm: function ( ) {
			o.loadUnreadDocuments();
			o.saveValue = 0;

			// Hide form, unload djvu viewer
			document.getElementById('unreadDocumentsFormDiv').style.display = 'none';
			dojo.widget.byId('unreadDocumentViewPane').setUrl('<!--{$controller}-->/blank');
		},
		selectUnreadDocument: function ( ) {
			var w = dojo.widget.byId('unreadDocuments');
			var val = w.getSelectedData();
			if (val != 'undefined') {
				// Save the value
				o.saveValue = val.id;

				// Populate/display
				document.getElementById('unreadDocumentsFormDiv').style.display = 'block';
				dojo.widget.byId('unreadDocumentViewPane').setUrl('<!--{$controller}-->/org.freemedsoftware.widget.djvuviewer?type=UnreadDocuments&id=' + val.id);
				document.getElementById('unread_date').innerHTML = val.urfdate_mdy;
				document.getElementById('unread_patient').innerHTML = val.patient;
				document.getElementById('unread_category').innerHTML = val.category;
				document.getElementById('unread_note').innerHTML = val.urfnote;
				return true;
			}
		}
	};

	// Make sure we load this upon page load
	_container_.addOnLoad(o.loadUnreadDocuments);

	// Handle in context loading for these widgets
	_container_.addOnLoad(function(){
		dojo.event.connect(dojo.widget.byId('unreadDocuments'), "onSelect", o, "selectUnreadDocument");
		dojo.event.connect(dojo.widget.byId('fileUnreadDocumentButton'), "onClick", o, "fileUnreadDocument");
		dojo.event.connect(dojo.widget.byId('wrongUnreadDocumentButton'), "onClick", o, "wrongUnreadDocument");
		dojo.event.connect(dojo.widget.byId('sendToProviderButton'), "onClick", o, "sendToAnotherProvider");
		dojo.event.connect(dojo.widget.byId('cancelButton'), "onClick", o, "cancelDocument");
		dojo.event.connect(dojo.widget.byId('deleteButton'), "onClick", o, "deleteDocument");
	});
	_container_.addOnUnload(function(){
		dojo.event.disconnect(dojo.widget.byId('unreadDocuments'), "onSelect", o, "selectUnreadDocument");
		dojo.event.disconnect(dojo.widget.byId('fileUnreadDocumentButton'), "onClick", o, "fileUnreadDocument");
		dojo.event.disconnect(dojo.widget.byId('wrongUnreadDocumentButton'), "onClick", o, "wrongUnreadDocument");
		dojo.event.disconnect(dojo.widget.byId('sendToProviderButton'), "onClick", o, "sendToAnotherProvider");
		dojo.event.disconnect(dojo.widget.byId('cancelButton'), "onClick", o, "cancelDocument");
		dojo.event.disconnect(dojo.widget.byId('deleteButton'), "onClick", o, "deleteDocument");
	});

</script>

<h3><!--{t}-->Unread Documents<!--{/t}--></h3>

<div dojoType="SplitContainer" orientation="horizontal" sizerWidth="5" activeSizing="0" layoutAlign="client" style="height: 100%;">

	<div dojoType="ContentPane" executeScripts="true" sizeMin="30" sizeShare="50" style="height: 100%;">

		<div class="tableContainer">
			<table dojoType="FilteringTable" id="unreadDocuments" widgetId="unreadDocuments" headClass="fixedHeader"
			 tbodyClass="scrollContent" enableAlternateRows="true" rowAlternateClass="alternateRow"
			 valueField="id" border="0" multiple="false" maxSelect="1">
			<thead>
				<tr>
					<th field="urfdate_mdy" dataType="Date"><!--{t}-->Date<!--{/t}--></th>
					<th field="patient" dataType="String"><!--{t}-->Patient<!--{/t}--></th>
					<th field="urfnote" dataType="String"><!--{t}-->Note<!--{/t}--></th>
				</tr>
			</thead>
			<tbody></tbody>
			</table>
		</div>

		<div id="unreadDocumentsFormDiv" style="display: none;">
		<table border="0">
			<tr>
				<td><!--{t}-->Date<!--{/t}--></td>
				<td><span id="unread_date"></span></td>
			</tr>
			<tr>
				<td><!--{t}-->Patient<!--{/t}--></td>
				<td><span id="unread_patient"></span></td>
			</tr>
			<tr>
				<td><!--{t}-->Category<!--{/t}--></td>
				<td><span id="unread_category"></span></td>
			</tr>
			<tr>
				<td><!--{t}-->Note<!--{/t}--></td>
				<td><span id="unread_note"></span></td>
			</tr>
			<tr>
				<td colspan="2">
					<i><!--{t}-->By clicking on the 'Sign' button below, I agree that I am the physician in question and have reviewed this document or facsimile transmission.<!--{/t}--></i>
				</td>

			</tr>
		</table>

		<div align="center" id="unreadButtons">
		<table border="0">
			<tr>
				<td align="right"><button dojoType="Button" id="fileUnreadDocumentButton" widgetId="fileUnreadDocumentButton"><!--{t}-->Sign<!--{/t}--></button></td>
				<td align="left"><button dojoType="Button" id="cancelButton"><!--{t}-->Cancel<!--{/t}--></button></td>
			</tr>
			<tr>
				<td align="right"><button dojoType="Button" id="wrongUnreadDocumentButton" widgetId="wrongUnreadDocumentButton"><!--{t}-->Send to<!--{/t}--><br/><!--{t}-->Another Provider<!--{/t}--></button></td>
				<td align="left"><button dojoType="Button" id="modifyDirectlyNoCoverButton" widgetId="modifyDirectlyNoCoverButton"><!--{t}-->File Directly<!--{/t}--><br/><!--{t}-->(w/o first page)<!--{/t}--></button></td>
			</tr>
		</table>
		</div>

		</div>

	</div>

	<div dojoType="ContentPane" executeScripts="true" sizeMin="30" sizeShare="50" id="unreadDocumentViewPane" style="overflow-x: scroll; overflow-y: scroll; max-height: 92%; max-width: 92%;">
	</div>
</div>

<!--{* Hidden dialog *}-->

<div dojoType="Dialog" style="display: none;" id="sendToProviderDialog" widgetId="sendToProviderDialog">
	<h3><!--{t}-->Send to Another Provider<!--{/t}--></h3>

	<p><!--{t}-->Please choose another provider who should receive this document.<!--{/t}--></p>

	<p>
		<!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="ProviderModule" varname="sendToProvider"}-->
	</p>

	<table border="0">
		<tr>
			<td align="right">
				<button dojoType="Button" id="sendToProviderButton" widgetId="sendToProviderButton">
					<div><img src="<!--{$htdocs}-->/images/teak/check_go.16x16.png" border="0" /> <!--{t}-->Send<!--{/t}--></div>
				</button>
			</td>

			<td align="left">
				<button dojoType="Button" id="cancelSendToProviderButton" onClick="dojo.widget.byId('sendToProviderDialog').hide();">
					<div><img src="<!--{$htdocs}-->/images/teak/x_stop.16x16.png" border="0" /> <!--{t}-->Cancel<!--{/t}--></div>
				</button>
			</td>
		</tr>
	</table>
</div>
