var hidRankBegin = 0;
var hidRankEnd = 0;
var hidUserCnt = 0;
var listType = 2;

$().ready(function() {
    hidRankBegin = parseInt($("#hidRankBegin").val());
    hidRankEnd = parseInt($("#hidRankEnd").val());
    hidUserCnt = parseInt($("#hidUserCnt").val());
    $("#scoreUp").bind ("click", function() { clickUp(); } );
    $("#scoreDown").bind ("click", function() { clickDown(); } );
})

function aflacHelp()
{
    if (!document.playArea.IsPlaying()) {
        document.playArea.GotoFrame(0);
    }
}

function aflacInvitation() {
    invite();
}

function aflacSendFeed(score) {
    
}

function aflacActivity(uid,score,secret) 
{
    //send activity
    postActivityWithPic(score + '点獲得したにゃー','http://aflac.communityfactory.net/static/apps/afrac/img/activity.gif','image/gif');
    
    var ajaxUrl = UrlConfig.BaseUrl + '/ajax/afrac/updatescore';
    $.ajax({
	    type: "POST",   
	    url: ajaxUrl,
	    data: {
                score : score,
                secret : secret
               },
	    dataType: "json",
	    success: function(responseObject) {
	        if (responseObject == 1) {
                if (listType == 1) {
                    listType = 2;
                    changeType(1);
                }
                else {
                    listType = 1;
                    changeType(2);
                }
            }
        }
    });
} 

function clickUp()
{   
    $("#scoreUp").unbind("click");
      
    if (hidRankBegin != 1) {
        //begin ajax request
        getRankInfo('up');
    }
    else {
        $("#scoreUp").bind ("click", function() { clickUp(); } );
    }

}

function clickDown() 
{
    $("#scoreDown").unbind("click");
    
    if (hidRankEnd < hidUserCnt) {
        //begin ajax request
        getRankInfo('down');
    }
    else {
        $("#scoreDown").bind ("click", function() { clickDown(); } );
    }
}

function  getRankInfo(direction) 
{
    var ajaxUrl = UrlConfig.BaseUrl + '/ajax/afrac/getrankinfo';
	try {
	    $.ajax({
		    type: "POST",   
		    url: ajaxUrl,
		    data: "rankStart=" + hidRankBegin + "&rankEnd=" + hidRankEnd +"&userCnt=" + hidUserCnt + "&direction=" + direction + "&type=" + listType,
		    dataType: "json",
		    success: function(responseObject) 
		    {
    		    //alert('ajaxok');
    	        //show response array data to list table
                if (responseObject) 
                { 
                	var aryInfo = responseObject.info;
                	hidRankBegin = parseInt(responseObject.begin);
                	hidRankEnd = parseInt(responseObject.end);      	
                	var htm="";
                	
                    for (i = 0 ; i < aryInfo.length ; i++)
                    {
                        htm +='<li>'
                            +'<p class="pic" style="background-image:url(' + aryInfo[i].miniThumbnailUrl + ');">' + aryInfo[i].displayName + '</p>'
                            +'<p class="rank">〓<span>' + aryInfo[i].rank + '位</span>〓</p>'
                            +'<p class="name">' + aryInfo[i].displayName + '</p>'
                            +'<p class="score">' + aryInfo[i].score + '点</p>'
                            +'</li>';
                    }
                    $('#olli').html(htm);
                    $("#scoreUp").bind ("click", function() { clickUp(); } );
                    $("#scoreDown").bind ("click", function() { clickDown(); } );
            	}
            },
             error: function(XMLHttpRequest, textStatus, errorThrown) {
		         //alert(textStatus);
		    }
	    });
	}
	catch (e) {
       // alert(e);
    }
}

function changeType(number)
{
    if (listType != number) {
        //change button classes
        if (number ==1) {
            $('#allRanking').removeClass().addClass("btn active");
            $('#myMixiRanking').removeClass().addClass("btn");
        }
        else {
            $('#allRanking').removeClass().addClass("btn");
            $('#myMixiRanking').removeClass().addClass("btn active");
        }
        
        listType = number;
        var ajaxUrl = UrlConfig.BaseUrl + '/ajax/afrac/changetype';
    	try {
    	    $.ajax({
    		    type: "POST",   
    		    url: ajaxUrl,
    		    data: "type=" + listType,
    		    dataType: "json",
    		    success: function(responseObject) 
    		    {
        		    //alert('ajaxok');
        	        //show response array data to list table
                    if (responseObject) 
                    { 
                    	var aryInfo = responseObject.info;
                    	hidRankBegin = parseInt(responseObject.begin);
                    	hidRankEnd = parseInt(responseObject.end);
                    	hidUserCnt = parseInt(responseObject.userCnt);	            	
                    	var htm="";
                    	
                        for (i = 0 ; i < aryInfo.length ; i++)
                        {
                            htm +='<li>'
                                +'<p class="pic" style="background-image:url(' + aryInfo[i].miniThumbnailUrl + ');">' + aryInfo[i].displayName + '</p>'
                                +'<p class="rank">〓<span>' + aryInfo[i].rank + '位</span>〓</p>'
                                +'<p class="name">' + aryInfo[i].displayName + '</p>'
                                +'<p class="score">' + aryInfo[i].score + '点</p>'
                                +'</li>';
                        }
                        $('#olli').html(htm);
                	}
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
    		         //alert(textStatus);
    		    }
    	    });
    	}
    	catch (e) {
            //alert(e);
        }
    }
}