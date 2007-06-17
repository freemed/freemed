// load widget handler code
dojo.require("dojo.widget.*");

// Load user custom widgets
dojo.registerModulePath('mywidgets', '../mywidgets');
dojo.widget.manager.registerWidgetPackage('mywidgets.widget');