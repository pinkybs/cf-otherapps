<?php 

/**
 * osapiPointStatus
 * @author hulj 
 *
 */
class osapiPointStatus
{
  public $is_test;
  
  public function __construct($is_test = false)
  {
    $this->is_test = $is_test;
  }
  
  public function isTest()
  {
      return $this->is_test;
  }
  
  public function setTest($isTest)
  {
      $this->is_test = $isTest;
  }
  
  public function toAtomString()
  {
      return '<point:status is_test="' . ($this->is_test ? 'true' : 'false') .  '" />';
  }
    
}
