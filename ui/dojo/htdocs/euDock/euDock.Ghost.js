/*
 * euDock.Ghost
 *
 * euDock 2.0 plugin
 *
 * Copyright (C) 2006 Parodi (Pier...) Eugenio <piercingslugx@inwind.it>
 *                                              http://eudock.jules.it
 *
 * This library is free software; you can redistribute it and/or             
 * modify it under the terms of the GNU Lesser General Public                
 * License as published by the Free Software Foundation; either              
 * version 2.1 of the License, or (at your option) any later version.        
 *                                                                           
 * This library is distributed in the hope that it will be useful,           
 * but WITHOUT ANY WARRANTY; without even the implied warranty of            
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU         
 * Lesser General Public License for more details.                           
 *                                                                           
 * You should have received a copy of the GNU Lesser General Public          
 * License along with this library; if not, write to the Free Software       
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

 /*
  *
  * This program is absolutely free...
  *           ...BUT...
  * If you modify(OR TRY TO DO THAT)this Source Code,
  * my SOUL will carry you some monstrous Nightmares
  *
  * Have a nice day
  * enjoy yourself.
  *          Pier...
  *
  * (Sorry but I'm Italian not an American writer)
  *                            (...a day Maybe...)
  */
  
if (!window.euPreloadImage)
	function euPreloadImage(a) {
		var d=document;
		if(d.images){
			if(!d.p) d.p=new Array();
			d.p.push(new Image());
			d.p[d.p.length-1].src=a;
		}
	};
	
euEnv.euGhostIE_Array = new Array();

/* 
 ****************************************
 ******    euGhost Object         *******
 ******     (START)               *******
 **************************************** 
 */
		function euGhost(id,args,container,onLoadFunc){
			this.id = id;
			
			this.setProperties = function(x,y,w,h){
				this.setPos(x,y);
				this.setDim(w,h);
			};
			
			this.setPos = function(x,y){
				this.setPosX(x);
				this.setPosY(y);
			};	
			
			this.setDim = function(w,h){
				this.setWidth(w);
				this.setHeight(h);
			};
			
			this.getPosX   = function() {return document.getElementById(this.id).style.left.replace(/[^0-9]/g,"");};			
			this.setPosX   = function(x) {
				document.getElementById(this.id).style.left=x+'px';
				document.getElementById(this.id+"_OVERDIV").style.left=x+'px';
				x=this.originalWidth/this.getWidth();
				if (this.shadowPos)
					if (y=Math.round(this.shadowPos.x/x))
						this.shadow.setPosX(x);
			};
			this.getPosY   = function() {return document.getElementById(this.id).style.top.replace(/[^0-9]/g,"");};	
			this.setPosY   = function(y) {
				document.getElementById(this.id).style.top=y+'px';
				document.getElementById(this.id+"_OVERDIV").style.top=y+'px';
				y=this.originalHeight/this.getHeight();
				if (this.shadowPos)
					if (y=Math.round(this.shadowPos.y/y))
						this.shadow.setPosY(y);
			};
			
			this.getWidth  = function() {return this.ghost.getWidth();};
			
			this.setWidth  = function(w){
				if (!this.originalWidth) return;
				var prop=this.originalWidth/w;
				this.ghost.setWidth(w);
				this.eyeball.setWidth(w);
				document.getElementById(this.id+"_OVERDIV").style.width=Math.round(w)+'px';
				if (this.shadow)
					this.shadow.setWidth(w);
				if (this.spotCoord1.eyespot_width)
					this.eyespot1.setWidth(this.spotCoord1.eyespot_width/prop); 
				if (this.spotCoord2.eyespot_width)					
					this.eyespot2.setWidth(this.spotCoord2.eyespot_width/prop);
			};
			
			this.getHeight  = function() {
				return this.ghost.getHeight();
			};
			
			this.setHeight = function(h){
				if (!this.originalHeight) return;
				var prop=this.originalHeight/h;
				this.ghost.setHeight(h);
				this.eyeball.setHeight(h);
				document.getElementById(this.id+"_OVERDIV").style.height=Math.round(h)+'px';
				if (this.shadow)
					this.shadow.setHeight(h);
				if (this.spotCoord1.eyespot_height)					
					this.eyespot1.setHeight(this.spotCoord1.eyespot_height/prop); 
				if (this.spotCoord2.eyespot_height)				
					this.eyespot2.setHeight(this.spotCoord2.eyespot_height/prop);
			};
			
			this.hide = function(){	
				if (this.shadow)
					this.shadow.hide();
				this.ghost.hide();   
				this.eyeball.hide();
				this.eyespot1.hide(); 
				this.eyespot2.hide(); 
			};			
			
			this.show = function(){
				if (this.shadow)
					this.shadow.show();
				this.ghost.show();   
				this.eyeball.show();
				this.eyespot1.show(); 
				this.eyespot2.show(); 
			};
			
			this.onLoadPrev = function(){
				if (this.originalWidth && this.originalHeight)return;
				if (this.ghost.onLoadPrev)
					this.ghost.onLoadPrev();
				this.originalWidth	= this.ghost.getWidth();
				this.originalHeight	= this.ghost.getHeight();
			};
			
			this.onLoadPrevGetEyeSpot = function(obj,vars){				
				if (vars.eyespot_height && vars.eyespot_width)return;
				if (obj.onLoadPrev)
					obj.onLoadPrev();
				vars.eyespot_width=obj.getWidth();
				vars.eyespot_height=obj.getHeight();
			};			
			
			this.setFading = function(fad){this.ghost.setFading(fad);};
			
			this.spotCoord1=args.spotCoord1;
			this.spotCoord2=args.spotCoord2;
			if (args.shadow)
				this.shadowPos=args.shadowPos;
			
			container.innerHTML   +="<div id='"+this.id+"' style='visibility:hidden;position:absolute;'></div>";
			var objContainer = document.getElementById(this.id);
			
			euEnv.euGhostIE_Array[id]=this;
			var bkPngObjIE = args.PngObjIE;
			if (args.shadow){
				args.image=args.shadow;
				euPreloadImage(args.image);
				args.PngObjIE=euImageNoFadingIE_PNG;			
				this.shadow= new euImage(id+"_shadow"   ,args,objContainer,"if (euEnv.euGhostIE_Array."+this.id+".shadow.onLoadPrev)euEnv.euGhostIE_Array."+this.id+".shadow.onLoadPrev();");
				euEnv.euGhostIE_Array[id+"_shadow"]=this.shadow;
			}
			args.image=args.ghost;
			euPreloadImage(args.image);
			args.PngObjIE=bkPngObjIE;
			this.ghost     = new euImage(id+"_ghost"    ,args,objContainer,onLoadFunc);
			euEnv.euGhostIE_Array[id+"_ghost"]=this.ghost;
			
			args.image=args.eyeball;
			euPreloadImage(args.image);
			args.PngObjIE=euImageNoFadingIE_PNG;	
			this.eyeball   = new euImage(id+"_eyeball"  ,args,objContainer,"if (euEnv.euGhostIE_Array."+this.id+".eyeball.onLoadPrev)euEnv.euGhostIE_Array."+this.id+".eyeball.onLoadPrev();");
			euEnv.euGhostIE_Array[id+"_eyeball"]=this.eyeball;
			
			args.image=args.eyespot_1;
			euPreloadImage(args.image);
			args.PngObjIE=euImageNoFadingIE_PNG;	
			this.eyespot1  = new euImage(id+"_eyespot_1",args,objContainer,"euEnv.euGhostIE_Array."+this.id+".onLoadPrevGetEyeSpot(euEnv.euGhostIE_Array."+this.id+".eyespot1,euEnv.euGhostIE_Array."+this.id+".spotCoord1)");
			euEnv.euGhostIE_Array[id+"_eyespot_1"]=this.eyespot1;
			
			args.image=args.eyespot_2;
			euPreloadImage(args.image);
			args.PngObjIE=euImageNoFadingIE_PNG;	
			this.eyespot2  = new euImage(id+"_eyespot_2",args,objContainer,"euEnv.euGhostIE_Array."+this.id+".onLoadPrevGetEyeSpot(euEnv.euGhostIE_Array."+this.id+".eyespot2,euEnv.euGhostIE_Array."+this.id+".spotCoord2)");
			euEnv.euGhostIE_Array[id+"_eyespot_2"]=this.eyespot2;
			
			container.innerHTML+="<div id='"+this.id+"_OVERDIV' style='position:absolute;'></div>";
			
			this.setEyeParams = function(spotCoord,idObj,x,y){
				if (!this.originalWidth && !this.originalHeight)return;
				var propW=this.originalWidth/this.getWidth();
				var propH=this.originalHeight/this.getHeight();
								
				var xx = x-spotCoord.x/propW-euIdObjLeft(document.getElementById(this.id));
				var yy = y-spotCoord.y/propH-euIdObjTop(document.getElementById(this.id));				
				
				var rad=spotCoord.rad/propW;
								
				if ((xx*xx+yy*yy)>(rad*rad)){
					var alpha;
					if (xx==0){
						if (yy>0)
							alpha=Math.PI/2;
						else
							alpha=-Math.PI/2;
					}else
						alpha = Math.atan(yy/xx);
					
					if (xx<0)					
						alpha+=Math.PI;

					xx=rad*Math.cos(alpha);
					yy=rad*Math.sin(alpha);
				}
				spotCoord.posX  = Math.round((spotCoord.x/propW+xx)-idObj.getWidth()/2);
				spotCoord.posY	= Math.round((spotCoord.y/propH+yy)-idObj.getHeight()/2);
				idObj.setPos(spotCoord.posX,spotCoord.posY);
			};
			
			this.mousePosX=100;
			this.mousePosY=100;	
					
			this.mouseMove = function(x,y){
				this.mousePosX=x;
				this.mousePosY=y;
				return false;				
			};			
			
			this.riposition = function(){
				if (!this.originalWidth)
					return;				
				this.setEyeParams(this.spotCoord1,this.eyespot1,this.mousePosX,this.mousePosY);
				this.setEyeParams(this.spotCoord2,this.eyespot2,this.mousePosX,this.mousePosY);
			};
		};
/* 
 ****************************************
 ******    euGhost Object         *******
 ******     (END)                 *******
 **************************************** 
 */