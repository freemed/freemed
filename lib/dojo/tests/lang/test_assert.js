/*
	Copyright (c) 2004-2006, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/community/licensing.shtml
*/

dojo.require("dojo.lang.assert");

function test_lang_assert() {
	dojo.lang.assert(true);
	dojo.lang.assert(true, "400");
	dojo.lang.assert((1 == 1), "401");
	dojo.lang.assert("not a boolean value", "402");
	dojo.lang.assert(28, "403");

	var caught404 = false;
	try {
		dojo.lang.assert(false, "404");
	} catch (e) {
		caught404 = true;
	}
	jum.assertTrue("404", caught404);
	// dojo.log.debug("leaving test_lang_assert()");
}

function test_lang_assertType() {
	dojo.lang.assertType("foo", String, "410");
	dojo.lang.assertType(12345, Number, "411");
	dojo.lang.assertType(false, Boolean, "412");
	dojo.lang.assertType([6, 8], Array, "413");
	dojo.lang.assertType(dojo.lang.assertType, Function, "414");
	dojo.lang.assertType({foo: "bar"}, Object, "415");
	dojo.lang.assertType(new Date(), Date, "416");
	dojo.lang.assertType(new Error(), Error, "417");
	dojo.lang.assertType([6, 8], ["array", "optional"], "418");
	dojo.lang.assertType(null, ["array", "optional"], "419");

	var caught430 = false;
	try {
		dojo.lang.assertType(12345, Boolean, "430");
	} catch (e) {
		caught430 = true;
	}
	jum.assertTrue("430", caught430);

	var caught431 = false;
	try {
		dojo.lang.assertType("foo", [Number, Boolean, Object], "431");
	} catch (e) {
		caught431 = true;
	}
	jum.assertTrue("431", caught431);
	// dojo.log.debug("leaving test_lang_assertType()");
}

function test_lang_assertValidKeywords() {
	dojo.lang.assertValidKeywords({a: 1, b: 2}, ["a", "b"], "440");
	dojo.lang.assertValidKeywords({a: 1, b: 2}, ["a", "b", "c"], "441");
	dojo.lang.assertValidKeywords({foo: "iggy"}, ["foo"], "442");
	dojo.lang.assertValidKeywords({foo: "iggy"}, ["foo", "bar"], "443");
	dojo.lang.assertValidKeywords({foo: "iggy"}, {foo: null, bar: null}, "444");

	var caught450 = false;
	try {
		dojo.lang.assertValidKeywords({a: 1, b: 2, c: 3}, ["a", "b"], "450");
	} catch (e) {
		caught450 = true;
	}
	jum.assertTrue("450", caught450);

	// dojo.log.debug("leaving test_lang_assertValidKeywords()");
}
