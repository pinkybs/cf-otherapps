<?php 

/**
 * osapiPointResult
 * @author hulj 
 *
 */
class osapiPointResult
{
  public $id;
  public $updated;
  public $link;
  
  public function __construct($id, $updated, $link)
  {
    $this->id = $callback_url;
    $this->updated = $updated;
    $this->link = $link;
  }
  
  public function getId()
  {
      return $this->id;
  }
  
  public function getPointCode()
  {
      return $this->id;
  }
    
  public function getLink()
  {
      return $this->link;
  }
  
  public function getUpdated()
  {
      return $this->updated;
  }
  
  public function toArray()
  {
      return array(
        'id' => $this->id,
        'updated' => $this->updated,
        'link' => $this->link
      );
  }
  
  public static function getPointResult($resultXML, $format = 'Array')
  {
      $entry = simplexml_load_string($resultXML);
            
      $id = (string)$entry->id;
      $updated = strtotime($entry->updated);
      $linkElement = $entry->link;
      $linkAttributes = $linkElement->attributes();
      $link = (string)$linkAttributes->href;
      
      if ($format == 'Array') {
          return array(
            'id' => $id,
            'updated' => $updated,
            'link' => $link          
          );
      }
      
      return new osapiPointResult($id, $updated, $link);
  }
  
  

}
