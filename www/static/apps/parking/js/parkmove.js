/**
 * parking(/parking/parkmove.js)
 * 
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/02/19    Liz
 */

var parkMove = Class.create();
        
parkMove.prototype = {
	
	   initialize : function(parkCount)
	   {
			Event.observe('moveLeft', 'mouseover', this.mouseoverLeft.bindAsEventListener(this));
			Event.observe('moveLeft', 'mouseout', this.mouseout.bindAsEventListener(this));
			Event.observe('moveRight', 'mouseover', this.mouseoverRight.bindAsEventListener(this));
			Event.observe('moveRight', 'mouseout', this.mouseout.bindAsEventListener(this));
			
			this.maxRight = -260 * parkCount - 20 + 690;
			this.moveTime = parkCount/2;
			
			if (parseInt($('dynamicArea').style.left) != 0 ) {
				new Effect.Morph('dynamicArea',{style:'left:0px;', duration:1});
			}			
	  },
	  
	  setParkCount : function(parkCount)
	  {
		this.maxRight = -260 * parkCount - 20 + 690;
		this.moveTime = parkCount/2 ;
		
		if (this.moveDirection == 1) {
			if (this.effect.state == 'running') {
				this.mouseoverRight();
			}
		}
	  },
	  
	  mouseoverLeft : function() 
	  {     
	  	this.moveDirection = 0;
        if ( $('dynamicArea') ) {
          $('parkingLeft').className = "hover";
		  this.effect = new Effect.Morph('dynamicArea',{style:'left:0px;', duration:this.moveTime});
        }
	  },
	  
	  mouseoverRight : function() 
	  {
	  	this.moveDirection = 1;
        if ( $('dynamicArea') ) {
          $('parkingRight').className = "hover";
		  this.effect = new Effect.Morph('dynamicArea',{style:'left:' + this.maxRight + 'px;', duration:this.moveTime});
	    }
	  },
	  
	  mouseout : function() 
	  {
        $('parkingLeft').className =  "";
        $('parkingRight').className =  "";
	  	this.moveDirection = -1;
		this.effect.cancel();		
	  }
	}
