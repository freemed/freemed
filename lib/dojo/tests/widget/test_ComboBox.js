/*
	Copyright (c) 2004-2006, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/community/licensing.shtml
*/

dojo.require("dojo.widget.ComboBox");

var comboData = [
	["Alabama","AL"],
	["Alaska","AK"],
	["American Samoa","AS"],
	["Arizona","AZ"],
	["Arkansas","AR"],
	["Armed Forces Europe","AE"],
	["Armed Forces Pacific","AP"],
	["Armed Forces the Americas","AA"],
	["California","CA"],
	["Colorado","CO"],
	["Connecticut","CT"],
	["Delaware","DE"],
	["District of Columbia","DC"],
	["Federated States of Micronesia","FM"],
	["Florida","FL"],
	["Georgia","GA"],
	["Guam","GU"],
	["Hawaii","HI"],
	["Idaho","ID"],
	["Illinois","IL"],
	["Indiana","IN"],
	["Iowa","IA"],
	["Kansas","KS"],
	["Kentucky","KY"],
	["Louisiana","LA"],
	["Maine","ME"],
	["Marshall Islands","MH"],
	["Maryland","MD"],
	["Massachusetts","MA"],
	["Michigan","MI"],
	["Minnesota","MN"],
	["Mississippi","MS"],
	["Missouri","MO"],
	["Montana","MT"],
	["Nebraska","NE"],
	["Nevada","NV"],
	["New Hampshire","NH"],
	["New Jersey","NJ"],
	["New Mexico","NM"],
	["New York","NY"],
	["North Carolina","NC"],
	["North Dakota","ND"],
	["Northern Mariana Islands","MP"],
	["Ohio","OH"],
	["Oklahoma","OK"],
	["Oregon","OR"],
	["Pennsylvania","PA"],
	["Puerto Rico","PR"],
	["Rhode Island","RI"],
	["South Carolina","SC"],
	["South Dakota","SD"],
	["Tennessee","TN"],
	["Texas","TX"],
	["Utah","UT"],
	["Vermont","VT"],
	["Virgin Islands, U.S.","VI"],
	["Virginia","VA"],
	["Washington","WA"],
	["West Virginia","WV"],
	["Wisconsin","WI"],
	["Wyoming","WY"]
];

function test_combobox_ctor(){
	var b1 = new dojo.widget.ComboBox();

	jum.assertEquals("test10", typeof b1, "object");
	jum.assertEquals("test20", b1.widgetType, "ComboBox");
	jum.assertEquals("test21", typeof b1["attachProperty"], "undefined");
}

function test_combobox_dataprovider(){
	var box = new dojo.widget.ComboBox();

	jum.assertEquals("test30", typeof dojo.widget.ComboBoxDataProvider, "function");

	var provider = new dojo.widget.ComboBoxDataProvider();
	provider.setData(comboData);

	// test the results of our search
	var searchTester = function(data){
		var expectedReturns = [
			["Washington","WA"],
			["West Virginia","WV"],
			["Wisconsin","WI"],
			["Wyoming","WY"]
		];

		var expectedLabels = [];
		for(var x=0; x<expectedReturns.length; x++){
			expectedLabels.push(expectedReturns[x][0]);
		}
		jum.assertEquals("test40", data.length, 4);
		for(var x=0; x<data.length; x++){
			//jum.debug(data[x][0]);
			jum.assertTrue("testfoo", dojo.lang.find(expectedLabels, data[x][0]) != -1);
		}
	}

	dojo.event.connect(provider, "provideSearchResults", searchTester);
	provider.startSearch("W");
}
