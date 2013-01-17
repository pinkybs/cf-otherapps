<?php 

/**
 * osapiPointItem
 * @author hulj 
 *
 */
class osapiPointItem
{
  public $id;
  public $name;
  public $point;
  
  public function __construct($id, $name, $point)
  {
    $this->id = $id;
    $this->name = $name;
    $this->point = $point;
  }
  
  public function getId()
  {
      return $this->id;
  }
  
  public function setId($newId)
  {
      $this->id = $newId;
  }
  
  public function getName()
  {
      return $this->name;
  }
  
  public function setName($newName)
  {
      $this->name = $newName;
  }  
  
  public function getPoint()
  {
      return $this->point;
  }
  
  public function setPoint($newPoint)
  {
      $this->point = $newPoint;
  }
  
  public function toAtomString()
  {
      return '<point:item id="' . $this->id . '" name="' . $this->name .  '" point="' . $this->point . '" />';
  }
  
}
