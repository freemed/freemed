<?php
 // $Id$
 // $Author$
 // $Log$
 // Revision 1.1  2001/12/14 16:35:38  rufustfirefly
 // renamed from soap_* to xmlrpc_* (since it's really XMLRPC, not SOAP)
 //
 // Revision 1.1  2001/11/20 15:02:45  rufustfirefly
 // added SOAP/XMLRPC services provider
 //

//----- Load neccesary headers
include_once ("lib/freemed.php");
include_once ("lib/xmlrpc_services.php");

//----- Add services (done in lib/soap_services.php)

//----- Run SOAP (XML RPC) server
$XMLRPC_SERVER = new xmlrpc_server( $XMLRPC_METHODS );

?>
