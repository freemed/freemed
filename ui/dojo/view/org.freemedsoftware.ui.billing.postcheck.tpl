<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
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

<script language="javascript">

	var postCheck = {
		populate: function ( ) {
			eval('ids = ' + "<!--{$claims}-->");
			dojo.io.bind({
				method: "GET",
				content: {
					param0: dojo.json.serialize(ids)
				},
				url: "<!--{$relay}-->/org.freemedsoftware.api.Ledger.GetClaims",
				load: function ( type, data, evt ) {
					if ( data ) {
						postCheck.buildTable( data );
					}
				},
				mimetype: "text/json"
			});
		},
		buildTable: function ( d ) {
			var b = document.getElementById('postCheckMatrixBody');		
			for ( var i=0; i<d.length; i++ ) {
				// Create table row
				var tR = document.createElement('tr');
				var tC = new Array ( );
				for ( var j=0; j<8; j++ ) {
					tC[j] = document.createElement('td');
					tR.appendChild( tC[j] );
				}
				b.appendChild( tR );

				// Populate cells /w static data
				tC[0].innerHTML = d[i].date_of_service_mdy;
				tC[1].innerHTML = d[i].cpt;
				tC[2].innerHTML = d[i].billed_amount;

				// Dynamic elements
				var tAAmount = document.createElement('input');
				tAAmount.type = 'text';
				tAAmount.size = 10;
				tAAmount.id = 'post_check_allowed_' + d[i].claim_id.toString();
				tAAmount.onchange = postCheck.CallbackAllowedAmount;
				tC[3].appendChild( tAAmount );

				var tCopay = document.createElement('input');
				tCopay.type = 'text';
				tCopay.size = 10;
				tCopay.id = 'post_check_copay_' + d[i].claim_id.toString();
				tCopay.onchange = postCheck.CallbackCopay;
				tC[4].appendChild( tCopay );

				var tPaid = document.createElement('input');
				tPaid.type = 'text';
				tPaid.size = 10;
				tPaid.id = 'post_check_paid_' + d[i].claim_id.toString();
				tPaid.onchange = postCheck.CallbackPaid;
				tC[5].appendChild( tPaid );

				var tBalance = document.createElement('input');
				tBalance.type = 'text';
				tBalance.size = 10;
				tBalance.id = 'post_check_balance_' + d[i].claim_id.toString();
				tBalance.disabled = true;
				tC[6].appendChild( tBalance );

				var tSendBal= document.createElement('input');
				tSendBal.type = 'checkbox';
				tSendBal.checked = true;
				tSendBal.value = 1;
				tSendBal.id = 'post_check_sendbal_' + d[i].claim_id.toString();
				tC[7].appendChild( tSendBal );
			}
		},
		CallbackAllowedAmount: function ( evt ) {
			alert(this.id);
		},
		CallbackCopay: function ( evt ) {
			alert(this.id);
		},
		CallbackPaid: function ( evt ) {
			alert(this.id);
		}
	};

	_container_.addOnLoad(function() {
		postCheck.populate();
	});

</script>

<h3><!--{t}-->Post Check<!--{/t}--></h3>

<table border="0" cellpadding="5" cellspacing="0">

	<tr>
		<td><!--{t}-->Payer<!--{/t}--></td>
		<td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" varname="criteriaPayer" module="insurancecompanymodule"}--></td>
	</tr>

	<tr>
		<td><!--{t}-->Check Amount<!--{/t}--></td>
		<td><input type="text" id="checkAmount" /></td>
	</tr>

	<tr>
		<td><!--{t}-->Check Number<!--{/t}--></td>
		<td><input type="text" id="checkNumber" /></td>
	</tr>

</table>

<table border="0" id="postCheckMatrix">
	<thead>
	<tr>
		<th><!--{t}-->Date of Service<!--{/t}--></th>
		<th><!--{t}-->CPT Code<!--{/t}--></th>
		<th><!--{t}-->Billed Amount<!--{/t}--></th>
		<th><!--{t}-->Allowed Amount<!--{/t}--></th>
		<th><!--{t}-->Copay<!--{/t}--></th>
		<th><!--{t}-->Paid<!--{/t}--></th>
		<th><!--{t}-->Leftover Balance<!--{/t}--></th>
		<th><!--{t}-->Send balance to Patient?<!--{/t}--></th>
	</tr>
	</thead>
	<tbody id="postCheckMatrixBody" style="height: 250px; overflow-y: scroll;">
	</tbody>
</table>

<div align="center">
	<button dojoType="Button" id="postCheckButton" widgetId="postCheckButton">
		<!--{t}-->Post Check<!--{/t}-->
	</button>
</div>

