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
<!--{*

	File:	org.freemedsoftware.widget.clinicallookup.allergies

	Reusable patient clinical lookup widget.

*}-->
<script language="javascript">

	var t = {
		initialLoad: function ( ) {
			dojo.io.bind({
				method: 'POST',
				content: {
					param0: <!--{$patient|escape:'javascript'}-->
				},
				url: "<!--{$relay}-->/org.freemedsoftware.module.Allergies.GetMostRecent",
				load: function ( type, data, evt ) {
					dojo.widget.byId( 'allergiesLookupTable_<!--{$unique}-->' ).store.setData( data );
				},
				mimetype: 'text/json'
			});
		}
	}; // end t

	// Autoloading routine
	_container_.addOnLoad(function(){
		// Show loading
		t.initialLoad();
	});

	_container_.addOnUnload(function(){
	});

</script>

<div class="tableContainer" id="allergiesLookupContainerDiv_<!--{$unique}-->">

	<table dojoType="FilteringTable" id="allergiesLookupTable_<!--{$unique}-->"
	 widgetId="allergiesLookupTable_<!--{$unique}-->" headClass="fixedHeader"
	 tbodyClass="scrollContent" enableAlternateRows="true"
	 valueField="id" border="0" multiple="false"
	 maxSelect="1" rowAlternateClass="alternateRow" sizerWidth="2"
	 style="height: 100%;">
	<thead>
		<tr>
			<th field="allergy" dataType="String"><small><!--{t}-->Allergy<!--{/t}--></small></th>
			<th field="reaction" dataType="String"><small><!--{t}-->Reaction<!--{/t}--></small></th>
			<th field="severity" dataType="String"><small><!--{t}-->Severity<!--{/t}--></small></th>
		</tr>
	</thead>
	<tbody></tbody>
	</table>

</div>

