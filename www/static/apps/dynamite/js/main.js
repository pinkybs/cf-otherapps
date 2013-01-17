/*
----------------------------------------------
DDP main JavaScript

Created Date: 2009/06/29
Author: Yu Uno
Last Up Date : 2009/06/29
Author: Yu Uno
----------------------------------------------
*/

var $j = jQuery.noConflict();

var ddpUseID = {
	scrollArea1 : '#newsAreaInner',
	scrollArea2 : '#helpList',
	slideWrap : '#playArea',
	slideArea : '#playAreaWrap',
	slideNav : '#slideNav',
	slideWidth : 687,
	slideTime : 600
};

var jsonData = {
	src : 'cmn/js/list.js'//読み込みJson
};


/*onLoad*/
$j(function(){
	
	/*プレイエリアスライド*/
	var slideWrapID = $j(ddpUseID.slideWrap);
	var slideAreaID = $j(ddpUseID.slideArea);
	var slideNavID = $j(ddpUseID.slideNav);
	var slideLength = slideWrapID.find('.inner').length;
	var slideMaxWidth = ddpUseID.slideWidth * (slideLength - 1);
	var slideMaxLeft = '-' + slideMaxWidth + 'px';
	var homePosition = 1 * ddpUseID.slideWidth;
	
	slideAreaID.css('left','-' + homePosition + 'px');//ホーム初期位置設定
				
	/*スクロールバー変更*/
	$j.extend($j.fn.jScrollPane.defaults, {
		dragMinHeight: 73,
		dragMaxHeight: 200,
		reinitialiseOnImageLoad: true,
		animateTo:true,
		animateInterval:50,
		animateStep:3
	});
	$j(ddpUseID.scrollArea1).jScrollPane({
		scrollbarWidth: 27
	});
	$j(ddpUseID.scrollArea2).jScrollPane({
		scrollbarWidth: 27
	});
	
});

function dynamiteSlideNavOperate(direction, firstChildId, lastChildId, currentId)
{
    var slideAreaID = $j(ddpUseID.slideArea);
    var nowPosition = slideAreaID.css('left');
    var homePosition = 1 * ddpUseID.slideWidth;
        
    if (direction == 'left') {
        direction = '+=';
        leftRight(direction, firstChildId, lastChildId, currentId);
    }
    else if (direction == 'right') {
        direction = '-=';
        leftRight(direction, firstChildId, lastChildId, currentId);
    } else if (direction == 'home') {
        toHome();
    }
    
    function leftRight(direction, firstChildId, lastChildId, currentId){
    
        slideAreaID.stop().animate({
            left: direction + ddpUseID.slideWidth + 'px'
        }, ddpUseID.slideTime, 'swing', function(){removeOtherChild(firstChildId, lastChildId, currentId);});
    }
    function toHome(){
        slideAreaID.stop().animate({
            left: '0px'
        }, ddpUseID.slideTime, 'swing');
    }
    
    function removeOtherChild(firstChildId, lastChildId, currentId){
        if ( firstChildId != currentId ) {
            $j('#' + firstChildId).remove();
        }
        else {
            $j('#' + lastChildId).remove();
        }
        $j('#playAreaWrap').css('left','0px');
    }
    
}
