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
	dojo.require("dojo.widget.Editor2");
	dojo.require("dojo.widget.FilteringTable");
	dojo.require('dojo.widget.DropdownDatePicker');

	patientEmrAttachments = {
		currentItem: 0,
		itemsToPrint: [],
		createAnnotation: function ( ) {
			dojo.io.bind({
				method: 'POST',
				content: {
					param0: this.currentItem,
					param1: document.getElementById('emrAnnotation').value
					},
				url: '<!--{$relay}-->/org.freemedsoftware.module.Annotations.NewAnnotation',
				error: function() { },
				load: function( type, data, evt ) {
					if (data) {
						// Hide annotations box
						dojo.widget.byId('emrAnnotationDialog').hide();
						this.currentItem = 0;
						// Force reload
						patientLoadEmrAttachments();
						freemedMessage("<!--{t}-->Added annotation.<!--{/t}-->", "INFO");
					} else {
						alert('<!--{t}-->Failed to annotate record.<!--{/t}-->');
					}
				},
				mimetype: "text/json"
			});
		},
		showAnnotations: function ( id ) {
			dojo.widget.byId('emrSimpleDialog').show();
			dojo.widget.byId('emrSimpleDialogContent').setUrl( '<!--{$controller}-->/org.freemedsoftware.ui.annotation.show?id=' + id );
		},
		patientEmrAction: function ( action, id ) {
			// Extract from data store...
			var x = dojo.widget.byId('patientEmrAttachments').store.getDataByKey( id );
	
			switch ( action ) {
				case 'annotate':
				// Store locally
				this.currentItem = id;
				dojo.widget.byId('emrAnnotationDialog').show();
				document.getElementById('emrAnnotation').focus();
				break;

				case 'lock':
				if (!confirm("<!--{t}-->Are you sure you want to lock this record?<!--{/t}-->")) {
					return false;
				}
				dojo.io.bind({
					method: 'POST',
					content: {
						param0: x.oid
						},
					url: '<!--{$relay}-->/org.freemedsoftware.module.' + x.module_namespace + '.lock',
					error: function() { },
					load: function( type, data, evt ) {
						if (data) {
							// Force reload
							patientLoadEmrAttachments();
						} else {
							alert('<!--{t}-->Failed to lock record.<!--{/t}-->');
						}
					},
					mimetype: "text/json"
				});
				break;

				case 'modify':
				dojo.widget.byId('emrSimpleDialog').show();
				dojo.widget.byId('emrSimpleDialogContent').setUrl( '<!--{$controller}-->/org.freemedsoftware.module.' + x.module_namespace.toLowerCase() + '.form?id=' + x.oid );
				break;
	
				default:
				alert( "TODO: " + action + " " + id );
				break;
			}
		}, // end patientEmrAction
		emrDateFilter: function ( dt ) { 
			//return (dt > new Date('6/20/05') && dt < new Date('11/17/08'));
			return (dt >= new Date(dojo.widget.byId('emrRangeBegin').inputNode.value) && dt <= new Date(dojo.widget.byId('emrRangeEnd').inputNode.value));
		},
		printMultiple: function ( ) {
			var w = dojo.widget.byId('patientEmrAttachments');
			var val = w.getSelectedData();
			if ( val.length == 0 ) {
				alert("<!--{t}-->No EMR attachments were selected.<!--{/t}-->");
				return false;
			}
			this.itemsToPrint = [];
			for (i=0; i<val.length; i++) {
				this.itemsToPrint[i] = val[i].id;
				dojo.widget.byId('emrPrintDialog').show();
			}
		}
	};
	
	function patientLoadEmrAttachments ( ) {
		// Initial data load
		dojo.io.bind({
			method: 'POST',
			content: {
				param0: '<!--{$patient|escape}-->'

			},
			url: '<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.api.PatientInterface.EmrAttachmentsbyPatient',
			error: function() { },
			load: function( type, data, evt ) {
				if (typeof(data) == 'object') {
					for (i=0; i<data.length; i++) {	
						data[i]['actions'] = '';
						data[i]['actions'] += "<a onClick=\"patientEmrAction('print', " + data[i]['id'] + ");\"><img src=\"<!--{$htdocs}-->/images/summary_print.png\" border=\"0\" alt=\"<!--{t}-->Print Record<!--{/t}-->\" /></a>&nbsp;";
						if (data[i]['locked'] == 0) {
							// All unlocked actions go here:
							data[i]['actions'] += "<a onClick=\"patientEmrAttachments.patientEmrAction('lock', " + data[i]['id'] + ");\"><img src=\"<!--{$htdocs}-->/images/summary_lock.png\" border=\"0\" alt=\"<!--{t}-->Lock Record<!--{/t}-->\" /></a>&nbsp;";
							data[i]['actions'] += "<a onClick=\"patientEmrAttachments.patientEmrAction('modify', " + data[i]['id'] + ");\"><img src=\"<!--{$htdocs}-->/images/summary_modify.png\" border=\"0\" alt=\"<!--{t}-->Modify Record<!--{/t}-->\" /></a>&nbsp;";
						} else {
							// All locked stuff goes here:
							data[i]['actions'] += "<a href=\"\"><img src=\"<!--{$htdocs}-->/images/summary_locked.png\" border=\"0\" alt=\"<!--{t}-->Locked<!--{/t}--> /></a>&nbsp;";
						}
						if (data[i]['annotation'] != null) {
							data[i]['notes'] = "<a onClick=\"patientEmrAttachments.showAnnotations(" + data[i]['id'] + ");\"><img src=\"<!--{$htdocs}-->/images/annotation_icon.png\" border=\"0\" alt=\"<!--{t}-->Annotation<!--{/t}--> /></a>";
			
						}
						// Common things
						data[i]['actions'] += "<a onClick=\"patientEmrAttachments.patientEmrAction('annotate', " + data[i]['id'] + ");\"><img src=\"<!--{$htdocs}-->/images/annotate.png\" border=\"0\" alt=\"<!--{t}-->Annotate<!--{/t}-->\" /></a>&nbsp;";
					}
					dojo.widget.byId('patientEmrAttachments').store.setData( data );
				}
			},
			mimetype: "text/json"
		});
	}

	_container_.addOnLoad(patientLoadEmrAttachments);

</script>

<div>
<table width="100%" border="0">
<tr>
	<td><button dojoType="button" onClick="dojo.widget.byId('patientEmrAttachments').clearFilters();"><!--{t}-->Reset<!--{/t}--></button></td>
	<td><!--{t}-->Date Range:<!--{/t}-->
		<input dojoType="DropdownDatePicker" id="emrRangeBegin" />
		-
		<input dojoType="DropdownDatePicker" id="emrRangeEnd" />
	</td>
	<td align="right"><button dojoType="button" onClick="dojo.widget.byId('patientEmrAttachments').setFilter('date_mdy', patientEmrAttachments.emrDateFilter);"><!--{t}-->Apply<!--{/t}--></button></td>
	<td align="right"><button dojoType="button" onClick="patientEmrAttachments.printMultiple();"><!--{t}-->Print<!--{/t}--></button></td>
</tr>
</table>
</div>

<div class="tableContainer">
	<table dojoType="FilteringTable" id="patientEmrAttachments" widgetId="patientEmrAttachments" headClass="fixedHeader"
	 tbodyClass="scrollContent" enableAlternateRows="true" rowAlternateClass="alternateRow"
	 valueField="id" border="0" multiple="yes" style="height: 100%;">
	<thead>
		<tr>
			<th field="date_mdy" dataType="Date"><!--{t}-->Date<!--{/t}--></th>
			<th field="summary" dataType="String"><!--{t}-->Summary<!--{/t}--></th>
			<th field="type" dataType="String"><!--{t}-->Type<!--{/t}--></th>
			<th field="notes" dataType="Html"><!--{t}-->Notes<!--{/t}--></th>
			<th field="actions" dataType="Html"><!--{t}-->Actions<!--{/t}--></th>
		</tr>
	</thead>
	<tbody></tbody>
	</table>
</div>

<!-- Print dialog -->

<div dojoType="Dialog" style="display: none;" id="emrPrintDialog" widgetId="emrPrintDialog">
	<form>
	<table border="0">
		<tr>
			<td width="25"><input type="radio" id="printMethodPrinter" name="printMethod" value="printer" /></td>
			<td align="right"><label for="printMethodPrinter"><!--{t}-->Printer<!--{/t}--></label</td>
			<td align="left"></td>
		</tr>
		<tr>
			<td width="25"><input type="radio" id="printMethodFax" name="printMethod" value="fax" /></td>
			<td align="right"><label for="printMethodFax"><!--{t}-->Fax<!--{/t}--></label</td>
			<td align="left"><input type="text" name="faxNumber" id="faxNumber" onFocus="document.getElementById('printMethodFax').checked = true;" /></td>
		</tr>
		<tr>
			<td width="25"><input type="radio" id="printMethodBrowser" name="printMethod" value="browser" checked="checked" /></td>
			<td align="right"><label for="printMethodBrowser"><!--{t}-->Browser Based<!--{/t}--></label</td>
			<td align="left">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" align="center">
				<table border="0" align="center"><tr>
				<td align="right"><button dojoType="Button"><!--{t}-->Print<!--{/t}--></button></td>
				<td align="left"><button dojoType="Button" onClick="dojo.widget.byId('emrPrintDialog').hide();"><!--{t}-->Cancel<!--{/t}--></button></td>
				</tr></table>
			</td>
		</tr>
	</table>
	</form>
</div>

<!-- Annotation creation dialog -->

<div dojoType="Dialog" style="display: none;" id="emrAnnotationDialog" widgetId="emrAnnotationDialog">
	<form>
	<table border="0">
		<tr align="center">
			<textarea dojoType="Editor2" id="emrAnnotation" name="emrAnnotation" rows="5" cols="60" htmlEditing="true" useActiveX="true" shareToolbar="false" toolbarAlwaysVisible="true"></textarea>
		</tr>
		<tr>
			<td align="center">
				<table border="0" align="center"><tr>
				<td align="right"><button dojoType="Button" onClick="patientEmrAttachments.createAnnotation();"><!--{t}-->Annotate<!--{/t}--></button></td>
				<td align="left"><button dojoType="Button" onClick="dojo.widget.byId('emrAnnotationDialog').hide();"><!--{t}-->Cancel<!--{/t}--></button></td>
				</tr></table>
			</td>
		</tr>
	</table>
	</form>
</div>

<!-- All purpose, simple dialog -->

<div dojoType="Dialog" style="display: none;" id="emrSimpleDialog" widgetId="emrSimpleDialog">
	<div dojoType="ContentPane" id="emrSimpleDialogContent" cacheContent="false" executeScripts="true" adjustPaths="false" style="height: 100%; width: 100%;"></div>	
	<div align="center">
		<button dojoType="Button" onClick="dojo.widget.byId('emrSimpleDialog').hide();"><!--{t}-->Cancel<!--{/t}--></button>
	</div>
</div>

