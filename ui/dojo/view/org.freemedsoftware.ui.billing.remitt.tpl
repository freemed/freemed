<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
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

<script language="javascript">

	var remittMenu = {
		status: false,
		init: function ( ) {
			dojo.io.bind({
				method: "POST",
				content: { },
				url: "<!--{$relay}-->/org.freemedsoftware.api.Remitt.GetServerStatus",
				load: function ( type, data, evt ) {
					if ( data ) {
						remittMenu.status = true;
						document.getElementById('remittServerStatus').innerHTML = "<!--{t}-->REMITT Server Running<!--{/t}-->";
					} else {
						remittMenu.status = false;
						document.getElementById('remittServerStatus').innerHTML = "<span style=\"color: #ff0000;\"><!--{t}-->REMITT Server NOT Running<!--{/t}--></span>";
					}
				},
				mimetype: "text/json",
				sync: true
			});
		}
	};

	_container_.addOnLoad(function() {
		remittMenu.init();
	});

</script>

<h3><!--{t}-->REMITT Billing<!--{/t}--></h3>

<div style="padding: 1em; border: 1px solid #0000ff; background: #aaaaff; width: auto; text-align: center; margin: 1em;" id="remittServerStatus">
	<img src="<!--{$htdocs}-->/images/loading.gif" border="0" />
</div>

<table border="0" cellpadding="5" cellspacing="0">

	<tr>
		<td><a class="clickable" onClick="freemedLoad('org.freemedsoftware.ui.billing.remitt.bill');"><!--{t}-->Perform Billing<!--{/t}--></a></td>
		<td><!--{t}-->Perform Remitt billing runs.<!--{/t}--></td>
	</tr>

	<tr>
		<td><a class="clickable" onClick="freemedLoad('org.freemedsoftware.ui.billing.remitt.rebill');"><!--{t}-->Rebill<!--{/t}--></a></td>
		<td><!--{t}-->Select a previous billing to rebill.<!--{/t}--></td>
	</tr>

	<tr>
		<td><a class="clickable" onClick="freemedLoad('org.freemedsoftware.ui.billing.remitt.reports');"><!--{t}-->Show Reports<!--{/t}--></a></td>
		<td><!--{t}-->View output files and logs from Remitt.<!--{/t}--></td>
	</tr>

</table>
