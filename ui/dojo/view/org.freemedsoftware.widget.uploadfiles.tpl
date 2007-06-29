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

	File:	org.freemedsoftware.widget.uploadfiles.tpl

	Reusable SWFUpload-based upload widget

	Parameters:

		$varname - Variable name to use for uploaded file. Defaults
		to 'file'.

		$relayPoint - Data relay point for upload.
*}-->

<!--{if not $varname}-->
<!--{assign var='varname' value='file'}-->
<!--{/if}-->

<!--{* ----- Callbacks for upload widget ----- *}-->
<script language="javascript">

try {

function fileQueued(file, queuelength) {
	var listingfiles = document.getElementById("SWFUploadFileListingFiles");

	if(!listingfiles.getElementsByTagName("ul")[0]) {
		
		var info = document.createElement("h4");
		info.appendChild(document.createTextNode("File queue"));
		
		listingfiles.appendChild(info);
		
		var ul = document.createElement("ul")
		listingfiles.appendChild(ul);
	}
	
	listingfiles = listingfiles.getElementsByTagName("ul")[0];
	
	var li = document.createElement("li");
	li.id = file.id;
	li.className = "SWFUploadFileItem";
	li.innerHTML = file.name + " <span class='progressBar' id='" + file.id + "progress'></span><a id='" + file.id + "deletebtn' class='cancelbtn' href='javascript:swfu.cancelFile(\"" + file.id + "\");'><!-- IE --></a>";

	listingfiles.appendChild(li);
	
	var queueinfo = document.getElementById("queueinfo");
	queueinfo.innerHTML = queuelength + " files queued";
	document.getElementById(swfu.movieName + "UploadBtn").style.display = "block";
	document.getElementById("cancelqueuebtn").style.display = "block";
}

function uploadFileCancelled(file, queuelength) {
	var li = document.getElementById(file.id);
	li.innerHTML = file.name + " - cancelled";
	li.className = "SWFUploadFileItem uploadCancelled";
	var queueinfo = document.getElementById("queueinfo");
	queueinfo.innerHTML = queuelength + " files queued";
}

function uploadFileStart(file, position, queuelength) {
	var div = document.getElementById("queueinfo");
	div.innerHTML = "<!--{t}-->Uploading file<!--{/t}--> " + position + " of " + queuelength;

	var li = document.getElementById(file.id);
	li.className += " fileUploading";
}

function uploadProgress(file, bytesLoaded) {
	var progress = document.getElementById(file.id + "progress");
	var percent = Math.ceil((bytesLoaded / file.size) * 200)
	progress.style.background = "#f0f0f0 url(<!--{$htdocs}-->/swfupload/progressbar.png) no-repeat -" + (200 - percent) + "px 0";
}

function uploadError(errno) {
	// SWFUpload.debug(errno);
}

function uploadFileComplete(file) {
	var li = document.getElementById(file.id);
	li.className = "SWFUploadFileItem uploadCompleted";
}

function cancelQueue() {
	swfu.cancelQueue();
	document.getElementById(swfu.movieName + "UploadBtn").style.display = "none";
	document.getElementById("cancelqueuebtn").style.display = "none";
}

function uploadQueueComplete(file) {
	var div = document.getElementById("queueinfo");
	div.innerHTML = "<!--{t}-->All files uploaded.<!--{/t}-->";
	document.getElementById("cancelqueuebtn").style.display = "none";
}

} catch (loadError) { }

_container_.addOnLoad(function(){
	var swfu = new SWFUpload({
		upload_script : "<!--{$relayPoint}-->",
		target : "SWFUploadTarget",
		flash_path : "<!--{$htdocs}-->/swfupload/SWFUpload.swf",
		allowed_filesize : 30720,	// 30 MB
		allowed_filetypes : "*.*",
		allowed_filetypes_description : "<!--{t}-->All files...<!--{/t}-->",
		browse_link_innerhtml : "<!--{t}-->Browse<!--{/t}-->",
		upload_link_innerhtml : "<!--{t}-->Upload queue<!--{/t}-->",
		browse_link_class : "swfuploadbtn browsebtn",
		upload_link_class : "swfuploadbtn uploadbtn",
		flash_loaded_callback : 'swfu.flashLoaded',
		upload_file_queued_callback : "fileQueued",
		upload_file_start_callback : 'uploadFileStart',
		upload_progress_callback : 'uploadProgress',
		upload_file_complete_callback : 'uploadFileComplete',
		upload_file_cancel_callback : 'uploadFileCancelled',
		upload_queue_complete_callback : 'uploadQueueComplete',
		upload_error_callback : 'uploadError',
		upload_cancel_callback : 'uploadCancel',
		auto_upload : true
	});
});

</script>



<style type="text/css">
	.swfuploadbtn {
		display: block;
		width: 100px;
		padding: 0 0 0 20px;
	}
	
	.browsebtn { background: url(<!--{$htdocs}-->/swfupload/add.png) no-repeat 0 4px; }
	.uploadbtn { 
		display: none;
		background: url(<!--{$htdocs}-->/swfupload/accept.png) no-repeat 0 4px; 
	}
	
	.cancelbtn { 
		display: block;
		width: 16px;
		height: 16px;
		float: right;
		background: url(<!--{$htdocs}-->/swfupload/cancel.png) no-repeat; 
	}
	
	#cancelqueuebtn {
		display: block;
		display: none;
		background: url(<!--{$htdocs}-->/swfupload/cancel.png) no-repeat 0 4px;
		margin: 10px 0;
	}
	
	#SWFUploadFileListingFiles ul {
		margin: 0;
		padding: 0;
		list-style: none;
	}

	.SWFUploadFileItem {
		display: block;
		width: 230px;
		height: 70px;
		float: left;
		background: #eaefea;
		margin: 0 10px 10px 0;
		padding: 5px;
	}

	.fileUploading { background: #fee727; }
	.uploadCompleted { background: #d2fa7c; }
	.uploadCancelled { background: #f77c7c; }
		
	.uploadCompleted .cancelbtn, .uploadCancelled .cancelbtn {
		display: none;
	}
		
	span.progressBar {
		width: 200px;
		display: block;
		font-size: 10px;
		height: 4px;
		margin-top: 2px;
		margin-bottom: 10px;
		background-color: #CCC;
	}
		
</style>

<div id="SWFUploadTarget">
	<form action="<!--{$relayPoint}-->" method="post" enctype="multipart/form-data">
		<input type="file" name="<!--{$varname}-->[]" id="<!--{$varname}-->[]" />
		<input type="submit" value="<!--{t}-->Upload files<!--{/t}-->" />
	</form>
</div>

<h4 id="queueinfo"><!--{t}-->Upload queue is empty.<!--{/t}--></h4>

<div id="SWFUploadFileListingFiles"></div>

<br class="clr" />

<a class="swfuploadbtn" id="cancelqueuebtn" href="javascript:cancelQueue();">Cancel queue</a>
