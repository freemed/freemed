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

	File:	org.freemedsoftware.widget.djvuwidget

	Reusable DHTML/JS Djvu viewer widget.

	Parameters:

		$id - Record id

		$type - Module name
*}-->

<!-- Djvu Viewer, DHTML Style -->

<div align="center">
<table border="0" cellspacing="0" cellpadding="2" style="border: 1px solid #000000; width: 620px;">
<tr><td align="center" style="background-image: url('<!--{$htdocs}-->/images/brushed.gif');">
	<table border="0" cellspacing="0" cellpadding="0" width="100%"><tr>
		<td align="left"><button dojoType="Button" id="djvuPagePrevTop" widgetId="djvuPagePrevTop" value=" &lt; " disabled="true">&lt;</button></td>
		<td align="center"><span id="djvuCurrentPageTop">1</span> of <span id="djvuTotalPageTop">i<img src="<!--{$htdocs}-->/images/loading.gif" border="0" /></span></td>
		<td align="right"><button dojoType="Button" id="djvuPageNextTop" widgetId="djvuPageNextTop" value=" &gt; " disabled="true">&gt;</button></td>
	</tr></table>
</td></tr>
<tr>
<td align="center" style="background-image: url('<!--{$htdocs}-->/images/brushed.gif');"><img src="<!--{$relay}-->/org.freemedsoftware.module.<!--{$type|escape:'url'}-->.GetDocumentPage?param0=<!--{$id|escape:'url'}-->&param1=1" height="800" width="600" border="0" id="djvuViewer" /></td>
</tr>
<tr><td align="center" style="background-image: url('<!--{$htdocs}-->/images/brushed.gif');">
	<table border="0" cellspacing="0" cellpadding="0" width="100%"><tr>
		<td align="left"><button dojoType="Button" id="djvuPagePrevBottom" widgetId="djvuPagePrevBottom" value=" &lt; " disabled="true">&lt;</button></td>
		<td align="center"><span id="djvuCurrentPageBottom">1</span> of <span id="djvuTotalPageBottom"><img src="<!--{$htdocs}-->/images/loading.gif" border="0" /></span></td>
		<td align="right"><button dojoType="Button" id="djvuPageNextBottom" widgetId="djvuPageNextBottom" value=" &gt; " disabled="true">&gt;</button></td>
	</tr></table>
</td></tr>
</table>
<script language="javascript">
	var totalDjvuPages = 1;
	var djvuViewer = {
		currentDjvuPage: 1,
		prevPage: function ( ) { this.djvuChangePage( this.currentDjvuPage - 1 ); },
		nextPage: function ( ) { this.djvuChangePage( this.currentDjvuPage + 1 ); },
		djvuChangePage: function ( page ) {
			this.currentDjvuPage = page;

			document.getElementById('djvuViewer').src = "<!--{$relay}-->/org.freemedsoftware.module.<!--{$type|escape:'url'}-->.GetDocumentPage?param0=<!--{$id|escape:'url'}-->&param1=" + page;

			// Handle enable/disabled
			document.getElementById('djvuCurrentPageTop').innerHTML = page;
			document.getElementById('djvuCurrentPageBottom').innerHTML = page;
			if (page > 1) {
				dojo.widget.byId('djvuPagePrevTop').setDisabled( false );
				dojo.widget.byId('djvuPagePrevBottom').setDisabled( false );
			} else {
				dojo.widget.byId('djvuPagePrevTop').setDisabled( true );
				dojo.widget.byId('djvuPagePrevBottom').setDisabled( true );
			}
			if (this.currentDjvuPage < totalDjvuPages) {
				dojo.widget.byId('djvuPageNextTop').setDisabled( false );
				dojo.widget.byId('djvuPageNextBottom').setDisabled( false );
			} else {
				dojo.widget.byId('djvuPageNextTop').setDisabled( true );
				dojo.widget.byId('djvuPageNextBottom').setDisabled( true );
			}

			// Return true
			return true;
		},
		OnLoad: function ( ) {
			dojo.io.bind({
				method: 'POST',
				content: {
					param0: '<!--{$id|escape}-->'
				},
				url: '<!--{$relay}-->/org.freemedsoftware.module.<!--{$type|escape:'url'}-->.NumberOfPages',
				load: function( type, data, evt ) {
					totalDjvuPages = data;
					document.getElementById('djvuTotalPageTop').innerHTML = totalDjvuPages;
					document.getElementById('djvuTotalPageBottom').innerHTML = totalDjvuPages;
					this.currentDjvuPage = 1;

					// Handle enable/disabled
					document.getElementById('djvuCurrentPageTop').innerHTML = this.currentDjvuPage;
					document.getElementById('djvuCurrentPageBottom').innerHTML = this.currentDjvuPage;
					if (this.currentDjvuPage < totalDjvuPages) {
						dojo.widget.byId('djvuPageNextTop').setDisabled( false );
						dojo.widget.byId('djvuPageNextBottom').setDisabled( false );
					} else {
						dojo.widget.byId('djvuPageNextTop').setDisabled( true );
						dojo.widget.byId('djvuPageNextBottom').setDisabled( true );
					}

				},
				mimetype: "text/json"
			})
		}
	};
	_container_.addOnLoad(function(){
		dojo.event.connect(dojo.widget.byId('djvuPagePrevTop'), "onClick", djvuViewer, "prevPage");
		dojo.event.connect(dojo.widget.byId('djvuPagePrevBottom'), "onClick", djvuViewer, "prevPage");
		dojo.event.connect(dojo.widget.byId('djvuPageNextTop'), "onClick", djvuViewer, "nextPage");
		dojo.event.connect(dojo.widget.byId('djvuPageNextBottom'), "onClick", djvuViewer, "nextPage");
		djvuViewer.OnLoad();
	});

	_container_.addOnUnload(function(){
		dojo.event.disconnect(dojo.widget.byId('djvuPagePrevTop'), "onClick", djvuViewer, "prevPage");
		dojo.event.disconnect(dojo.widget.byId('djvuPagePrevBottom'), "onClick", djvuViewer, "prevPage");
		dojo.event.disconnect(dojo.widget.byId('djvuPageNextTop'), "onClick", djvuViewer, "nextPage");
		dojo.event.disconnect(dojo.widget.byId('djvuPageNextBottom'), "onClick", djvuViewer, "nextPage");
	});

</script>
</div>

</form>

