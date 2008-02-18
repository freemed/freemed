<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2008 FreeMED Software Foundation
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

	File:	org.freemedsoftware.widget.patientreferrals

	Reusable patient referrals widget.

	Parameters:

		$float - If set as right or left, will float the area

*}-->
<script language="javascript">
	var patientReferrals = {
		addReferralForm: function ( ) {
			dojo.widget.byId('emrSimpleDialog').show();
			dojo.widget.byId('emrSimpleDialogContent').setUrl( '<!--{$controller}-->/org.freemedsoftware.module.referrals.form' );
		},
		initialLoad: function ( ) {
			dojo.io.bind({
				method: 'GET',
				content: {
					param0: '<!--{$patient}-->'
				},
				url: '<!--{$relay}-->/org.freemedsoftware.module.Referrals.GetAllActiveByPatient',
				load: function(type, data, evt) {
					document.getElementById('patientReferralContainerInnerDiv').innerHTML = '';
					if (data.length > 0) {
						var buf = '';
						buf += "<table border=\"0\"><tr><th><!--{t}-->Provider<!--{/t}--></th><th><!--{t}-->Date<!--{/t}--></th></tr>";
						for (var i=0; i<data.length; i++) {
							var direction = data[i].direction=='inbound' ? '&lt;' : '&gt;';
							buf += '<tr><td>'+direction+' '+data[i].provider+'</td><td>'+data[i].stamp_mdy+'</td></tr>';
						}
						buf += "</table>";
						document.getElementById('patientReferralContainerInnerDiv').innerHTML += buf;
					} else {
						var buf = "<center><!--{t}-->No active referrals.<!--{/t}--></center>";
						document.getElementById('patientReferralContainerInnerDiv').innerHTML = buf;

					}
				},
				mimetype: "text/json"
			});
		} // end initialLoad
	}; // end patientReferrals

	// Autoloading routine
	_container_.addOnLoad(function(){
		// Show loading
		patientReferrals.initialLoad();
		document.getElementById('patientReferralContainerInnerDiv').innerHTML = '<img src="<!--{$htdocs}-->/images/loading.gif" border="0" /> <b><!--{t}-->Loading<!--{/t}--></b> ... ';
		dojo.event.connect( dojo.widget.byId("patientReferralAdd"), "onClick", patientReferrals, "addReferralForm" );
	});

	_container_.addOnUnload(function(){
		dojo.event.disconnect( dojo.widget.byId("patientReferralAdd"), "onClick", patientReferrals, "addReferralForm" );
	});

</script>
<div id="patientReferralContainerDiv" class="patientEmrWidgetContainer" style="<!--{if $float}-->float:<!--{$float}-->;<!--{/if}-->">
	<div align="center" width="100%" class="patientEmrWidgetHeader"><!--{t}-->Outstanding Patient Referrals<!--{/t}--></div>
	<div id="patientReferralContainerInnerDiv"></div>
	<div id="formDiv" align="center">
		<button dojoType="Button" id="patientReferralAdd" widgetId="patientReferralAdd"><!--{t}-->Add Referral<!--{/t}--></button>
	</div>
</div>

