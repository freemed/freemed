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
					param0: val
				},
				url: '<!--{$relay}-->/org.freemedsoftware.module.MessagesModule.GetAllByTag',
				error: function() { },
				load: function( type, data, evt ) {
					dojo.widget.byId('messagesTable').store.setData( data );
				},
				mimetype: "text/json"
			});
		},
		selectMessage: function ( ) {
			var d = document.getElementById('messageViewPaneDiv');
			var data = dojo.widget.byId('messagesTable').getSelectedData();
			d.innerHTML = '<pre>' + data.content + '</pre>';
		},
		newMessage: function ( ) {
			dojo.widget.byId('newMessageDialog').show();
			document.getElementById('msgsubject').focus();
		},
		deleteMessage: function ( ) {
			var msg = this.getSelectedMessage();
			if ( typeof msg == 'undefined' ) {
				alert("<!--{t}-->A message must be selected.<!--{/t}-->");
			} else {
				dojo.io.bind({
					method: 'POST',
					content: {
						param0: msg.id
					},
					url: '<!--{$relay}-->/org.freemedsoftware.module.MessagesModule.del',
					load: function( type, data, evt ) {
						o.loadMessages();
					},
					mimetype: "text/json"
				});
			}
		},
		modifyTag: function ( ) {
			var msg = this.getSelectedMessage();
			if ( typeof msg == 'undefined' ) {
				alert("<!--{t}-->A message must be selected.<!--{/t}-->");
			} else {
				alert('modify tag ' + dojo.widget.byId('messagesTag').getValue() );
			}
		},
		selectTagView: function ( ) {
			var msg = this.getSelectedMessage();
			if ( typeof msg == 'undefined' ) {
				alert("<!--{t}-->A message must be selected.<!--{/t}-->");
			} else {
				alert('select tag view');
			}
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
					//alert("<!--{t}-->Unable to complete.<!--{/t}-->");
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
		dojo.event.connect(dojo.widget.byId('messageMoveButton'), "onClick", o, "modifyTag");
		dojo.event.connect(dojo.widget.byId('messageTagButton'), "onClick", o, "selectTagView");
		dojo.event.connect(dojo.widget.byId('createMessageButton'), "onClick", o, "createMessageCallback");
	});
	_container_.addOnUnload(function(){
		dojo.event.disconnect(dojo.widget.byId('messagesTable'), "onSelect", o, "selectMessage");
		dojo.event.disconnect(dojo.widget.byId('messageNewButton'), "onClick", o, "newMessage");
		dojo.event.disconnect(dojo.widget.byId('messageDeleteButton'), "onClick", o, "deleteMessage");
		dojo.event.disconnect(dojo.widget.byId('messageMoveButton'), "onClick", o, "modifyTag");
		dojo.event.disconnect(dojo.widget.byId('messageTagButton'), "onClick", o, "selectTagView");
		dojo.event.disconnect(dojo.widget.byId('createMessageButton'), "onClick", o, "createMessageCallback");
	});

</script>

<h3><!--{t}-->Messaging<!--{/t}--></h3>

<div dojoType="SplitContainer" orientation="vertical" sizerWidth="5" activeSizing="0" layoutAlign="client" style="height: 100%;">

	<div dojoType="ContentPane" executeScripts="true" sizeMin="30" sizeShare="50" style="height: 100%;">

		<div id="messagesBar">
			<table border="0"><tr>
				<td width="30">
					<button dojoType="Button" class="messageButton" id="messageNewButton" widgetId="messageNewButton">
						<img src="<!--{$htdocs}-->/images/summary_envelope.png" border="0" />
					</button>
				</td>
				<td width="30">
					<button dojoType="Button" class="messageButton" id="messageDeleteButton" widgetId="messageDeleteButton">
						<img src="<!--{$htdocs}-->/images/summary_delete.png" border="0" />
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
						<img src="<!--{$htdocs}-->/images/summary_modify.png" border="0" />
					</button>
				</td>
				<td width="30">
					<button dojoType="Button" class="messageButton" id="messageTagButton" widgetId="messageTagButton">
						<img src="<!--{$htdocs}-->/images/summary_view.png" border="0" />
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
					<th field="stamp_mdy" dataType="Date"><!--{t}-->Date<!--{/t}--></th>
					<th field="from_user" dataType="String"><!--{t}-->From<!--{/t}--></th>
					<th field="regarding" dataType="String"><!--{t}-->Regarding<!--{/t}--></th>
					<th field="subject" dataType="String"><!--{t}-->Note<!--{/t}--></th>
				</tr>
			</thead>
			<tbody></tbody>
			</table>
		</div>

	</div>

	<div dojoType="ContentPane" executeScripts="true" sizeMin="30" sizeShare="50" id="messagesViewPane" style="overflow: scroll;">
		<div id="messageViewPaneDiv"></div>
	</div>
</div>

<div dojoType="Dialog" id="newMessageDialog" widgetId="newMessageDialog" style="display: none;">
	<h3><!--{t}-->Create Message<!--{/t}--></h3>
	<table border="0">
		<tr>
			<td valign="top" align="right"><!--{t}-->Recipient<!--{/t}--> : </td>
			<td>
				<select dojoType="Select"
				 id="msgfor" widgetId="msgfor"
				 style="width: 150px;"
				 mode="remote" autocomplete="false"
				 dataUrl="<!--{$relay}-->/org.freemedsoftware.api.UserInterface.GetUsers?param0=%{searchString}"
				 /></select>
			</td>
		</tr>
		<tr>
			<td valign="top" align="right"><!--{t}-->Patient<!--{/t}--> : </td>
			<td>
				<!--{include file="org.freemedsoftware.widget.patientpicklist.tpl" varname="msgpatient"}-->
			</td>
		</tr>
		<tr>
			<td valign="top" align="right"><!--{t}-->Subject<!--{/t}--> : </td>
			<td><input type="text" id="msgsubject" name="msgsubject" size="50" value="" /></td>
		</tr>
		<tr>
			<td valign="top" align="right"><!--{t}-->Message<!--{/t}--> : </td>
			<td><textarea dojoType="Editor2" id="msgtext" widgetId="msgtext" cols="60" rows="6"></textarea></td>
		</tr>
	</table>
	<div align="center">
		<table border="0"><tr>
			<td align="right"><button dojoType="Button" id="createMessageButton" widgetId="createMessageButton"><!--{t}-->Create<!--{/t}--></button></td>
			<td align="left"><button dojoType="Button" onClick="dojo.widget.byId('newMessageDialog').hide();"><!--{t}-->Cancel<!--{/t}--></button></td>
		</tr></table>
	</div>
</div>
