<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2009 FreeMED Software Foundation
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
	var o = {
		saveValue: 0,
		init: function () {
			dojo.io.bind({
				method: 'POST',
				url: '<!--{$relay}-->/org.freemedsoftware.module.Superbill.GetForDates',
				content: {
					param0: '',
					param1: ''
				},
				error: function() { },
				load: function( type, data, evt ) {
					dojo.widget.byId( 'superbillTable' ).store.setData( data );
				},
				mimetype: "text/json"
			});
		},
		cancelSuperbill: function ( ) {
			// Hide form, unload djvu viewer
			document.getElementById('superbillFormDiv').style.display = 'none';
			dojo.widget.byId('superbillViewPane').setUrl('<!--{$controller}-->/blank');

			// Unset all selections...
			dojo.widget.byId('superbill').resetSelections();
			dojo.widget.byId('superbill').renderSelections();
			this.saveValue = 0;
		},
		deleteSuperbill: function ( ) {
			var x = confirm("<!--{t|escape:'javascript'}-->Are you sure you want to permanently remove this document?<!--{/t}-->");
			if (x) {
				dojo.io.bind({
					method: 'POST',
					url: '<!--{$relay}-->/org.freemedsoftware.module.Superbill.del',
					content: {
						param0: this.saveValue
					},
					error: function( type, data, event ) {
						alert("<!--{t|escape:'javascript'}-->The system was unable to complete your request at this time.<!--{/t}-->");
					},
					load: function( type, data, event ) {
						this.resetForm();
					},
					mimetype: "text/json"
				});
			}
		},
		modifySuperbill: function ( ) {
			var p = {
				id: this.saveValue,
				reviewed: 1
			};

			dojo.io.bind({
				method: 'POST',
				url: '<!--{$relay}-->/org.freemedsoftware.module.Superbill.mod',
				content: {
					param0: p
				},
				error: function( type, data, event ) {
					//alert("<!--{t|escape:'javascript'}-->The system was unable to complete your request at this time.<!--{/t}-->");
					this.resetForm();
				},
				load: function( type, data, event ) {
					this.resetForm();
				},
				mimetype: "text/json"
			});
		},
		// specialty button actions here:
		resetForm: function ( ) {
			loadSuperbills();
			this.saveValue = 0;
			document.getElementById( 'superbillView' ).innerHTML = '';
		},
		handleDxCheck: function ( evt ) {
			var x = this.id.replace( 'dx_', '' );
			o.currentDx[ x ] = o.currentDx[ x ] ? 0 : 1;
		},
		handlePxCheck: function ( evt ) {
			var x = this.id.replace( 'px_', '' );
			o.currentPx[ x ] = o.currentPx[ x ] ? 0 : 1;
		},
		currentDx: {},
		currentPx: {},
		selectSuperbill: function ( ) {
			var w = dojo.widget.byId( 'superbillTable' );
			var val = w.getSelectedData();
			if (val != 'undefined') {
				// Save the value
				this.saveValue = val.id;

				// Populate/display
				document.getElementById('superbillFormDiv').style.display = 'block';

				// Form superbill
				document.getElementById( 'superbillView' ).innerHTML = "<!--{$paneLoading|replace:'"':''}-->";
				dojo.io.bind({
					method: 'POST',
					url: "<!--{$relay}-->/org.freemedsoftware.module.Superbill.GetSuperbill",
					content: {
						param0: val.id
					},
					load: function( type, data, evt ) {
						var v = document.getElementById( 'superbillView' );
						v.innerHTML = '';
						var hPx = document.createElement( 'h4' );
						hPx.innerHTML = "<!--{t|escape:'javascript'}-->Procedures<!--{/t}-->";
						v.appendChild( hPx );

						o.currentPx = {};
						var ulPx = document.createElement( 'ul' );
						ulPx.style.listStyle = 'none';
						for (var i=0; i<data.px.length; i++) {
							var liPx = document.createElement( 'li' );
							var liCheck = document.createElement( 'input' );
							liCheck.type = 'checkbox';
							liCheck.id = 'px_' + data.px[i].id;
							liCheck.checked = 'checked';
							liPx.appendChild( liCheck );
							liPx.innerHTML += ' <label for="px_' + data.px[i].id + '">' + data.px[i].code + ' - ' + data.px[i].descrip + '</label>';
							ulPx.appendChild( liPx );
							o.currentPx[ data.px[i].id ] = 1;
						}
						v.appendChild( ulPx );

						var hDx = document.createElement( 'h4' );
						hDx.innerHTML = "<!--{t|escape:'javascript'}-->Diagnoses<!--{/t}-->";
						v.appendChild( hDx );

						o.currentDx = {};
						var ulDx = document.createElement( 'ul' );
						ulDx.style.listStyle = 'none';
						for (var i=0; i<data.dx.length; i++) {
							var liDx = document.createElement( 'li' );
							var liCheck = document.createElement( 'input' );
							liCheck.type = 'checkbox';
							liCheck.id = 'dx_' + data.dx[i].id;
							liCheck.checked = 'checked';
							liDx.appendChild( liCheck );
							liDx.innerHTML += ' <label for="dx_' + data.dx[i].id + '">' + data.dx[i].code + ' - ' + data.dx[i].descrip + '</label>';
							ulDx.appendChild( liDx );
							o.currentDx[ data.dx[i].id ] = 1;
						}
						v.appendChild( ulDx );

						// Show all checks initially
						for(var i=0; i<data.px.length; i++) {
							document.getElementById( 'px_' + data.px[i].id ).checked = 'checked';
							document.getElementById( 'px_' + data.px[i].id ).onclick = o.handlePxCheck;
						}
						for(var i=0; i<data.dx.length; i++) {
							document.getElementById( 'dx_' + data.dx[i].id ).checked = 'checked';
							document.getElementById( 'dx_' + data.dx[i].id ).onclick = o.handleDxCheck;
						}
					},
					mimetype: 'text/json'
				});

				// Set up the coverage widget to work properly
				var cW = dojo.widget.byId( 'sbcov_widget' );
				cW.setLabel(''); cW.setValue(0);
				cW.dataProvider.searchUrl = "<!--{$relay}-->/org.freemedsoftware.module.PatientCoverages.GetCoverages?param0=" + val.patient_id + "&param1=" + val.dateofservice;
				sbauth.assign( 0 );
				return true;
			}
		}
	};

	// Handle in context loading for these widgets
	_container_.addOnLoad(function(){
		o.init();
		dojo.event.connect(dojo.widget.byId('superbillTable'), "onSelect", o, "selectSuperbill");
		dojo.event.connect(dojo.widget.byId('cancelButton'), "onClick", o, "cancelSuperbill");
		dojo.event.connect(dojo.widget.byId('deleteButton'), "onClick", o, "deleteSuperbill");
		dojo.event.connect(dojo.widget.byId('confirmButton'), "onClick", o, "confirmSuperbill");
	});
	_container_.addOnUnload(function(){
		dojo.event.disconnect(dojo.widget.byId('superbill'), "onSelect", o, "selectSuperbill");
		dojo.event.disconnect(dojo.widget.byId('cancelButton'), "onClick", o, "cancelSuperbill");
		dojo.event.disconnect(dojo.widget.byId('deleteButton'), "onClick", o, "deleteSuperbill");
		dojo.event.disconnect(dojo.widget.byId('confirmButton'), "onClick", o, "confirmSuperbill");
	});

</script>

<h3><!--{t}-->Superbills<!--{/t}--></h3>

<div dojoType="SplitContainer" orientation="horizontal" sizerWidth="5" activeSizing="0" layoutAlign="client" style="height: 100%;">

	<div dojoType="ContentPane" executeScripts="true" sizeMin="30" sizeShare="50" style="height: 100%;">

		<div class="tableContainer">
			<table dojoType="FilteringTable" id="superbillTable" widgetId="superbillTable" headClass="fixedHeader"
			 tbodyClass="scrollContent" enableAlternateRows="true" rowAlternateClass="alternateRow"
			 valueField="id" border="0" multiple="false" maxSelect="1">
			<thead>
				<tr>
					<th field="dateofservice_mdy" dataType="Date"><!--{t}-->Date<!--{/t}--></th>
					<th field="patient_name" dataType="String"><!--{t}-->Patient<!--{/t}--></th>
					<th field="provider_name" dataType="String"><!--{t}-->Provider<!--{/t}--></th>
					<th field="cpt" dataType="String"><!--{t}-->Procedural Codes<!--{/t}--></th>
				</tr>
			</thead>
			<tbody></tbody>
			</table>
		</div>

		<div id="superbillFormDiv" style="display: none;">
		<table border="0">

			<tr>
				<td><!--{t}-->Coverage<!--{/t}--></td>
				<td><input dojoType="Select"
					autocomplete="false"
					id="sbcov_widget" widgetId="sbcov_widget"
					style="width: 300px;"
					dataUrl="<!--{$relay}-->/"
					mode="remote"
					setValue="document.getElementById( 'sbcov' ).value = arguments[1];"
				/><input type="hidden" id="sbcov" value="0" /></td>
			</tr>

			<tr>
				<td><!--{t}-->Authorization<!--{/t}--></td>
				<td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="Authorizations" varname="sbauth"}--></td>
			</tr>

			<tr>
				<td><!--{t}-->Note<!--{/t}--></td>
				<td><input type="text" id="sbnote" name="sbnote" size="50" value="" /></td>
			</tr>

		</table>

		<div align="center">
		<table border="0">
			<tr>
				<td align="right">
					<button dojoType="Button" id="confirmButton">
	                			<div><img src="<!--{$htdocs}-->/images/teak/check_go.16x16.png" border="0" width="16" height="16" /> <!--{t}-->Process Superbill<!--{/t}--></div>
					</button>
				</td>
				<td align="left">
					<button dojoType="Button" id="cancelButton">
	                			<div><img src="<!--{$htdocs}-->/images/teak/x_stop.16x16.png" border="0" width="16" height="16" /> <!--{t}-->Cancel<!--{/t}--></div>
					</button>
				</td>
			</tr>
		</table>
		</div>

		</div>

	</div>

	<div dojoType="ContentPane" executeScripts="false" sizeMin="30" sizeShare="50" id="superbillViewPane" style="overflow: scroll;">
		<div id="superbillView"></div>
	</div>
</div>

