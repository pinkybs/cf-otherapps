<?php

/**
 * osapiSchool - model class for school
 * @author hulj
 *
 */
class osapiSchool extends osapiModel
{
    public $id;
    public $token;
    public $division;

  public function __construct($id=null, $token=null, $division=null)
  {
      $this->id = $id;
      $this->token = $token;
      $this->division = $division;
  }

  public function getId()
  {
      return $this->id;
  }

  public function setId($value)
  {
      $this->id = $value;
  }

  public function getToken()
  {
      return $this->token;
  }

  public function setToken($value)
  {
      $this->token = $value;
  }

  public function getDivision()
  {
      return $this->division;
  }

  public function setDivision($value)
  {
      $this->division = $value;
  }
}
