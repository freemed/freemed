<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2006 FreeMED Software Foundation
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
		contentPane.setUrl( "<!--{$base_uri}-->/controller.php/<!--{$ui}-->/org.freemedsoftware.controller.patient.overview?patient=" + patient );
		return true;
	}
</script>

	<div dojoType="SplitContainer"
		orientation="horizontal"
		sizerWidth="5"
		activeSizing="0"
		layoutAlign="client"
	>
		<!-- this pane contains the actual application -->
		<div id="freemedContent" dojoType="ContentPane" executeScripts="true" sizeMin="20" sizeShare="70" href="<!--{$base_uri}-->/controller.php/<!--{$ui}-->/org.freemedsoftware.controller.mainframe?piece=defaultpane"></div>
		<div dojoType="LinkPane" id="rightPane" style="width: 250px; background-image: url(<!--{$htdocs}-->/images/stipedbg.png); overflow: auto;" href="<!--{$base_uri}-->/controller.php/<!--{$ui}-->/org.freemedsoftware.controller.mainframe?piece=links" layoutAlign="right"></div>
	</div>

<!--{include file="org.freemedsoftware.ui.footer.tpl"}-->

