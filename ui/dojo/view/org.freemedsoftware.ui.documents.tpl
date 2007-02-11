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

<!--{include file="org.freemedsoftware.ui.framework.tpl"}-->

<script language="javascript">
	function freemedPatientLoad ( patient ) {
		var contentPane = dojo.widget.getWidgetById('freemedContent');
		//contentPane.setUrl( "<!--{$base_uri}-->/controller.php/<!--{$ui}-->/org.freemedsoftware.controller.patient.overview?patient=" + patient );
		window.location = "<!--{$base_uri}-->/controller.php/<!--{$ui}-->/org.freemedsoftware.controller.patient.overview?patient=" + patient;
		return true;
	}
</script>

	<div dojoType="SplitContainer"
		orientation="horizontal"
		sizerWidth="5"
		activeSizing="0"
		layoutAlign="client"
	>
		<div dojoType="ContentPane" id="leftPane" style="width: 100px; background-image: url(<!--{$htdocs}-->/images/stipedbg.png); overflow: auto;">
			<div class="paddedIcon" align="center" onClick="dojo.widget.byId('documentOuterContentPane').setUrl('<!--{$base_uri}-->/controller.php/<!--{$ui}-->/org.freemedsoftware.ui.documents.unfiled');">
				<img src="<!--{$htdocs}-->/images/unfiled.png" height="64" width="64" border="0" /><br/>
				Unfiled
			</div>
			<div class="paddedIcon" align="center">
				<img src="<!--{$htdocs}-->/images/unread.png" height="64" width="64" border="0" /><br/>
				Unread
			</div>
		</div>
		<div id="documentOuterContentPane" dojoType="ContentPane" executeScripts="true" sizeMin="20" sizeShare="80">
		</div>
	</div>

<!--{include file="org.freemedsoftware.ui.footer.tpl"}-->

