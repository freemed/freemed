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

<style type="text/css">
	.messageButton {
		padding: 5px;
		margin: 5px;
		}

	#messagesBar {
		border: 1px solid #0000ff;
		background: #aaaaff;
		-moz-border-radius: 10px;
		}
</style>

<script type="text/javascript">
	dojo.require("dojo.event.*");
	dojo.require("dojo.widget.Editor2");
	dojo.require("dojo.widget.FilteringTable");

	// Special scope variable because of ContentPane
	// see http://manual.dojotoolkit.org/WikiHome/DojoDotBook/Book30
	var o = {
		currentLocation: '<!--{t|escape:'javascript'}-->INBOX<!--{/t}-->',
		messages: [],
		getSelectedMessage: function ( ) {
			return dojo.widget.byId('messagesTable<!--{$unique}-->').getSelectedData();
		},
		loadMessages: function ( ) {
			// Clear HTML
			document.getElementById('messageViewPaneDiv<!--{$unique}-->').innerHTML = '';
			// Grab messages
			var val = dojo.widget.byId('messagesTag<!--{$unique}-->').getValue();
			if ( val == '<!--{t|escape:'javascript'}-->INBOX<!--{/t}-->' ) { val = ''; }
			dojo.io.bind({
				method: 'POST',
				content: {
					param0: val,
					param1: false
				},
				url: '<!--{$relay}-->/org.freemedsoftware.module.MessagesModule.GetAllByTag',
				error: function() { },
				load: function( type, data, evt ) {
					var d = data;
					for(var i=0; i<d.length; i++) {
						o.messages.push( d[i].id );
						d[i].stamp_mdy = new Date( d[i].stamp_mdy );
						d[i].select = '<input id="message_delete_' + d[i].id + '" type="checkbox" />';
					}
					dojo.widget.byId('messagesTable<!--{$unique}-->').store.setData( d );
				},
				mimetype: "text/json"
			});
		},
		selectMessage: function ( ) {
			var d = document.getElementById('messageViewPaneDiv<!--{$unique}-->');
			try {
				var data = dojo.widget.byId('messagesTable<!--{$unique}-->').getSelectedData();
				d.innerHTML = '<tt>' + data.content.replace(/\n/g, "<br/>\n").replace(/\\/g, "") + '</tt>';
			} catch (e) {
				dojo.debug( 'Nothing selected, do nothing' );
			}
		},
		newMessage: function ( ) {
			freemedLoad( 'org.freemedsoftware.ui.messaging.compose' );
		},
		deleteMessage: function ( ) {
			var msg = this.getSelectedMessage();
			if ( typeof msg == 'undefined' ) {
				alert("<!--{t|escape:'javascript'}-->A message must be selected.<!--{/t}-->");
			} else {
				dojo.io.bind({
					method: 'POST',
					content: {
						param0: msg.id
					},
					url: '<!--{$relay}-->/org.freemedsoftware.module.MessagesModule.del',
					load: function( type, data, evt ) {
						freemedMessage( "<!--{t|escape:'javascript'}-->Message deleted successfully.<!--{/t}-->", 'INFO' );
						o.loadMessages();
					},
					mimetype: "text/json"
				});
			}
		},
		deleteMessages: function ( ) {
			var selected = [];
			for ( var i=0; i<o.messages.length; i++ ) {
				if ( document.getElementById( 'message_delete_' + o.messages[ i ] ).checked ) {
					selected.push( o.messages[ i ] );
				}
			}
			if ( selected.length < 1 ) {
				alert("<!--{t|escape:'javascript'}-->At least one message must be selected.<!--{/t}-->");
			} else {
				dojo.io.bind({
					method: 'POST',
					content: {
						param0: selected
					},
					url: '<!--{$relay}-->/org.freemedsoftware.module.MessagesModule.del',
					load: function( type, data, evt ) {
						freemedMessage( "<!--{t|escape:'javascript'}-->Messages deleted successfully.<!--{/t}-->", 'INFO' );
						o.loadMessages();
					},
					mimetype: "text/json"
				});
			}
		},
		printMessage: function ( ) {
			var msg = this.getSelectedMessage();
			try {
				if ( msg.id ) {
					dojo.widget.byId( 'messagePrintDialog<!--{$unique}-->' ).show();
				}
			} catch (e) { }
		},
		printMessageCallback: function ( ) {
			var msg = this.getSelectedMessage();
			dojo.widget.byId( 'messagePrintDialog<!--{$unique}-->' ).hide();

			if ( document.getElementById( 'printMethodBrowser<!--{$unique}-->' ).checked ) {
				freemedMessage("<!--{t|escape:'javascript'}-->Sending document to your web browser.<!--{/t}-->", "INFO");
				document.getElementById( 'messagePrintView<!--{$unique}-->' ).src = "<!--{$relay}-->/org.freemedsoftware.module.MessagesModule.RenderSinglePDF?param0=" + encodeURIComponent( msg.id );
				return true;
			}

			if ( document.getElementById( 'printMethodPrinter<!--{$unique}-->' ).checked ) {
				// Make async call to print
				dojo.io.bind({
					method: "POST",
					url: "<!--{$relay}-->/org.freemedsoftware.module.MessagesModule.PrintSinglePDF?param0=" + encodeURIComponent( msg.id ) + "&param1=printer",
					load: function( type, data, evt ) {
						freemedMessage("<!--{t|escape:'javascript'}-->Sending document to printer<!--{/t}-->: " + document.getElementById('messagePrinter<!--{$unique}-->').value, "INFO");
					},
					mimetype: "text/json"
				});
				return true;
			}
		},
		modifyTag: function ( ) {
			var msg = this.getSelectedMessage();
			if ( typeof msg == 'undefined' ) {
				alert("<!--{t|escape:'javascript'}-->A message must be selected.<!--{/t}-->");
			} else {
				var newTag = dojo.widget.byId('messagesTag<!--{$unique}-->').getValue();
				if ( newTag == '<!--{t|escape:'javascript'}-->INBOX<!--{/t}-->' ) { newTag = ''; }
				dojo.io.bind({
					method: 'POST',
					content: {
						param0: msg.id,
						param1: newTag
					},
					url: '<!--{$relay}-->/org.freemedsoftware.api.Messages.TagModify',
					load: function( type, data, evt ) {
						freemedMessage( "<!--{t|escape:'javascript'}-->Message moved successfully.<!--{/t}-->", 'INFO' );
						// Force reload
						dojo.widget.byId('messagesTag<!--{$unique}-->').setValue( o.currentLocation );
						o.loadMessages();
					},
					mimetype: "text/json"
				});
			}
		},
		selectTagView: function ( ) {
			o.currentLocation = dojo.widget.byId('messagesTag<!--{$unique}-->').getValue();
			o.loadMessages();
		},
		createMessageCallback: function ( ) {
			dojo.io.bind({
				method: 'POST',
				content: {
					param0: {
						user: dojo.widget.byId('msgfor').getValue(),
						subject: document.getElementById('msgsubject').value,
						patient: document.getElementById('msgpatient').value,
						text: document.getElementById('msgtext').value
					}
				},
				url: '<!--{$relay}-->/org.freemedsoftware.api.Messages.send',
				error: function( type, data, evt ) {
					//alert("<!--{t|escape:'javascript'}-->Unable to complete.<!--{/t}-->");
					dojo.widget.byId('newMessageDialog').hide();
					this.loadMessages();
				},
				load: function( type, data, evt ) {
					dojo.widget.byId('newMessageDialog').hide();
					this.loadMessages();
				},
				mimetype: "text/json"
			});
		}
	};

	// Handle in context loading for these widgets
	_container_.addOnLoad(function(){
		dojo.widget.byId('messagesTag<!--{$unique}-->').setValue( '<!--{t|escape:'javascript'}-->INBOX<!--{/t}-->' );
		o.loadMessages();
		dojo.event.connect(dojo.widget.byId('messagesTable<!--{$unique}-->'), "onSelect", o, "selectMessage");
		dojo.event.connect(dojo.widget.byId('messageNewButton<!--{$unique}-->'), "onClick", o, "newMessage");
		dojo.event.connect(dojo.widget.byId('messagePrintButton<!--{$unique}-->'), "onClick", o, "printMessage");
		dojo.event.connect(dojo.widget.byId('messagePrintButtonStart<!--{$unique}-->'), "onClick", o, "printMessageCallback");
		dojo.event.connect(dojo.widget.byId('messageDeleteButton<!--{$unique}-->'), "onClick", o, "deleteMessage");
		dojo.event.connect(dojo.widget.byId('messageMultipleDeleteButton<!--{$unique}-->'), "onClick", o, "deleteMessages");
		dojo.event.connect(dojo.widget.byId('messageMoveButton<!--{$unique}-->'), "onClick", o, "modifyTag");
		dojo.event.connect(dojo.widget.byId('messageTagButton<!--{$unique}-->'), "onClick", o, "selectTagView");
	});
	_container_.addOnUnload(function(){
		dojo.event.disconnect(dojo.widget.byId('messagesTable<!--{$unique}-->'), "onSelect", o, "selectMessage");
		dojo.event.disconnect(dojo.widget.byId('messageNewButton<!--{$unique}-->'), "onClick", o, "newMessage");
		dojo.event.disconnect(dojo.widget.byId('messagePrintButton<!--{$unique}-->'), "onClick", o, "printMessage");
		dojo.event.disconnect(dojo.widget.byId('messagePrintButtonStart<!--{$unique}-->'), "onClick", o, "printMessageCallback");
		dojo.event.disconnect(dojo.widget.byId('messageDeleteButton<!--{$unique}-->'), "onClick", o, "deleteMessage");
		dojo.event.disconnect(dojo.widget.byId('messageMultipleDeleteButton<!--{$unique}-->'), "onClick", o, "deleteMessages");
		dojo.event.disconnect(dojo.widget.byId('messageMoveButton<!--{$unique}-->'), "onClick", o, "modifyTag");
		dojo.event.disconnect(dojo.widget.byId('messageTagButton<!--{$unique}-->'), "onClick", o, "selectTagView");
	});

</script>

<div dojoType="LayoutContainer" layoutChildPriority="top-bottom" style="height: 50%;">

	<div dojoType="ContentPane" layoutAlign="top">
		<h3><!--{t}-->Messaging<!--{/t}--></h3>
	</div>

	<div dojoType="ContentPane" layoutAlign="top" style="height: 2em;">
		<div id="messagesBar">
			<table border="0"><tr>
				<td width="30">
					<button dojoType="Button" class="messageButton" id="messageNewButton<!--{$unique}-->" widgetId="messageNewButton<!--{$unique}-->">
						<img src="<!--{$htdocs}-->/images/teak/summary_envelope.16x16.png" border="0" height="16" width="16" />
					</button>
				</td>
				<td width="30">
					<button dojoType="Button" class="messageButton" id="messageDeleteButton<!--{$unique}-->" widgetId="messageDeleteButton<!--{$unique}-->">
						<img src="<!--{$htdocs}-->/images/teak/summary_delete.16x16.png" border="0" height="16" width="16" />
					</button>
				</td>
				<td width="30">
					<button dojoType="Button" class="messageButton" id="messageMultipleDeleteButton<!--{$unique}-->" widgetId="messageMultipleDeleteButton<!--{$unique}-->">
						<img src="<!--{$htdocs}-->/images/teak/summary_delete_2.16x16.png" border="0" height="16" width="16" />
					</button>
				</td>
				<td width="100">
					<small><!--{t}-->Location<!--{/t}-->:</small>
				</td>
				<td width="150">
					<input dojoType="ComboBox"
					 id="messagesTag<!--{$unique}-->" widgetId="messagesTag<!--{$unique}-->"
					 value=""
					 style="width: 150px;"
					 mode="remote"
					 dataUrl="<!--{$relay}-->/org.freemedsoftware.module.MessagesModule.MessageTags"
					 />
				</td>
				<td width="30">
					<button dojoType="Button" class="messageButton" id="messageMoveButton<!--{$unique}-->" widgetId="messageMoveButton<!--{$unique}-->">
						<img src="<!--{$htdocs}-->/images/teak/summary_modify.16x16.png" border="0" height="16" width="16" />
					</button>
				</td>
				<td width="30">
					<button dojoType="Button" class="messageButton" id="messageTagButton<!--{$unique}-->" widgetId="messageTagButton<!--{$unique}-->">
						<img src="<!--{$htdocs}-->/images/teak/summary_view.16x16.png" border="0" height="16" width="16" />
					</button>
				</td>
				<td width="30">
					<button dojoType="Button" class="messageButton" id="messagePrintButton<!--{$unique}-->" widgetId="messagePrintButton<!--{$unique}-->">
						<img src="<!--{$htdocs}-->/images/teak/ico.printer.16x16.png" border="0" height="16" width="16" />
					</button>
				</td>
				<td></td>
			</tr></table>
		</div>
	</div>

	<div dojoType="ContentPane" layoutAlign="client">

		<div class="tableContainer">
			<table dojoType="FilteringTable" id="messagesTable<!--{$unique}-->" widgetId="messagesTable<!--{$unique}-->" headClass="fixedHeader"
			 tbodyClass="scrollContent" enableAlternateRows="true" rowAlternateClass="alternateRow"
			 valueField="id" border="0" multiple="false" maxSelect="1">
			<thead>
				<tr>
					<th field="select" dataType="Html">&nbsp;</th>
					<th field="stamp_mdy" dataType="Date" sort="desc"><!--{t}-->Date<!--{/t}--></th>
					<th field="from_user" dataType="String"><!--{t}-->From<!--{/t}--></th>
					<th field="regarding" dataType="String"><!--{t}-->Regarding<!--{/t}--></th>
					<th field="subject" dataType="String"><!--{t}-->Subject<!--{/t}--></th>
				</tr>
			</thead>
			<tbody id="messagesTableBody<!--{$unique}-->"></tbody>
			</table>
		</div>

		</div>

	</div>

	<div dojoType="ContentPane" layoutAlign="bottom" style="background: #ffffff;">
		<div style="border: 2px solid #000000; background: #ffffff;" id="messageViewPaneDiv<!--{$unique}-->"></div>
	</div>
</div>

<!-- Print dialog -->

<div dojoType="Dialog" style="display: none;" id="messagePrintDialog<!--{$unique}-->" widgetId="messagePrintDialog<!--{$unique}-->">
	<form>
	<table border="0">
		<tr>
			<td width="25"><input type="radio" id="printMethodPrinter<!--{$unique}-->" name="printMethod<!--{$unique}-->" value="printer" /></td>
			<td align="right"><label for="printMethodPrinter<!--{$unique}-->"><!--{t}-->Printer<!--{/t}--></label</td>
			<td align="left">
				<input dojoType="Select"
					autocomplete="true"
					id="messagePrinter_widget<!--{$unique}-->" widgetId="messagePrinter_widget<!--{$unique}-->"
					style="width: 200px;"
					dataUrl="<!--{$relay}-->/org.freemedsoftware.api.Printing.GetPrinters?param0=%{searchString}"
					setValue="document.getElementById('messagePrinter<!--{$unique}-->').value = arguments[0]; document.getElementById('printMethodPrinter<!--{$unique}-->').checked = true;"
					mode="remote" value="" />
				<input type="hidden" id="messagePrinter<!--{$unique}-->" name="messagePrinter<!--{$unique}-->" value="" />
			</td>
		</tr>
		<tr>
			<td width="25"><input type="radio" id="printMethodFax<!--{$unique}-->" name="printMethod<!--{$unique}-->" value="fax" /></td>
			<td align="right"><label for="printMethodFax<!--{$unique}-->"><!--{t}-->Fax<!--{/t}--></label</td>
			<td align="left"><input type="text" name="faxNumber" id="faxNumber" onFocus="document.getElementById('printMethodFax<!--{$unique}-->').checked = true;" /></td>
		</tr>
		<tr>
			<td width="25"><input type="radio" id="printMethodBrowser<!--{$unique}-->" name="printMethod<!--{$unique}-->" value="browser" checked="checked" /></td>
			<td align="right"><label for="printMethodBrowser<!--{$unique}-->"><!--{t}-->Browser Based<!--{/t}--></label</td>
			<td align="left">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" align="center">
				<table border="0" align="center"><tr>
				<td align="right"><button dojoType="Button" id="messagePrintButtonStart<!--{$unique}-->" widgetId="messagePrintButtonStart<!--{$unique}-->">
					<div><img src="<!--{$htdocs}-->/images/teak/ico.printer.16x16.png" border="0" height="16" width="16" /> <!--{t}-->Print<!--{/t}--></div>
				</button></td>
				<td align="left"><button dojoType="Button" onClick="dojo.widget.byId('messagePrintDialog<!--{$unique}-->').hide();">
					<div><img src="<!--{$htdocs}-->/images/teak/x_stop.16x16.png" border="0" width="16" height="16" /> <!--{t}-->Cancel<!--{/t}--></div>
				</button></td>
				</tr></table>
			</td>
		</tr>
	</table>
	</form>
</div>

<!-- Hidden frame for printing -->

<iframe id="messagePrintView<!--{$unique}-->" style="display: none;"></iframe>

