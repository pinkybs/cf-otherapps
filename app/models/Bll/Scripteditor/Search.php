<?php

require_once 'Bll/Abstract.php';

/**
 * logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/05/20    Liz
 */
class Bll_Scripteditor_Search extends Bll_Abstract{

    /**
     * search entry
     * 
     * @param string $search
     * @return array
     */
    public function searchEntry($search, $langType, $pageIndex, $pageSize)
    {
        $search = str_replace("　", " ", $search);
        $search = str_replace("'", "", $search);
        $search = str_replace('"', "", $search);
        
        $search = preg_replace('/\s(?=\s)/', '', $search);
        $search = str_replace(" ", ",", $search);
        $search = preg_replace('/\,(?=\,)/', '', $search);
        if ( $search == ',' ) {
            $search = '';
        }
        else {
            $arrSearch = explode(',', $search);
        }
        
        
        require_once 'Dal/Scripteditor/Language.php';
        $dalSterLanguage = new Dal_Scripteditor_Language();

        if ( $pageIndex == '1' ) {
            //add search log
            $this->addSearchLog($arrSearch);
        }
        
        //if 限制单一搜索语言
        if ( $langType ) {
            $isSearchAll = 0;
            $searchLanguage = $langType;
            //get language info by id
            $langInfo = $dalSterLanguage->getLanguage($langType);
            $searchType['0'] = $langInfo;
        }
        else {
            //get language list info
            $languageList = $dalSterLanguage->getLanguageList();
            
            //get search type array info
            $searchType = array();
            for ( $i=0, $count=count($arrSearch); $i<$count; $i++ ) {
                for ( $j=0, $jcount=count($languageList); $j<$jcount; $j++ ) {
                    if ( strtolower($arrSearch[$i]) == strtolower($languageList[$j]['language_name']) ) {
                        $searchType[] = $languageList[$j];
                        break;
                    }
                }
            }
            
            //is search all of the entry or not
            if ( count($searchType) > 0 ) {
                $isSearchAll = 0;
            }
            else {
                $isSearchAll = 1;
            }
            
            $searchLanguage = $searchType['0']['id'];
        }
        
        require_once 'Dal/Scripteditor/Search.php';
        $dalSterSearch = new Dal_Scripteditor_Search();

        //get search entry info
        $array = $dalSterSearch->getSearchEntry($arrSearch, $isSearchAll, $searchLanguage, $pageIndex, $pageSize);
        
        if ( $array ) {
            require_once 'Bll/User.php';
            Bll_User::appendPeople($array, 'uid');
        
            //get search count info
            if ( $isSearchAll == 1 ) {
                for ( $m=0, $mcount=count($languageList); $m<$mcount; $m++ ) {
                    $languageList[$m]['count'] = $dalSterSearch->getSearchEntryCount($arrSearch, $languageList[$m]['id']);
                    $countInfo = $languageList;
                    $countInfo['languageCount'] = $m + 1;
                    $countInfo['allCount'] = $dalSterSearch->getSearchEntryCount($arrSearch, 0);
                }
            }
            else {
                $searchType['0']['count'] = $dalSterSearch->getSearchEntryCount($arrSearch, $searchType['0']['id']);
                $countInfo = $searchType['0'];
                $countInfo['allCount'] = $countInfo['count'];
            }
        }
        else {
            //get tag list
            $tagList = Bll_Cache_Scripteditor::getTagList(1);
            //rand array
            $tagList = $this->__randArray($tagList, count($tagList));
        }
        
        $result = array('info' => $array,
                        'search' => $search,
                        'isSearchAll' => $isSearchAll,
                        'countInfo' => $countInfo,
                        'tagList' => $tagList);
        
        return $result;
    }

    /**
     * add search log
     *
     * @param array $arrSearch
     * @return boolean
     */
    public function addSearchLog($arrSearch)
    {
        $result = false;

        //begin transaction
        $this->_wdb->beginTransaction();

        try {
            require_once 'Dal/Scripteditor/Search.php';
            $dalSterSearch = new Dal_Scripteditor_Search();
           
            for ( $i=0,$count=count($arrSearch); $i<$count; $i++ ) {
                //get tag info
                $tagInfo[$i] = $dalSterSearch->getSearchTag($arrSearch[$i]);
                
                if ( $tagInfo[$i] ) {
                    //update count, +1
                    $dalSterSearch->updateSearchCount($tagInfo[$i]['id']);
                }
                /*
                else {
                    //insert new log
                    $info = array('tag'=>$arrSearch[$i]);
                    $dalSterSearch->insertSearch($info);
                }*/
            }
            
            //end of transaction
            $this->_wdb->commit();

            $result = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return false;
        }

        return $result;
    }
    
    /**
     * get tag list
     * 
     * @return array
     */
    public function getTagList()
    {
        require_once 'Dal/Scripteditor/Search.php';
        $dalSterSearch = new Dal_Scripteditor_Search();
        
        $tagList = $dalSterSearch->getTagList(200);
        $allSreachCount = $dalSterSearch->getAllSearchCount();
        
        $count = count($tagList);
        
        switch ( $allSreachCount ) {
            case $allSreachCount < 11 :
                //get tag class info
                for ($i=0; $i < $count; $i ++ ) {
                    //get the percent,相対値
                    $p = round($tagList[$i]['search_count']/$allSreachCount, 2)*100;
                    
                    //get the tag class 
                    switch ( $p ) {
                        case $p < 21 :
                            $tagList[$i]['tagClass'] = 1;
                            break;
                        case $p > 20 && $p < 41 :
                            $tagList[$i]['tagClass'] = 2;
                            break;
                        case $p > 40 && $p < 61 :
                            $tagList[$i]['tagClass'] = 3;
                            break;
                        case $p > 60 && $p < 81 :
                            $tagList[$i]['tagClass'] = 4;
                            break;
                        case $p > 80 && $p < 101 :
                            $tagList[$i]['tagClass'] = 5;
                            break;                
                    }
                }
                break;
                
            case $allSreachCount > 10 && $allSreachCount < 31 :
                //get tag class info
                for ($i=0; $i < $count; $i ++ ) {
                    //get the percent,相対値
                    $p = round($tagList[$i]['search_count']/$allSreachCount, 2)*100;
                    
                    //get the tag class 
                    switch ( $p ) {
                        case $p < 16 :
                            $tagList[$i]['tagClass'] = 1;
                            break;
                        case $p > 15 && $p < 31 :
                            $tagList[$i]['tagClass'] = 2;
                            break;
                        case $p > 30 && $p < 46 :
                            $tagList[$i]['tagClass'] = 3;
                            break;
                        case $p > 45 && $p < 61 :
                            $tagList[$i]['tagClass'] = 4;
                            break;
                        case $p > 60 && $p < 76 :
                            $tagList[$i]['tagClass'] = 5;
                            break;    
                        case $p > 75 && $p < 91 :
                            $tagList[$i]['tagClass'] = 6;
                            break;  
                        case $p > 90 && $p < 101 :
                            $tagList[$i]['tagClass'] = 7;
                            break;           
                    }
                }
                break;
                
            case $allSreachCount > 30 && $allSreachCount < 61 :
                //get tag class info
                for ($i=0; $i < $count; $i ++ ) {
                    //get the percent,相対値
                    $p = round($tagList[$i]['search_count']/$allSreachCount, 2)*100;
                    
                    //get the tag class 
                    switch ( $p ) {
                        case $p < 11 :
                            $tagList[$i]['tagClass'] = 1;
                            break;
                        case $p > 10 && $p < 21 :
                            $tagList[$i]['tagClass'] = 2;
                            break;
                        case $p > 20 && $p < 31 :
                            $tagList[$i]['tagClass'] = 3;
                            break;
                        case $p > 30 && $p < 41 :
                            $tagList[$i]['tagClass'] = 4;
                            break;
                        case $p > 40 && $p < 51 :
                            $tagList[$i]['tagClass'] = 5;
                            break;    
                        case $p > 50 && $p < 61 :
                            $tagList[$i]['tagClass'] = 6;
                            break;  
                        case $p > 60 && $p < 71 :
                            $tagList[$i]['tagClass'] = 7;
                            break;   
                        case $p > 70 && $p < 81 :
                            $tagList[$i]['tagClass'] = 8;
                            break; 
                        case $p > 80 && $p < 91 :
                            $tagList[$i]['tagClass'] = 9;
                            break; 
                        case $p > 90 && $p < 101 :
                            $tagList[$i]['tagClass'] = 10;
                            break;
                    }
                } 
                break;
                
            case $allSreachCount > 60 :
                //get tag class info
                for ($i=0; $i < $count; $i ++ ) {
                    //get the percent,相対値
                    $p = round($tagList[$i]['search_count']/$allSreachCount, 2)*100;
                    
                    //get the tag class 
                    switch ( $p ) {
                        case $p < 6 :
                            $tagList[$i]['tagClass'] = 1;
                            break;
                        case $p > 5 && $p < 11 :
                            $tagList[$i]['tagClass'] = 2;
                            break;
                        case $p > 15 && $p < 16 :
                            $tagList[$i]['tagClass'] = 3;
                            break;
                        case $p > 15 && $p < 21 :
                            $tagList[$i]['tagClass'] = 4;
                            break;
                        case $p > 20 && $p < 26 :
                            $tagList[$i]['tagClass'] = 5;
                            break;
                        case $p > 25 && $p < 31 :
                            $tagList[$i]['tagClass'] = 6;
                            break;
                        case $p > 30 && $p < 36 :
                            $tagList[$i]['tagClass'] = 7;
                            break;
                        case $p > 35 && $p < 41 :
                            $tagList[$i]['tagClass'] = 8;
                            break;
                        case $p > 40 && $p < 46 :
                            $tagList[$i]['tagClass'] = 9;
                            break;
                        case $p > 45 && $p < 51 :
                            $tagList[$i]['tagClass'] = 10;
                            break;
                        case $p > 50 && $p < 56 :
                            $tagList[$i]['tagClass'] = 11;
                            break;
                        case $p > 55 && $p < 61 :
                            $tagList[$i]['tagClass'] = 12;
                            break;
                        case $p > 60 && $p < 66 :
                            $tagList[$i]['tagClass'] = 13;
                            break;
                        case $p > 65 && $p < 71 :
                            $tagList[$i]['tagClass'] = 14;
                            break;
                        case $p > 70 && $p < 76 :
                            $tagList[$i]['tagClass'] = 15;
                            break;
                        case $p > 75 && $p < 81 :
                            $tagList[$i]['tagClass'] = 16;
                            break;
                        case $p > 80 && $p < 86 :
                            $tagList[$i]['tagClass'] = 17;
                            break;
                        case $p > 85 && $p < 91 :
                            $tagList[$i]['tagClass'] = 18;
                            break;
                        case $p > 90 && $p < 96 :
                            $tagList[$i]['tagClass'] = 19;
                            break;
                        case $p > 95 && $p < 101 :
                            $tagList[$i]['tagClass'] = 20;
                            break;
                    }
                }
                break;
        }
        
        return $tagList;
    }
    
    /**
     * rand array
     * 
     * @return array
     */
    function __randArray($arrays, $num="") {
        $num = empty($num) ? count($arrays) : $num;
        $rand_array = array_rand($arrays, $num);
        $len = count($rand_array);
        for ($i=0; $i<$len; $i++) {
            {
                $new_array[$i] = $arrays[$rand_array[$i]];
            }
        }
        return $new_array;
    }
}