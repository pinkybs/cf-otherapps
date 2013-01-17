<?php

/**
 * OpenSocial API class for Mixi Point requests
 * Supported methods are create
 *
 * @author hulj
 */
class osapiMixiPoint extends osapiService {

  /**
   * Creates a point.
   *
   * @param array $params the parameters defining the point to create
   * @return osapiRequest the request
   */
  public function create($params) {
    // basic sanity checking of the request object
    if (!isset($params['userId'])) throw new osapiException("Missing 'userId' param for osapiMixiPoint->create");
    if (!isset($params['point'])) throw new osapiException("Missing 'point' param for osapiMixiPoint->create");
    if (!$params['point'] instanceof osapiPoint) throw new osapiException("Point param should be a osapiPoint in osapiMixiPoint->create");
    // strip out the null values before we post the point
    $params['point'] = self::trimResponse($params['point']);    
    return osapiRequest::createRequest('mixipoint.create', $params);
  }
  
  public function get($params) {
    throw new osapiException("getting point is not supported");
  }

  public function update($params) {
    throw new osapiException("Updating point is not supported");
  }

  public function delete($params) {
    throw new osapiException("Deleting point is not supported");
  }

}

