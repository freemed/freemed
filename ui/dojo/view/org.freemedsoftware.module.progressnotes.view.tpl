<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
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

	#pnotesScroll {
		overflow-y: auto;
		}

</style>

<script type="text/javascript">
	//	Functions
	var o = {
		data: {},
		loadView: function() {
			var recordId = "<!--{$id|escape}-->";
			dojo.io.bind({
				method: 'POST',
				content: {
					param0: recordId
				},
				url: '<!--{$relay}-->/org.freemedsoftware.module.progressnotes.GetRecord',
				load: function( type, data, evt ) {
					o.data = data;
					o.populate( 'note.relevantDate', 'pnotesdt' );
					o.populate( 'note.subjective', 'pnotes_S' );
					o.populate( 'note.objective', 'pnotes_O' );
					o.populate( 'note.assessment', 'pnotes_A' );
					o.populate( 'note.plan', 'pnotes_P' );
					o.populate( 'note.interval', 'pnotes_I' );
					o.populate( 'note.education', 'pnotes_E' );
					o.populate( 'note.rx', 'pnotes_R' );
				},
				mimetype: "text/json"
			});
		},
		populate: function ( domName, keyName ) {
			document.getElementById(domName).style.display = 'none';
			try {
				if ( typeof o.data[keyName] != 'undefined' ) {
					if ( o.data[keyName].length > 1 ) {
						document.getElementById(domName).innerHTML += o.data[keyName];
						document.getElementById(domName).style.display = 'block';
					}
				}
			} catch (e) { }
		}
	};

	//	Initialization / Event Connection
	_container_.addOnLoad(function(){
		o.loadView( );
		try {
			var x = dojo.widget.byId( 'freemedContent' );
			var node = x.containerNode || x.domNode;
			var h = parseInt( node.style.height ) - 80;
			document.getElementById( 'pnotesScroll' ).style.height = h + 'px';
		} catch ( e ) { }
	});
	_container_.addOnUnload(function(){
	});

</script>

<style type="text/css">
	#viewClose {
		color: #555555;
		text-decoration: underline;
		}
	#viewClose:hover {
		color: #ff5555;
		cursor: pointer;
		}
	.notesContainer {
		padding: .5ex;
		border: 1px solid #555555;
		display: none;
		}
</style>

<!--{if $embed ne 1}-->
<h3><!--{t}-->Progress Note<!--{/t}--> [ <a onClick="freemedPatientContentLoad('<!--{$controller}-->/org.freemedsoftware.ui.patient.overview.default?patient=<!--{$patient}-->');" id="viewClose">X</a> ]</h3>
<!--{/if}-->

<div id="pnotesScroll">

<div><b><!--{t}-->Date<!--{/t}--></b> : <span id="note.relevantDate"></span><br/></div>

<div id="note.subjective" class="notesContainer">
	<h4><!--{t}-->Subjective<!--{/t}--></h4>
</div>
<div id="note.objective" class="notesContainer">
	<h4><!--{t}-->Objective<!--{/t}--></h4>
</div>
<div id="note.assessment" class="notesContainer">
	<h4><!--{t}-->Assessment<!--{/t}--></h4>
</div>
<div id="note.plan" class="notesContainer">
	<h4><!--{t}-->Plan<!--{/t}--></h4>
</div>
<div id="note.interval" class="notesContainer">
	<h4><!--{t}-->Interval<!--{/t}--></h4>
</div>
<div id="note.education" class="notesContainer">
	<h4><!--{t}-->Education<!--{/t}--></h4>
</div>
<div id="note.rx" class="notesContainer">
	<h4><!--{t}-->Rx<!--{/t}--></h4>
</div>

</div>

