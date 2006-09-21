<?php
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

include_once ( dirname(__FILE__).'/bootstrap.test.php' );

t("org.freemedsoftware.api.PatientInterface.picklist", CallMethod("org.freemedsoftware.api.PatientInterface.picklist", 'john'));
t("org.freemedsoftware.api.PatientInterface.ProceduresToBill", CallMethod("org.freemedsoftware.api.PatientInterface.ProceduresToBill"));
t("org.freemedsoftware.api.PatientInterface.ToText", CallMethod("org.freemedsoftware.api.PatientInterface.ToText", 1 ));

?>
