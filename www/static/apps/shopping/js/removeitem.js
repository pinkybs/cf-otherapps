/**
 * removeitem(/shopping/removeitem.js)
 * shopping wishitem remove
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/08/14    xiali
 */
 
 $j(document).ready(function() {
	 $j('#removeNext').click(removeItemAction);
	 adjustHeight();  
});
 
 /*
 remove wishitem
 */
 function removeItemAction()
 {
    var ajaxUrl = UrlConfig.BaseUrl + '/ajax/shopping/removewishitem';
    try {
        $j.ajax({
            type: "GET",   
            url: ajaxUrl,
            data:"iid=" + $j('#hidIid').val(),
            dataType: "text",
            success: function(responseText) {
                if (responseText) {                
                    $j('#message').html('以下の商品を欲しいものリストからはずしました。');
                    $j('#btnRemoveNext').hide();
                    $j('#btnRemoveConfirm').show();                    
                }
                else {
                    $j('#message').html('削除欲しいものリスト失敗しました。');
                    $j('#wishItem').hide();
                    $j('#btnRemoveNext').hide();
                }
            }
        });
    }catch (e) {
        //alert(e);
    }
 }
 