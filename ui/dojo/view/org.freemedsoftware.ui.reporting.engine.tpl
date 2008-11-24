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

<script language="javascript">
	dojo.require("dojo.event.*");
	dojo.require("dojo.widget.Form");
	dojo.require("dojo.widget.DropdownDatePicker");
	dojo.require("dojo.widget.FilteringTable");

	var reportingEngine = {
		populateReportsList: function ( ) {
			dojo.io.bind({
				method: 'POST',
				content: {
					param0: 'en_US' // TODO: FIXME!
				},
				url: '<!--{$relay}-->/org.freemedsoftware.module.Reporting.GetReports',
				load: function(type, data, evt) {
					if (data) {
						dojo.widget.byId('reportsList<!--{$unique}-->').store.setData( data );
					}
				},
				mimetype: "text/json"
			});
		},
		selectReport: function ( ) {
			var myReport = dojo.widget.byId('reportsList<!--{$unique}-->').getSelectedData().report_uuid;
			dojo.io.bind({
				method: 'POST',
				content: {
					param0: myReport,
					param1: true
				},
				url: '<!--{$relay}-->/org.freemedsoftware.module.Reporting.GetReportParameters',
				load: function(type, data, evt) {
					if (!data) { return false; }
					document.getElementById('reportEngineForm<!--{$unique}-->').style.display = 'block';
					document.getElementById('reportEngineFormContent<!--{$unique}-->').innerHTML = '';
					switch ( data.report_type ) {
						case 'standard':
						document.getElementById('reportEngineFormStatic<!--{$unique}-->').style.display = 'block';
						document.getElementById('reportEngineFormStaticGraph<!--{$unique}-->').style.display = 'none';
						document.getElementById('reportEngineFormStaticRlib<!--{$unique}-->').style.display = 'none';
						break;

						case 'graph':
						document.getElementById('reportEngineFormStatic<!--{$unique}-->').style.display = 'none';
						document.getElementById('reportEngineFormStaticGraph<!--{$unique}-->').style.display = 'block';
						document.getElementById('reportEngineFormStaticRlib<!--{$unique}-->').style.display = 'none';
						break;

						case 'rlib':
						document.getElementById('reportEngineFormStatic<!--{$unique}-->').style.display = 'none';
						document.getElementById('reportEngineFormStaticGraph<!--{$unique}-->').style.display = 'none';
						document.getElementById('reportEngineFormStaticRlib<!--{$unique}-->').style.display = 'block';
						break;

						default: break;
					}
					if ( data.params.length > 0 ) {
						reportingEngine.populateForm( data );
					}
				},
				mimetype: "text/json"
			});
		},
		populateForm: function ( data ) {
			//alert(dojo.json.serialize(data));

			// Initialize
			document.getElementById('reportEngineFormContent<!--{$unique}-->').innerHTML = '';

			var tT = document.createElement('table');
			var tTr = new Array ( );
			var tTd = new Array ( );
			var tDiv = new Array ( );
			var tHidden = new Array ( );

			// Save a copy of the parameters structure
			this.reportParameters = data;

			for (var i=0; i<data.params.length; i++) {
				tTr[ i ] = document.createElement( 'tr' );
				tTd[ (i * 2) ] = document.createElement( 'td' );
				tTd[ (i * 2) + 1 ] = document.createElement( 'td' );
				tDiv[ i ] = document.createElement( 'div' );

				tTd[ (i * 2) ].innerHTML = '<b>' + data.params[i].name + '</b>';

				// Add container div to TD cell
				tTd[ (i * 2) + 1 ].appendChild( tDiv[ i ] );

				// Depending on what kind of element we have, determine which element to create
				switch ( data.params[i].type ) {
					case 'Date':
					// DropdownDatePicker element
					dojo.widget.createWidget(
						'DropdownDatePicker',
						{
							name: '<!--{$unique}-->param' + i.toString(),
							id: '<!--{$unique}-->param' + i.toString()
						},
						tDiv[ i ]
					);
					break; // Date

					case 'Provider':
					dojo.widget.createWidget(
						'Select',
						{
							name: '<!--{$unique}-->param' + i.toString(),
							id: '<!--{$unique}-->param' + i.toString() + '_widget',
							width: '300px',
							dataUrl: "<!--{$relay}-->/org.freemedsoftware.module.ProviderModule.picklist?param0=%{searchString}",
							mode: 'remote',
							autocomplete: false,
							iteration: i,
							setValue: function ( ) { if (arguments[0]) { document.getElementById('<!--{$unique}-->param" + this.iteration.toString() + "').value = arguments[0]; } }
						},
						tDiv[ i ]
					);
					// Keep track of the data here ...
					tHidden[ i ] = document.createElement( 'input' );
					tHidden[ i ].type = 'hidden';
					tHidden[ i ].id = "<!--{$unique}-->param" + i.toString();
					tHidden[ i ].name = "<!--{$unique}-->param" + i.toString();
					tDiv[ i ].appendChild( tHidden[ i ] );
					break; // Provider

					default:
					tDiv[ i ].innerHTML = "<!--{t|escape:'javascript'}-->Unknown element.<!--{/t}-->";
					break; // default / unknown
				}

				// Add TD cells to TR row
				tTr[ i ].appendChild( tTd[ (i * 2) ] );
				tTr[ i ].appendChild( tTd[ (i * 2) + 1 ] );

				// Append row to table
				tT.appendChild( tTr[ i ] );
			}

			// Append entire table to container DIV
			document.getElementById('reportEngineFormContent<!--{$unique}-->').appendChild( tT );
		},
		buildParameters: function ( ) {
			var b = new Array ( );
			try {
				if ( this.reportParameters.params.length < 1 ) {
					return b;
				}
			} catch (err) {
				return b
			}
			for ( var i=0; i<this.reportParameters.params.length; i++) {
				switch ( this.reportParameters.params[i].type ) {
					case 'Date':
					b[ i ] = dojo.widget.byId( '<!--{$unique}-->param' + i.toString() ).inputNode.value;
					break;

					default:
					b[ i ] = document.getElementById( '<!--{$unique}-->param' + i.toString() ).value;
					//alert("DEFAULT b[ " + i.toString() + " ] = " + b[i] );
					break;
				}
			}
			return b;
		},
		generate: function ( type ) {
			var myReport = dojo.widget.byId('reportsList<!--{$unique}-->').getSelectedData().report_uuid;
			var params = this.buildParameters( );
			var uri = "<!--{$relay}-->/org.freemedsoftware.module.Reporting.GenerateReport?param0=" + encodeURIComponent( myReport ) + "&param1=" + type.toLowerCase() + "&param2=" + encodeURIComponent( dojo.json.serialize( params ) );

			switch ( type ) {
				case 'HTML': case 'XML': case 'PDF':
				// Open browser-viewable things in new tab/window
				window.open( uri );
				break;

				default:
				// Handle in hidden iFrame
				document.getElementById('reportView').src = uri;
				break;
			}
		},

		// Individual button callbacks
		generateGraph: function ( ) { this.generate('XML'); },
		generateCSV: function ( ) { this.generate('CSV'); },
		generateHTML: function ( ) { this.generate('HTML'); },
		generatePDF: function ( ) { this.generate('PDF'); },
		generateXML: function ( ) { this.generate('XML'); }
	};

	_container_.addOnLoad(function() {
		reportingEngine.populateReportsList();
		dojo.event.connect(dojo.widget.byId('reportsList<!--{$unique}-->'), "onSelect", reportingEngine, 'selectReport');
		dojo.event.connect(dojo.widget.byId('reportSubmitGraph'), "onClick", reportingEngine, 'generateGraph');
		dojo.event.connect(dojo.widget.byId('reportSubmitCSV'), "onClick", reportingEngine, 'generateCSV');
		dojo.event.connect(dojo.widget.byId('reportSubmitHTML'), "onClick", reportingEngine, 'generateHTML');
		dojo.event.connect(dojo.widget.byId('reportSubmitPDF'), "onClick", reportingEngine, 'generatePDF');
		dojo.event.connect(dojo.widget.byId('reportSubmitXML'), "onClick", reportingEngine, 'generateXML');
		dojo.event.connect(dojo.widget.byId('reportSubmitRlibCSV'), "onClick", reportingEngine, 'generateCSV');
		dojo.event.connect(dojo.widget.byId('reportSubmitRlibHTML'), "onClick", reportingEngine, 'generateHTML');
		dojo.event.connect(dojo.widget.byId('reportSubmitRlibPDF'), "onClick", reportingEngine, 'generatePDF');
	});

	_container_.addOnUnload(function() {
		dojo.event.disconnect(dojo.widget.byId('reportsList<!--{$unique}-->'), "onSelect", reportingEngine, 'selectReport');
		dojo.event.disconnect(dojo.widget.byId('reportSubmitGraph'), "onClick", reportingEngine, 'generateGraph');
		dojo.event.disconnect(dojo.widget.byId('reportSubmitCSV'), "onClick", reportingEngine, 'generateCSV');
		dojo.event.disconnect(dojo.widget.byId('reportSubmitHTML'), "onClick", reportingEngine, 'generateHTML');
		dojo.event.disconnect(dojo.widget.byId('reportSubmitPDF'), "onClick", reportingEngine, 'generatePDF');
		dojo.event.disconnect(dojo.widget.byId('reportSubmitXML'), "onClick", reportingEngine, 'generateXML');
		dojo.event.disconnect(dojo.widget.byId('reportSubmitRlibCSV'), "onClick", reportingEngine, 'generateCSV');
		dojo.event.disconnect(dojo.widget.byId('reportSubmitRlibHTML'), "onClick", reportingEngine, 'generateHTML');
		dojo.event.disconnect(dojo.widget.byId('reportSubmitRlibPDF'), "onClick", reportingEngine, 'generatePDF');
	});

</script>

<div dojoType="SplitContainer" orientation="vertical" activesizing="0" layoutAlign="client" sizerWidth="2" style="height: 100%;">

	<div dojoType="ContentPane" layoutAlign="top" sizeShare="60" style="width: 100%; overflow: auto;">

	<h3><!--{t}-->Reporting Engine<!--{/t}--></h3>

	<div class="tableContainer">
		<table dojoType="FilteringTable" id="reportsList<!--{$unique}-->" widgetId="reportsList<!--{$unique}-->" headClass="fixedHeader" tbodyClass="scrollContent" enableAlternateRows="true" rowAlternateClass="alternateRow" valueField="report_uuid" border="0" multiple="false">
			<thead>
				<tr>
					<th field="report_name" dataType="String"><!--{t}-->Name<!--{/t}--></th>
					<th field="report_desc" dataType="String"><!--{t}-->Description<!--{/t}--></th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>

	</div> <!--{* ContentPane for FilteringTable *}-->

	<div dojoType="ContentPane" layoutAlign="bottom" sizeShare="60" style="width: 100%; overflow: auto;">

		<div id="reportEngineForm<!--{$unique}-->" style="display: none;">

			<!-- Generated report parameters thrown into this DIV -->
			<div id="reportEngineFormContent<!--{$unique}-->" align="center"></div>

			<div id="reportEngineFormStatic<!--{$unique}-->" align="center">

				<table border="0" style="width: auto;"><tr>
					<td><div dojoType="Button" id="reportSubmitCSV"><img src="<!--{$htdocs}-->/images/csv.32x32.png" border="0" height="32" width="32" /><br/>CSV</div></td>
					<td><div dojoType="Button" id="reportSubmitHTML"><img src="<!--{$htdocs}-->/images/html.32x32.png" border="0" height="32" width="32" /><br/>HTML</div></td>
					<td><div dojoType="Button" id="reportSubmitPDF"><img src="<!--{$htdocs}-->/images/pdf.32x32.png" border="0" height="32" width="32" /><br/>PDF</div></td>
					<td><div dojoType="Button" id="reportSubmitXML"><img src="<!--{$htdocs}-->/images/xml.32x32.png" border="0" height="32" width="32" /><br/>XML</div></td>
				</tr></table>

			</div>

			<div id="reportEngineFormStaticGraph<!--{$unique}-->" align="center">
				<table border="0" style="width: auto;"><tr>
					<td><div dojoType="Button" id="reportSubmitGraph"><img src="<!--{$htdocs}-->/images/xml.32x32.png" border="0" height="32" width="32" /><br/><!--{t}-->Graph<!--{/t}--></div></td>
				</tr></table>
			</div>

			<div id="reportEngineFormStaticRlib<!--{$unique}-->" align="center">
				<table border="0" style="width: auto;"><tr>
					<td><div dojoType="Button" id="reportSubmitRlibCSV"><img src="<!--{$htdocs}-->/images/csv.32x32.png" border="0" height="32" width="32" /><br/>CSV</div></td>
					<td><div dojoType="Button" id="reportSubmitRlibHTML"><img src="<!--{$htdocs}-->/images/html.32x32.png" border="0" height="32" width="32" /><br/>HTML</div></td>
					<td><div dojoType="Button" id="reportSubmitRlibPDF"><img src="<!--{$htdocs}-->/images/pdf.32x32.png" border="0" height="32" width="32" /><br/>PDF</div></td>
				</tr></table>
			</div>

		</div>

	</div>

	<!-- Hidden iFrame for passing reports. ContentPane does not work for this. -->
	<iframe id="reportView" style="display: none;"></iframe>

</div>

