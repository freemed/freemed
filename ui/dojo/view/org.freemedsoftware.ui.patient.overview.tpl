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

<!--{if $MODE ne 'widget'}-->
<!--{include file="org.freemedsoftware.ui.framework.tpl"}-->
<!--{/if}-->

<script language="javascript">
	dojo.require("dojo.widget.DropdownContainer");
	dojo.addOnLoad(function(){
		dojo.widget.byId('contactDropdown').inputNode.style.display = 'none';
	});
</script>

<style type="text/css">
	#patientInfoBar {
		border: 1px solid #555555;
		background-color: #ccccff;
		padding: 5px;
		-moz-border-radius-bottomright: 15px;
		-moz-border-radius-bottomleft: 15px;
	}
</style>

<div id="patientInfoBar">
	<table width="100%" border="0" cellspacing="0" cellpadding="3"><tr>
	<td><b>Patient : </b> <!--{method namespace="org.freemedsoftware.api.PatientInterface.ToText" param="$patient"}--></td>
		<!--{* Form a contact "box" *}-->
	<td>
	<!--{if $record.ptprefcontact == 'home'}-->
		H: <!--{$record.pthphone}-->
	<!--{elseif $record.ptprefcontact == 'work'}-->
		W: <!--{$record.ptwphone}-->
	<!--{elseif $record.ptprefcontact == 'mobile'}-->
		C: <!--{$record.ptwphone}-->
	<!--{elseif $record.ptprefcontact == 'email'}-->
		<!--{$record.ptemail}-->
	<!--{else}-->
		<i><!--{t}-->no contact<!--{/t}--></i>
	<!--{/if}-->
	<div dojoType="DropdownContainer" widgetId="contactDropdown" id="contactDropdown">
		<div class="infoBox">
			<!--{if $record.pthphone ne ''}--><div>H: <!--{$record.pthphone}--></div><!--{/if}-->
			<!--{if $record.ptwphone ne ''}--><div>W: <!--{$record.ptwphone}--></div><!--{/if}-->
			<!--{if $record.ptmphone ne ''}--><div>C: <!--{$record.ptmphone}--></div><!--{/if}-->
			<!--{if $record.ptemail ne ''}--><div><!--{$record.ptemail}--></div><!--{/if}-->
			<hr/>
			<div><!--{$record.ptaddr1|escape}--></div>
			<!--{if $record.ptaddr2 ne ''}--><div><!--{$record.ptaddr2|escape}--></div><!--{/if}-->
			<div><!--{$record.ptcity|escape}-->, <!--{$record.ptstate}--> <!--{$record.ptzip}--></div>
		</div>
	</div></td>
		<!--{* Icon bar for easy actions *}-->
	<td>
		<span onClick="freemedLoad('<!--{$base_uri}-->/controller.php/<!--{$ui}-->/org.freemedsoftware.controller.patient.form?patient=<!--{$patient}-->');"><img src="<!--{$htdocs}-->/images/summary_modify.png" border="0" alt="<!--{t}-->Modify Patient Information<!--{/t}-->" /></span>
	</td>
	</tr></table>
</div>

<table border="0"><tr><td valign="top">
<!--{include file="org.freemedsoftware.widget.patientemrattachments.tpl" patient=$patient}-->
</td><td width="250" valign="top">
<!--{include file="org.freemedsoftware.widget.patienttags.tpl" patient=$patient}-->
</td></tr></table>

<!--{if $MODE ne 'widget'}-->
<!--{include file="org.freemedsoftware.ui.footer.tpl"}-->
<!--{/if}-->

