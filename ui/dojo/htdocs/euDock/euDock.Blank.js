/*
 * euDock.Blank
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
 ******    euBlank Object         *******
 ******     (START)               *******
 **************************************** 
 */
		function euBlank(id,args,container,onLoadFunc){
			this.id = id;
			
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
			
			this.x=0;
			this.y=0;
			this.width=0;
			this.height=0;
			
			this.getPosX   = function() {return this.x;};			
			this.setPosX   = function(x){this.x=x;};
			this.getPosY   = function() {return this.y;};	
			this.setPosY   = function(y){this.y=y;};
			this.getWidth  = function() {return this.width;};
			this.setWidth  = function(w){this.width=w;};
			this.getHeight  = function(){return this.height;};		
			this.setHeight = function(h){this.height=h;};		
			
			this.hide = function(){};			
			this.show = function(){};			
			
			this.setFading = function(fad){};

			if (typeof(args)!="undefined" && args!=null)
					for (var i in args)
						this[i]=args[i];
						
			window.setTimeout(onLoadFunc,500);
		};
/* 
 ****************************************
 ******    euBlank Object         *******
 ******     (END)                 *******
 **************************************** 
 */