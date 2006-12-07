/*
 * euDock.DivMousePos
 *
 * euDock 2.0 plugin
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
 ******    euDivMousePos Object   *******
 ******     (START)               *******
 **************************************** 
 */
		function euDivMousePos(id,args,container,onLoadFunc){
			this.id = id;
			var styleAdd = '';
			if (args.style)
				styleAdd = args.style		
			
			this.setParameters = function(args){
				if (typeof(args)!="undefined" && args!=null){
					for (var i in args){
						this[i](args[i]);
					}
				}	
			};
			
			this.setProperties = function(x,y,w,h,fad){
				this.setPos(x,y);
				this.setDim(w,h);
				this.setFading(fad);
			};
			
			this.setPos = function(x,y){
				this.setPosX(x);
				this.setPosY(y);
			};	
			
			this.setDim = function(w,h){
				this.setWidth(w);
				this.setHeight(h);
			};
			
			this.getPosX   = function() {return 1*document.getElementById(this.id).style.left.replace(/[^0-9]/g,"");};			
			this.setPosX   = function(x){document.getElementById(this.id).style.left=x+'px';};
			this.getPosY   = function() {return 1*document.getElementById(this.id).style.top.replace(/[^0-9]/g,"");};	
			this.setPosY   = function(y){document.getElementById(this.id).style.top=y+'px';};
			this.getWidth  = function() {return document.getElementById(this.id).clientWidth;};
			this.setWidth  = function(w){document.getElementById(this.id).style.width=Math.round(w)+'px';};
			this.getHeight  = function(){return document.getElementById(this.id).clientHeight;};		
			this.setHeight = function(h){document.getElementById(this.id).style.height=Math.round(h)+'px';};		
			
			this.hide = function(){document.getElementById(this.id).style.visibility='hidden';};			
			this.show = function(){document.getElementById(this.id).style.visibility='visible';};			
			
			this.setFading = function(fad){
				fad=Math.round(fad);
				if (fad<0)
					fad=0;
				if (fad>100)
					fad=100;				
        		document.getElementById(this.id).style.opacity = (fad/100);
        		document.getElementById(this.id).style.filter = 'alpha(opacity='+(fad)+')';
			};
			
			//this.mouseMove = function(x,y){				
				/*
				document.getElementById(this.id).innerHTML  = "Width="     +this.getWidth()+"-";
				document.getElementById(this.id).innerHTML += "<br>Height="+this.getHeight()+"-";
				document.getElementById(this.id).innerHTML += "<br>Mouse(x,y)<br>("+x+","+y+")";
				*/
			//	document.getElementById(this.id).innerHTML = "("+x+","+y+")";
			//};			
			//this.mouseMove(0,0);
			
			container.innerHTML+="<div id='"+this.id+"' style='position:absolute;visibility:hidden;"+styleAdd+"'></div>";	
			
			window.setTimeout(onLoadFunc,500);
		};
/* 
 ****************************************
 ******    euDivMousePos Object   *******
 ******     (END)                 *******
 **************************************** 
 */