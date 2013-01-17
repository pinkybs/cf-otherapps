<?php 

/**
 * osapiPoint
 * @author hulj 
 *
 */
class osapiPoint extends osapiModel
{
  public $url;
  public $items;
  public $status = null;
  
  public function __construct($url)
  {
    $this->url = $url;
  }
  
  public function getUrl()
  {
      return $this->url;
  }
  
  public function setUrl($newUrl)
  {
      $this->url = $newUrl;
  }
  
  public function getItems()
  {
      return $this->items;
  }
  
  public function setItems($newItems)
  {
      $this->items = $newItems;
  }

  public function getStatus()
  {
      return $this->status;
  }
  
  public function setStatus($newStatus)
  {
      $this->status = $newStatus;
  }
  
  public function toAtomString()
  {
      $atom = '<?xml version="1.0"?>'
            . '<entry xmlns="http://www.w3.org/2005/Atom" xmlns:app="http://www.w3.org/2007/app" xmlns:point="http://mixi.jp/atom/ns#point">'
            . '<title />'
            . '<id />'
            . '<updated />'
            . '<author><name /></author>'
            . '<content type="text/xml">'
            . $this->url->toAtomString()
            . '<point:items>';
            
      foreach ($this->items as $item) {
          $atom .= $item->toAtomString();
      }
      
      $atom .= '</point:items>';
      
      if (isset($this->status)) {
          $atom .= $this->status->toAtomString();
      }
      
      $atom .= '</content>'
             . '</entry>';

     return $atom;
  }
  
}
