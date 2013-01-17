<?php 

/**
 * osapiPointUrl
 * @author hulj 
 *
 */
class osapiPointUrl
{
  public $callback_url;
  public $finish_url;
  
  public function __construct($callback_url, $finish_url)
  {
    $this->callback_url = $callback_url;
    $this->finish_url = $finish_url;
  }
  
  public function getCallBackUrl()
  {
      return $this->callback_url;
  }
  
  public function setCallBackUrl($newCallBackUrl)
  {
      $this->callback_url = $newCallBackUrl;
  }
  
  public function getFinishUrl()
  {
      return $this->finish_url;
  }
  
  public function setFinishUrl($newFinishUrl)
  {
      $this->finish_url = $newFinishUrl;
  }  

  public function toAtomString()
  {
      return '<point:url callback_url="' . $this->callback_url . '" finish_url="' . $this->finish_url .  '" />';
  }
}
