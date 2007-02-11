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

	File:	org.freemedsoftware.widget.supportpicklist

	Reusable SupportModule widget.

	Parameters:

		$varname - Variable name

		$module - Module class name

*}-->

<input dojoType="Select" value=""
	autocomplete="false"
	id="<!--{$varname|escape}-->_widget" widgetId="<!--{$varname|escape}-->_widget"
	setValue="if (arguments[0]) { document.getElementById('<!--{$varname|escape}-->').value = arguments[0]; }"
	style="width: 300px;"
	dataUrl="<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.module.<!--{$module|escape}-->.picklist?param0=%{searchString}"
	mode="remote" />
<input type="hidden" id="<!--{$varname|escape}-->" name="<!--{$varname|escape}-->" value="0" />

