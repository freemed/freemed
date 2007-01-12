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
<!--{*

	File:	org.freemedsoftware.widget.patienttags

	Reusable patient tags widget.

	Parameters:

		$float - If set as right or left, will float the area

*}-->
<style type="text/css">
	.patientTagContainer {
		border: 1px solid #000000;
		background-color: #ffffff;
		width: 300px;
		}

	.tagLink {
		text-decoration: none;
		}

	.tagLink:hover {
		font-weight: bold;
		cursor: pointer;
		}
</style>
<script language="javascript">
	// Autoloading routine
	dojo.addOnLoad(function(){
		dojo.io.bind({
			method: 'POST',
			content: {
				param0: '<!--{$patient}-->'
			},
			url: '<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.module.PatientTag.TagsForPatient',
			error: function(type, data, evt) {
				alert('error');
			},
			load: function(type, data, evt) {
				if (data) {
					var buf = '';
					for (var i=0; i<data.length; i++) {
						buf += '<a class="tagLink" onClick="window.location=\'<!--{$base_uri}-->/controller.php/<!--{$ui}-->/org.freemedsoftware.ui.tag.simplesearch?tag='+data[i]+'\'; return true;">' + data[i] + '</a> &nbsp;';
					}
					document.getElementById('patientTagContainerDiv').innerHTML += buf;
				}
			},
			mimetype: "text/json"
		});
	});
</script>
<div id="patientTagContainerDiv" class="patientTagContainer" style="<!--{if $float}-->float:<!--{$float}-->;<!--{/if}-->">
	<div align="center"><!--{t}-->Patient Tags<!--{/t}--></div>
</div>
