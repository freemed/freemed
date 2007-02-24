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

<script language="javascript">

	var workLists = {
		providers: [],
		createWorkLists: function ( ) {
			dojo.io.bind({
				method: "POST",
				url: "<!--{$relay}-->/org.freemedsoftware.module.worklistsmodule.GenerateWorkLists",
				content: { },
				load: function ( type, data, evt ) {
					var w = document.getElementById('dashboardWidgetWorklistsModule');
					var buf = '<table border="0">';
					for ( var i in data ) {
						var wl = data[i];
						if ( wl == null ) {
							buf = "<div align=\"center\"><!--{t}-->No work lists for today.<!--{/t}--></div>";
							w.innerHTML = buf;
							return false;
						}
						for (j=0; j<wl.length; j++) {
							var d = wl[j];
							buf += '<tr>';
							buf += '<td>' + d['patient_name'] + '</td>';
							buf += '<td>' + d['hour'] + ':' + d['minute'] + '</td>';
							buf += '</tr>';
						}
					}
					buf += '</table>';
					w.innerHTML = buf;
				},
				mimetype: "text/json"
			});

		}
	};

	_container_.addOnLoad(function(){
		workLists.createWorkLists();
	});

</script>

<div class="dashboardWidgetContainer">
	<h4><!--{t}-->Work Lists<!--{/t}--></h4>
	<div id="dashboardWidgetWorklistsModule">
		<div align="center" valign=""><img src="<!--{$htdocs}-->/images/loading.gif" border="0" /></div>
	</div>
</div>

