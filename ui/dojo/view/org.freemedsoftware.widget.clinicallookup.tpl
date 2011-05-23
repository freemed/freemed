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
<!--{*

	File:	org.freemedsoftware.widget.clinicallookup

	Reusable patient clinical lookup widget.

	Parameters:

		$float - If set as right or left, will float the area

*}-->

<div id="patientClinicalLookupContainerDiv_<!--{$unique}-->" class="patientEmrWidgetContainer" style="<!--{if $float}-->float:<!--{$float}-->;<!--{/if}-->">
	<div align="center" width="100%" class="patientEmrWidgetHeader" onClick="toggleDiv( 'patientClinicalLookupContainerInnerDiv_<!--{$unique}-->' );"><!--{t|escape:'javascript'}-->Clinical Information<!--{/t}--></div>
	<div id="patientClinicalLookupContainerInnerDiv_<!--{$unique}-->">
		<div id="patientClinicalLookupTabContainer_<!--{$unique}-->" dojoType="TabContainer" labelPosition="bottom" style="width: 100%; height: 250px;">
			<div id="clinicalLookupTab1_<!--{$unique}-->" dojoType="ContentPane" href="<!--{$controller}-->/org.freemedsoftware.widget.clinicallookup.photoid?patient=<!--{$patient}-->" refreshOnShow="true" label="&lt;img src='<!--{$htdocs}-->/images/teak/patient_avatar.16x16.png' border='0' height=16 width=16 /&gt;" executeScripts="true" adjustPaths="false"></div>
			<div id="clinicalLookupTab2_<!--{$unique}-->" dojoType="ContentPane" href="<!--{$controller}-->/org.freemedsoftware.widget.clinicallookup.medications?patient=<!--{$patient}-->" refreshOnShow="true" label="&lt;img src='<!--{$htdocs}-->/images/teak/rx_prescriptions.16x16.png' border='0' height=16 width=16 /&gt;" style="display: none;" executeScripts="true" adjustPaths="false"></div>
			<div id="clinicalLookupTab3_<!--{$unique}-->" dojoType="ContentPane" href="<!--{$controller}-->/org.freemedsoftware.widget.clinicallookup.allergies?patient=<!--{$patient}-->" refreshOnShow="true" label="&lt;img src='<!--{$htdocs}-->/images/allergies_icon.16x16.png' border='0' height=16 width=16 /&gt;" style="display: none;" executeScripts="true" adjustPaths="false"></div>
		</div>	
	</div>

</div>

