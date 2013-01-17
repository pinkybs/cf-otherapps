/**
 * additem(/shopping/additem.js)
 * shopping wishitem add
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/08/14    xiali
 */
 
 $j(document).ready(function() {
     $j('#addNext').click(addItemAction);
     adjustHeight();   
});
 
 /*
 add wishitem
 */
 function addItemAction()
 {
    var ajaxUrl = UrlConfig.BaseUrl + '/ajax/shopping/addwishitem';
    try {
        $j.ajax({
            type: "GET",   
            url: ajaxUrl,
            data:"iid=" + $j('#hidIid').val(),
            dataType: "text",
            success: function(responseText) {
                if (responseText) {                
                    $j('#message').html('以下の商品を欲しいものリストに追加しました。');
                    $j('#btnAddNext').hide();
                    $j('#btnaddConfirm').show();                    
                }
                else {
                    $j('#message').html('追加欲しいものリスト失敗しました。');
                    $j('#wishItem').hide();
                    $j('#btnAddNext').hide();
                }
            }
        });
    }catch (e) {
        //alert(e);
    }
 }
 