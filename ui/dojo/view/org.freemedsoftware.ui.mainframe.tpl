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
		contentPane.setUrl( "<!--{$controller}-->/org.freemedsoftware.controller.patient.overview?patient=" + patient );
		//window.location = "<!--{$controller}-->/org.freemedsoftware.controller.patient.overview?patient=" + patient;
		return true;
	}
</script>

	<div dojoType="SplitContainer"
		orientation="horizontal"
		sizerWidth="5"
		activeSizing="0"
		layoutAlign="client"
	>
		<div dojoType="ContentPane" id="leftPane" style="width: 100px; background-image: url(<!--{$htdocs}-->/images/stipedbg.png); overflow: auto;" href="<!--{$controller}-->/org.freemedsoftware.ui.taskpane" layoutAlign="left" executeScripts="true" loadingMessage="<!--{$paneLoading|escape}-->"></div>
		<!-- this pane contains the actual application -->
		<div id="freemedContent" dojoType="ContentPane" executeScripts="true" sizeMin="20" sizeShare="80" cacheContent="false" adjustPaths="false" href="<!--{$controller}-->/org.freemedsoftware.controller.dashboard" loadingMessage="<!--{$paneLoading|escape}-->"></div>
	</div>

<!--{include file="org.freemedsoftware.ui.footer.tpl"}-->

