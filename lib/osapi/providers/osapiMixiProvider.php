<?php

class osapiMixiProvider extends osapiProvider {

  /**
   * Specifies the appropriate data for an mixi request.
   * @param osapiHttpProvider httpProvider The HTTP request provider to use.
   */
  public function __construct(osapiHttpProvider $httpProvider = null) {
    parent::__construct(null, null, null, 'http://api.mixi-platform.com/os/0.8', null, "Mixi", true, $httpProvider);
  }

  
  /**
   * Set's the signer's useBodyHash to true
   * @param mixed $request The osapiRequest object being processed, or an array
   *     of osapiRequest objects.
   * @param string $method The HTTP method used for this request.
   * @param string $url The url being fetched for this request.
   * @param array $headers The headers being sent in this request.
   * @param osapiAuth $signer The signing mechanism used for this request.
   */

  public function preRequestProcess(&$request, &$method, &$url, &$headers, osapiAuth &$signer) {
    /*
     * Mixi does not support oauth_body_hash
     * Mixi does not sign body in oauth_signature also.
     * it's bad!
    */

    if (method_exists($signer, 'setSignBody')) {
        if ($request->method == 'mixipoint.create') {
            $signer->setSignBody(true);
            if (method_exists($signer, 'setUseBodyHash')) {
                $signer->setUseBodyHash(true);
            }
        } else {
            $signer->setSignBody(false);
        }
    }    

  }
  
  /**
   * Encode the mixed $value into the JSON format
   * it diffrent with json_encode()
   * json_encode convert unicode charactor (chinese,japanese,...) to like \u8765\u5678
   * 
   * @param mixed $value
   * @return string JSON encoded object
   */
  public function jsonEncode($value)
  {
      require_once 'Zend/Json/Encoder.php';
      return Zend_Json_Encoder::encode($value);
  }
  
  public function getRestEndpoint(&$request, &$method)
  {
      if ($request->method == 'mixipoint.create') {
          return 'http://api.mixi-platform.com';
      }
      
      return $this->restEndpoint;
  }
  
}
