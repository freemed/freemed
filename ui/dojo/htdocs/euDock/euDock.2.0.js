/*
 * euDock - javascript Emulation of Dock style MAC OS X bar
 *
 * Version: 2.0.04
 *
 * Copyright (C) 2006 Parodi (Pier...) Eugenio <eudock@inwind.it>
 *                                              http://eudock.jules.it
 *
 * SPECIAL THANKS TO Tiago D'Herbe (tvidigal) FOR (Multiple Dock) INSPIRATION
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

if (!euEnv)
	var euEnv      = new Array();
euEnv.Kost         = new Array();
euEnv.Kost.num     = 0;
euEnv.Kost.next    = function(){return this.num++;}
euEnv.euDockArray  = new Array();
euEnv.refreshTime  = 35;
euEnv.exeThread          = true;
euEnv.exeThreadWhiteLoop = 0;
euEnv.x = 0;
euEnv.y = 0;
euEnv.mouseMoved=false;

var euUP       = 1;
var euDOWN     = 2;
var euLEFT     = 3;
var euRIGHT    = 4;

var euICON     = 5;
var euMOUSE    = 6;

var euSCREEN   = 7;
var euOBJECT   = 8;
var euABSOLUTE = 9;
var euRELATIVE = 10;

var euHORIZONTAL = 11;
var euVERTICAL   = 12;
var euCENTER     = 13;

var euTRANSPARENT = 14;
var euFIXED       = 15;
var euOPAQUE      = 16;



/* 
 ****************************************
 ****** Standard euDock Functions *******
 ******  (BEGIN)                  *******
 **************************************** 
 */		
		function euIdObjTop(euObj){
		    var ret = euObj.offsetTop;
		    while ((euObj = euObj.offsetParent)!=null)
		        ret += euObj.offsetTop;
		    return ret;
		};
		
		function euIdObjLeft(euObj){
		    var ret = euObj.offsetLeft;
		    while ((euObj = euObj.offsetParent)!=null)
		        ret += euObj.offsetLeft;
		    return ret;
		};
		
		function isEuInside(euObj,x,y){
			var euTop  = euIdObjTop(euObj);
			var euLeft = euIdObjLeft(euObj);			
			return ((euTop<=y && (euTop+euObj.offsetHeight)>=y)&&(euLeft<=x && (euLeft+euObj.offsetWidth)>=x));
		};		
	
		/*
		 * euDimensioni()
		 *
		 * standard code fo retrieve width and Height of Screen
		 *
		 */
		function euDimensioni(){
		    if( typeof( window.innerWidth ) == 'number' ) {
		        //Non-IE
		        euEnv.euFrameWidth = window.innerWidth-16;
		        euEnv.euFrameHeight = window.innerHeight;
		    } else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
		        //IE 6+ in 'standards compliant mode'
		        euEnv.euFrameWidth = document.documentElement.clientWidth-16;
		        euEnv.euFrameHeight = document.documentElement.clientHeight;
		    } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
		        //IE 4 compatible
		        euEnv.euFrameWidth = document.body.clientWidth;
		        euEnv.euFrameHeight = document.body.clientHeight;
		    }
		};
		
		function offsEut() {
		    euEnv.euScrOfY = 0;
		    euEnv.euScrOfX = 0;
		    if( typeof( window.pageYoffsEut ) == 'number' ) {
		        //Netscape compliant
		        euEnv.euScrOfY = window.pageYoffsEut;
		        euEnv.euScrOfX = window.pageXoffsEut;
		    } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
		        //DOM compliant
		        euEnv.euScrOfY = document.body.scrollTop;
		        euEnv.euScrOfX = document.body.scrollLeft;
		    } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
		        //IE6 standards compliant mode
		        euEnv.euScrOfY = document.documentElement.scrollTop;
		        euEnv.euScrOfX = document.documentElement.scrollLeft;
		    }		    
		};
/* 
 ****************************************
 ****** Standard euDock Functions *******
 ******  (END)                    *******
 **************************************** 
 */

/* 
 ****************************************
 ****** euDock Trans Functions    *******
 ******  (BEGIN)                  *******
 **************************************** 
 */

	function euKostFunc30(x){
		return 0.3;
	};
	
	function euKostFunc100(x){
		return 1;
	};	
 
 	function euLinear(x){
		return x;
	};
  
	function euLinear30(x){
		return 1*(x+(1-x)*0.3);
	};
	
	function euLinear20(x){	
		return x+(1-x)*0.2;
	};
	
	function euExp30(x){
		return euLinear30(x*x*x);
	};

	function euLinear50(x){	
		return x+(1-x)*0.5;
	};		
	
	function euHarmonic(x){
		return euLinear30((1-Math.cos(Math.PI*x))/2);
	};
	
	function euSemiHarmonic(x){
		return euLinear30(Math.cos(Math.PI*(1-x)/2));
	};	
 
/* 
 ****************************************
 ****** euDock Trans Functions    *******
 ******  (END)                    *******
 **************************************** 
 */ 
 
/* 
 ****************************************
 ******    euDock Object          *******
 ******     (START)               *******
 **************************************** 
 */
		function euDock(){
			this.id = 'euDock_'+euEnv.Kost.next();
			document.write("<div id='"+this.id+"_bar' style='z-index:1000;position:fixed;border:0px solid black;'></div>");	
			document.write("<div onMouseOut='euEnv.euDockArray."+this.id+".mouseOut();' onMouseOver='euEnv.euDockArray."+this.id+".mouseOver();' id='"+this.id+"' style='z-index:1000;position:fixed;border:0px solid black;'></div>");	
			this.div   =document.getElementById(this.id);
			this.divBar=document.getElementById(this.id+"_bar");
			this.iconsArray=new Array();
			this.isInside=false;
			euEnv.euDockArray[this.id]=this;
			this.bar=null;
			
			this.mouseX = 0;
			this.mouseY = 0;
			
			this.centerPosX = 0;
			this.centerPosY = 0;
			this.offset     = 0;
			this.iconOffset = 0;
			
			this.venusHillSize  = 3;//200;
			this.venusHillTrans = euLinear;

			this.position    = euUP;
			this.align       = euSCREEN;
			this.objectAlign = euDOWN;
			this.idObjectHook;
			this.animaition  = euICON;
			this.animFading  = euABSOLUTE;
			
			this.setIconsOffset = function(offset){
				this.iconOffset=offset;
			};
			
			this.setAnimation = function(anim,size){				
				this.animaition    = anim;
				this.venusHillSize = size;
			};
			
			this.setPointAlign = function(x,y,pos){
				this.offset   = 0;
				this.align    = euABSOLUTE;
				this.position = pos;
				this.setCenterPos(x,y);
			}
			
			this.setObjectAlign = function(idObj,align,offset,pos){
				this.offset       = offset;
				this.align        = euOBJECT;
				this.objectAlign  = align;
				this.position     = pos;
				this.idObjectHook = document.getElementById(idObj);
				this.setObjectCoord();
			};
			
			this.setObjectCoord = function(){
				if (this.objectAlign==euDOWN)
					this.setCenterPos(
						euIdObjLeft(this.idObjectHook) + (this.idObjectHook.offsetWidth/2),
						euIdObjTop(this.idObjectHook)  + this.idObjectHook.offsetHeight + this.offset);
				else if (this.objectAlign==euUP)
					this.setCenterPos(
						euIdObjLeft(this.idObjectHook) + (this.idObjectHook.offsetWidth/2),
						euIdObjTop(this.idObjectHook) - this.offset);
				else if (this.objectAlign==euLEFT)
					this.setCenterPos(
						euIdObjLeft(this.idObjectHook) - this.offset,
						euIdObjTop(this.idObjectHook)  + (this.idObjectHook.offsetHeight/2));
				else if (this.objectAlign==euRIGHT)
					this.setCenterPos(
						euIdObjLeft(this.idObjectHook) + this.idObjectHook.offsetWidth + this.offset,
						euIdObjTop(this.idObjectHook)  + (this.idObjectHook.offsetHeight/2));
				else if (this.objectAlign==euCENTER){
					if (this.position==euUP || this.position==euDOWN || this.position==euHORIZONTAL)	
						this.setCenterPos(
							euIdObjLeft(this.idObjectHook) + (this.idObjectHook.offsetWidth/2),
							euIdObjTop(this.idObjectHook)  + (this.idObjectHook.offsetHeight/2) - this.offset);
					else
						this.setCenterPos(
							euIdObjLeft(this.idObjectHook) + (this.idObjectHook.offsetWidth/2) + this.offset,
							euIdObjTop(this.idObjectHook)  + (this.idObjectHook.offsetHeight/2));												
				}	
			};			
			
			this.setScreenAlign = function(align,offset){
				this.offset=offset;
				this.align = euSCREEN;
				if (align==euUP)
					this.position=euDOWN;
				else if (align==euDOWN)					
					this.position=euUP;
				else if (align==euLEFT)
					this.position=euRIGHT;
				else if (align==euRIGHT)					
					this.position=euLEFT;
				this.setScreenCoord();
			};
			
			this.setScreenCoord = function(){
				euDimensioni();
				offsEut();
				if (this.position==euDOWN)
					this.setCenterPos(
						euEnv.euScrOfX+euEnv.euFrameWidth/2,
						euEnv.euScrOfY+this.offset);
				else if (this.position==euUP)
					this.setCenterPos(
						euEnv.euScrOfX+euEnv.euFrameWidth/2,
						euEnv.euScrOfY+euEnv.euFrameHeight-this.offset);					
				else if (this.position==euRIGHT)
					this.setCenterPos(
						euEnv.euScrOfX+this.offset,
						euEnv.euScrOfY+euEnv.euFrameHeight/2);
				else if (this.position==euLEFT)
					this.setCenterPos(
						euEnv.euScrOfX+euEnv.euFrameWidth-this.offset,
						euEnv.euScrOfY+euEnv.euFrameHeight/2);				
			};
			
			this.refreshDiv = function(){
				if (this.position==euDOWN){
					this.setPos(this.centerPosX-this.getWidth()/2,this.centerPosY+this.iconOffset);					
				}else if (this.position==euUP){
					this.setPos(this.centerPosX-this.getWidth()/2,this.centerPosY-this.getHeight()-this.iconOffset);
				}else if (this.position==euRIGHT){					
					this.setPos(this.centerPosX+this.iconOffset,this.centerPosY-this.getHeight()/2);
				}else if (this.position==euLEFT){					
					this.setPos(this.centerPosX-this.getWidth()-this.iconOffset,this.centerPosY-this.getHeight()/2);
				}else if (this.position==euHORIZONTAL){
					this.setPos(this.centerPosX-this.getWidth()/2,this.centerPosY-this.getHeight()/2+this.iconOffset);
				}else if (this.position==euVERTICAL){
					this.setPos(this.centerPosX-this.getWidth()/2+this.iconOffset,this.centerPosY-this.getHeight()/2);
				}
				if (this.bar){
					if (this.position==euDOWN){
						this.setBarPos(this.centerPosX-this.getWidth()/2,this.centerPosY);					
					}else if (this.position==euUP){
						this.setBarPos(this.centerPosX-this.getWidth()/2,this.centerPosY-this.bar.getSize());
					}else if (this.position==euRIGHT){
						this.setBarPos(this.centerPosX,this.centerPosY-this.getHeight()/2);
					}else if (this.position==euLEFT){
						this.setBarPos(this.centerPosX-this.bar.getSize(),this.centerPosY-this.getHeight()/2);
					}else if (this.position==euHORIZONTAL){
						this.setBarPos(this.centerPosX-this.getWidth()/2,this.centerPosY-this.bar.getSize()/2);						
					}else if(this.position==euVERTICAL){
						this.setBarPos(this.centerPosX-this.bar.getSize()/2,this.centerPosY-this.getHeight()/2);			
					}
				}
			}			
			
			this.riposition = function(){
				if (this.align == euSCREEN)
					this.setScreenCoord();
				else if (this.align == euOBJECT)
					this.setObjectCoord();				
			};
									
			this.setCenterPos = function(x,y){
				this.centerPosX = x;
				this.centerPosY = y;
				this.refreshDiv();
			};

			this.setPos = function(x,y){				
				this.setPosX(x);
				this.setPosY(y);
			};	
			
			this.setBarPos = function(x,y){
				this.setBarPosX(x);
				this.setBarPosY(y);
			};			
			
			this.setDim = function(w,h){
				this.setWidth(w);
				this.setHeight(h);				
			};	
			
			
			this.setBarPosX   = function(x) {document.getElementById(this.id+"_bar").style.left=x+'px';};
			this.setBarPosY   = function(y) {document.getElementById(this.id+"_bar").style.top=y+'px';};			
			
			this.getPosX   = function() {return document.getElementById(this.id).style.left.replace(/[^0-9]/g,"");};			
			this.setPosX   = function(x) {document.getElementById(this.id).style.left=x+'px';};
			this.getPosY   = function() {return document.getElementById(this.id).style.top.replace(/[^0-9]/g,"");};	
			this.setPosY   = function(y) {document.getElementById(this.id).style.top=y+'px';};
			this.getWidth  = function() {return document.getElementById(this.id).style.width.replace(/[^0-9]/g,"");};
			this.setWidth  = function(w){document.getElementById(this.id).style.width=Math.round(w)+'px';};
			this.getHeight  = function() {return document.getElementById(this.id).style.height.replace(/[^0-9]/g,"");};		
			this.setHeight = function(h){document.getElementById(this.id).style.height=Math.round(h)+'px';};
			
			this.getVenusWidth  = function() {return this.venusHillSize*this.getWidth();};
			this.getVenusHeight = function() {return this.venusHillSize*this.getHeight();};
			
			this.getMouseRelativeX = function(){return this.mouseX-euIdObjLeft(this.div);};
			this.getMouseRelativeY = function(){return this.mouseY-euIdObjTop(this.div);};
			
			this.updateDims = function(){
				var bakWidth  = 0;
				var bakHeight = 0;
				for (var i in this.iconsArray) if (this.iconsArray[i].id){					
					if (this.position==euUP || this.position==euDOWN || this.position==euHORIZONTAL){						
						bakWidth  += this.iconsArray[i].getWidth();
						bakHeight = (this.iconsArray[i].getHeight()>bakHeight)?this.iconsArray[i].getHeight():bakHeight;
						bakHeight = Math.round(bakHeight);
					}else{						
						bakHeight += this.iconsArray[i].getHeight();
						bakWidth  = (this.iconsArray[i].getWidth()>bakWidth)?this.iconsArray[i].getWidth():bakWidth;
						bakWidth = Math.round(bakWidth);
					}
				}
				
				if (this.bar){
					if (this.position==euUP || this.position==euDOWN || this.position==euHORIZONTAL)
						this.bar.setProperties(bakWidth,this.position)
					else
						this.bar.setProperties(bakHeight,this.position)
					this.bar.refresh();
				}
				
				//bakWidth=Math.ceil(bakWidth);			
				//bakHeight=Math.ceil(bakHeight);
								
				var posx=0;
				var posy=0;
				var updPosX=0;
				var updPosY=0;
				for (var i in this.iconsArray) if (this.iconsArray[i].id){					
					if (this.position==euDOWN){
						updPosX=posx;
						updPosY=posy;
						posx+=this.iconsArray[i].getWidth();
					}else if (this.position==euUP){
						updPosX=posx;
						updPosY=bakHeight-this.iconsArray[i].getHeight();
						posx+=this.iconsArray[i].getWidth();
					}else if (this.position==euRIGHT){
						updPosX=posx;
						updPosY=posy;
						posy+=this.iconsArray[i].getHeight();
					}else if (this.position==euLEFT){
						updPosX=bakWidth-this.iconsArray[i].getWidth();
						updPosY=posy;
						posy+=this.iconsArray[i].getHeight();
					}else if (this.position==euHORIZONTAL){
						updPosX=posx;
						updPosY=(bakHeight-this.iconsArray[i].getHeight())/2;
						posx+=this.iconsArray[i].getWidth();
					}else if (this.position==euVERTICAL){
						updPosX=(bakWidth-this.iconsArray[i].getWidth())/2;
						updPosY=posy;
						posy+=this.iconsArray[i].getHeight();						
					}
					this.iconsArray[i].setPos(updPosX,updPosY);
					this.iconsArray[i].refresh();
					
				}
				
				this.setDim(bakWidth,bakHeight);
				this.refreshDiv();
			};
			
			this.kernel = function(){				
				if (this.isInside)
					return this.kernelMouseOver();
				else
					return this.kernelMouseOut();			
			};
			
			this.kernelMouseOver = function(){				
				var ret=false;
				var overI = -1;
				var mouseRelX = this.getMouseRelativeX();
				var mouseRelY = this.getMouseRelativeY();
				var mediana;
				var border;
				var frameTo;
				var venusWidth;
				var venusHeight;
				var overIcon;
				if (this.position==euUP || this.position==euDOWN || this.position==euHORIZONTAL){
					venusWidth = this.getVenusWidth();
					for (var i in this.iconsArray) if (this.iconsArray[i].id)
						if (this.iconsArray[i].isInsideX(mouseRelX)){
							overIcon=i;
							border=this.iconsArray[i].getWidth()/2;
							if (this.animaition==euICON){
								mouseRelX  = this.iconsArray[i].posX+border;
								border=0;
							}
						}
					for (var i in this.iconsArray) if (this.iconsArray[i].id){
						mediana = this.iconsArray[i].posX+this.iconsArray[i].getWidth()/2;
						if (Math.abs(mediana-mouseRelX)<=border)
							mediana=mouseRelX;
						else if (mediana<mouseRelX)
							mediana+=this.iconsArray[i].getWidth()/2;
						else if (mediana>mouseRelX)
							mediana-=this.iconsArray[i].getWidth()/2;
						if (this.animaition==euICON  && Math.abs(i-overIcon)<=this.venusHillSize)
							frameTo = this.venusHillTrans(1-Math.abs(i-overIcon)/this.venusHillSize);
						else if (this.animaition==euMOUSE && Math.abs(mediana-mouseRelX)<=venusWidth)
							frameTo = this.venusHillTrans(1-Math.abs(mediana-mouseRelX)/venusWidth);
						else
							frameTo = 0;
							
						if (frameTo==0 || frameTo==1 || Math.abs(frameTo-this.iconsArray[i].frame)>0.01)
							ret|=this.iconsArray[i].setFrameTo(frameTo);
							
						if (this.animFading==euABSOLUTE)
							if (this.iconsArray[i].isInsideX(mouseRelX))
								ret|=this.iconsArray[i].setFadingTo(1);
							else
								ret|=this.iconsArray[i].setFadingTo(0);
						else
							ret|=this.iconsArray[i].setFadingTo(frameTo);
						
					}
				}else{
					venusHeight = this.getVenusHeight();
					for (var i in this.iconsArray) if (this.iconsArray[i].id)
						if (this.iconsArray[i].isInsideY(mouseRelY)){
							overIcon=i;
							border=this.iconsArray[i].getHeight()/2;
							if (this.animaition==euICON){
								mouseRelY  = this.iconsArray[i].posY+border;
								border=0;
							}
						}					
					for (var i in this.iconsArray) if (this.iconsArray[i].id){
						mediana = this.iconsArray[i].posY+this.iconsArray[i].getHeight()/2;
						if (Math.abs(mediana-mouseRelY)<=border)
							mediana=mouseRelY;
						else if (mediana<mouseRelY)
							mediana+=this.iconsArray[i].getHeight()/2;
						else if (mediana>mouseRelY)
							mediana-=this.iconsArray[i].getHeight()/2;
						if (this.animaition==euICON  && Math.abs(i-overIcon)<=this.venusHillSize)
							frameTo = this.venusHillTrans(1-Math.abs(i-overIcon)/this.venusHillSize);
						else if (this.animaition==euMOUSE && Math.abs(mediana-mouseRelY)<=venusHeight)
							frameTo = this.venusHillTrans(1-Math.abs(mediana-mouseRelY)/venusHeight);
						else
							frameTo = 0;
							
						if (frameTo==0 || frameTo==1 || Math.abs(frameTo-this.iconsArray[i].frame)>0.01)
							ret|=this.iconsArray[i].setFrameTo(frameTo);
							
						if (this.animFading==euABSOLUTE)
							if (this.iconsArray[i].isInsideY(mouseRelY))
								ret|=this.iconsArray[i].setFadingTo(1);
							else
								ret|=this.iconsArray[i].setFadingTo(0);
						else
							ret|=this.iconsArray[i].setFadingTo(frameTo);
						
					}										
				}
				if (ret)
					this.updateDims();
				return ret;				
			};
			
			this.kernelMouseOut = function(){
				var ret=false;
				for (var i in this.iconsArray) if (this.iconsArray[i].id)
					ret|=this.iconsArray[i].setAllFrameTo(0);	
				if (ret)
					this.updateDims();				
				return ret;	
			};			
			
			this.mouseOut = function(){
				this.isInside=false;
				euEnv.exeThreadWhiteLoop=5;				
			};
			
			this.mouseOver = function(){
				this.isInside=true;
				euEnv.exeThreadWhiteLoop=5;				
			};			
			
			this.mouseMove = function(x,y){
				var inside = isEuInside(this.div,x,y);
				var ret = (this.mouseX!=x || this.mouseY!=y) && inside;
				
				this.mouseX=x;
				this.mouseY=y;

				
				if (inside!=this.isInside){					
					this.isInside=inside;
					ret=true;
				}
								
				for (var i in this.iconsArray) if (this.iconsArray[i].id)
						ret|=this.iconsArray[i].isRunning();					
				return ret;
			};
			
			this.iconParams=new Array();
			this.setAllFrameStep = function(step){
				this.iconParams.frameStep=step;
				for (var i in this.iconsArray) if (this.iconsArray[i].id)
					this.iconsArray[i].frameStep=step;				
			};
			
			this.setAllZoomFunc = function(func){
				this.setAllZoomFuncW(func);
				this.setAllZoomFuncH(func);
			};		
			
			this.setAllZoomFuncW = function(func){
				this.iconParams.zoomFuncW=func;
				for (var i in this.iconsArray) if (this.iconsArray[i].id)
					this.iconsArray[i].zoomFuncW=func;
			};
			
			this.setAllZoomFuncH = function(func){
				this.iconParams.zoomFuncH=func;
				for (var i in this.iconsArray) if (this.iconsArray[i].id)
					this.iconsArray[i].zoomFuncH=func;	
			};
			
			this.setBar = function(args){
				var id = 'euDock_bar_'+euEnv.Kost.next(); 
				euEnv.euDockArray[id] = new euDockBar(id,this);
				euEnv.euDockArray[id].setElements(args);
				this.bar=euEnv.euDockArray[id];				
				return euEnv.euDockArray[id];				
			};
			
			this.addIcon = function(args,params){
				var id = 'euDock_icon_'+euEnv.Kost.next(); 
				euEnv.euDockArray[id] = new euDockIcon(id,this);
				euEnv.euDockArray[id].addElement(args);
				this.iconsArray.push(euEnv.euDockArray[id]);
				for (i in this.iconParams)
					euEnv.euDockArray[id][i]=this.iconParams[i];
				for (i in params)
					euEnv.euDockArray[id][i]=params[i];				
				return euEnv.euDockArray[id];				
			};
			
		};
/* 
 ****************************************
 ******    euDock Object          *******
 ******     (END)                 *******
 **************************************** 
 */
 
/* 
 ****************************************
 ******    euDock Icon Object     *******
 ******     (START)               *******
 **************************************** 
 */
		function euDockIcon(id,dock){
			this.id = id;			
			
			this.parentDock = dock;
			
			this.elementsArray;
			
			this.zoomFuncW=euLinear30;
			this.zoomFuncH=euLinear30;
			
			this.posX          = 0;
			this.posY          = 0;
			this.width         = 0;
			this.height        = 0;
			this.frame         = 0;
			this.frameStep     = 0.5;
			this.fadingFrame   = 0;
			this.fadingStep    = 1;
			this.fadingType    = euTRANSPARENT;
			
			this.loaded        = false;
			this.runningFrame  = false;
			this.runningFading = false;
			
			this.updateDims = function(){
				if (!this.loaded)return;
				
				for (var i=0;i<this.elementsArray.length;i++)
					this.elementsArray[i].setProperties(this.posX,this.posY,this.getWidth(),this.getHeight());
			};
			
			this.updateFading = function(){
				if (!this.loaded)return;
				var stato = this.fadingFrame*(this.elementsArray.length-1);
				var prev  = Math.floor(stato);
				var next  = Math.ceil( stato);
				var fading=0;
				for (var i=0;i<this.elementsArray.length;i++){
					if (this.fadingType==euFIXED){
						if (i==next)
							fading=100-100*(i-stato);
						else if (i<next)
							fading=100;
						else
							fading=0;
					}else{
						if (i==next)
							fading=100-100*(i-stato);
						else if (i==prev){
							if (this.fadingType==euTRANSPARENT)
								fading=100-100*(stato-i);
							else
								fading=100;
						}else
							fading=0;
					}
					this.elementsArray[i].setFading(fading);
				}				
			};
			
			this.refresh = function(){
				this.updateDims();
				this.updateFading();
			};

			this.isAbsoluteInside = function(x,y){
				x-=this.getAbsolutePosX();
				y-=this.getAbsolutePosY();
				return x>0 && y>0 && x<this.getWidth() && y<this.getHeight();
			};			
						
			this.isInside = function(x,y){
				return this.isInsideX(x) && this.isInsideY(y);
			};
			
			this.isInsideX = function(x){			
				return 	(this.loaded && (this.posX<=x) && ((this.posX+this.getWidth())>=x));
			};
			
			this.isInsideY = function(y){
				return 	(this.loaded && (this.posY<=y) && ((this.posY+this.getHeight())>=y));
			};			
			
			this.retrieveLoadingDims = function(elem,num){
				if (elem.onLoadPrev)
					elem.onLoadPrev();
				if (num==0 && !this.loaded)
					this.setDim(elem.getWidth(),elem.getHeight());
				elem.loaded=true;				
				var ret=true;			
				for (var i in this.elementsArray) if (this.elementsArray[i].id)
						ret&=this.elementsArray[i].loaded
				this.loaded=ret;	
				if (this.loaded){
					this.parentDock.updateDims();					
					for (var i in this.elementsArray) if (this.elementsArray[i].id)
						this.elementsArray[i].show();
				}
				if (elem.onLoadNext)
					elem.onLoadNext();				
			};
			
			this.setPos = function(x,y){
				this.posX = x;
				this.posY = y;				
			};	
			
			this.setDim = function(w,h){
				if (this.width==0)
					this.width  = w;
				if (this.height==0)
					this.height = h;				
			};	
			
			this.getAbsolutePosX = function(){return euIdObjLeft(this.parentDock.div)+this.posX;};
			this.getAbsolutePosY = function(){return euIdObjTop(this.parentDock.div)+this.posY;};
			
			this.setPosX   = function(x) {this.posX=x;};
			this.setPosY   = function(y) {this.posY=y;};
			this.getWidth  = function()  {if (!this.loaded)return 0; return this.width*this.zoomFuncW(this.frame);};
			this.getHeight = function()  {if (!this.loaded)return 0; return this.height*this.zoomFuncH(this.frame);};		
			
			this.isRunning = function(){
				return this.runningFrame || this.runningFading;
			};
			
			this.setAllFrameTo = function(to){
				this.setFadingTo(to);
				this.setFrameTo(to) ;
				return this.isRunning();
			};
			
			this.setFadingTo = function(fadingTo){
				if (this.fadingFrame==fadingTo)
					this.runningFading = false;
				else{					
					if (this.fadingFrame>fadingTo)
						this.fadingFrame-=this.fadingStep;
					else
						this.fadingFrame+=this.fadingStep;
						
					this.runningFading = true;					
						
					if (Math.abs(this.fadingFrame-fadingTo)<this.fadingStep)
						this.fadingFrame=fadingTo;
				
					if (this.fadingFrame<0)
						this.fadingFrame = 0;
					if (this.fadingFrame>1)
						this.fadingFrame = 1;
				}
				return this.runningFading;
			};
			
			this.setFrameTo = function(frameTo){
				//frameTo=(Math.round(frameTo*100))/100;				
				if (this.frame==frameTo)
					this.runningFrame = false;
				else{
					this.runningFrame = true;
					
					this.frame+=(frameTo-this.frame)*this.frameStep;					
	
					if (Math.abs(this.frame-frameTo)<0.01)
							this.frame=frameTo;
				
					
					if (this.frame<0)
						this.frame = 0;
					if (this.frame>1)
						this.frame = 1;				
				}
				return this.runningFrame;
			};
			
			this.addElement = function(args){				
				if (typeof(args)!="undefined" && args!=null){
					this.elementsArray=new Array();
					this.fadingStep = 0.5/args.length;

					for (var i=0;i<args.length;i++)
						for (var ii in args[i]){
							var id = "euDock_"+ii+"_"+euEnv.Kost.next();
							euEnv.euDockArray[id]= new window[ii](id,args[i][ii],this.parentDock.div,"euEnv.euDockArray."+this.id+".retrieveLoadingDims(euEnv.euDockArray."+id+","+i+");");
							this.elementsArray.push(euEnv.euDockArray[id]);
							euEnv.euDockArray[id].loaded=false;							
						}
				}
			};
			
			this.mouseClick = function(x,y){
				if (this.isAbsoluteInside(x,y)){
					if (this.link)					
						document.location.href=this.link;
					else if (this.code)
						eval(this.code);
					else if (this.mouseInsideClick)
						this.mouseInsideClick(x,y);						
				}
			};
			
		};
/* 
 ****************************************
 ******    euDock Icon Object     *******
 ******     (END)                 *******
 **************************************** 
 */
 
 /* 
 ****************************************
 ******    euDock Bar Object     *******
 ******     (START)               *******
 **************************************** 
 */
		function euDockBar(id,dock){
			this.id = id;
			
			this.parentDock = dock;
			
			this.elementsArray=new Array();
			
			this.len=0;
			this.align=euUP;
			
			this.loaded = false;
			
			this.getSize = function(){
				if (!this.loaded)
					return 0;				
				if (this.align==euUP || this.align==euDOWN || this.align==euHORIZONTAL)				
					return this.elementsArray.left.getHeight();
				else
					return this.elementsArray.top.getWidth();
			};
			
			this.refresh = function(){
				if (!this.loaded)
					return;			
				if (this.align==euUP || this.align==euDOWN || this.align==euHORIZONTAL){
					this.elementsArray.left.setPos(-this.elementsArray.left.getWidth(),0);
					this.elementsArray.horizontal.setProperties(0,0,Math.round(this.len),this.getSize());
					this.elementsArray.right.setPos(Math.round(this.len),0);
					this.elementsArray.left.show();
					this.elementsArray.horizontal.show();
					this.elementsArray.right.show();
					if (this.elementsArray.top)
						this.elementsArray.top.hide();
					if (this.elementsArray.bottom)
						this.elementsArray.bottom.hide();
					if (this.elementsArray.vertical){
						this.elementsArray.vertical.setProperties(0,0,0,0);
						this.elementsArray.vertical.hide();
					}
				}else{
					this.elementsArray.top.setPos(0,-this.elementsArray.top.getHeight());
					this.elementsArray.vertical.setProperties(0,0,this.getSize(),Math.round(this.len));
					this.elementsArray.bottom.setPos(0,Math.round(this.len));
					this.elementsArray.top.show();
					this.elementsArray.vertical.show();
					this.elementsArray.bottom.show();
					if (this.elementsArray.left)
						this.elementsArray.left.hide();
					if (this.elementsArray.right)
						this.elementsArray.right.hide();
					if (this.elementsArray.horizontal){
						this.elementsArray.horizontal.setProperties(0,0,0,0);
						this.elementsArray.horizontal.hide();
					}
				}				
					
			};
			
			this.setProperties = function(len,align){				
				this.len=len+1;
				this.align=align;
				this.refresh();
			};
			
			this.retrieveLoadingDims = function(elem){
				if (elem.onLoadPrev)
					elem.onLoadPrev();
				elem.loaded=true;				
				var ret=true;			
				for (var i in this.elementsArray) if (this.elementsArray[i].id)
					ret&=this.elementsArray[i].loaded
				this.loaded=ret;	
				if (this.loaded){
					this.parentDock.updateDims();					
					for (var i in this.elementsArray) if (this.elementsArray[i].id)
						this.elementsArray[i].show();
				}
				if (elem.onLoadNext)
					elem.onLoadNext();
			};
			
			this.setElements = function(args){
				if (typeof(args)!="undefined" && args!=null){
					for (var i in args)
						for (var ii in args[i]){
							var id = "euDock_"+ii+"_"+euEnv.Kost.next();
							//if (this.elementsArray[i]){
							//	this.elementsArray[i].hide();
							//	euEnv.euDockArray[this.elementsArray[i].id]=null;
							//}
							euEnv.euDockArray[id]=new window[ii](id,args[i][ii],this.parentDock.divBar,"euEnv.euDockArray."+this.id+".retrieveLoadingDims(euEnv.euDockArray."+id+");");
							this.elementsArray[i]=euEnv.euDockArray[id];
							euEnv.euDockArray[id].loaded=false;
						}
				}				
			};
		};
/* 
 ****************************************
 ******    euDock Bar Object      *******
 ******     (END)                 *******
 **************************************** 
 */
function euThread(){
	euDimensioni();
	offsEut();
    euEnv.timeout=window.setTimeout("euThread();",euEnv.refreshTime);
    
    euEnv.exeThread = false;
    if (euEnv.mouseMoved)
		for (var i in euEnv.euDockArray)
			if (euEnv.euDockArray[i].mouseMove)
				euEnv.exeThread |= euEnv.euDockArray[i].mouseMove(euEnv.euScrOfX+euEnv.x,euEnv.euScrOfY+euEnv.y);
	euEnv.mouseMoved=false;			
	if (euEnv.exeThread)
		euEnv.exeThreadWhiteLoop=5;    
  
    if(euEnv.exeThreadWhiteLoop>0)
    	euKernel();    	
		
	for (var i in euEnv.euDockArray)
		if (euEnv.euDockArray[i].riposition)
			euEnv.euDockArray[i].riposition();    	
};

function euKernel(){
	euEnv.exeThread = false;
	for (var i in euEnv.euDockArray)
		if (euEnv.euDockArray[i].kernel)
			euEnv.exeThread |= euEnv.euDockArray[i].kernel();

	if (euEnv.exeThread)
		euEnv.exeThreadWhiteLoop=5;
	else
		euEnv.exeThreadWhiteLoop--;					
}; 

function on_MouseMove(e) {	
	if (!e) var e = window.event;
	euEnv.x = e.clientX;
	euEnv.y = e.clientY;
	euEnv.mouseMoved = true;
	if (euEnv.onmousemoveBK)
		return euEnv.onmousemoveBK(e);
	return true;
};

function on_MouseDown(e) {
	if (!e) var e = window.event;	
	for (var i in euEnv.euDockArray)
		if (euEnv.euDockArray[i].mouseDown)
			euEnv.exeThread |= euEnv.euDockArray[i].mouseDown(euEnv.euScrOfX+e.clientX,euEnv.euScrOfY+e.clientY);
	if (euEnv.onmousedownBK)
		return euEnv.onmousedownBK(e);
	return true;
};

function on_MouseUp(e) {
	if (!e) var e = window.event;	
	for (var i in euEnv.euDockArray)
		if (euEnv.euDockArray[i].mouseUp)
			euEnv.exeThread |= euEnv.euDockArray[i].mouseUp(euEnv.euScrOfX+e.clientX,euEnv.euScrOfY+e.clientY);
	if (euEnv.onmouseupBK)
		return euEnv.onmouseupBK(e);
	return true;
};

function on_MouseClick(e) {
	if (!e) var e = window.event;
	for (var i in euEnv.euDockArray)
		if (euEnv.euDockArray[i].mouseClick)
			euEnv.exeThread |= euEnv.euDockArray[i].mouseClick(euEnv.euScrOfX+e.clientX,euEnv.euScrOfY+e.clientY);
	if (euEnv.onclickBK)
		return euEnv.onclickBK(e);
	return true;
};

if (document.onmousemove)
	euEnv.onmousemoveBK = document.onmousemove;
document.onmousemove  = on_MouseMove;

if (document.onmousedown)
	euEnv.onmousedownBK = document.onmousedown;
document.onmousedown  = on_MouseDown;

if (document.onmouseup)
	euEnv.onmouseupBK = document.onmouseup;
document.onmouseup    = on_MouseUp;

if (document.onclick)
	euEnv.onclickBK = document.onclick;
document.onclick      = on_MouseClick;

euDimensioni();
offsEut();
euThread();
