<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2011 FreeMED Software Foundation
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

	var systemTaskInbox = {
		providers: [],
		populateStatus: function ( ) {
			dojo.io.bind({
				method: "POST",
				url: "<!--{$relay}-->/org.freemedsoftware.module.systemnotifications.GetSystemTaskInboxCount",
				content: { },
				load: function ( type, data, evt ) {
					var w = document.getElementById('dashboardWidgetSystemTaskInbox');
					var buf = '';
					if ( data == 0 ) {
						buf += '<div align="center"><img src="<!--{$htdocs}-->/images/teak/check_go.24x24.png" border="0" /> &nbsp; ' + "<!--{t|escape:'javascript'}-->Completed<!--{/t}-->" + '</div>';
					} else {
						buf += '<div align="center" class="clickable" onclick="freemedLoad(\'org.freemedsoftware.ui.systemtaskinbox\');"><img src="<!--{$htdocs}-->/images/teak/x_stop.24x24.png" border="0" /> &nbsp; ' + "<!--{t|escape:'javascript'}-->Tasks not completed<!--{/t}-->" + '</div>';
					}
					w.innerHTML = buf;
				},
				mimetype: "text/json"
			});

		}
	};

	_container_.addOnLoad(function(){
		systemTaskInbox.populateStatus();
	});

</script>

<div class="dashboardWidgetContainer">
	<h4><!--{t}-->Tasks<!--{/t}--></h4>
	<div id="dashboardWidgetSystemTaskInbox">
		<div align="center" valign=""><img src="<!--{$htdocs}-->/images/loading.gif" border="0" /></div>
	</div>
</div>

