<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2008 FreeMED Software Foundation
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

require_once(GWTPHP_DIR.'/RemoteServiceServlet.class.php');
require_once(GWTPHP_DIR.'/exceptions/AuthenticationException.class.php');

// Class: org.freemedsoftware.core.AuthenticatedRemoteServiceServlet
//
//	Class to extend GWTPHP's RemoteServiceServlet to handle FreeMED's
//	authentication scheme
//

class AuthenticatedRemoteServiceServlet extends RemoteServiceServlet {

    /**
     * Override this method to examine the decoded request that will be
     * processing by RemoteServiceServlet. The default implementation does nothing and need
     * not be called by subclasses.
     * FOCUS: this method is used only in PHP implementation of GWT RemoteServiceServlet
     */
    public function onAfterRequestDecoded(RPCRequest $rpcRequest) {
        if ( ! $this->checkAuthenticationPolicy( $rpcRequest->getMethod()->getDeclaringMappedClass()->getMappedName() ) ) {
		syslog(LOG_INFO,  $rpcRequest->getMethod()->getDeclaringMappedClass()->getMappedName() );
            $ex = new AuthenticationException( );
            throw new AuthenticationException( );
        }
    }

    protected function checkAuthenticationPolicy( $methodName ) {
        // Return false if attempting to access core namespace
        if ( strpos( $methodName, 'org.freemedsoftware.core.' ) !== false ) {
            return false;
        }

	// Always allow public methods
        if ( strpos( $methodName, 'org.freemedsoftware.gwt.client.Public' ) !== false ) {
            return true;
        }
        if ( strpos( $methodName, 'org.freemedsoftware.public.' ) !== false ) {
            return true;
        }

        // Check authentication
	if ( CallMethod( 'org.freemedsoftware.public.Login.LoggedIn' ) == false ) {
            syslog(LOG_INFO, "Not logged in, failing checkAuthenticationPolicy");
            return false;
        }

        // Default to returning "true"
        return true;
    } // end method checkAuthenticationPolicy

}

?>
