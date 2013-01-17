<?php

require_once 'Bll/Abstract.php';

/**
 * logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/05/20    Liz
 */
class Bll_Scripteditor_Config extends Bll_Abstract{


    /**
     * get feature info
     * 
     * @param string $userFeatrue
     * @param integer $maxCount
     * @return array
     */
    public function getFeatureInfo($userFeatrue, $maxCount)
    {
        $result['status'] = 1;
        
        if ( $userFeatrue == '00000000000000000000000000' ) {
            return $result['status'] = 0;
        }
        
        require_once 'Dal/Scripteditor/Feature.php';
        $dalSterFeature = new Dal_Scripteditor_Feature();
        
        $j = 0;
        for ( $i = 0; $i < 25; $i++ ) {
            //get five features, feature type > 0
            $featureType = substr($userFeatrue, $i, 1);
            if ( $featureType > 0 ) {
                $feature[$j]['id'] = $i+1;
                $feature[$j]['type'] = $featureType;
                $j++;
            }
            //max count
            if ($j == $maxCount) {
                break;
            }
        }
        
        for ( $m = 0, $mCount = count($feature); $m < $mCount; $m++ ) {
            //get feature content
            $feature[$m]['feature_content'] = $dalSterFeature->getFeature($feature[$m]['id']);
            //get feature type
            $feature[$m]['feature_type'] = $this->_getFeatureType($feature[$m]['type']);
            
            //get feature info
            //$result['featureInfo'][$m] = $feature[$m]['feature_content'] . '&nbsp;&nbsp;&nbsp;' . $feature[$m]['feature_type'];
            $result['featureInfo'] = $feature;
        }
        $result['count'] = count($feature);
        
        return $result;
    }



    /**
     * update user info
     *
     * @param string $uid
     * @param array $userInfo
     * @return boolean
     */
    public function updateUserInfo($uid, $userInfo, $photoField)
    {
        $result = false;

        //begin transaction
        $this->_wdb->beginTransaction();

        try {
            //update user info
            require_once 'Dal/Scripteditor/User.php';
            $dalSterUser = new Dal_Scripteditor_User();
            $dalSterUser->updateUserInfo($uid, $userInfo);

            //if photo upload is not empty
            if (!empty($_FILES[$photoField]) && is_uploaded_file($_FILES[$photoField]['tmp_name'])) {
                //upload photo
                require_once 'Bll/PhotoUpload.php';
                $photoUpload = new Bll_PhotoUpload(array('field' => $photoField, 'section' => 1, 'id' => $uid));
                $uploadfile = $photoUpload->doUpload();
                //if do upload
                if ($uploadfile) {
                    $bigPic = Zend_Registry::get('photo') . '/' . $uploadfile['bigfilename'];
                    $smallPic = Zend_Registry::get('photo') . '/' . $uploadfile['smallfilename'];
                    $dalSterUser->updateUserInfo($uid, array('pic' => $bigPic,'pic_s' => $smallPic));
                }
                else {
                    $this->_wdb->rollBack();
                    return false;
                }
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
     * get feature type
     *
     * @param integer $type
     * @return string
     */
    function _getFeatureType($type)
    {
        switch ($type) {
            case '0' : $featureType = 'なし'; 
                break;
            case '1' : $featureType = '独学'; 
                break;
            case '2' : $featureType = '実務 〜 1年'; 
                break;
            case '3' : $featureType = '実務 1〜2年'; 
                break;
            case '4' : $featureType = '実務 2年〜'; 
                break;
        }
        
        return $featureType;
    }

}