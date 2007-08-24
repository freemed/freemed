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
	dojo.require("dojo.widget.RichText");
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
						freemedMessage("<!--{t}-->Added annotation.<!--{/t}-->", "INFO");
						// Force reload
						freemedPatientContentLoad( 'org.freemedsoftware.ui.patient.overview.default?patient=<!--{$patient}-->' );
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
			// Store locally
			patientEmrAttachments.currentItem = id;
	
			switch ( action ) {
				case 'annotate':
				dojo.widget.byId('emrAnnotationDialog').show();
				document.getElementById('emrAnnotation').focus();
				return true;
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
							patientEmrAttachments.initialLoad();
						} else {
							alert('<!--{t}-->Failed to lock record.<!--{/t}-->');
						}
					},
					mimetype: "text/json"
				});
				break;

				case 'modify':
				freemedPatientContentLoad( '<!--{$controller}-->/org.freemedsoftware.module.' + x.module_namespace.toLowerCase() + '.form?id=' + x.oid + '&patient=<!--{$patient|escape}-->' );
				break;
	
				case 'print':
				patientEmrAttachments.printMultiple();
				break;
	
				case 'view':
				freemedPatientContentLoad( '<!--{$controller}-->/org.freemedsoftware.module.' + x.module_namespace.toLowerCase() + '.view?id=' + x.oid + '&patient=<!--{$patient|escape}-->' );
				break;
	
				default:
				alert( "TODO: " + action + " " + id );
				break;
			}
		}, // end patientEmrAction
		setFilters: function ( ) {
			dojo.widget.byId('patientEmrAttachments').setFilter('date_mdy', this.emrDateFilter);
			dojo.widget.byId('patientEmrAttachments').setFilter('type', this.emrModuleFilter);
			return true;
		},
		resetFilters: function ( ) {
			dojo.widget.byId( 'emrSection' ).setValue( '' );
			dojo.widget.byId( 'emrSection' ).setLabel( '' );
			dojo.widget.byId( 'patientEmrAttachments' ).clearFilters();
			return true;
		},
		emrDateFilter: function ( dt ) { 
			// If not set, return true by default
			if (dojo.widget.byId('emrRangeBegin').inputNode.value.length < 4) { return true; }
			if (dojo.widget.byId('emrRangeEnd').inputNode.value.length < 4) { return true; }
			var dtB = dojo.widget.byId( 'emrRangeBegin' ).getDate();
			var dtE = dojo.widget.byId( 'emrRangeEnd' ).getDate();
			return ( dt >= dtB && dt <= dtE );
		},
		emrModuleFilter: function ( m ) {
			// If not set, return true by default
			var v = dojo.widget.byId( 'emrSection' ).getValue();
			var l = dojo.widget.byId( 'emrSection' ).getLabel();
			if ( v.length < 5 ) { return true; }
			//dojo.debug ( m + ' == ' + dojo.widget.byId('emrSection').getLabel() );
			return ( m == l );
		},
		printMultiple: function ( ) {
			if ( patientEmrAttachments.currentItem ) {
				this.itemsToPrint = [];
				this.itemsToPrint[0] = patientEmrAttachments.currentItem;
				dojo.widget.byId('emrPrintDialog').show();
				return false;
			}
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
		},
		OnPrintSingle: function ( ) {
		},
		OnPrint: function ( ) {
			var url;
			if (patientEmrAttachments.itemsToPrint.length == 1) {
				var x = dojo.widget.byId('patientEmrAttachments').store.getDataByKey( patientEmrAttachments.currentItem );
				url = "<!--{$relay}-->/org.freemedsoftware.module." + x.module_namespace + ".RenderSinglePDF?param0=" + encodeURIComponent( x.oid );
			} else {
				url = "<!--{$relay}-->/org.freemedsoftware.api.ModuleInterface.PrintMultiple?param0=" + encodeURIComponent( dojo.json.serialize( patientEmrAttachments.itemsToPrint ) );
			}

			// Hide beforehand so it actually gets done
			dojo.widget.byId('emrPrintDialog').hide();

			if ( document.getElementById('printMethodBrowser').checked ) {
				// Load in hidden frame
				freemedMessage("<!--{t}-->Sending document to your web browser.<!--{/t}-->", "INFO");
				document.getElementById('patientPrintView').src = url;
				return true;
			}
			if ( document.getElementById('printMethodFax').checked ) {
				alert('STUB: print to fax');
				return true;
			}
			if ( document.getElementById('printMethodPrinter').checked ) {
				if (patientEmrAttachments.itemsToPrint.length == 1) {
					var x = dojo.widget.byId('patientEmrAttachments').store.getDataByKey( patientEmrAttachments.currentItem );
					url = "<!--{$relay}-->/org.freemedsoftware.module." + x.module_namespace + ".PrintSinglePDF?param0=" + encodeURIComponent( x.oid ) + "&param1=printer";
				} else {
					url = "<!--{$relay}-->/org.freemedsoftware.api.ModuleInterface.PrintMultiple?param0=" + encodeURIComponent( dojo.json.serialize( patientEmrAttachments.itemsToPrint ) ) + "&param1=printer";
				}
				// Make async call to print
				dojo.io.bind({
					method: "POST",
					url: url,
					load: function( type, data, evt ) {
						freemedMessage("<!--{t}-->Sending document to printer<!--{/t}-->: " + document.getElementById('emrPrinter').value, "INFO");
					},
					mimetype: "text/json"
				});
				return true;
			}
		},
		OnAdd: function ( ) {
			var m = dojo.widget.byId('emrSection').getValue();
			if ( ! m.length ) {
				alert( "<!--{t}-->You must select a module first.<!--{/t}-->" );
				return false;
			}
			freemedPatientContentLoad( '<!--{$controller}-->/org.freemedsoftware.module.' + m.toLowerCase() + '.form?patient=<!--{$patient|escape}-->' );
		},
		initialLoad: function ( ) {
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
							data[i]['date_mdy'] = new Date(data[i]['date_mdy']);
							data[i]['actions'] = '';
							data[i]['actions'] += "<a onClick=\"patientEmrAttachments.patientEmrAction('view', " + data[i]['id'] + ");\"><img src=\"<!--{$htdocs}-->/images/summary_view.png\" border=\"0\" alt=\"<!--{t}-->View<!--{/t}-->\" /></a>&nbsp;";
							data[i]['actions'] += "<a onClick=\"patientEmrAttachments.patientEmrAction('print', " + data[i]['id'] + ");\"><img src=\"<!--{$htdocs}-->/images/summary_print.png\" border=\"0\" alt=\"<!--{t}-->Print Record<!--{/t}-->\" /></a>&nbsp;";
							if (data[i]['locked'] == 0) {
								// All unlocked actions go here:
								<!--{acl category="emr" permission="lock"}-->
								data[i]['actions'] += "<a onClick=\"patientEmrAttachments.patientEmrAction('lock', " + data[i]['id'] + ");\"><img src=\"<!--{$htdocs}-->/images/summary_lock.png\" border=\"0\" alt=\"<!--{t}-->Lock Record<!--{/t}-->\" /></a>&nbsp;";
								<!--{/acl}-->
								<!--{acl category="emr" permission="modify"}-->
								data[i]['actions'] += "<a onClick=\"patientEmrAttachments.patientEmrAction('modify', " + data[i]['id'] + ");\"><img src=\"<!--{$htdocs}-->/images/summary_modify.png\" border=\"0\" alt=\"<!--{t}-->Modify Record<!--{/t}-->\" /></a>&nbsp;";
								<!--{/acl}-->
							} else {
								// All locked stuff goes here:
								data[i]['actions'] += "<img src=\"<!--{$htdocs}-->/images/summary_locked.png\" border=\"0\" alt=\"<!--{t}-->Locked<!--{/t}-->\" />&nbsp;";
							}
							if (data[i]['annotation'] != null) {
								data[i]['notes'] = "<a onClick=\"patientEmrAttachments.showAnnotations(" + data[i]['id'] + ");\"><img src=\"<!--{$htdocs}-->/images/annotation_icon.png\" border=\"0\" alt=\"<!--{t}-->Annotation<!--{/t}-->\" /></a>";
							}
							// Common things
							data[i]['actions'] += "<a onClick=\"patientEmrAttachments.patientEmrAction('annotate', " + data[i]['id'] + ");\"><img src=\"<!--{$htdocs}-->/images/annotate.png\" border=\"0\" alt=\"<!--{t}-->Annotate<!--{/t}-->\" /></a>&nbsp;";
						}
						dojo.widget.byId('patientEmrAttachments').store.setData( data );
						try {
							var x = dojo.widget.byId( 'freemedPatientContent' );
							var node = x.containerNode || x.domNode;
							var h = parseInt( node.offsetHeight ) - ( document.getElementById( 'patientParamBar' ).style.height + document.getElementById( 'patientEmrAttachmentsHead' ).style.height + 100 );
							document.getElementById( 'patientEmrAttachmentsBody' ).style.height = h + 'px';
						} catch ( e ) { }
					}
				},
				mimetype: "text/json"
			});
		}
	};
	
	_container_.addOnLoad(function() {
		dojo.widget.byId( 'emrSection' ).setLabel( '' );
		dojo.widget.byId( 'emrSection' ).setValue( '' );
		patientEmrAttachments.initialLoad( );
		dojo.event.connect( dojo.widget.byId('emrPrintButton'), 'onClick', patientEmrAttachments, 'OnPrint' );
		dojo.event.connect( dojo.widget.byId('emrAddButton'), 'onClick', patientEmrAttachments, 'OnAdd' );
		dojo.event.connect( dojo.widget.byId('emrResetButton'), 'onClick', patientEmrAttachments, 'resetFilters' );
		dojo.event.connect( dojo.widget.byId('emrSection'), 'onValueChanged', patientEmrAttachments, 'setFilters' );
		dojo.event.connect( dojo.widget.byId('emrRangeBegin'), 'onValueChanged', patientEmrAttachments, 'setFilters' );
		dojo.event.connect( dojo.widget.byId('emrRangeEnd'), 'onValueChanged', patientEmrAttachments, 'setFilters' );
	});

	_container_.addOnUnload(function() {
		dojo.event.disconnect( dojo.widget.byId('emrPrintButton'), 'onClick', patientEmrAttachments, 'OnPrint' );
		dojo.event.disconnect( dojo.widget.byId('emrAddButton'), 'onClick', patientEmrAttachments, 'OnAdd' );
		dojo.event.disconnect( dojo.widget.byId('emrResetButton'), 'onClick', patientEmrAttachments, 'resetFilters' );
		dojo.event.disconnect( dojo.widget.byId('emrSection'), 'onValueChanged', patientEmrAttachments, 'setFilters' );
		dojo.event.disconnect( dojo.widget.byId('emrRangeBegin'), 'onValueChanged', patientEmrAttachments, 'setFilters' );
		dojo.event.disconnect( dojo.widget.byId('emrRangeEnd'), 'onValueChanged', patientEmrAttachments, 'setFilters' );
	});

</script>

<div id="patientParamBar">
<table width="100%" border="0">
<tr>
	<td><button dojoType="button" id="emrResetButton" widgetId="emrResetButton"><!--{t}-->Reset<!--{/t}--></button></td>
	<td><!--{t}-->Date Range:<!--{/t}-->
		<input dojoType="DropdownDatePicker" id="emrRangeBegin" value="" />
		-
		<input dojoType="DropdownDatePicker" id="emrRangeEnd" value="" />
	</td>
	<td>
		<input dojoType="Select"
			autocomplete="false"
			id="emrSection" widgetId="emrSection"
			style="width: 150px;"
			dataUrl="<!--{$relay}-->/org.freemedsoftware.api.TableMaintenance.GetModules?param0=EmrModule&param1=%{searchString}&param2=1"
			mode="remote" value="" />
	</td>
	<td align="left"><button dojoType="button" id="emrAddButton" widgetId="emrAddButton"><!--{t}-->Add<!--{/t}--></button></td>
	<td align="right"><button dojoType="button" onClick="patientEmrAttachments.printMultiple();"><!--{t}-->Print<!--{/t}--></button></td>
</tr>
</table>
</div>

<div class="tableContainer">
	<table dojoType="FilteringTable" id="patientEmrAttachments" widgetId="patientEmrAttachments" headClass="fixedHeader"
	 tbodyClass="scrollContent" enableAlternateRows="true" rowAlternateClass="alternateRow"
	 valueField="id" border="0" multiple="yes" style="height: 100%;">
	<thead id="patientEmrAttachmentsHead">
		<tr>
			<th field="date_mdy" dataType="Date" sort="desc"><!--{t}-->Date<!--{/t}--></th>
			<th field="summary" dataType="String"><!--{t}-->Summary<!--{/t}--></th>
			<th field="type" dataType="String"><!--{t}-->Type<!--{/t}--></th>
			<th field="notes" dataType="Html"><!--{t}-->Notes<!--{/t}--></th>
			<th field="actions" dataType="Html"><!--{t}-->Actions<!--{/t}--></th>
		</tr>
	</thead>
	<tbody id="patientEmrAttachmentsBody"></tbody>
	</table>
</div>

<!-- Print dialog -->

<div dojoType="Dialog" style="display: none;" id="emrPrintDialog" widgetId="emrPrintDialog">
	<form>
	<table border="0">
		<tr>
			<td width="25"><input type="radio" id="printMethodPrinter" name="printMethod" value="printer" /></td>
			<td align="right"><label for="printMethodPrinter"><!--{t}-->Printer<!--{/t}--></label</td>
			<td align="left">
				<input dojoType="Select"
					autocomplete="true"
					id="emrPrinter_widget" widgetId="emrPrinter_widget"
					style="width: 200px;"
					dataUrl="<!--{$relay}-->/org.freemedsoftware.api.Printing.GetPrinters?param0=%{searchString}"
					setValue="document.getElementById('emrPrinter').value = arguments[0]; document.getElementById('printMethodPrinter').checked = true;"
					mode="remote" value="" />
				<input type="hidden" id="emrPrinter" name="emrPrinter" value="" />
			</td>
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
				<td align="right"><button dojoType="Button" id="emrPrintButton" widgetId="emrPrintButton">
					<div><img src="<!--{$htdocs}-->/images/summary_print.png" border="0" height="16" width="16" /> <!--{t}-->Print<!--{/t}--></div>
				</button></td>
				<td align="left"><button dojoType="Button" onClick="dojo.widget.byId('emrPrintDialog').hide();">
					<div><img src="<!--{$htdocs}-->/images/teak/x_stop.16x16.png" border="0" width="16" height="16" /> <!--{t}-->Cancel<!--{/t}--></div>
				</button></td>
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
			<textarea id="emrAnnotation" rows="5" cols="40"></textarea>
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

<!-- Hidden frame for printing -->

<iframe id="patientPrintView" style="display: none;"></iframe>

