/*
 * euDock.ImageDebug
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
  
function euPreloadImage(a) {
	var d=document;
	if(d.images){
		if(!d.p) d.p=new Array();
		d.p.push(new Image());
		d.p[d.p.length-1].src=a;
	}
};

/* 
 ****************************************
 ******    euImage Object         *******
 ******     (START)               *******
 **************************************** 
 */
		function euImage(id,args,container,onLoadFunc){
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
			this.setPosX   = function(x) {document.getElementById(this.id).style.left=x+'px';document.getElementById(this.id+"pippo").style.left=x+'px';};
			this.getPosY   = function() {return document.getElementById(this.id).style.top.replace(/[^0-9]/g,"");};	
			this.setPosY   = function(y) {document.getElementById(this.id).style.top=y+'px';document.getElementById(this.id+"pippo").style.top=y+'px';};
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
        		document.getElementById(this.id).style.filter = 'alpha(opacity='+(fad)+')';
        		document.getElementById(this.id+"pippo").innerHTML  = "x,y="+this.getPosX()+','+this.getPosY()+"<br>";
        		document.getElementById(this.id+"pippo").innerHTML += "w,h="+this.getWidth()+','+this.getHeight();
			};
			
			container.innerHTML+="<img onLoad='"+onLoadFunc+"' id='"+this.id+"' src='"+args.image+"' style='position:absolute;visibility:hidden;'>";	
			container.innerHTML+="<div id='"+this.id+"pippo' style='position:absolute;border:1px solid black;'></div>";	
			//document.write("<img onLoad='"+onLoadFunc+"' id='"+this.id+"' src='"+args.image+"' style='position:absolute;visibility:hidden;'>");	
			
		};
/* 
 ****************************************
 ******    euImage Object         *******
 ******     (END)                 *******
 **************************************** 
 */