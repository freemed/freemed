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

package org.freemedsoftware.gwt.client.widget;

import com.google.gwt.user.client.ui.ChangeListener;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.SuggestBox;
import com.google.gwt.user.client.ui.SuggestOracle;
import com.google.gwt.user.client.ui.Widget;
import com.google.gwt.user.client.rpc.IsSerializable;
import java.util.*;
import java.lang.StringBuffer;

public class PatientWidget extends Composite {

	protected int value = 0;
    /**
     * @gwt.typeArgs <java.lang.String, java.lang.String>
     */
	protected HashMap map;
	
	public PatientWidget() {

		final SuggestOracle suggestOracle = new SuggestOracle() {
			ArrayList items = new ArrayList();

		     final class StartsWithSuggestion implements Suggestion, IsSerializable {
		             private String _value;
		             private String _displayString;

		             /**
		              * Constructor used by RPC.
		              */
		             public StartsWithSuggestion() { }

		             /**
		              * Constructor for <code>StartsWithSuggestion</code>.
		              */
		             public StartsWithSuggestion(String value, String displayString) {
		                 _value = value;
		                 _displayString = displayString;
		             }

		             public String getDisplayString() {
		                 return _displayString;
		             }

		             public String getReplacementString() {
		            	 return _displayString;
		             }
		             
		             public Object getValue() {
		                 return _value;
		             }
		         } 
			
		    private StartsWithSuggestion getFormattedSuggestion(String query,String suggestion) {
		    	StringBuffer formattedSuggestion = new StringBuffer()
		    		            .append("<strong>")
		    		            .append(suggestion.substring(0, query.length()))
		    		            .append("</strong>")
		    		            .append(suggestion.substring(query.length()));
		    	return new StartsWithSuggestion(suggestion, formattedSuggestion.toString());
		    }
			
			/**
			 * 
			 * @param query
			 * @param limit
			 * @return
			 * @gwt.typeArgs <java.lang.String>
			 */
		    private List getItems(String query, int limit)
		    {
		    	/**
		    	 * @gwt.typeArgs <java.lang.String>
		    	 */
		        ArrayList/*<StartsWithSuggestion>*/ matches = new ArrayList();
		        for (int i = 0; i < items.size() && matches.size() < limit; i++) {
		            if (query.matches(((String) items.get(i)).substring(0, query.length()).toLowerCase())) {
		                matches.add(getFormattedSuggestion(query, (String) items.get(i)));
		            }
		        }
		        //Log.debug("found " + matches.size() + " matches for query " + query);
		        return matches;
		    }


			public void requestSuggestions(SuggestOracle.Request request, SuggestOracle.Callback callback) {
				/**
				 * @gwt.typeArgs <StartsWithSuggestion>
				 */
		        final List/*<StartsWithSuggestion>*/ suggestions =
		        	getItems(request.getQuery().toLowerCase(), request.getLimit());
		        	        Response response = new Response(suggestions);
		        	        callback.onSuggestionsReady(request, response); 
			}
			
		};
		final SuggestBox suggestBox = new SuggestBox(suggestOracle);
		initWidget(suggestBox);
		suggestBox.addChangeListener(new ChangeListener() {
			public void onChange(final Widget sender) {
			}
		});
	}

	/**
	 * Get integer value of currently selected patient.
	 * @return Current selected patient value
	 */
	public int getValue() {
		return value;
	}
	
}
