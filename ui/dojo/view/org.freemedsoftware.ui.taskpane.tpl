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

<div dojoType="AccordionContainer" duration="200" labelNodeClass="label"
	style="overflow: hidden; width: 100%; height: 100%;"
	containerNodeClass="accordionBody">

	<div dojoType="ContentPane" selected="true" label="<!--{t}-->System<!--{/t}-->" class="basicPane">

		<div class="paddedIcon" align="center" onClick="freemedLoad('<!--{$controller}-->/org.freemedsoftware.controller.dashboard');">
			<img src="<!--{$htdocs}-->/images/home_icon.png" height="64" width="64" border="0" /><br/>
			<!--{t}-->Dashboard<!--{/t}-->
		</div>

		<div class="paddedIcon" align="center" onClick="freemedLoad('<!--{$controller}-->/org.freemedsoftware.ui.scheduler.dailyappointments');">
			<img src="<!--{$htdocs}-->/images/pda_icon.png" height="64" width="64" border="0" /><br/>
			<!--{t}-->Day Schedule<!--{/t}-->
		</div>

		<div class="paddedIcon" align="center" onClick="freemedLoad('<!--{$controller}-->/org.freemedsoftware.ui.messaging');">
			<img src="<!--{$htdocs}-->/images/messages_icon.png" height="64" width="64" border="0" /><br/>
			<!--{t}-->Messaging<!--{/t}-->
		</div>

	</div>

	<div dojoType="ContentPane" label="<!--{t}-->Patients<!--{/t}-->" class="basicPane">

		<div class="paddedIcon" align="center" onClick="freemedLoad('<!--{$controller}-->/org.freemedsoftware.ui.patient.search?clear=1');">
			<img src="<!--{$htdocs}-->/images/search.png" height="64" width="64" border="0" /><br/>
			<!--{t}-->Search<!--{/t}-->
		</div>

		<div class="paddedIcon" align="center" onClick="freemedLoad('<!--{$controller}-->/org.freemedsoftware.ui.patient.form');">
			<img src="<!--{$htdocs}-->/images/messages_icon.png" height="64" width="64" border="0" /><br/>
			<!--{t}-->Patient Entry<!--{/t}-->
		</div>

		<div class="paddedIcon" align="center" onClick="freemedLoad('<!--{$controller}-->/org.freemedsoftware.ui.callin.manage');">
			<img src="<!--{$htdocs}-->/images/landline.png" height="64" width="64" border="0" /><br/>
			<!--{t}-->Call-in<!--{/t}-->
		</div>

	</div>

	<div dojoType="ContentPane" label="<!--{t}-->Documents<!--{/t}-->" class="basicPane">
		<div class="paddedIcon" align="center" onClick="freemedLoad('<!--{$controller}-->/org.freemedsoftware.ui.documents.unfiled');">
			<img src="<!--{$htdocs}-->/images/unfiled.png" height="64" width="64" border="0" /><br/>
			<!--{t}-->Unfiled<!--{/t}-->
		</div>

		<div class="paddedIcon" align="center" onClick="freemedLoad('<!--{$controller}-->/org.freemedsoftware.ui.documents.unread');">
			<img src="<!--{$htdocs}-->/images/unread.png" height="64" width="64" border="0" /><br/>
			<!--{t}-->Unread<!--{/t}-->
		</div>
	</div>

	<div dojoType="ContentPane" label="<!--{t}-->Billing<!--{/t}-->" class="basicPane">

		<div class="paddedIcon" align="center" onClick="freemedLoad('<!--{$controller}-->/org.freemedsoftware.ui.billing.accountsreceivable');">
			<img src="<!--{$htdocs}-->/images/calc.png" height="64" width="64" border="0" /><br/>
			<!--{t}-->Accounts Receivable<!--{/t}-->
		</div>

		<div class="paddedIcon" align="center" onClick="freemedLoad('<!--{$controller}-->/org.freemedsoftware.ui.billing.claimsmanager');">
			<img src="<!--{$htdocs}-->/images/calc.png" height="64" width="64" border="0" /><br/>
			<!--{t}-->Claims Manager<!--{/t}-->
		</div>

	</div>

	<div dojoType="ContentPane" label="<!--{t}-->Reporting<!--{/t}-->" class="basicPane">

		<div class="paddedIcon" align="center" onClick="freemedLoad('<!--{$controller}-->/org.freemedsoftware.ui.reporting.engine');">
			<img src="<!--{$htdocs}-->/images/calc.png" height="64" width="64" border="0" /><br/>
			<!--{t}-->Reporting Engine<!--{/t}-->
		</div>

	</div>

</div>

