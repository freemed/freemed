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

	File:	org.freemedsoftware.widget.photoid

	Reusable photographic ID widget.

	Parameters:

		$float - If set as right or left, will float the area

*}-->
<div id="patientPhotoIdContainerDiv" class="patientEmrWidgetContainer" style="<!--{if $float}-->float:<!--{$float}-->;<!--{/if}-->">
	<div align="center" width="100%" class="patientEmrWidgetHeader"><b><!--{t}-->Photo<!--{/t}--></b></div>
	<center>
	<div id="patientTagContainerInnerDiv"><img src="<!--{$relay}-->/org.freemedsoftware.module.PhotographicIdentification.GetPhotoID?param0=<!--{$patient}-->&dojo.nocache=<!--{$smarty.now|date_format:"%s"}-->" style="width: 230px; height: auto;" border="0" /></div>
	</center>
</div>

