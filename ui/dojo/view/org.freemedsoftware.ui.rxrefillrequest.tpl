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

<style type="text/css">

	.rrPane {
		margin: 1ex;
		}

</style>

<script language="javascript">

	var rr = {
		init: function () {
		},
		onChangePatient: function ( ) {
			var patient = dojo.widget.byId( 'rxrefillpatient_widget' ).getValue();
			dojo.io.bind({
				method: 'POST',
				content: {
					param0: patient
				},
				url: "<!--{$relay}-->/org.freemedsoftware.module.Prescription.GetDistinctRx",
				load: function( type, data, evt ) {
					if (data) {
						rr.populate( patient, data );
					} else {
						freemedMessage( "<!--{t|escape:'javascript'}-->Failed to retrieve prescriptions for this patient.<!--{/t}-->", 'ERROR' );
					}
				},
				mimetype: 'text/json'
			});
		},
		onCheckboxClick: function ( evt ) {
			var id = this.id.replace( 'rx_refill_<!--{$unique}-->', '' );
			//dojo.debug('onCheckboxClick ( orig = ' + this.id + ', id = ' + id + ' ) ' );
			rr.store[ id ] = ! rr.store[ id ];
		},
		store: { },
		populate: function ( patient, data ) {
			var c = document.getElementById( 'rxRefillContainer<!--{$unique}-->' );
			c.innerHTML = '';
			c.style.display = 'none';
			for (var i=0; i<data.length; i++) {
				if ( data[i].rx ) {
					rr.store[ data[i].id ] = 0;
					var li = document.createElement( 'li' );
					var cb = document.createElement( 'input' );
					cb.type = 'checkbox';
					cb.value = '1';
					cb.id = 'rx_refill_<!--{$unique}-->' + data[i].id;
					cb.onclick = rr.onCheckboxClick;

					var label = document.createElement( 'label' );
					label.htmlFor = cb.id;
					label.innerHTML = "<b>" + data[i].rx + "</b>" + ( data[i].sig ? " - <i>" + data[i].sig + "</i>" : "" );

					li.appendChild( cb );
					li.appendChild( label );
					c.appendChild( li );
				}
			}
			c.style.display = 'block';
		},
		onCommit: function ( ) {
			var patient = dojo.widget.byId( 'rxrefillpatient_widget' ).getValue();

			if ( ! patient ) {
				alert( "<!--{t|escape:'javascript'}-->You must select a patient.<!--{/t}-->" );
				return false;
			}
			var l = [ ];
			for ( var i in rr.store ) {
				if ( rr.store[ i ] ) {
					l.push( i );
				}
			}
			dojo.debug( dojo.json.serialize( l ) );
			var hash = {
				patient: patient,
				rxorig: l,
				note: document.getElementById( 'note<!--{$unique}-->' ).value
			};
			dojo.io.bind({
				method: 'POST',
				content: {
					param0: hash
				},
				url: "<!--{$relay}-->/org.freemedsoftware.module.Prescription.GetDistinctRx",
				load: function( type, data, evt ) {
					if (data) {
						rr.populate( patient, data );
					} else {
						freemedMessage( "<!--{t|escape:'javascript'}-->Failed to retrieve prescriptions for this patient.<!--{/t}-->", 'ERROR' );
					}
				},
				mimetype: 'text/json'
			});
			return false;
		}
	};

	_container_.addOnLoad(function(){
		rr.init();
		dojo.widget.byId('rxrefillpatient_widget').setLabel( '' );
		dojo.widget.byId('rxrefillpatient_widget').textInputNode.focus();
		dojo.event.connect( dojo.widget.byId( 'rrCommitButton<!--{$unique}-->' ), 'onClick', rr, 'onCommit' );
		dojo.event.connect( dojo.widget.byId( 'rxrefillpatient_widget' ), 'onValueChanged', rr, 'onChangePatient' );
	});
	_container_.addOnUnload(function(){
		dojo.event.disconnect( dojo.widget.byId( 'rrCommitButton<!--{$unique}-->' ), 'onClick', rr, 'onCommit' );
		dojo.event.disconnect( dojo.widget.byId( 'rxrefillpatient_widget' ), 'onValueChanged', rr, 'onChangePatient' );
	});

</script>

<h3><!--{t}-->Prescription Refill Request<!--{/t}--></h3>

<table border="0">
	<tr>
		<td><!--{t}-->Patient<!--{/t}--> : </td>
		<td><!--{include file="org.freemedsoftware.widget.patientpicklist.tpl" varname="rxrefillpatient"}--></td>
	</tr>
	<tr>
		<td><!--{t}-->Note<!--{/t}--> : </td>
		<td><input type="text" id="note<!--{$unique}-->" name="note<!--{$unique}-->" size="40" maxlength="250" />
	</tr>
</table>

<ul id="rxRefillContainer<!--{$unique}-->" style="display: none; list-style: none;"></ul>

<div align="center">
	<table border="0" style="width: auto;">
		<tr>
			<td>
				<button dojoType="Button" type="button" id="rrCommitButton<!--{$unique}-->" widgetId="rrCommitButton<!--{$unique}-->">
					<div><img src="<!--{$htdocs}-->/images/teak/check_go.16x16.png" border="0" width="16" height="16" /><!--{t}-->Submit Request<!--{/t}--></div>
				</button>
			</td>
			<td>
				<button dojoType="Button" type="button" id="rrCancelButton<!--{$unique}-->" widgetId="rrCancelButton<!--{$unique}-->">
					<div><img src="<!--{$htdocs}-->/images/teak/x_stop.16x16.png" border="0" width="16" height="16" /> <!--{t}-->Cancel<!--{/t}--></div>
				</button>
			</td>
		</tr>
	</table>
</div>

