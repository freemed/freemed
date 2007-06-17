dojo.provide("mywidgets.widget.ModalAlert");

dojo.require("dojo.widget.FloatingPane");

dojo.declare("mywidgets.widget.ModalAlert", null,
	function(params){
		this.widgetId = (params.widgetId?params.widgetId:this.widgetId);
		this.title = (params.title?params.title:this.title);
		this.iconSrc = (params.iconSrc?params.iconSrc:this.iconSrc);
		this.alertText = (params.alertText?params.alertText:this.alertText);
		this.width = (params.width?params.width:this.width);
		this.height = (params.height?params.height:this.height);
		
		this.execute();
	},
	{
	widgetId: "modalAlert",
	title: "Attention",
	iconSrc: "",
	alertText: "",
	width: "350px",
	height: "150px",
	
	execute: function(){
		var button = "<br /><br /><br /><p align=center><button style=\"width:60px;\" onClick=\"dojo.widget.byId('" + this.widgetId + "').hide();\">OK</button></p>";
		if(!dojo.widget.byId(this.widgetId)){
			var div = document.createElement("div");
			div.style.position="absolute";
			div.innerHTML = this.alertText + button;
			
			dojo.body().appendChild(div);
			div.style.width = this.width;
			div.style.height = this.height;
			
			var params = {
				widgetId:this.widgetId,
				title:this.title,
				iconSrc:this.iconSrc,
				toggle:"fade",
				resizable:false,
				windowState:"normal",
				hasShadow:true
			};
			var widget = dojo.widget.createWidget("dojo:ModalFloatingPane", params, div);
		}else{
			dojo.byId(this.widgetId+'_container').innerHTML = this.alertText + button;
			dojo.widget.byId(this.widgetId).show();
		}
	}
});
