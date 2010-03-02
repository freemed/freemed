<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2010 FreeMED Software Foundation
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

	File:	org.freemedsoftware.ui.patient.overview.tpl

	Parameters:

		$patient - Patient ID

		$screen - (optional) Destination screen. Defaults to
		org.freemedsoftware.ui.patient.overview.default

*}-->

<!--{method var='patientName' namespace="org.freemedsoftware.api.PatientInterface.ToText" param="$patient"}-->

<script language="javascript">
	dojo.require("dojo.widget.DropdownContainer");

	var patientBar = {
		deletePatient: function () {
			// Make sure that the user is *really* sure.
			if ( ! confirm( "<!--{t|escape:'javascript'}-->Are you sure you want to remove this patient from the system?<!--{/t}-->" ) ) {
				return false;
			}
			dojo.io.bind({
				method: 'POST',
				content: {
					param0: {
						id: <!--{$patient}-->,
						ptarchive: 1
					}
				},
				url: "<!--{$relay}-->/org.freemedsoftware.module.PatientModule.mod",
				load: function( type, data, evt ) {
					if ( data ) {
						freemedMessage( "<!--{t|escape:'javascript'}-->Patient was removed from the system.<!--{/t}-->", 'INFO' );
						freemedGlobal.removePatientFromHistory( <!--{$patient}--> );
						freemedLoad( 'org.freemedsoftware.ui.patient.search' );
					} else {
						freemedMessage( "<!--{t|escape:'javascript'}-->Patient failed to be removed from the system.<!--{/t}-->", 'ERROR' );
					}
				},
				mimetype: 'text/json'
			});
		}
	};

	_container_.addOnLoad(function(){
		dojo.widget.byId('contactDropdown').inputNode.style.display = 'none';
		document.getElementById( 'deletePatient' ).onclick = patientBar.deletePatient;

		// Push this into the history
		freemedGlobal.addPatientToHistory( '<!--{$patient}-->', '<!--{$patientName}-->' );

		// Title change
		document.title = "FreeMED v<!--{$VERSION}--> : <!--{$patientName}-->";
	});
	_container_.addOnUnload(function(){
		// Title reset
		document.title = "FreeMED v<!--{$VERSION}-->";
		document.getElementById( 'deletePatient' ).onclick = null;
	});
</script>

<style type="text/css">
	#patientInfoBar {
		border: 1px solid #555555;
		background-color: #ccccff;
		padding: 5px;
		-moz-border-radius-bottomright: 15px;
		-moz-border-radius-bottomleft: 15px;
	}
</style>

<div id="patientInfoBar">
	<table width="100%" border="0" cellspacing="0" cellpadding="3"><tr>
	<td><b><!--{t}-->Patient<!--{/t}--> : </b> <!--{$patientName}--></td>
		<!--{* Form a contact "box" *}-->
	<td>
	<!--{if $record.ptprefcontact == 'home'}-->
		H: <!--{$record.pthphone|phone_format}-->
	<!--{elseif $record.ptprefcontact == 'work'}-->
		W: <!--{$record.ptwphone|phone_format}-->
	<!--{elseif $record.ptprefcontact == 'mobile'}-->
		C: <!--{$record.ptwphone|phone_format}-->
	<!--{elseif $record.ptprefcontact == 'email'}-->
		<!--{$record.ptemail}-->
	<!--{else}-->
		<i><!--{t}-->no contact<!--{/t}--></i>
	<!--{/if}-->
	<div dojoType="DropdownContainer" widgetId="contactDropdown" id="contactDropdown">
		<div class="infoBox">
			<!--{if $record.pthphone ne ''}--><div>H: <!--{$record.pthphone|phone_format}--></div><!--{/if}-->
			<!--{if $record.ptwphone ne ''}--><div>W: <!--{$record.ptwphone|phone_format}--></div><!--{/if}-->
			<!--{if $record.ptmphone ne ''}--><div>C: <!--{$record.ptmphone|phone_format}--></div><!--{/if}-->
			<!--{if $record.ptemail ne ''}--><div><!--{$record.ptemail}--></div><!--{/if}-->
			<hr/>
			<div><!--{$record.ptaddr1|escape}--></div>
			<!--{if $record.ptaddr2 ne ''}--><div><!--{$record.ptaddr2|escape}--></div><!--{/if}-->
			<div><!--{$record.ptcity|escape}-->, <!--{$record.ptstate}--> <!--{$record.ptzip}--></div>
		</div>
	</div></td>
		<!--{* Icon bar for easy actions *}-->
	<td>
		<span class="clickable" onClick="freemedPatientContentLoad('<!--{$controller}-->/org.freemedsoftware.ui.patient.overview.default?patient=<!--{$patient}-->');"><img src="<!--{$htdocs}-->/images/teak/patient.24x24.png" height="24" width="24" border="0" alt="<!--{t|escape:'javascript'}-->Overview<!--{/t}-->" /></span>
		<span class="clickable" onClick="freemedLoad('<!--{$controller}-->/org.freemedsoftware.controller.patient.form?patient=<!--{$patient}-->');"><img src="<!--{$htdocs}-->/images/teak/edit_mini.24x24.png" border="0" alt="<!--{t|escape:'javascript'}-->Modify Patient Information<!--{/t}-->" /></span>
		<span class="clickable" onClick="freemedPatientContentLoad('<!--{$controller}-->/org.freemedsoftware.ui.encounterconsole?patient=<!--{$patient}-->');"><img src="<!--{$htdocs}-->/images/teak/encounter.24x24.png" border="0" alt="<!--{t|escape:'javascript'}-->Encounter Console<!--{/t}-->" /></span>
		<span class="clickable" onClick="freemedPatientContentLoad('<!--{$controller}-->/org.freemedsoftware.ui.scheduler.book?patient=<!--{$patient}-->');"><img src="<!--{$htdocs}-->/images/teak/book_appt.24x24.png" border="0" alt="<!--{t|escape:'javascript'}-->Book Appointment<!--{/t}-->" /></span>
		<span class="clickable" id="deletePatient"><img src="<!--{$htdocs}-->/images/teak/x_stop.24x24.png" border="0" alt="<!--{t|escape:'javascript'}-->Remove Patient<!--{/t}-->" /></span>
	</td>
	</tr></table>
</div>

<div id="freemedPatientContent" dojoType="ContentPane" style="width: 100%; height: 100%;" executeScripts="true" sizeMin="20" sizeShare="80" cacheContent="false" parseWidgets="true" adjustPaths="false" href="<!--{$controller}-->/<!--{if $screen}--><!--{$screen|escape}--><!--{else}-->org.freemedsoftware.ui.patient.overview.default<!--{/if}-->?patient=<!--{$patient}-->" loadingMessage="<!--{$paneLoading|escape}-->"></div>

