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
<!--{*

	File:	org.freemedsoftware.widget.patienttags

	Reusable patient tags widget.

	Parameters:

		$float - If set as right or left, will float the area

*}-->
<style type="text/css">

	.tagLink {
		text-decoration: none;
		}

	.tagLink:hover, .tagRemoveLink:hover {
		font-weight: bold;
		cursor: pointer;
		}

</style>
<script language="javascript">
	patientTags = {
		globalTagSpan: 0,
		addTag: function ( obj, tag ) {
			//val obj = dojo.widget.byId('tagSubmit');
			//tag = obj.getValue();
			if (tag.length < 3) {
				return false;
			}
			obj.disable();
			dojo.io.bind({
				method: 'POST',
				content: {
					param0: '<!--{$patient}-->',
					param1: tag
				},
				url: '<!--{$relay}-->/org.freemedsoftware.module.PatientTag.CreateTag',
				load: function(type, data, evt) {
					// Increment global counter
					this.globalTagSpan += 1;
	
					// Add tag to list of displayed tags
					document.getElementById('patientTagContainerInnerDiv').innerHTML += '<span id="tagspan'+this.globalTagSpan+'"><a class="tagLink" onClick="freemedLoad(\'<!--{$controller}-->/org.freemedsoftware.ui.tag.simplesearch?tag='+tag+'\'); return true;">' + tag + '</a><a class="tagRemoveLink" onClick="patientTags.expireTag(\'tagspan'+this.globalTagSpan+'\', \''+tag+'\'); return true;"><sup>X</sup></a> &nbsp;</span>';
	
					// Remove previous value
					obj.enable();
					obj.setValue('');
					obj.setLabel('');
					return true;
				},
				mimetype: "text/json"
			});
		}, // end function addTag
		expireTag: function ( obj, tag ) {
			if (!confirm("<!--{t}-->Are you sure you want to remove this tag?<!--{/t}-->")) { return false; }
			dojo.io.bind({
				method: 'GET',
				content: {
					param0: '<!--{$patient}-->',
					param1: tag
				},
				url: '<!--{$relay}-->/org.freemedsoftware.module.PatientTag.ExpireTag',
				error: function(type, err) {
					alert (err.message);
				},
				load: function(type, data, evt) {
					// Remove this from display after it is expired
					document.getElementById(obj).style.display = 'none';
					return true;
				},
				mimetype: 'text/json'
			});
		} // end function expireTag
	}; // end patientTags

	// Autoloading routine
	_container_.addOnLoad(function(){
		// Show loading
		document.getElementById('patientTagContainerInnerDiv').innerHTML = '<img src="<!--{$htdocs}-->/images/loading.gif" border="0" /> <b><!--{t}-->Loading<!--{/t}--></b> ... ';
		dojo.io.bind({
			method: 'GET',
			content: {
				param0: '<!--{$patient}-->'
			},
			url: '<!--{$relay}-->/org.freemedsoftware.module.PatientTag.TagsForPatient',
			error: function(type, data, evt) {
				alert('Error refreshing');
			},
			load: function(type, data, evt) {
				document.getElementById('patientTagContainerInnerDiv').innerHTML = '';
				if (data) {
					var buf = '';
					for (var i=0; i<data.length; i++) {
						patientTags.globalTagSpan += 1;
						buf += '<span id="tagspan'+patientTags.globalTagSpan+'"><a class="tagLink" onClick="freemedLoad(\'<!--{$controller}-->/org.freemedsoftware.ui.tag.simplesearch?tag='+data[i]+'\'); return true;">' + data[i] + '</a><a class="tagRemoveLink" onClick="patientTags.expireTag(\'tagspan'+patientTags.globalTagSpan+'\', \''+data[i]+'\'); return true;"><sup>X</sup></a> &nbsp;</span>';
					}
					document.getElementById('patientTagContainerInnerDiv').innerHTML += buf;
				}
			},
			mimetype: "text/json"
		});
		try {
			dojo.widget.byId('tagSubmit').inputNode.value = '';
		} catch ( err ) { }
	});

	_container_.addOnUnload(function(){
		try {
			dojo.widget.byId('tagSubmit').inputNode.value = '';
		} catch ( err ) { }
	});

</script>
<div id="patientTagContainerDiv" class="patientEmrWidgetContainer" style="<!--{if $float}-->float:<!--{$float}-->;<!--{/if}-->">
	<div align="center" width="100%" class="patientEmrWidgetHeader"><b><!--{t}-->Patient Tags<!--{/t}--></b></div>
	<div id="patientTagContainerInnerDiv"></div>
	<div id="formDiv">
	<input dojoType="Select"
		autocomplete="false"
		id="tagSubmit" widgetId="tagSubmit"
		style="width: 150px;"
		dataUrl="<!--{$relay}-->/org.freemedsoftware.module.PatientTag.ListTags?param0=%{searchString}"
		setValue="patientTags.addTag(this, arguments[0]);"
		mode="remote" value="" />
	</div>
</div>

