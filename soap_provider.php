<?php
 // $Id$
 // $Author$
 // $Log$
 // Revision 1.1  2001/11/20 15:02:45  rufustfirefly
 // added SOAP/XMLRPC services provider
 //

//----- Load neccesary headers
include_once ("lib/freemed.php");
include_once ("lib/soap_services.php");

//----- Add services (done in lib/soap_services.php)

//----- Run SOAP (XML RPC) server
$SOAP_SERVER = new xmlrpc_server( $XMLRPC_METHODS );

?>
