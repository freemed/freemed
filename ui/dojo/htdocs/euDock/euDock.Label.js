/*
 * euDock.Label
 *
 * euDock 2.0.04 plugin
 *
 * Copyright (C) 2006 Parodi (Pier...) Eugenio <eudock@inwind.it>
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
  

/* 
 ****************************************
 ******    euLabel Object         *******
 ******     (START)               *******
 **************************************** 
 */
		function euLabel(id,args,container,onLoadFunc){
			
			this.id = id;
			
			this.anchor  = euDOWN;	
			this.offsetX = 0;
			this.offsetY = 0;
			
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
			
			this.width=0;
			this.height=0;
			this.posX=0;
			this.posY=0;
			
			this.getPosX   = function() {return this.object.getPosX();};			
			this.setPosX   = function(x) {this.posX=x;this.object.setPosX(x);};
			this.getPosY   = function() {return this.object.getPosY();};	
			this.setPosY   = function(y) {this.posY=y;this.object.setPosY(y);
			};
			this.getWidth  = function() {return this.object.getWidth();};
			this.setWidth  = function(w){this.width=w;this.object.setWidth(w);};
			this.getHeight  = function() {return this.object.getHeight();};		
			this.setHeight = function(h){this.height=h;this.object.setHeight(h);};
			
			this.hide = function(){this.object.hide();document.getElementById(this.id).style.visibility='hidden';};			
			this.show = function(){this.object.show();document.getElementById(this.id).style.visibility='visible';};			
			
			this.setFading = function(fad){
				fad=Math.round(fad);
				if (fad<0)
					fad=0;
				if (fad>100)
					fad=100;
       			document.getElementById(this.id).style.opacity = (fad/100);
       			document.getElementById(this.id).style.filter = "alpha(opacity="+(fad)+");";
       			this.object.setFading(fad);
			};
			
			this.onLoadPrev = function(){
				if (this.object.onLoadPrev)
					this.object.onLoadPrev();
			};	
			
			this.onLoadNext = function(){
				if (this.object.onLoadNext)
					this.object.onLoadNext();
			};
			
			this.mouseMove = function(x,y){
				if (this.object.mouseMove)
					this.object.mouseMove(x,y);
				return false;				
			};			
			
			this.riposition = function(){
				if (this.object.riposition)
					this.object.riposition();		
				if (this.anchor==euDOWN){
					document.getElementById(this.id).style.left=(this.posX+this.offsetX+(this.width-document.getElementById(this.id).offsetWidth)/2)+'px';					
					document.getElementById(this.id).style.top =(this.posY+this.height+this.offsetY)+'px';
				}else if (this.anchor==euUP){
					document.getElementById(this.id).style.left=(this.posX+this.offsetX+(this.width-document.getElementById(this.id).offsetWidth)/2)+'px';					
					document.getElementById(this.id).style.top =(this.posY+this.offsetY)+'px';
				}else if (this.anchor==euLEFT){
					document.getElementById(this.id).style.left=(this.posX+this.offsetX)+'px';					
					document.getElementById(this.id).style.top =(this.posY+this.offsetY+(this.height-document.getElementById(this.id).offsetHeight)/2)+'px';					
				}else if (this.anchor==euRIGHT){
					document.getElementById(this.id).style.left=(this.posX+this.width+this.offsetX)+'px';					
					document.getElementById(this.id).style.top =(this.posY+this.offsetY+(this.height-document.getElementById(this.id).offsetHeight)/2)+'px';					
				}else if (this.anchor==euCENTER){
					document.getElementById(this.id).style.left=(this.posX+this.offsetX+(this.width-document.getElementById(this.id).offsetWidth)/2)+'px';					
					document.getElementById(this.id).style.top =(this.posY+this.offsetY+(this.height-document.getElementById(this.id).offsetHeight)/2)+'px';					
				}
			};			
			
			if (args.anchor)	
				this.anchor  = args.anchor;
			if (args.offsetX)
				this.offsetX  = args.offsetX;

							
			if (args.offsetY)
				this.offsetY  = args.offsetY;

			var style="";
			if (args.style)
				style = args.style;
						
			for (var i in args.object)
				this.object = new window[i](this.id+"_LABEL_OBJECT",args.object[i],container,onLoadFunc);
			container.innerHTML+="<span id='"+this.id+"' src='"+args.image+"' style='position:absolute;visibility:hidden;"+style+";'>"+args.txt+"</span>";
		};
/* 
 ****************************************
 ******    euLabel Object         *******
 ******     (END)                 *******
 **************************************** 
 */