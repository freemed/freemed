{* Smarty *}
{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // Copyright (C) 1999-2006 FreeMED Software Foundation
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
*}
<script type="text/javascript">
                dojo.require("dojo.widget.TabContainer");
//              dojo.require("dojo.widget.Tooltip");
                dojo.require("dojo.widget.ContentPane");
                dojo.require("dojo.widget.Button");
</script>

<div dojoType="TabContainer" id="mainTabContainer" style="width: 100%; height: 85%;">
	<div dojoType="ContentPane" id="patientDemographicsPane" label="Demographics" href="{$base_uri}/controller.php/{$ui}/org.freemedsoftware.controller.patient.form?page=demographics">
	</div>
	<div dojoType="ContentPane" id="patientContactPane" label="Contact" href="{$base_uri}/controller.php/{$ui}/org.freemedsoftware.controller.patient.form?page=contact">
	</div>
</div>

