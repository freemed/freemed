/*
	Copyright (c) 2004-2006, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/community/licensing.shtml
*/

dojo.require("dojo.lang.declare");

function test_lang_declare() {
	dojo.declare('my.classes.foo', null, {
		instanceId: [ 'bad' ], // make sure we test a non-primitive									 
		initializer: function(arg) {
			this.instanceId = [ 'foo' ]; // this must supercede the prototype in every instance
		},
		protoId: 'foo',
		getProtoId: function() {
			var ancestorId = this.inherited('getProtoId', arguments);
			// NOTE: _getPropContext is not intended for public usage except in very rare cases
			return "I am a " + this._getPropContext().protoId + (ancestorId ? " and " + ancestorId : '');
		},
		getInstanceId: function(extra) {
			var ancestorId = this.inherited('getInstanceId', [ extra ]);
			return "a " + this.instanceId[0] + (ancestorId ? " is " + ancestorId : '');
		},
		getId: function() {
			return "I am a foo";
		},
		method: function() {
			return "A method in foo";
		}
	});
	jum.assertEquals("30", "function", typeof my.classes.foo);

	dojo.declare('my.classes.bar', my.classes.foo, {
		initializer: function(arg) {
			this.instanceId = [ 'bar' ]; // this must supercede the prototype in every instance
		},
		protoId: 'bar',
		getId: function(extra) {
			return "I am a bar and " + this.inherited('getId', [ extra ]);
		}
	});
	jum.assertEquals("31", "function", typeof my.classes.bar);
	
	b = new my.classes.bar();
	jum.assertEquals("32", "object", typeof b);
	
	dojo.declare('my.classes.zot', my.classes.bar, {
		initializer: function(arg) {
			dojo.debug('zot: initializing instance' + (arg ? ' [' + arg + ']' : '')); 
			this.instanceId = [ 'zot' ]; // this must supercede the prototype in every instance
		},
		protoId: 'zot',
		getId: function(extra) {
			return "I am a zot and " + this.inherited('getId', [ extra ]);
		}
	});
	jum.assertEquals("33", "function", typeof my.classes.zot);
	
	f = new my.classes.foo();
	jum.assertEquals("34", "object", typeof f);
	
	z = new my.classes.zot("with an argument");
	jum.assertEquals("35", "object", typeof z);
	
	// getId tests 'inherited' over generations
	// getInstanceId ensures that instance properties are really per-instance
	// getProtoId tests the prototype descent algorithm
	
	jum.assertEquals("36.1", "I am a foo", f.getId());
	jum.assertEquals("36.2", "a foo", f.getInstanceId());
	jum.assertEquals("36.3", "I am a foo", f.getProtoId());
	
	jum.assertEquals("37.1", "I am a bar and I am a foo", b.getId());
	jum.assertEquals("37.2", "a bar is a bar", b.getInstanceId());
	jum.assertEquals("37.3", "I am a bar and I am a foo", b.getProtoId());
	
	jum.assertEquals("38.1", "I am a zot and I am a bar and I am a foo", z.getId());
	jum.assertEquals("38.2", "a zot is a zot is a zot", z.getInstanceId());
	jum.assertEquals("38.3", "I am a zot and I am a bar and I am a foo", z.getProtoId());
	
	jum.assertEquals("39", z.inherited("method"), "A method in foo");
}