dojo.provide("mywidgets.manifest");
dojo.require("dojo.string.extras");

dojo.registerNamespaceResolver("mywidgets",
	function(name){ 
		return "mywidgets.widget."+dojo.string.capitalize(name);
	}
);