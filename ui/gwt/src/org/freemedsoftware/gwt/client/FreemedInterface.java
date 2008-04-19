/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2008 FreeMED Software Foundation
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 */

package org.freemedsoftware.gwt.client;

import com.google.gwt.core.client.EntryPoint;
import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.RootPanel;
import com.google.gwt.user.client.ui.Widget;

import com.google.gwt.core.client.GWT;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.rpc.ServiceDefTarget;

import org.freemedsoftware.gwt.client.screen.*;

/* RPC classes */

import org.freemedsoftware.gwt.client.Api.UserInterface;
import org.freemedsoftware.gwt.client.Api.UserInterfaceAsync;

import org.freemedsoftware.gwt.client.Public.Login;
import org.freemedsoftware.gwt.client.Public.LoginAsync;
import org.freemedsoftware.gwt.client.Public.Protocol;
import org.freemedsoftware.gwt.client.Public.ProtocolAsync;

/**
 * Entry point classes define <code>onModuleLoad()</code>.
 */
public class FreemedInterface implements EntryPoint {

  /**
   * This is the entry point method.
   */
  public void onModuleLoad() {
	  MainScreen mainScreen = new MainScreen();
	  RootPanel.get("rootPanel").add(mainScreen);

	/*
	  
	 // OLD CODE BELOW ... KEEP FOR NOW ...
    final Button button = new Button("org.freemedsoftware.public.Login.Validate");
    final Label label = new Label();

    button.addClickListener(new ClickListener() {
      public void onClick(Widget sender) {
    	LoginAsync loginService = null;
    	try {
    		loginService = (LoginAsync) Util.getProxy("org.freemedsoftware.gwt.client.Public.Login");
    	} catch (Exception e) {
    		GWT.log( "Could not instantiate login proxy", e);
    	}
        button.setText( "Processing" );
        label.setText( "" );
        GWT.log( "Calling login service", null );
        loginService.Validate( "demo", "demo", new AsyncCallback() {
            public void onSuccess( Object result ) {
                button.setText( "org.freemedsoftware.public.Login.Validate" );
                label.setText( "onSuccess [accepted username and password]" );
                if ( (Boolean) result == java.lang.Boolean.TRUE ) {
                    label.setText( "onSuccess [accepted username and password]" );
                } else {
                    label.setText( "onSuccess [denied username and password]" );
                }
            }
            public void onFailure( Throwable caught ) {
                GWT.log( "Error", caught );
                button.setText( "org.freemedsoftware.public.Login.Validate" );
                //label.setText( "onFailure" );
                label.setText( caught.getCause() + ": " + caught.getMessage() );
            }
          });
      }
    });

    RootPanel.get("slot1").add(button);
    RootPanel.get("slot2").add(label);
    */
  }
}

