/*
 * euDock.Image
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

if (!euEnv.imageBasePath)
	euEnv.imageBasePath = "./";
/* 
 ****************************************
 ******    euImage Object         *******
 ******     (START)               *******
 **************************************** 
 */
		function euImage(id,args,container,onLoadFunc){
			if (!args.PngObjIE)
				args.PngObjIE=euImageIE_PNG;
			if (typeof( window.innerWidth ) != 'number' && args.image.toLowerCase().indexOf("png")!=-1)
				return new args.PngObjIE(id,args,container,onLoadFunc);			
			
			this.id = id;
			euPreloadImage(args.image);	
			
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
			this.setPosX   = function(x) {document.getElementById(this.id).style.left=x+'px';};
			this.getPosY   = function() {return document.getElementById(this.id).style.top.replace(/[^0-9]/g,"");};	
			this.setPosY   = function(y) {document.getElementById(this.id).style.top=y+'px';};
			this.getWidth  = function() {return document.getElementById(this.id).width;};
			this.setWidth  = function(w){document.getElementById(this.id).width=Math.round(w);};
			this.getHeight  = function() {return document.getElementById(this.id).height;};		
			this.setHeight = function(h){document.getElementById(this.id).height=Math.round(h);};
			
			this.hide = function(){document.getElementById(this.id).style.visibility='hidden';};			
			this.show = function(){document.getElementById(this.id).style.visibility='visible';};			
			
			this.setFading = function(fad){
				fad=Math.round(fad);
				if (fad<0)
					fad=0;
				if (fad>100)
					fad=100;
       			document.getElementById(this.id).style.opacity = (fad/100);
       			document.getElementById(this.id).style.filter = "alpha(opacity="+(fad)+");";
			};
			
			container.innerHTML+="<img onLoad='"+onLoadFunc+";' id='"+this.id+"' src='"+args.image+"' style='position:absolute;visibility:hidden;'>";
		};
/* 
 ****************************************
 ******    euImage Object         *******
 ******     (END)                 *******
 **************************************** 
 */
 
 /* 
 ****************************************
 ******    euImageIE_PNG Object   *******
 ******     (START)               *******
 **************************************** 
 */
		function euImageIE_PNG(id,args,container,onLoadFunc){
			this.id = id;
			euPreloadImage(args.image);	
			
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
				};
			this.getPosY   = function() {return document.getElementById(this.id).style.top.replace(/[^0-9]/g,"");};	
			this.setPosY   = function(y) {
				document.getElementById(this.id).style.top=y+'px';
			};
			
			
			this.getWidth  = function() {if (!this.width)return 0;return this.width;};
			this.setWidth  = function(w){
				if (!this.width)return;
				this.width=Math.round(w);		
				document.getElementById(this.id).style.width=Math.round(w)+'px';
				document.getElementById(this.id+"_IMG").style.width=Math.round(w)+'px';				
				//if (document.getElementById(this.id).contentWindow.document.getElementById('IMG'))
				//	document.getElementById(this.id).contentWindow.document.getElementById('IMG').style.width=Math.round(w)+'px';
			};
			this.getHeight  = function() {if (!this.height)return 0;return this.height;};		
			this.setHeight = function(h){
				if (!this.height)return;
				this.height=Math.round(h);		
				document.getElementById(this.id).style.height=Math.round(h)+'px';
				document.getElementById(this.id+"_IMG").style.height=Math.round(h)+'px';				
				//if (document.getElementById(this.id).contentWindow.document.getElementById('IMG'))
				//	document.getElementById(this.id).contentWindow.document.getElementById('IMG').style.height=Math.round(h)+'px';
			};
			
			this.onLoadPrev = function(){
				if (this.width && this.height)return;
				this.width=document.getElementById(this.id+"_IMG_BAK").width;
				this.height=document.getElementById(this.id+"_IMG_BAK").height;
				document.getElementById(this.id+"_IMG_BAK").width=0;
				document.getElementById(this.id+"_IMG_BAK").height=0;							
				this.setDim(this.width,this.height);
			};
			
			this.hide = function(){document.getElementById(this.id).style.visibility='hidden';};			
			this.show = function(){document.getElementById(this.id).style.visibility='visible';if (this.width && this.height)this.setDim(this.width,this.height);};			
			
			this.setFading = function(fad){
				fad=Math.round(fad);
				if (fad<0)
					fad=0;
				if (fad>100)
					fad=100;
        			document.getElementById(this.id).style.opacity = (fad/100);
        			document.getElementById(this.id).style.filter = "alpha(opacity="+(fad)+");";
			};
			container.innerHTML+="<div id='"+this.id+"' style='position:absolute;'></div>";
			document.getElementById(this.id).innerHTML=	"<img src='"+euEnv.imageBasePath+"blank.gif' id='"+this.id+"_IMG' style=\"top:0px;left:0px;filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+args.image+"',sizingMethod='scale');position:absolute;\">";
			container.innerHTML+="<img onLoad='"+onLoadFunc+";' id='"+this.id+"_IMG_BAK' src='"+args.image+"' style='visibility:hidden;position:absolute;'>";		
		};
/* 
 ****************************************
 ******    euImageIE_PNG Object   *******
 ******     (END)                 *******
 **************************************** 
 */
 
 /* 
 ****************************************
 *****euImageNoFadingIE_PNG Object ******
 ******     (START)               *******
 **************************************** 
 */
		function euImageNoFadingIE_PNG(id,args,container,onLoadFunc){
			this.id = id;
			euPreloadImage(args.image);	
			
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
			this.setPosX   = function(x) {document.getElementById(this.id).style.left=x+'px';};
			this.getPosY   = function() {return document.getElementById(this.id).style.top.replace(/[^0-9]/g,"");};	
			this.setPosY   = function(y) {document.getElementById(this.id).style.top=y+'px';};
			
			this.getWidth  = function() {if (!this.width) return 0;return this.width;};
			this.setWidth  = function(w){if (!this.width) return;  this.width=Math.round(w);document.getElementById(this.id).style.width=Math.round(w)+'px';};
			this.getHeight  = function(){if (!this.height)return 0;return this.height;};		
			this.setHeight = function(h){if (!this.height)return;  this.height=Math.round(h);document.getElementById(this.id).style.height=Math.round(h)+'px';};
			
			this.hide = function(){document.getElementById(this.id).style.visibility='hidden';};			
			this.show = function(){document.getElementById(this.id).style.visibility='visible';};
			
			this.onLoadPrev = function(){
				if (this.width && this.height)return;
				this.width=document.getElementById(this.id+"_IMG_BAK").width;
				this.height=document.getElementById(this.id+"_IMG_BAK").height;
				document.getElementById(this.id+"_IMG_BAK").width=0;
				document.getElementById(this.id+"_IMG_BAK").height=0;							
				this.setDim(this.width,this.height);
			};			
			
			this.setFading = function(fad){};
			
		container.innerHTML+="<img src='"+euEnv.imageBasePath+"blank.gif' id='"+this.id+"' style='position:absolute;visibility:hidden;filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\""+args.image+"\",sizingMethod=\"scale\");' >";
		container.innerHTML+="<img onLoad='"+onLoadFunc+";' id='"+this.id+"_IMG_BAK' src='"+args.image+"' style='position:absolute;visibility:hidden;'>";
		};
/* 
 ****************************************
 *****euImageNoFadingIE_PNG Object ******
 ******     (END)                 *******
 **************************************** 
 */