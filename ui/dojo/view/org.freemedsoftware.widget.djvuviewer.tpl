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

	File:	org.freemedsoftware.widget.djvuwidget

	Reusable DHTML/JS Djvu viewer widget.

	Parameters:

		$id - Record id

		$type - Module name
*}-->

<!-- Djvu Viewer, DHTML Style -->

<div align="center">
<table border="0" cellspacing="0" cellpadding="2" style="border: 1px solid #000000; width: 620px;">
<tr><td align="center">
<input type="button" id="djvuPagePrevTop_<!--{$id|escape}-->" value=" &lt; " onClick="djvuChangePage(currentDjvuPage_<!--{$id|escape}--> - 1);" disabled="disabled" />
<span id="djvuCurrentPageTop_<!--{$id|escape}-->">1</span> of <span id="djvuTotalPageTop_<!--{$id|escape}-->">1</span>
<input type="button" id="djvuPageNextTop_<!--{$id|escape}-->" value=" &gt; " onClick="djvuChangePage(currentDjvuPage_<!--{$id|escape}--> + 1);" disabled="disabled" />
</td>
</tr>
<tr>
<td><img src="<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.module.<!--{$type|escape:'url'}-->.GetDocumentPage?param0=<!--{$id|escape:'url'}-->&param1=1" height="800" width="600" border="0" id="djvuViewer_<!--{$id|escape}-->" /></td>
</tr>
<tr><td align="center">
<input type="button" id="djvuPagePrevBottom_<!--{$id|escape}-->" value=" &lt; " onClick="djvuChangePage(currentDjvuPage_<!--{$id|escape}--> - 1);" disabled="disabled" />
<span id="djvuCurrentPageBottom_<!--{$id|escape}-->">1</span> of <span id="djvuTotalPageBottom_<!--{$id|escape}-->">1</span>
<input type="button" id="djvuPageNextBottom_<!--{$id|escape}-->" value=" &gt; " onClick="djvuChangePage(currentDjvuPage_<!--{$id|escape}--> + 1);" disabled="disabled" />
</td>
</tr>
</table>
<script language="javascript">
	var currentDjvuPage_<!--{$id|escape}--> = 1;
	var totalDjvuPages_<!--{$id|escape}--> = 1;

	// Initial call
	dojo.addOnLoad(function () {
		dojo.io.bind({
			method: 'POST',
			content: {
				param0: '<!--{$id|escape}-->'
			},
			url: '<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.module.<!--{$type|escape:'url'}-->.NumberOfPages',
			load: function( type, data, evt ) {
				totalDjvuPages_<!--{$id|escape}--> = data;
				document.getElementById('djvuTotalPageTop_<!--{$id|escape}-->').innerHTML = totalDjvuPages;
				document.getElementById('djvuTotalPageBottom_<!--{$id|escape}-->').innerHTML = totalDjvuPages;
				djvuChangePage( 1 );
			},
			mimetype: "text/json"
		});
	});

	function djvuChangePage( page ) {
		currentDjvuPage_<!--{$id|escape}--> = page;
		document.getElementById('djvuViewer_<!--{$id|escape}-->').src = '<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.module.<!--{$type|escape:'url'}-->.GetDocumentPage?param0=<!--{$id|escape:'url'}-->&param1=' + currentDjvuPage_<!--{$id|escape}-->;

		// Handle enable/disabled
		document.getElementById('djvuCurrentPageTop_<!--{$id|escape}-->').innerHTML = currentDjvuPage;
		document.getElementById('djvuCurrentPageBottom_<!--{$id|escape}-->').innerHTML = currentDjvuPage;
		if (currentDjvuPage_<!--{$id|escape}--> > 1) {
			document.getElementById('djvuPagePrevTop_<!--{$id|escape}-->').disabled = false;
			document.getElementById('djvuPagePrevBottom_<!--{$id|escape}-->').disabled = false;
		} else {
			document.getElementById('djvuPagePrevTop_<!--{$id|escape}-->').disabled = true;
			document.getElementById('djvuPagePrevBottom_<!--{$id|escape}-->').disabled = true;
		}
		if (currentDjvuPage_<!--{$id|escape}--> < totalDjvuPages_<!--{$id|escape}-->) {
			document.getElementById('djvuPageNextTop_<!--{$id|escape}-->').disabled = false;
			document.getElementById('djvuPageNextBottom_<!--{$id|escape}-->').disabled = false;
		} else {
			document.getElementById('djvuPageNextTop_<!--{$id|escape}-->').disabled = true;
			document.getElementById('djvuPageNextBottom_<!--{$id|escape}-->').disabled = true;
		}

		// Return true
		return true;
	}
</script>
</div>

</form>

