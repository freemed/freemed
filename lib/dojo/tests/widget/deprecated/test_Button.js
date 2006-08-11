/*
	Copyright (c) 2004-2006, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/community/licensing.shtml
*/

dojo.require("dojo.widget.Button");

function test_button_ctor(){
	var b1 = new dojo.widget.Button();

	jum.assertTrue("test10", typeof b1 == "object");
	jum.assertTrue("test20", b1.widgetType == "Button");
	jum.assertTrue("test21", typeof b1["attachProperty"] == "undefined");
}
