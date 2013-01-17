
$j(function(){
    var featuresCount = 0;
    var fcstr = $j('#txtFeaturesCount').val();
    if (fcstr != '') {
        featuresCount = parseInt(fcstr);
    }
    var phpEntryCount = 0;
    var pcstr = $j('#txtPhpEntryCount').val();
    if (pcstr != '') {
        phpEntryCount = parseInt(pcstr);
    }
            
    var heightFF = 1055;
    var heightIE = 1055;
    
    var heightEntryFF;
    var heightEntryIE;
    
    if ( phpEntryCount < 3 ) {
       heightEntryFF = 936;
       heightEntryIE = 936;
    }
    else {
       heightEntryFF = 134 + (phpEntryCount - 2)*29;
       heightEntryIE = 124 + (phpEntryCount - 2)*32;
    }
    
    var heighFeaturesFF;
    var heighFeaturesIE;
    
    heighFeaturesFF = 1071 + featuresCount*18;
    heighFeaturesIE = 1066 + featuresCount*18;
    
    if ( heightEntryFF > heighFeaturesFF ) {
       heightFF = heightEntryFF;
    }
    else {
       heightFF = heighFeaturesFF;
    }
    
    if ( heightEntryIE > heighFeaturesIE ) {
       heightIE = heightEntryIE
    }
    else {
       heightIE = heighFeaturesIE;
    }
                
    if ( !$j.browser.msie ) {
        adjustHeight(heightFF);
    }
    else {
        adjustHeight(heightIE);
    }
});