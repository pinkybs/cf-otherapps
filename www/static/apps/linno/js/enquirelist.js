/**
 * linno(/linno/enquire.js)
 * 
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/06/26    Liz
 */


/**
 * windows load function
 * Register function on window
 */
Event.observe(window, 'load', function() {
    
	var enquireCount = $F('enquireCount');
	var pageIndex = $F('pageIndex');
    if ( enquireCount > 5 ) {
        //show page nav
        var nav = showPagerNavByHtml(enquireCount, Number(pageIndex), 5, 3, '/linno/enquirelist');
    	$('pageNav').innerHTML = nav;
    }
});
