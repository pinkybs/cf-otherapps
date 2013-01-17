 <?php

/**
 * index controller
 * init each index page
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/02/10    HCH
 */
class TestController extends Zend_Controller_Action
{
    public function init()
    {
        $this->view->baseUrl = $this->_request->getBaseUrl();
        $this->view->staticUrl = Zend_Registry::get('static');
        $this->view->photoUrl = Zend_Registry::get('photo');
        $this->view->version = Zend_Registry::get('version');
        $this->view->hostUrl = Zend_Registry::get('host');
    }
    
    public function avatarAction()
    {
        $this->render();
    }


    public function intHash($key)
    {
      $key += ~($key << 15);
      $key ^= ($key >> 10);
      $key += ($key << 3);
      $key ^= ($key >> 6);
      $key += ~($key << 11);
      $key ^= ($key >> 16);
      return $key % 1000000;
    }

	public function orderidAction()
	{
        //micro seconds 13 lens
        $mtime = floor(microtime(true)*1000);
        
        //server id, 2 lens
        if (defined('SERVER_ID')) {
            $serverid = SERVER_ID;
        } else {
            $serverid = 1;
        }
        
        if ($serverid < 10) {
            $s = '0';
        } else {
            $s = '';
        }
        
        //rand number, 1~9999, 4 lens
        srand(mtime);
        $rnd = rand(1, 9999);

        echo $mtime . $s . ($serverid*10000 + $rnd);
		
		exit;
	}
    
    public function testallAction()
    {
        $data = array(
            'id' => '1110',
            'displayName' => 'testuser1',
            'thumbnailUrl' => 'http://img.mixi.jp/img/basic/common/noimage_member76.gif',
            'profileUrl' => 'http://platform001.mixi.jp/show_friend.pl?id=1110',
            'bloodType' => 'A',
            'gender' => 'MALE'
        );
        
        $person = new OpenSocial_Person($data);
        Bll_User::updatePerson($person);
        
        
        exit;
    }
     
    public function test1Action()
    {
		//echo phpinfo();
		require_once 'osapi/external/MixiSignatureMethod.php';
		$http_method = "POST";
		$http_url = 'http://ship.mixi.communityfactory.net/callback/removeapp';

		$parameters = Zend_JSON::decode('{"opensocial_app_id":"13651","eventtype":"event.removeapp","id":"24899958","oauth_consumer_key":"mixi.jp","oauth_nonce":"df36ff9095eff8f3ef2a","oauth_signature":"srX2I3VDS0ZDL%2FVZEXR0yegr6rKTJnYTCijt5%2Bv4mJiyvXFSSHMV5yU8oBQtJp9wHnq7ekNB%2FHZR%0Au2gsEgwapkJeg9oL0mlegRD3fi7jxi4kwh3gLQsYSM03mGd0auCR27EhpCc%2F8hmvQA7F1niRZuiE%0AVR9Yz3I3V%2FxRr45yjCo%3D","oauth_signature_method":"RSA-SHA1","oauth_timestamp":"1264420834","oauth_version":"1.0"}');


//$parameters = Zend_JSON::decode('{"eventtype":"event.addapp","id":"10541895","opensocial_app_id":"13332","oauth_consumer_key":"mixi.jp","oauth_nonce":"5ec0b268a6293d565d90","oauth_signature":"hlKuVM3OfgNmA3kf\/zq\/PNV0KexgK63+JeTxt9y01AiReQCeleEAHySDTa6s+ZlwucuILkSXFrfF\nxKLViYjbunRXZZgAWAtjYuMpkqMLD4fT6qUa\/1JgmYcedEdBGc87qQh0ElW+U+hbySwjCNgLaBQ8\nQPi3VfqwhmKnILH50Rw=","oauth_signature_method":"RSA-SHA1","oauth_timestamp":"1260864003","oauth_version":"1.0"}');
		$req = new OAuthRequest($http_method, $http_url, $parameters);

		$signature_method = new MixiSignatureMethod();

		//print_r (rawurldecode($req->get_parameter('oauth_signature')));

		$sig = $req->get_parameter('oauth_signature');

		echo '<br/><br/>' . $sig . '<br/><br/>';
		echo '<br/><br/>' . urldecode($sig) . '<br/><br/>';
		echo '<br/><br/>' . rawurldecode($sig) . '<br/><br/>';

		$signature_valid = $signature_method->check_signature($req, null, null, rawurldecode($sig));
		if ($signature_valid) {
			echo 'ok';
		}else {
			echo 'false';
		}
		
        exit;
    }
    
    public function test2Action()
    {
		$param_post = array(
			"opensocial_app_id" => "10796",
			"opensocial_owner_id" => "25510851",
			"point_code" => "r7Rb9Wn3EjXTHKct09CS",
			"status" => "10",
			"updated" => "2009-12-28T05:50:51Z"
		);
		
		$param_headers = array(
			"oauth_consumer_key"=>"c8cc4495fdf0d942223f", 
			"oauth_nonce"=>"06657314989ff7930693", 
			"oauth_signature"=>"FLk4sniikCzfk1FAEyPkV1XWGEI%3D", 
			"oauth_signature_method"=>"HMAC-SHA1", 
			"oauth_timestamp"=>"1261979451", 
			"oauth_version"=>"1.0"
		);

		$params = array_merge($param_get, $param_headers);

        require_once 'osapi/external/OAuth.php';
        //Build a request object from the current request
        $request = OAuthRequest::from_request("GET", "http://iadm-cfactory.disney.co.jp/mobile/disney/paydownload/CF_pid/8", $params);

		$signature_method = new OAuthSignatureMethod_HMAC_SHA1();
        $consumer = new OAuthConsumer('c8cc4495fdf0d942223f', '4100be34ef0900b98f0f19554ad9a7d9cc4e8228');
        //Check the request signature
        @$signature_valid = $signature_method->check_signature($request, $consumer, null, urldecode($request->get_parameter('oauth_signature')));

		echo $signature_valid ? 'true' : 'false';
        
        exit;
    }
    
    public function userAction()
    {
        //$owner_id = 22677405;
        //$owner_id = 13915816;
        //$owner_id = 22112882;
        //$owner_id = 21224066;
        //$app_id = 3459;
        
        $owner_id = 22677405;
        //$owner_id = 23815092;
        //$owner_id = 23815088;
        //$owner_id = 23815106;
        $app_id = 12235;
        
        require_once 'Bll/Restful2.php';
        
        //$restful = Bll_Restful::getInstance($owner_id, $app_id);
		$restful = new Bll_Restful2('0af51e2a3569417eaf52', '52c807f5b762e3bf2991360a106a32ac09b2549c', 10712);
        
        $user = $restful->getUser();
        //http://profile.img.mixi.jp/photo/member/50/95/23815095_1981379106s.jpg
        if ($restful->hasError()) {
            echo $restful->getErrorMessage();
        } else {
            $person = $restful->parsePerson($user['user']);
            print_r($person);
        }
        
        exit;
        /*
        $data = array(
            'id' => 23815106,
            'displayName' => '伊丹秀蔵',
            'thumbnailUrl' => 'http://profile.img.mixi.jp/photo/member/51/6/23815106_2177023523s.jpg',
            'profileUrl' => 'http://mixi.jp/show_friend.pl?id=23815106',
            'gender' => 'MALE',
            'age' => 24,
            'address' => 'asdfsdfdsa'
        );*/
        
         $data = array(
            'id' => 23815091,
            'displayName' => '馬場三子男_4',
            'thumbnailUrl' => 'http://profile.img.mixi.jp/photo/member/50/91/23815091_4019936896s.jpg',
            'profileUrl' => 'http://mixi.jp/show_friend.pl?id=23815091',
            'gender' => 'MALE',
            'age' => 34,
            'dateOfBirth' => null,
            'address' => '東京都'
        );       
        
        
        $oldPerson = new OpenSocial_Person($data);
        
        if ($oldPerson->isDifferentWith($person)) {
            echo 'diff';
        } else {
            echo 'same';
        }
        
        
        exit;
    }
    
    public function keyAction()
    {
        $id = 21224066;
        
        //$key = Bll_Cache_User::getCacheKey('getPerson', $id);
        $key = Bll_Cache_User::getCacheKey('getFriends', $id);
        
        echo $key;
        
        exit;
    }
    
    public function friendAction()
    {
        //$owner_id = 22677405;
        //$owner_id = 23815098;
		$owner_id = 21103625;

        $app_id = 9477;
        
        
        require_once 'Bll/Restful.php';
        
        $restful = Bll_Restful::getInstance($owner_id, $app_id);
        
        $friends = $restful->getFriends();
        if ($restful->hasError()) {
            echo $restful->getErrorMessage();
        } else {
            print_r($friends);
        }
        
		/*
        $cURLVersion = curl_version();
        $ua = 'CommunityFactory-osapi/1.0';
        
        $ua = 'PHP-cURL/' . $cURLVersion['version'] . ' ' . $ua;
        
        echo $ua;
		*/
        
        exit;
    }   
    
    public function activityAction()
    {
        //$owner_id = 22677405;
        $owner_id = 13915816;
        $app_id = 3459;
        
        require_once 'Bll/Restful.php';
        
        $restful = Bll_Restful::getInstance($owner_id, $app_id);
        //$title = mb_convert_encoding('最新アプリ情報 for test', 'euc-jp', 'UTF-8');
        $title = 'Ha, Ha, Ha';
        $url = 'http://mixi.jp/view_appli.pl?id=' . $app_id;
        $mobileUrl = 'http://ma.mixi.jp/' . $app_id . '/?guid=ON&url=' . urldecode('http://mixitest.communityfactory.net/mobile/dynamite/home');
        $picUrl = 'http://static.mixitest.communityfactory.net/apps/dynamite/img/activity_image/10.gif';
        
        $restful->createActivityWithPic(array('title' => $title, 'url' => $url), $picUrl, 'image/gif');
        //http://profile.img.mixi.jp/photo/member/50/95/23815095_1981379106s.jpg
        if ($restful->hasError()) {
            echo $restful->getErrorMessage();
        } else {
            echo 'ok';
        }
        
        exit;
    }
    
    
    public function albumsAction()
    {   
        $owner_id = 13915816;
        $app_id = 3459;
        
        require_once 'Bll/Restful.php';
        
        $restful = Bll_Restful::getInstance($owner_id, $app_id);
        
        $albums = $restful->getAlbums();
        if ($restful->hasError()) {
            echo $restful->getErrorMessage();
        } else {
            print_r($albums);
        }
        
        
        exit;
    }
    
    public function classmatesAction()
    {   
        $owner_id = 23815091;
        $app_id = 12235;
        
        require_once 'Bll/Restful.php';
        
        $restful = Bll_Restful::getInstance($owner_id, $app_id);
        
        $school = $restful->getClassmates();
        if ($restful->hasError()) {
            echo $restful->getErrorMessage();
        } else {
            print_r($school);
        }
        
        
        exit;
    }
    
    public function itemsAction()
    {   
        $owner_id = 13915816;
        $app_id = 3459;
        //$album_id = 'mixi.jp:38385243';
        $album_id = '38385243';
        
        require_once 'Bll/Restful.php';
        
        $restful = Bll_Restful::getInstance($owner_id, $app_id);
        
        $items = $restful->getMediaItems($album_id);
        if ($restful->hasError()) {
            echo $restful->getErrorMessage();
        } else {
            print_r($items);
        }
        
        exit;
    }
    
    public function pointAction()
    {   
        $owner_id = 24270673;
        $app_id = 10796;
        
        require_once 'Bll/Restful.php';
        
        $restful = Bll_Restful::getInstance($owner_id, $app_id);
        
        $url = array(
            'callback_url' => 'http://mixitest.communityfactory.net/mobile/disney/pointcallback',
            'finish_url' => 'http://mixitest.communityfactory.net/mobile/disney/pointfinish'
        );

        $items = array(
            array(
                'id' => '1',
                'name' => 'testitem',
                'point' => '20'
            )
        );
        
        $items = $restful->createPoint($url, $items, true);
        if ($restful->hasError()) {
            echo $restful->getErrorMessage();
        } else {
            print_r($items);
        }
        
        exit;
    }   
    
    public function tttAction()
    {
        $a = 'NpwdnBhlz/GdHcgmxFqFch0BAKYy3hcC8otkv1mr7uMN05XO69suglgjwY2U0ca3uZIW2zET3WoWFyw/y0KYjP6i8SE=';
        $b = 'NpwdnBhlz';
        
        echo base64_decode($b);
        
        exit;
    }
	
	public function s3testAction()
	{
		require_once 'Zend/Service/Amazon/S3.php';
		$s3 = new Zend_Service_Amazon_S3('AKIAISGAFMPIRU3MA63A', 'hiG8iDYsbmQ0vZnM8xoGew/IjiVnuRGcrP0p6nYd');
		$s3->putObject("cache.communityfactory/test/hello.txt", 'hello world! ccccccccc',
			array(Zend_Service_Amazon_S3::S3_ACL_HEADER =>Zend_Service_Amazon_S3::S3_ACL_PUBLIC_READ));

		echo 'ok';

		exit;
	}

    /**
     * magic function
     *   if call the function is undefined,then forward to not found
     *
     * @param string $methodName
     * @param array $args
     * @return void
     */
    function __call($methodName, $args)
    {
        return $this->_forward('notfound','error','default');
    }

 }
