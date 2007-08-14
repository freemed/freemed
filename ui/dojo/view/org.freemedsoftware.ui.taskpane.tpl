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
			<img src="<!--{$htdocs}-->/images/teak/dashboard.32x32.png" height="32" width="32" border="0" /><br/>
			<!--{t}-->Dashboard<!--{/t}-->
		</div>

		<div class="paddedIcon" align="center" onClick="freemedLoad('<!--{$controller}-->/org.freemedsoftware.ui.scheduler.dailyappointments');">
			<img src="<!--{$htdocs}-->/images/teak/scheduler_daily.32x32.png" height="32" width="32" border="0" /><br/>
			<!--{t}-->Day Schedule<!--{/t}-->
		</div>

		<div class="paddedIcon" align="center" onClick="freemedLoad('<!--{$controller}-->/org.freemedsoftware.ui.scheduler.book');">
			<img src="<!--{$htdocs}-->/images/teak/book_appt.32x32.png" height="32" width="32" border="0" /><br/>
			<!--{t}-->Book Appointment<!--{/t}-->
		</div>

		<div class="paddedIcon" align="center" onClick="freemedLoad('<!--{$controller}-->/org.freemedsoftware.ui.scheduler');">
			<img src="<!--{$htdocs}-->/images/teak/scheduler.32x32.png" height="32" width="32" border="0" /><br/>
			<!--{t}-->Scheduler<!--{/t}-->
		</div>

		<div class="paddedIcon" align="center" onClick="freemedLoad('<!--{$controller}-->/org.freemedsoftware.ui.messaging');">
			<img src="<!--{$htdocs}-->/images/teak/messaging.32x32.png" height="32" width="32" border="0" /><br/>
			<!--{t}-->Messaging<!--{/t}-->
		</div>

		<!--{* ----- Non-static items ----- *}-->
		<!--{get_templates var=systemItems glob='org.freemedsoftware.hook.task.system.*.tpl'}-->
		<!--{foreach from=$systemItems item=component}-->
		<!--{include file="$component"}-->
		<!--{/foreach}-->

	</div>

	<div dojoType="ContentPane" label="<!--{t}-->Patients<!--{/t}-->" class="basicPane">

		<div class="paddedIcon" align="center" onClick="freemedLoad('<!--{$controller}-->/org.freemedsoftware.ui.patient.search?clear=1');">
			<img src="<!--{$htdocs}-->/images/teak/chart_search.32x32.png" height="32" width="32" border="0" /><br/>
			<!--{t}-->Search<!--{/t}-->
		</div>

		<div class="paddedIcon" align="center" onClick="freemedLoad('<!--{$controller}-->/org.freemedsoftware.ui.patient.form');">
			<img src="<!--{$htdocs}-->/images/teak/patient_entry.32x32.png" height="32" width="32" border="0" /><br/>
			<!--{t}-->Patient Entry<!--{/t}-->
		</div>

		<div class="paddedIcon" align="center" onClick="freemedLoad('<!--{$controller}-->/org.freemedsoftware.ui.callin.manage');">
			<img src="<!--{$htdocs}-->/images/teak/callin.32x32.png" height="32" width="32" border="0" /><br/>
			<!--{t}-->Call-in<!--{/t}-->
		</div>

		<!--{* ----- Non-static items ----- *}-->
		<!--{get_templates var=patientItems glob='org.freemedsoftware.hook.task.patient.*.tpl'}-->
		<!--{foreach from=$patientItems item=component}-->
		<!--{include file="$component"}-->
		<!--{/foreach}-->

	</div>

	<div dojoType="ContentPane" label="<!--{t}-->Documents<!--{/t}-->" class="basicPane">
		<div class="paddedIcon" align="center" onClick="freemedLoad('<!--{$controller}-->/org.freemedsoftware.ui.documents.unfiled');">
			<img src="<!--{$htdocs}-->/images/teak/unfiled.32x32.png" height="32" width="32" border="0" /><br/>
			<!--{t}-->Unfiled<!--{/t}-->
		</div>

		<div class="paddedIcon" align="center" onClick="freemedLoad('<!--{$controller}-->/org.freemedsoftware.ui.documents.unread');">
			<img src="<!--{$htdocs}-->/images/teak/unread.32x32.png" height="32" width="32" border="0" /><br/>
			<!--{t}-->Unread<!--{/t}-->
		</div>
	</div>

	<!--{acl category="financial" permission="menu"}-->
	<div dojoType="ContentPane" label="<!--{t}-->Billing<!--{/t}-->" class="basicPane">

		<div class="paddedIcon" align="center" onClick="freemedLoad('<!--{$controller}-->/org.freemedsoftware.ui.billing.accountsreceivable');">
			<img src="<!--{$htdocs}-->/images/teak/accounts_receivable.32x32.png" height="32" width="32" border="0" /><br/>
			<!--{t}-->Accounts Receivable<!--{/t}-->
		</div>

		<div class="paddedIcon" align="center" onClick="freemedLoad('<!--{$controller}-->/org.freemedsoftware.ui.billing.claimsmanager');">
			<img src="<!--{$htdocs}-->/images/teak/claims_manage.32x32.png" height="32" width="32" border="0" /><br/>
			<!--{t}-->Claims Manager<!--{/t}-->
		</div>

		<div class="paddedIcon" align="center" onClick="freemedLoad('<!--{$controller}-->/org.freemedsoftware.ui.billing.remitt');">
			<img src="<!--{$htdocs}-->/images/teak/remitt.32x32.png" height="32" width="32" border="0" /><br/>
			<!--{t}-->REMITT Billing<!--{/t}-->
		</div>

		<div class="paddedIcon" align="center" onClick="freemedLoad('<!--{$controller}-->/org.freemedsoftware.ui.billing.superbill');">
			<img src="<!--{$htdocs}-->/images/teak/superbill.32x32.png" height="32" width="32" border="0" /><br/>
			<!--{t}-->Superbills<!--{/t}-->
		</div>

		<!--{* ----- Non-static items ----- *}-->
		<!--{get_templates var=billingItems glob='org.freemedsoftware.hook.task.billing.*.tpl'}-->
		<!--{foreach from=$billingItems item=component}-->
		<!--{include file="$component"}-->
		<!--{/foreach}-->

	</div>
	<!--{/acl}-->

	<!--{acl category="reporting" permission="menu"}-->
	<div dojoType="ContentPane" label="<!--{t}-->Reporting<!--{/t}-->" class="basicPane">

		<div class="paddedIcon" align="center" onClick="freemedLoad('<!--{$controller}-->/org.freemedsoftware.ui.reporting.engine');">
			<img src="<!--{$htdocs}-->/images/teak/report_engine.32x32.png" height="32" width="32" border="0" /><br/>
			<!--{t}-->Reporting Engine<!--{/t}-->
		</div>

		<!--{* ----- Non-static items ----- *}-->
		<!--{get_templates var=reportingItems glob='org.freemedsoftware.hook.task.reporting.*.tpl'}-->
		<!--{foreach from=$reportingItems item=component}-->
		<!--{include file="$component"}-->
		<!--{/foreach}-->

	</div>
	<!--{/acl}-->

	<div dojoType="ContentPane" label="<!--{t}-->Utilities<!--{/t}-->" class="basicPane">

		<div class="paddedIcon" align="center" onClick="freemedLoad('<!--{$controller}-->/org.freemedsoftware.ui.supportdata');">
			<img src="<!--{$htdocs}-->/images/teak/modules.32x32.png" height="32" width="32" border="0" /><br/>
			<!--{t}-->Support Data<!--{/t}-->
		</div>

		<!--{acl category="admin" permission="config"}-->
		<div class="paddedIcon" align="center" onClick="freemedLoad('<!--{$controller}-->/org.freemedsoftware.ui.configuration');">
			<img src="<!--{$htdocs}-->/images/teak/settings.32x32.png" height="32" width="32" border="0" /><br/>
			<!--{t}-->System Configuration<!--{/t}-->
		</div>
		<!--{/acl}-->

		<!--{acl category="admin" permission="user"}-->
		<div class="paddedIcon" align="center" onClick="freemedLoad('<!--{$controller}-->/org.freemedsoftware.ui.user');">
			<img src="<!--{$htdocs}-->/images/teak/chart_config.32x32.png" height="32" width="32" border="0" /><br/>
			<!--{t}-->User Administration<!--{/t}-->
		</div>
		<!--{/acl}-->

		<!--{* ----- Non-static items ----- *}-->
		<!--{get_templates var=utilityItems glob='org.freemedsoftware.hook.task.utilities.*.tpl'}-->
		<!--{foreach from=$utilityItems item=component}-->
		<!--{include file="$component"}-->
		<!--{/foreach}-->

	</div>

</div>

