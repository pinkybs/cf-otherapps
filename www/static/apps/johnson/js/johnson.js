
var hidRankBegin = 0;
var hidRankEnd = 0;
var hidUserCnt = 0;
var listType = 2;
var ajxComplete = false;
var lastRankType = 2;

$().ready(function() {
    hidRankBegin = parseInt($("#hidRankBegin").val());
    hidRankEnd = parseInt($("#hidRankEnd").val());
    hidUserCnt = parseInt($("#hidUserCnt").val());
})
//used in flash
function callFriendInvite()
{
    invite();
}

function addNewsFeed(msg, reciptents, result)
{
    if (1 == result) {
        postActivity(msg, reciptents);
    }
    otherTypeRank(lastRankType);
}

function otherTypeRank(rankType)
{
    lastRankType = rankType;
    //if (listType != rankType) {
        //change button classes
        if (rankType ==1) {
            $('#allRanking').removeClass().addClass("btn active");
            $('#myMixiRanking').removeClass().addClass("btn");
        }
        else {
            $('#allRanking').removeClass().addClass("btn");
            $('#myMixiRanking').removeClass().addClass("btn active");
        }
        
        if (listType != rankType) listType = rankType;
        
        var ajaxUrl = UrlConfig.BaseUrl + '/ajax/johnson/othertyperank';
        
        $.ajax({
            type: "POST",   
            url: ajaxUrl,
            data: "type=" + listType,
            dataType: "json",
            success: function(responseObject) 
            {
                //show response array data to list table
                if (responseObject) 
                { 
                    var aryInfo = responseObject.info;
                    hidRankBegin = parseInt(responseObject.begin);
                    hidRankEnd = parseInt(responseObject.end);
                    hidUserCnt = parseInt(responseObject.userCnt);                  
                    var htm = "";
                    
                    for (i = 0 ; i < aryInfo.length ; i++)
                    {
                        //var userRank = aryInfo[i].rank;
                        var userHonor = aryInfo[i].honor_id;
                        var className = '';
                        
                        if (userHonor == 13) {
                            className = 'king';
                        }
                        else if (userHonor >= 10 && userHonor <= 12) {
                            className = 'master';
                        }
                        htm +='<li class="'+className+'">'
                            +'<p class="pic" style="background-image:url(' + aryInfo[i].miniThumbnailUrl + ');">' + aryInfo[i].displayName + '</p>'
                            +'<p class="rank"><span>' + aryInfo[i].rank + '位</span></p>'
                            +'<p class="name">' + aryInfo[i].displayName + '</p>'
                            +'<p class="score">' + aryInfo[i].score + '点</p>'
                            +'</li>';
                    }
                    $('#olli').html(htm);
                   
                }
            }
            
        });
    //}
    
}

function moveToUpOrDown(direction)
{
    if (direction == 'down' && hidRankEnd >= hidUserCnt) {
        return;
    }
    if (direction == 'up' && hidRankBegin == 1) {
        return;
    }
    
    if (!ajxComplete) {
        
        ajxComplete = true;
        var ajaxUrl = UrlConfig.BaseUrl + '/ajax/johnson/gonext';
        
        $.ajax({
            type: "POST",   
            url: ajaxUrl,
            data: "rankStart=" + hidRankBegin + "&rankEnd=" + hidRankEnd +"&userCnt=" + hidUserCnt + "&direction=" + direction + "&type=" + listType,
            dataType: "json",
            error : function () {
                ajxComplete = false;
            },
            success: function(responseObject) 
            {
                //show response array data to list table
                if (responseObject) 
                { 
                    var aryInfo = responseObject.info;
                    hidRankBegin = parseInt(responseObject.begin);
                    hidRankEnd = parseInt(responseObject.end);
                    hidUserCnt = parseInt(responseObject.rankCount);      
                    var htm="";
                    
                    for (i = 0 ; i < aryInfo.length ; i++)
                    {
                        //var userRank = aryInfo[i].rank;
                        var userHonor = aryInfo[i].honor_id;
                        var className = '';
                        
                        if (userHonor == 13) {
                            className = 'king';
                        }
                        else if (userHonor >= 10 && userHonor <= 12) {
                            className = 'master';
                        }
                            
                        htm +='<li class="'+className+'">'
                            +'<p class="pic" style="background-image:url(' + aryInfo[i].miniThumbnailUrl + ');">' + aryInfo[i].displayName + '</p>'
                            +'<p class="rank"><span>' + aryInfo[i].rank + '位</span></p>'
                            +'<p class="name">' + aryInfo[i].displayName + '</p>'
                            +'<p class="score">' + aryInfo[i].score + '点</p>'
                            +'</li>';
                    }
                    $('#olli').html(htm);
                    ajxComplete = false;
                }
            }
             
        });
    }
}