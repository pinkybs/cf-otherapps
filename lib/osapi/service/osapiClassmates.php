<?php

/**
 * OpenSocial API class for Classmates requests
 *
 * @author hulj
 */
class osapiClassmates extends osapiService {
	
  /**
   * Gets a list of fields supported by this service
   *
   * @return osapiRequest the request
   */
  public function getSupportedFields() {
  	return osapiRequest::createRequest('classmates.getSupportedFields', array('userId' => '@supportedFields'));
  }
  
  /**
   * Gets classmates
   *
   * @param array $params the parameters defining which classmates to retrieve
   * @return osapiRequest the request
   */
  public function get($params) {
    return osapiRequest::createRequest('classmates.get', $params);
  }
  
  /**
   * Updates an classmate
   *
   * @param array $params the parameters defining the album data to update
   * @return osapiRequest the request
   */
  public function update($params){
    throw new osapiException("Updating classmates is not supported");
  }
  
  /**
   * Deletes an classmate
   *
   * @param array $params the parameters defining the album to delete
   * @return osapiRequest the request
   */
  public function delete($params){
  	throw new osapiException("Deleting classmates is not supported");
  }
  
  /**
   * Creates an classmate
   *
   * @param array $params the parameters defining the classmate to create
   * @return osapiRequest the request
   */
  public function create($params){
    throw new osapiException("Createing classmates is not supported");
  }

  /**
   * Converts a response into a native data type.
   *
   * @param array $array the raw data
   * @param boolean $strictMode whether to throw spec errors
   * @return osapiPerson
   */
  static public function convertArray($array, $strictMode = true) {
 	$school = new osapiSchool();
 	$defaults = get_class_vars('osapiSchool');
 	
 	if ($strictMode && sizeof($defaults != sizeof($array))) {
      throw new osapiException("Unexpected fields in classmates response". print_r($array, true));
    }
    
  	foreach ($array as $key=>$value) {
  		$school->setField($key, $value);
  	}
    return self::trimResponse($school);
  }
}
