<?php
 // $Id$
 // $Author$
 // $Log$
 // Revision 1.1  2001/11/01 16:34:28  rufustfirefly
 // removed freemed_close_db, added wddx_provider (STUB)
 //

//----- Load neccesary headers
include_once ("lib/freemed.php");
include_once ("lib/wddx_services.php");

//----- Add services (done in wddx_services.php)

//----- Run WDDX server
wddx_server();

?>
