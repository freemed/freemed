<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
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

	var remittStatus = {
		billkeys: [ ],
		billkeysProcessing: [ ],
		interval: undefined,
		init: function( ) {
			// Check to make sure that we have something in the pipe
			if ( freemedGlobal.state[ 'remittRunningBatches' ] != undefined ) {
				var div = document.getElementById( 'remittStatusContainer' );
				var t = document.createElement( 'table' );
				var tBody = document.createElement( 'tbody' );
				div.appendChild( t );

				// Create header row
				var hHead = document.createElement( 'thead' );
				hHead.className = 'fixedHeader';
				var hRow = document.createElement( 'tr' );
				var hCells = new Array ( );
				hCells[0] = document.createElement( 'th' );
				hCells[0].innerHTML = "<!--{t|escape:'javascript'}-->Mark<!--{/t}-->";
				hRow.appendChild( hCells[0] );
				hCells[1] = document.createElement( 'th' );
				hCells[1].innerHTML = "<!--{t|escape:'javascript'}-->Batch ID<!--{/t}-->";
				hRow.appendChild( hCells[1] );
				hCells[2] = document.createElement( 'th' );
				hCells[2].innerHTML = "<!--{t|escape:'javascript'}-->Format<!--{/t}-->";
				hRow.appendChild( hCells[2] );
				hCells[3] = document.createElement( 'th' );
				hCells[3].innerHTML = "<!--{t|escape:'javascript'}-->Target<!--{/t}-->";
				hRow.appendChild( hCells[3] );
				hCells[4] = document.createElement( 'th' );
				hCells[4].innerHTML = "<!--{t|escape:'javascript'}-->Status<!--{/t}-->";
				hRow.appendChild( hCells[4] );
				hHead.appendChild( hRow );

				t.appendChild( hHead );
				t.appendChild( tBody );

				for( var i=0; i<freemedGlobal.state.remittRunningBatches.length; i++ ) {
					var cur = freemedGlobal.state.remittRunningBatches[ i ];
					var row = document.createElement( 'tr' );
					var cells = new Array ( );
					for ( var j=0; j<5; j++ ) {
						cells[j] = document.createElement( 'td' );
						row.appendChild( cells[j] );
					}
					var cb = document.createElement( 'input' );
					cb.id = 'billkey_mark_' + cur.billkey;
					cb.type = 'checkbox';
					cb.disabled = true;
					cb.onclick = remittStatus.OnMark;

					cells[0].appendChild( cb );
					cells[1].innerHTML = '<label for="billkey_mark_' + cur.billkey + '">' + cur.billkey + '</label>';
					cells[2].innerHTML = '<label for="billkey_mark_' + cur.billkey + '">' + cur.format + '</label>';
					cells[3].innerHTML = '<label for="billkey_mark_' + cur.billkey + '">' + cur.target + '</label>';
					var d = document.createElement('div');
					d.id = 'status_div_' + cur.billkey;
					cells[4].appendChild( d );
					d.innerHTML = '<img src="<!--{$htdocs}-->/images/loading.gif" border="0" height="24" width="24" />'; // cur.result;
					tBody.appendChild( row );

					// Add to internal lists
					remittStatus.billkeys.push( cur.billkey );
					remittStatus.billkeysProcessing.push( cur.billkey );
				}
			} else {
				// If nothing here, return to the menu
				alert("<!--{t|escape:'javascript'}-->No claims in process.<!--{/t}-->");
				freemedLoad( 'org.freemedsoftware.ui.billing.remitt' );
			}
		},
		//----- Callbacks
		intervalCallback: function ( ) {
			// If we're done, remove the repetitive task
			if ( ! remittStatus.billkeysProcessing.length ){
				clearInterval( remittStatus.interval );
				return true;
			}

			// Fetch
			var transientStatus = [ ];
			dojo.io.bind({
				method: 'POST',
				content: {
					// Pass currently processing keys
					param0: remittStatus.billkeysProcessing
				},
				url: "<!--{$relay}-->/org.freemedsoftware.module.RemittBillingTransport.GetStatus",
				load: function ( type, data, evt ) {
					transientStatus = data;
				},
				mimetype: "text/dojo",
				sync: true
			});

			// Check for outstanding status
			for ( var i=0; i<transientStatus.length; i++ ) {
				var e = transientStatus[ i ];
				if ( e.status != -1 ) {
					var d = document.getElementById( 'status_div_' + e.billkey );
					d.innerHTML = e.status;
				}
			}
		},
		OnMark: function ( evt ) {
			var key = this.id.replace( 'billkey_mark_', '' );
			alert( 'mark for billkey = ' + key );
			document.getElementById( 'billkey_mark_' + key ).disabled = true;
		},
	};

	_container_.addOnLoad(function(){
		remittStatus.init( );

		// Set interval polling every 15 seconds
		remittStatus.interval = setInterval( remittStatus.intervalCallback, 1000 * 15 );
	});
	_container_.addOnUnload(function(){
		if ( remittStatus.interval ) { clearInterval( remittStatus.interval ); }
	});

</script>

<h3><!--{t}-->REMITT Billing<!--{/t}-->: <!--{t}-->Status<!--{/t}--></h3>

<table style="width: auto;" border="0">
	<tr>
		<td>
			<button dojoType="Button" id="remitt.Status.SelectAll" widgetId="remitt.Status.MarkAll">
				<!--{t}-->Mark All<!--{/t}-->
			</button>
		</td>
	</tr>
</table>

<div id="remittStatusContainer"></div>

