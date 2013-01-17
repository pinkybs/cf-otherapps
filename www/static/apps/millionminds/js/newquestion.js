//define default page size
var CONST_TEXTAREA_SIZE = 100;
var CONST_INPUTTEXT_SIZE = 30;
var catNow = '';
var nicknameNow = '';
var regionNow = '';
var confirmOk = 0;
var answerArray2 = new Array();

$().ready(function() 
{   
    catNow = $('input[name=cat]:checked').val();
    nicknameNow = $('input[name=nickname]:checked').val();
    regionNow = $('input[name=region]:checked').val();
    
    $("#inputTitle").blur( function() { getAlertInfo(); } );
    $("#inputTitle").keyup( function() { getAlertInfo(); } );
    $("#inputQ1").blur( function() { getAlertInfo(); } ); 
    $("#inputQ1").keyup( function() { getAlertInfo(); } ); 
    $("#inputQ2").blur( function() { getAlertInfo(); } );
    $("#inputQ2").keyup( function() { getAlertInfo(); } );
    $("#div02").hide();
    $("#div03").hide();
});

function cancelConfirm() 
{
    $("#div02").hide();
    $("#div01").show();
    gotoTop();
}

function submitConfirm()
{
    insertQuestion(answerArray2);
}

function toConfirm(){
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
		
		var words = $('#' + catNow).attr("title");
		htm += '<div class="section">';
		htm +=     '<label class="inputCat">カテゴリ（※）:</label>';
		htm +=     '<p>' + words +'</p>';
		htm += '</div>';
		
		htm += '<div class="section">';
		htm +=     '<label class="inputQ1">選択肢1（※）:</label>';
		htm +=     '<p>' + $('#inputQ1').val().escapeHTML() + '</p>';
		htm += '</div>';
        htm += '<div class="section">';
		htm +=     '<label class="inputQ2">選択肢2（※）:</label>';
		htm +=     '<p>' + $('#inputQ2').val().escapeHTML() + '</p>';
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
        $("#div01").hide();
        $("#div02").show();
        gotoTop();
    }
}

function changeCat(type)
{
    if ( type != catNow ) {
        if (type == 'politics') {
            catNow = 'politics';
            $('#cat0').addClass("active");
            $('#cat1').removeClass();
            $('#cat2').removeClass();
            $('#cat3').removeClass();
        }
        else if (type == 'life') {
            catNow = 'life';
            $('#cat1').addClass("active");
            $('#cat0').removeClass();
            $('#cat2').removeClass();
            $('#cat3').removeClass();
        }
        else if (type == 'entertainment') {
            catNow = 'entertainment';
            $('#cat2').addClass("active");
            $('#cat0').removeClass();
            $('#cat1').removeClass();
            $('#cat3').removeClass();
        }
        else if (type == 'hobby') {
            catNow = 'hobby';
            $('#cat3').addClass("active");
            $('#cat1').removeClass();
            $('#cat2').removeClass();
            $('#cat0').removeClass();
        }
    }
}

function changeNickname(type) 
{
    if ( type != nicknameNow ) {
        if (type == 'open') {
            nicknameNow = 'open';
            $('#nickname0').addClass("active");
            $('#nickname1').removeClass();
            $('#nicknameH0').addClass("active");
            $('#nicknameH1').removeClass();
        }
        else if (type == 'close') {
            nicknameNow = 'close';
            $('#nickname1').addClass("active");
            $('#nickname0').removeClass();
            $('#nicknameH1').addClass("active");
            $('#nicknameH0').removeClass();
        }
    }
}

function changeRegion(type) 
{
    if ( type != regionNow ) {
        if (type == 'all') {
            regionNow = 'all';
            $('#region0').addClass("active");
            $('#region1').removeClass();
            $('#region2').removeClass();
            $('#regionH0').addClass("active");
            $('#regionH1').removeClass();
            $('#regionH2').removeClass();
        }
        else if (type == 'friend') {
            regionNow = 'friend';
            $('#region1').addClass("active");
            $('#region0').removeClass();
            $('#region2').removeClass();
            $('#regionH1').addClass("active");
            $('#regionH0').removeClass();
            $('#regionH2').removeClass();
        }
        else if (type == 'fof') {
            regionNow = 'fof';
            $('#region2').addClass("active");
            $('#region0').removeClass();
            $('#region1').removeClass();
            $('#regionH2').addClass("active");
            $('#regionH0').removeClass();
            $('#regionH1').removeClass();
        }
    }
}

function getAlertInfo()
{
    $('#alertInfo').addClass("alert");
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
        $("#alertInfo > ul").html(alertInfo);
    }
    else {
        $('#alertInfo').removeClass();
        confirmOk = 1;
    }
}

function insertQuestion(answerArray) 
{
    answerArray = toJSON(answerArray);
    var ajaxUrl = UrlConfig.BaseUrl + '/ajax/millionminds/insertquestion';
    $.ajax({
	    type: "POST",   
	    url: ajaxUrl,
	    data: "answerArray=" + answerArray + "&inputTitle=" + $.trim($("#inputTitle").val().replace(/(\r)?\n/g,"")) + "&cat=" + catNow + "&nickname=" + nicknameNow + "&region=" + regionNow,
	    dataType: "json",
	    timeout : 10000,
	    success: function(responseObject) {
	        //show response array data to list table
            if (responseObject == 1) {   
                $("#timerImg").attr("src", UrlConfig.StaticUrl + "/apps/millionminds/img/timer.gif");
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


/**   
* length of chinese and jp charactor is change to 2  
*/   
String.prototype.len=function()
{
    return this.replace(/[^\x00-\xff]/g,"**").length;
}