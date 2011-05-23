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

<script language="javascript">
	var annotationShow = {
		initialLoad: function ( ) {
			dojo.io.bind({
				method: 'GET',
				url: '<!--{$relay}-->/org.freemedsoftware.module.Annotations.GetAnnotations',
				content: { param0: <!--{$id}--> },
				error: function ( ) {
					document.getElementById('annotationContainerDiv').innerHTML = "<!--{t|escape:'javascript'}-->Could not load annotations.<!--{/t}-->";
				},
				load: function( type, data, event ) {
					var aD = document.getElementById('annotationContainerDiv');
					var buf = "";
					buf += "<table border=\"0\" cellpadding=\"3\" cellspacing=\"2\"><tr><th>Annotation</th><th>By</th></tr>";
					for (i=0; i < data.length; i++) {
						buf += "<tr><td>" + data[i].annotation + "</td><td><i><small>" + data[i].user_description + "<br/>" + data[i].atimestamp + "</small></i></td></tr>";
					}
					buf += '</table>';
					aD.innerHTML = buf;
				},
				mimetype: "text/json"
			});
		}
	};

	_container_.addOnLoad(function(){
		document.getElementById('annotationContainerDiv').innerHTML = '';
		annotationShow.initialLoad();
	});

</script>

<div id="annotationContainerDiv"></div>

