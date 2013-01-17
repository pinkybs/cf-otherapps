//define default page size
var CONST_TEXTAREA_SIZE = 100;
var CONST_INPUTTEXT_SIZE = 30;
var catNow = '';
var nicknameNow = '';
var regionNow = '';
var confirmOk = 1;
var answerArray2 = new Array();
categoryCatE = ['all','character','politics','life','entertainment','hobby'];
categoryCat = ['すべて','性格診断','政治・経済','社会生活','芸能・スポーツ','趣味・その他'];
categoryNickname = ['非公開（匿名）','公開する'];
categoryRegion = ['全体公開','マイミクまで','マイミクのマイミクまで'];

$().ready(function() 
{   
    catNow = $("#hidCat").val();
    nicknameNow = $("#hidNickname").val();
    regionNow = $("#hidRegion").val();    
    
    setCat(catNow);
    setNickname(nicknameNow);
    setRegion(regionNow);
    
    $("#inputTitle").blur( function() { getAlertInfo(); } );
    $("#inputTitle").keyup( function() { getAlertInfo(); } );
    $("#inputQ1").blur( function() { getAlertInfo(); } ); 
    $("#inputQ1").keyup( function() { getAlertInfo(); } ); 
    $("#inputQ2").blur( function() { getAlertInfo(); } );
    $("#inputQ2").keyup( function() { getAlertInfo(); } );
    $("#div02").hide();
    $("#div03").hide();
    $("#div04").hide();
    $("#div05").hide();
});

function setCat(cat)
{
    $("#ulCat").find('li.active').removeClass();
    $("#cat" + cat).addClass("active");
    catNow = cat;
}

function setNickname(nickname)
{
    $("#ulNickname").find('li.active').removeClass();
    $("#nickname" + nickname).addClass("active");
    nicknameNow = nickname; 
}

function setRegion(region)
{
    $("#ulRegion").find('li.active').removeClass();
    $("#region" + region).addClass("active");
    regionNow = region;    
}

function cancelApprove() 
{   
    $("#div02").hide();
    $("#div01").show();
    gotoTop();
}

function cancelDeny() 
{   
    $("#div04").hide();
    $("#div01").show();
    gotoTop();
}

function submitApprove()
{   
    approveQuestion(answerArray2);
}

function submitDeny()
{   
    denyQuestion();
}

function toApprove() 
{
    if (confirmOk == 1) {
        answerArray2.length = 0;
        var answerArray = new Array();
        var i = 0;
        answerArray.push($.trim($('#inputQ1').val()));
        answerArray.push($.trim($('#inputQ2').val()));
        
        //write data to hidden div02
        //divwarpper2 
        var htm = '';
        htm += '<div class="section">';
		htm +=     '<label for="inputTitle" class="inputTitle">設問（※）:</label>';
		htm +=     '<p style="width:500px;" class="stringCut">' + $('#inputTitle').val().escapeHTML() + '</p>';
		htm += '</div>';
		
		var words = categoryCat[catNow];
		htm += '<div class="section">';
		htm +=     '<label class="inputCat">カテゴリ（※）:</label>';
		htm +=     '<p>' + words +'</p>';
		htm += '</div>';
		
		htm += '<div class="section">';
		htm +=     '<label class="inputQ1">選択肢1（※）:</label>';
		htm +=     '<p>' + $.trim($('#inputQ1').val()).escapeHTML() + '</p>';
		htm += '</div>';
        htm += '<div class="section">';
		htm +=     '<label class="inputQ2">選択肢2（※）:</label>';
		htm +=     '<p>' + $.trim($('#inputQ2').val()).escapeHTML() + '</p>';
		htm += '</div>';
		
        var j = 3;
        for (i=3;i<=10;i++) {
            if ($.trim($('#inputQ'+i).val()) != '') {
                answerArray.push($.trim($('#inputQ'+i).val()));
                htm += '<div class="section">';
				htm +=     '<label class="inputQ' + j + '">選択肢' + j + ':</label>';
				htm +=     '<p>' + $.trim($('#inputQ'+i).val().escapeHTML()) +'</p>';
				htm += '</div>';
				j++;
            }
        }
        
        $("#divwarpper2").html(htm);
        answerArray2 = answerArray;
        $("#nicknameshow02").html('<p>' + categoryNickname[nicknameNow] + '</p>');
        $("#regionshow02").html('<p>' + categoryRegion[regionNow-1] + '</p>');
        $("#div01").hide();
        $("#div02").show();
        gotoTop();
    }
}

function toDeny() 
{   
    if (confirmOk == 1) {        
        //write data to hidden div04
        //divwarpper2 
        var htm = '';
        htm += '<div class="section">';
		htm +=     '<label for="inputTitle" class="inputTitle">設問（※）:</label>';
		htm +=     '<p style="width:500px;" class="stringCut">' + $('#inputTitle').val().escapeHTML() + '</p>';
		htm += '</div>';
		
		var words = categoryCat[catNow];
		htm += '<div class="section">';
		htm +=     '<label class="inputCat">カテゴリ（※）:</label>';
		htm +=     '<p>' + words +'</p>';
		htm += '</div>';
		
		htm += '<div class="section">';
		htm +=     '<label class="inputQ1">選択肢1（※）:</label>';
		htm +=     '<p>' + $.trim($('#inputQ1').val()).escapeHTML() + '</p>';
		htm += '</div>';
        htm += '<div class="section">';
		htm +=     '<label class="inputQ2">選択肢2（※）:</label>';
		htm +=     '<p>' + $.trim($('#inputQ2').val()).escapeHTML() + '</p>';
		htm += '</div>';
		
		var j = 3;
        for (i=3;i<=10;i++) {
            if ($.trim($('#inputQ'+i).val()) != '') {
                htm += '<div class="section">';
				htm +=     '<label class="inputQ' + j + '">選択肢' + j + ':</label>';
				htm +=     '<p>' + $.trim($('#inputQ'+i).val().escapeHTML()) +'</p>';
				htm += '</div>';
				j++;
            }
        }
 
        $("#divwarpper4").html(htm);
        $("#nicknameshow04").html('<p>' + categoryNickname[nicknameNow] + '</p>');
        $("#regionshow04").html('<p>' + categoryRegion[regionNow-1] + '</p>');
        $("#div01").hide();
        $("#div04").show();
        gotoTop();
    }
}

function getAlertInfo()
{
    var alertInfo = '';
    confirmOk = 0;
    $("#alertInfo > ul").html(alertInfo);
    
    if ($.trim($('#inputTitle').val()) == ''){
        alertInfo += '<li>設問が未入力です</li>';
    }
        
    if ($.trim($("#inputTitle").val().replace(/(\r)?\n/g,"")).len() > CONST_TEXTAREA_SIZE) {
        alertInfo += '<li>設問が長すぎます</li>';
    }
    
    if ($.trim($('#inputQ1').val()) == ''){
        alertInfo += '<li>選択肢1が未入力です</li>'; 
    }
    
    if ($.trim($("#inputQ1").val()).len() > CONST_INPUTTEXT_SIZE) {
        alertInfo += '<li>選択肢1が長すぎます</li>';
    }
    
    if ($.trim($('#inputQ2').val()) == ''){
        alertInfo += '<li>選択肢2が未入力です</li>'; 
    }
    
    if ($.trim($("#inputQ2").val()).len() > CONST_INPUTTEXT_SIZE) {
        alertInfo += '<li>選択肢2が長すぎます</li>';
    }
    
    if (alertInfo != '') {
        $('#alertInfo').addClass("alert");
        $("#alertInfo > ul").html(alertInfo);
    }
    else {
        $('#alertInfo').removeClass();
        confirmOk = 1;
    }
}

function approveQuestion(answerArray) 
{
    answerArray = toJSON(answerArray);
    var ajaxUrl = UrlConfig.BaseUrl + '/ajax/millionminds/approve';
    $.ajax({
	    type: "POST",   
	    url: ajaxUrl,
	    data: "answerArray=" + answerArray + "&inputTitle=" + $.trim($("#inputTitle").val().replace(/(\r)?\n/g,"")) + "&cat=" + catNow
	          + "&nickname=" + nicknameNow + "&region=" + regionNow + "&uid=" + $('#hidUid').val() + "&id=" + $('#hidId').val(),
	    dataType: "json",
	    timeout : 10000,
	    success: function(responseObject) {
	        //show response array data to list table
            if (responseObject == 1) {
                $("#div02").hide();
                $("#div03").show();
                gotoTop();
        	}
        	else {
        	    $("#div02").hide();
                $("#div01").show();
                $('#alertInfo').addClass("alert");
        	    $("#alertInfo > ul").html('<li>システムエラー。</li>');
        	    gotoTop();
        	}
        },
        error: function(request, settings) {
            if (settings == 'timeout') {
                error = '<li>通信の問題で処理を中断しました。しばらく経ってからもう一度お試しください。</li>';
            }
            else {
                error = '<li>システムエラー。</li>';
            }
            $("#div02").hide();
            $("#div01").show();
            $('#alertInfo').addClass("alert");
	        $("#alertInfo > ul").html(error);
	        gotoTop();
	    }
    });
}

function denyQuestion ()
{
    var ajaxUrl = UrlConfig.BaseUrl + '/ajax/millionminds/deny';
    $.ajax({
	    type: "POST",   
	    url: ajaxUrl,
	    data: "id=" + $('#hidId').val(),
	    dataType: "json",
	    timeout : 10000,
	    success: function(responseObject) {
	        //show response array data to list table
            if (responseObject == 1) {
                $("#div04").hide();
                $("#div05").show();
                gotoTop();
        	}
        	else {
        	    $("#div04").hide();
                $("#div01").show();
                $('#alertInfo').addClass("alert");
        	    $("#alertInfo > ul").html('<li>システムエラー。</li>');
        	    gotoTop();
        	}
        },
        error: function(request, settings) {
            if (settings == 'timeout') {
                error = '<li>通信の問題で処理を中断しました。しばらく経ってからもう一度お試しください。</li>';
            }
            else {
                error = '<li>システムエラー。</li>';
            }
            $("#div04").hide();
            $("#div01").show();
            $('#alertInfo').addClass("alert");
	        $("#alertInfo > ul").html(error);
	        gotoTop();
	    }
    });
}

/**   
* length of chinese and jp charactor is change to 2  
*/   
String.prototype.len=function()
{
    return this.replace(/[^\x00-\xff]/g,"**").length; 
}