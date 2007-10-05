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
		currentLocation: 'INBOX',
		messages: [],
		getSelectedMessage: function ( ) {
			return dojo.widget.byId('messagesTable').getSelectedData();
		},
		loadMessages: function ( ) {
			// Clear HTML
			document.getElementById('messageViewPaneDiv').innerHTML = '';
			// Grab messages
			var val = dojo.widget.byId('messagesTag').getValue();
			if ( val == 'INBOX' ) { val = ''; }
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
					dojo.widget.byId('messagesTable').store.setData( d );
				},
				mimetype: "text/json"
			});
		},
		selectMessage: function ( ) {
			var d = document.getElementById('messageViewPaneDiv');
			var data = dojo.widget.byId('messagesTable').getSelectedData();
			d.innerHTML = '<tt>' + data.content.replace(/\n/g, "<br/>\n").replace(/\\/g, "") + '</tt>';
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
		modifyTag: function ( ) {
			var msg = this.getSelectedMessage();
			if ( typeof msg == 'undefined' ) {
				alert("<!--{t|escape:'javascript'}-->A message must be selected.<!--{/t}-->");
			} else {
				var newTag = dojo.widget.byId('messagesTag').getValue();
				if ( newTag == 'INBOX' ) { newTag = ''; }
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
						dojo.widget.byId('messagesTag').setValue( o.currentLocation );
						o.loadMessages();
					},
					mimetype: "text/json"
				});
			}
		},
		selectTagView: function ( ) {
			o.currentLocation = dojo.widget.byId('messagesTag').getValue();
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
		dojo.widget.byId('messagesTag').setValue( 'INBOX' );
		o.loadMessages();
		dojo.event.connect(dojo.widget.byId('messagesTable'), "onSelect", o, "selectMessage");
		dojo.event.connect(dojo.widget.byId('messageNewButton'), "onClick", o, "newMessage");
		dojo.event.connect(dojo.widget.byId('messageDeleteButton'), "onClick", o, "deleteMessage");
		dojo.event.connect(dojo.widget.byId('messageMultipleDeleteButton'), "onClick", o, "deleteMessages");
		dojo.event.connect(dojo.widget.byId('messageMoveButton'), "onClick", o, "modifyTag");
		dojo.event.connect(dojo.widget.byId('messageTagButton'), "onClick", o, "selectTagView");
		try {
			var x = dojo.widget.byId( 'messagesTablePane' );
			var node = x.containerNode || x.domNode;
			var h = parseInt( node.style.height ) - 45;
			document.getElementById( 'messagesTableBody' ).style.height = h + 'px';
		} catch ( e ) { }
	});
	_container_.addOnUnload(function(){
		dojo.event.disconnect(dojo.widget.byId('messagesTable'), "onSelect", o, "selectMessage");
		dojo.event.disconnect(dojo.widget.byId('messageNewButton'), "onClick", o, "newMessage");
		dojo.event.disconnect(dojo.widget.byId('messageDeleteButton'), "onClick", o, "deleteMessage");
		dojo.event.disconnect(dojo.widget.byId('messageMultipleDeleteButton'), "onClick", o, "deleteMessages");
		dojo.event.disconnect(dojo.widget.byId('messageMoveButton'), "onClick", o, "modifyTag");
		dojo.event.disconnect(dojo.widget.byId('messageTagButton'), "onClick", o, "selectTagView");
	});

</script>

<h3><!--{t}-->Messaging<!--{/t}--></h3>

<div dojoType="SplitContainer" orientation="vertical" sizerWidth="5" activeSizing="0" layoutAlign="client" style="height: 100%;">

	<div dojoType="ContentPane" executeScripts="true" sizeMin="30" sizeShare="50" style="height: 50%;" widgetId="messagesTablePane" id="messageTablePane">

		<div id="messagesBar">
			<table border="0"><tr>
				<td width="30">
					<button dojoType="Button" class="messageButton" id="messageNewButton" widgetId="messageNewButton">
						<img src="<!--{$htdocs}-->/images/teak/summary_envelope.16x16.png" border="0" height="16" width="16" />
					</button>
				</td>
				<td width="30">
					<button dojoType="Button" class="messageButton" id="messageDeleteButton" widgetId="messageDeleteButton">
						<img src="<!--{$htdocs}-->/images/teak/summary_delete.16x16.png" border="0" height="16" width="16" />
					</button>
				</td>
				<td width="30">
					<button dojoType="Button" class="messageButton" id="messageMultipleDeleteButton" widgetId="messageMultipleDeleteButton">
						<img src="<!--{$htdocs}-->/images/teak/summary_delete_2.16x16.png" border="0" height="16" width="16" />
					</button>
				</td>
				<td width="100">
					<!--{t}-->Location<!--{/t}-->:
				</td>
				<td width="150">
					<input dojoType="ComboBox"
					 id="messagesTag" widgetId="messagesTag"
					 value=""
					 style="width: 150px;"
					 mode="remote"
					 dataUrl="<!--{$relay}-->/org.freemedsoftware.module.MessagesModule.MessageTags"
					 />
				</td>
				<td width="30">
					<button dojoType="Button" class="messageButton" id="messageMoveButton" widgetId="messageMoveButton">
						<img src="<!--{$htdocs}-->/images/teak/summary_modify.16x16.png" border="0" height="16" width="16" />
					</button>
				</td>
				<td width="30">
					<button dojoType="Button" class="messageButton" id="messageTagButton" widgetId="messageTagButton">
						<img src="<!--{$htdocs}-->/images/teak/summary_view.16x16.png" border="0" height="16" width="16" />
					</button>
				</td>
				<td></td>
			</tr></table>
		</div>

		<div class="tableContainer">
			<table dojoType="FilteringTable" id="messagesTable" widgetId="messagesTable" headClass="fixedHeader"
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
			<tbody id="messagesTableBody"></tbody>
			</table>
		</div>

	</div>

	<div dojoType="ContentPane" executeScripts="true" sizeMin="30" sizeShare="50" id="messagesViewPane" style="overflow: scroll; background-color: #ffffff;">
		<div id="messageViewPaneDiv"></div>
	</div>
</div>

