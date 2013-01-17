<?php

require_once 'Bll/Abstract.php';

/**
 * logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/05/20    Liz
 */
class Bll_Scripteditor_Entry extends Bll_Abstract{


    /**
     * new entry
     *
     * @param string $uid
     * @param array $userInfo
     * @return boolean
     */
    public function newEntry($eid, $entry)
    {
        $result = '0';

        //begin transaction
        $this->_wdb->beginTransaction();

        try {
            require_once 'Dal/Scripteditor/Entry.php';
            $dalSterEntry = new Dal_Scripteditor_Entry();
            //get entry info
            $entryInfo = $dalSterEntry->getEntryInfo($eid);
            
            if ( $entryInfo ) {
                require_once 'Bll/User.php';
                Bll_User::appendPerson($entryInfo, 'uid');
            }
            
            $entry['tag'] = str_replace("　", " ", $entry['tag']);
            $entry['tag'] = preg_replace('/\s(?=\s)/', '', $entry['tag']);
            $entry['tag'] = str_replace(" ", ",", $entry['tag']);
            $entry['tag'] = preg_replace('/\,(?=\,)/', '', $entry['tag']);
            if ( $entry['tag'] == ',' ) {
                $entry['tag'] = '';
            }
            
            //if have, update
            if ($entryInfo) {
                $dalSterEntry->updateEntry($eid, $entry);
                $entryId = $eid;
            }//or new a entry
            else {
                $entryId = $dalSterEntry->insertEntry($entry);
            }
            
            //is new entry
            if ( $entry['status'] == '1' ) {
                if ( $entry['follow_id'] ) {
                    $followEntry = $dalSterEntry->getEntryInfo($entry['follow_id']);
                    require_once 'Bll/User.php';
                    Bll_User::appendPerson($entryInfo, 'uid');
                    if ( $followEntry ) {
                        $dalSterEntry->updateFollowCount($entry['follow_id'], '1');
                    }
                }
            
                require_once 'Dal/Scripteditor/Search.php';
                $dalSterSearch = new Dal_Scripteditor_Search();
                $arrSearch = explode(',', $entry['tag']);
                
                //add tag list
                for ( $i=0,$count=count($arrSearch); $i<$count; $i++ ) {
                    if ( $arrSearch[$i] ) {
                        $dalSterSearch->insertSearch($arrSearch[$i]);
                    }
                }
            }
            
            //end of transaction
            $this->_wdb->commit();

            $result = $entryId;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return false;
        }

        return $result;
    }
    

}