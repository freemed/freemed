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

	_container_.addOnLoad(function(){
		dojo.io.bind({
			method: "POST",
			url: "<!--{$relay}-->/org.freemedsoftware.module.messagesmodule.UnreadMessages",
			content: { },
			error: function ( ) { },
			load: function ( type, data, evt ) {
				var w = document.getElementById('dashboardWidgetMessagesModule');
				if (data > 0) {
					w.innerHTML = "<div align=\"center\"><a onClick=\"freemedLoad('<!--{$controller}-->/org.freemedsoftware.ui.messaging');\"><img src=\"<!--{$htdocs}-->/images/messages_icon.png\" border=\"0\" /><br/>" + data + " <!--{t}-->new messages found.<!--{/t}--></a></div>";
				} else {
					w.innerHTML = "<div align=\"center\"><!--{t}-->No new messages.<!--{/t}--></div>";
				}
			},
			mimetype: "text/json"
		});
	});

</script>

<div class="dashboardWidgetContainer">
	<h4><!--{t}-->Messages<!--{/t}--></h4>
	<div id="dashboardWidgetMessagesModule">
		<div align="center" valign=""><img src="<!--{$htdocs}-->/images/loading.gif" border="0" /></div>
	</div>
</div>

