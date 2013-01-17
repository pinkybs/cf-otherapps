<?php

require_once "osapi/osapi.php";

class Bll_Restful
{
    private $consumer_key;
    private $comsumer_secret;
    private $user_id;
    private $app_id;
    
    private $osapi;
    private $friend_count = 1000;
    private $profile_fields = null;
    private $self_request_params = null;
    private $friends_request_params = null;
    private $error = false;
    private $errmsg = '';
    
    /**
     * Bll_Restful
     *
     * @param unknown_type $user_id
     * @param unknown_type $app_id
     * @return Bll_Restful
     */
    public static function getInstance($user_id, $app_id)
    {        
        require_once  'Bll/Restful/Consumer.php';
        $consumer = Bll_Restful_Consumer::getConsumerData($app_id);
        
        if ($consumer != null) {
            return new self($consumer['consumer_key'], $consumer['comsumer_secret'], $user_id, $app_id);
        }

        return null;
    }
    
    public function checkSignature()
    {
        /*
        $headers = getallheaders();
        $authorization = $headers['Authorization'];
        
        if (!$authorization) {
            return false;
        }
        
        $arr = explode(',', $authorization);
        $auth_consumer_key = $arr['auth_consumer_key'];
        if ($auth_consumer_key != $this->consumer_key) {
            return false;
        }
        
        //$oauth_nonce = $arr['oauth_nonce'];
        $oauth_signature = $arr['oauth_signature'];
        $oauth_signature_mothod = $arr['oauth_signature_mothod'];
        //$oauth_timestamp = $arr['oauth_timestamp'];
        //$oauth_version = $arr['oauth_version'];
        
        if ($oauth_signature_mothod != 'HMAC-SHA1') {
            return false;
        }
        */
        
        require_once 'osapi/external/OAuth.php';
        //Build a request object from the current request
        $request = OAuthRequest::from_request(null, null, null);
        //Initialize the new signature method
        $signature_method = new OAuthSignatureMethod_HMAC_SHA1();
        $consumer = new OAuthConsumer($this->consumer_key, $this->comsumer_secret);
        //Check the request signature
        $signature = rawurldecode($request->get_parameter('oauth_signature'));
        @$signature_valid = $signature_method->check_signature($request, $consumer, null, $signature);
        
        return $signature_valid;
    }
    
    public function __construct($consumer_key, $comsumer_secret, $user_id, $app_id = 0)
    {
        $this->consumer_key = $consumer_key;
        $this->comsumer_secret = $comsumer_secret;
        $this->user_id = $user_id;
        $this->app_id = $app_id;
        
        // Require the osapi library
        $this->osapi = new osapi(new osapiMixiProvider(), new osapiOAuth2Legged($consumer_key, $comsumer_secret, $user_id));
        $this->osapi->setStrictMode(false);
        
        // The fields we will be fetching.
        $this->profile_fields = array('id', 'displayName', 'thumbnailUrl', 'profileUrl', 'hasApp', 'bloodType', 'addresses', 'birthday', 'gender');
        
        // Fetch the current user.
        $this->self_request_params = array(
            'userId' => $user_id, // Person we are fetching.
            'groupId' => '@self', // @self for one person.
            'fields' => $this->profile_fields // Which profile fields to request.
        );

        // Fetch the friends of the user
        $this->friends_request_params = array(
            'userId' => $user_id, // Person whose friends we are fetching.
            'groupId' => '@friends', // @friends for the Friends group.
            'fields' => $this->profile_fields, // Which profile fields to request.
            'count' => $this->friend_count // Max friends to fetch.
        );
    }
    
    public function parsePerson($osapiPerson)
    {
        $id = $osapiPerson->getId();
        if ($pos = strpos($id, ':')) {
            $id = substr($id, $pos + 1);
        }
        
        $fields = array(
            'id' => $id,
            'displayName' => MyLib_String::escapeString($osapiPerson->getDisplayName()),
            'thumbnailUrl' => $osapiPerson->getThumbnailUrl(),
            'profileUrl' => $osapiPerson->getProfileUrl(),
            'hasApp' => $osapiPerson->getHasApp()
        );
        
        $bloodType = $osapiPerson->getFieldByName('bloodType');
        if ($bloodType) {
            $fields['bloodType'] = $bloodType;
        }
        
        $addresses = $osapiPerson->getAddresses();
        if ($addresses) {
            $fields['address'] = MyLib_String::escapeString($addresses[0]['address']['formatted']);
        }
        
        $dateOfBirth = $osapiPerson->getBirthday();
        if ($dateOfBirth) {
            $fields['dateOfBirth'] = $dateOfBirth;
        }
                
        $gender = $osapiPerson->getGender();
        if ($gender) {
            //mixi restful return lower string: male, female
            //we should convert it to upper
            //$gender = $gender == 'male' ? 'MALE' : ($gender == 'female') ? 'FEMALE' : null;
            $fields['gender'] = strtoupper($gender);
        }

        return new OpenSocial_Person($fields);
    }
    
    public function getUser($uid = '@me')
    {
        $batch = $this->osapi->newBatch();
        
        $this->self_request_params['userId'] = $uid;
        $batch->add($this->osapi->people->get($this->self_request_params), 'user');
        
        $result = null;
        
        $this->error = false;
        $this->errmsg = '';
        
        try {
            // Send the batch request.
            $result = $batch->execute();
            
            if ($result) {
                $user = $result['user'];
                if ($user instanceof osapiError) {
                    $this->error = true;
                    $this->errmsg .= $user->getErrorMessage();
                }
            }
        }
        catch (Exception $e) {
            $this->error = true;
            $this->errmsg = $e->getMessage();
        }
        
        return $this->error ? null : $result;
    }
    
    public function getFriends($uid = '@me')
    {
        $batch = $this->osapi->newBatch();
        
        $this->self_request_params['userId'] = $uid;
        $batch->add($this->osapi->people->get($this->friends_request_params), 'friends');
        
        $result = null;
        
        $this->error = false;
        $this->errmsg = '';
        
        try {
            // Send the batch request.
            $result = $batch->execute();
            
            if ($result) {
                $friends = $result['friends'];
                if ($friends instanceof osapiError) {
                    $this->error = true;
                    $this->errmsg .= $friends->getErrorMessage();
                }
            }
        }
        catch (Exception $e) {
            $this->error = true;
            $this->errmsg = $e->getMessage();
        }
        
        return $this->error ? null : $result;
    }
    
    public function getUserAndFriends($uid = '@me')
    {
        // Start a batch so that many requests may be made at once.
        $batch = $this->osapi->newBatch();
        
        $this->self_request_params['userId'] = $uid;
        $batch->add($this->osapi->people->get($this->self_request_params), 'user');
        
        $this->friends_request_params['userId'] = $uid;
        $batch->add($this->osapi->people->get($this->friends_request_params), 'friends');
        
        $result = null;
        
        $this->error = false;
        $this->errmsg = '';
        
        try {
            // Send the batch request.
            $result = $batch->execute();
            
            if ($result) {
                $user = $result['user'];
                $friends = $result['friends'];
                if ($user instanceof osapiError) {
                    $this->error = true;
                    $this->errmsg .= $user->getErrorMessage();
                }
                if ($friends instanceof osapiError) {
                    $this->error = true;
                    $this->errmsg .= $friends->getErrorMessage();
                }
            }
        }
        catch (Exception $e) {
            $this->error = true;
            $this->errmsg = $e->getMessage();
        }
        
        return $this->error ? null : $result;
    }
    
    /**
     *
     * @param array $fields
     *         {
     *            'title'      => 'activity title',
     *            'url'        => 'pc activity link url',
     *            'mobileUrl'  => 'mobile activity link url'
     *            'recipients' => array(123, 456)
     *         }
     * @param string $uid
     * @return array
     */
    public function createActivity($fields, $uid = '@me')
    {
        $activity = new osapiActivity(null, null);
        foreach ($fields as $k => $v) {
            $activity->setField($k, $v);
        }
        if ($uid == '@me') {
            //it's also works for guid(start with 'mixi.jp:')
            //$uid = 'mixi.jp:' . $this->user_id;
            $uid = $this->user_id;
        }
        
        $create_params = array(
            'userId' => $uid,
            'groupId' => '@self',
            'appId' => '@app',
            'activity' => $activity
        );
        
        $batch = $this->osapi->newBatch();
        
        $batch->add($this->osapi->activities->create($create_params), 'createActivity');
        
        $this->error = false;
        $this->errmsg = '';
        
        try {
            $result = $batch->execute();
        }
        catch (Exception $e) {
            $this->error = true;
            $this->errmsg = $e->getMessage();
        }
        
        return $result;
    }
    
    public function createActivityWithPic($fields, $picUrl, $mimeType = 'image/jpeg', $uid = '@me')
    {
        $mediaItems = array();
        $item = new osapiMediaItem();
        $item->setField('type', 'IMAGE');
        $item->setField('mimeType', $mimeType);
        $item->setField('url', $picUrl);
        
        $mediaItems[] = $item;
        
        $activity = new osapiActivity(null, null);
        foreach ($fields as $k => $v) {
            $activity->setField($k, $v);
        }
        $activity->setMediaItems($mediaItems);
        if ($uid == '@me') {
            //it's also works for guid(start with 'mixi.jp:')
            //$uid = 'mixi.jp:' . $this->user_id;
            $uid = $this->user_id;
        }
        $create_params = array(
            'userId' => $uid,
            'groupId' => '@self',
            'appId' => '@app',
            'activity' => $activity
        );

        $batch = $this->osapi->newBatch();
        
        $batch->add($this->osapi->activities->create($create_params), 'createActivityWithPic');
        
        $this->error = false;
        $this->errmsg = '';
        
        try {
            $result = $batch->execute();
        }
        catch (Exception $e) {
            $this->error = true;
            $this->errmsg = $e->getMessage();
        } 
    }
    
    public function getAlbums($uid = '@me', $albumId = null)
    {
        // Request the albums of the current user.
        $params = array(
            'userId' => $uid, 
            'groupId' => '@self'
        );
        
        // object osapiAlbum
        //### album fields ###
        // caption          'my album'
        // mediaItemCount   2
        // id               'mixi.jp:38385243'
        // title            'my album'
        // description      '[m:234]OK[m:105]'    note: this field can include emoji character
        // ownerId          'mixi.jp:13915816'
        // mediaMimeType    Array ( [0] => image/jpeg )
        
        if ($albumId != null) {
            $params['albumId'] = $albumId;
        }
        
        $batch = $this->osapi->newBatch();
        
        $batch->add($this->osapi->albums->get($params), 'albums');
        
        $result = null;
        
        $this->error = false;
        $this->errmsg = '';
        
        try {
            // Send the batch request.
            $result = $batch->execute();
            
            if ($result) {
                $albums = $result['albums'];
                if ($albums instanceof osapiError) {
                    $this->error = true;
                    $this->errmsg .= $albums->getErrorMessage();
                }
            }
        }
        catch (Exception $e) {
            $this->error = true;
            $this->errmsg = $e->getMessage();
        }
        
        return $this->error ? null : $albums;
    }
    
    public function getMediaItems($albumId, $uid = '@me', $mediaItemId = null)
    {
        // Request the mediaItems for album
        $params = array(
            'userId' => $uid, 
            'groupId' => '@self', 
            'albumId' => $albumId,
        );
        
        if ($mediaItemId != null) {
            $params['mediaItemId'] = $mediaItemId;
        }
        
        // object osapiMediaItem
        //### MediaItems fields ###
        // id               'mixi.jp:3083509315'
        // title            'hi, baby'
        // thumbnailUrl     'http://ic.mixi.jp/p/f2700174e76b1e5ac8edb493bbd7f16acfb8f9e454/4abc75ab/album/38385243_3083509315s.jpg'
        // description      'hi, baby'
        // albumId          'mixi.jp:38385243'
        // fileSize         5856
        // url              'http://ic.mixi.jp/p/f2708f95748eb37292af642e177b4edd5fdd9ca7e6/4abc75ab/album/38385243_3083509315.jpg'
        
        $batch = $this->osapi->newBatch();
        
        $batch->add($this->osapi->mediaItems->get($params), 'mediaItems');
        
        $result = null;
        
        $this->error = false;
        $this->errmsg = '';
        
        try {
            // Send the batch request.
            $result = $batch->execute();
            
            if ($result) {
                $mediaItems = $result['mediaItems'];
                if ($mediaItems instanceof osapiError) {
                    $this->error = true;
                    $this->errmsg .= $mediaItems->getErrorMessage();
                }
            }
        }
        catch (Exception $e) {
            $this->error = true;
            $this->errmsg = $e->getMessage();
        }
        
        return $this->error ? null : $mediaItems;       
    }
    
    public function getClassmates($uid = '@me', $schoolId = '@schools')
    {
        // Request the school of the current user.
        $params = array(
            'userId' => $uid, 
            'schoolId' => $schoolId
        );
        
        $batch = $this->osapi->newBatch();
        
        $batch->add($this->osapi->classmates->get($params), 'classmates');
        
        $result = null;
        
        $this->error = false;
        $this->errmsg = '';
        
        try {
            // Send the batch request.
            $result = $batch->execute();
            
            if ($result) {
                $classmates = $result['classmates'];
                if ($classmates instanceof osapiError) {
                    $this->error = true;
                    $this->errmsg .= $classmates->getErrorMessage();
                }
            }
        }
        catch (Exception $e) {
            $this->error = true;
            $this->errmsg = $e->getMessage();
        }
        
        return $this->error ? null : $classmates;
         
    }
    
    public function createPoint($url, $items, $isTest = false, $uid = '@me')
    {
        $point = new osapiPoint(new osapiPointUrl($url['callback_url'] , $url['finish_url']));
        
        $pointItems = array();
        
        foreach ($items as $item) {
            $pointItems[] = new osapiPointItem($item['id'], $item['name'], $item['point']);
        }
        
        $point->setItems($pointItems);
        
        if ($isTest == true) {
            $point->setStatus(new osapiPointStatus(true));
        }
        
        if ($uid == '@me') {
            //it's also works for guid(start with 'mixi.jp:')
            //$uid = 'mixi.jp:' . $this->user_id;
            //$uid = $this->user_id;
        }
        
        $create_params = array(
            'userId' => $uid,
            'point' => $point
        );
        
        $batch = $this->osapi->newBatch();
        
        $batch->add($this->osapi->mixipoint->create($create_params), 'createPoint');
        
        $this->error = false;
        $this->errmsg = '';
        $data = null;
        
        try {
            $result = $batch->execute();
            if ($result) {
                $pointResponse = $result['createPoint'];
                if ($pointResponse instanceof osapiError) {
                    $this->error = true;
                    $this->errmsg .= $pointResponse->getErrorMessage();
                } else {
                    $data = osapiPointResult::getPointResult($pointResponse);
                }
            }
        }
        catch (Exception $e) {
            $this->error = true;
            $this->errmsg = $e->getMessage();
        }
        
        return $this->error ? null : $data; 
    }
    
    public function setFriendCount($count)
    {
        $this->friend_count = $count;
    }
    
    public function hasError()
    {
        return $this->error;
    }
    
    public function getErrorMessage()
    {
        return $this->errmsg;
    }
}