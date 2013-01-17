<?php

require_once 'Dal/User.php';
require_once 'Bll/Cache/User.php';

class Bll_User
{
    public static function getPerson($id)
    {
        return Bll_Cache_User::getPerson($id);
    }

    /*
    public static function getPeople($ids)
    {
        $dalUser = Dal_User::getDefaultInstance();

        return $dalUser->getPeople($ids);
    }
    */
    
    public static function getPeople($ids)
    {
        $items = array();
        $start = 0;
        $total = 0;
        
        foreach ($ids as $id) {
            $items[] = self::getPerson($id);
            $total++;
        }
        
        return new OpenSocial_Collection($start, $total, $items);
    }

    public static function updatePerson($person)
    {
        if ($person == null) {
			return;
		}
		
		//has no basic info
        $displayName = $person->getField('displayName');
        if (empty($displayName)) {
            return;
        }
        
        $id = $person->getId();
        
        $oldPerson = self::getPerson($id);
        
        if ($oldPerson == null || $oldPerson->isDifferentWith($person)) {

            $dalUser = Dal_User::getDefaultInstance();
            try {
                $dalUser->updatePerson($person);
                Bll_Cache_User::cleanPerson($id);
            }
            catch (Exception $e) {
                err_log($e->getMessage());
            }
        }
    }

    /*
    public static function updatePeople($people)
    {
        $updatePeople = array();
        $updateIds = array();

        foreach ($people as $person) {
            $id = $person->getId();
            $oldPerson = self::getPerson($id);

            if ($oldPerson == null || $oldPerson->isDifferentWith($person)) {
                $updatePeople[] = $person;
                $updateIds[] = $id;
            }
        }

        if (count($updateIds) > 0) {
            $dalUser = Dal_User::getDefaultInstance();
            $db = $dalUser->getWriter();
            try {
                $db->beginTransaction();
                $dalUser->deletePeople($updateIds);
                $dalUser->updatePeople($updatePeople);
                $db->commit();
                Bll_Cache_User::cleanPeople($updateIds);
            }
            catch (Exception $e) {
                $db->rollBack();
                err_log($e->getMessage());
            }
        }
    }
    */
    
    public static function updatePeople($people)
    {
        foreach ($people as $person) {
            self::updatePerson($person);
        }
    }

    public static function appendPersonData(&$data, $person = null, $more = false)
    {
        if ($person == null) {
        //  $data['id'] = '';
            $data['displayName'] = '';
            $data['unescapeDisplayName'] = '';
            $data['thumbnailUrl'] = '';
            $data['largeThumbnailUrl'] = '';
            $data['miniThumbnailUrl'] = '';
            $data['profileUrl'] = '';
            
            if ($more) {
                $data['bloodType'] = '';
                $data['address'] = '';
                $data['age'] = '';
                $data['dateOfBirth'] = '';
                $data['gender'] = '';
            }
        }
        else {
        //  $data['id'] = $person->getId();
            $data['displayName'] = $person->getDisplayName();
            $data['unescapeDisplayName'] = $person->getUnescapeDisplayName();
            $data['thumbnailUrl'] = $person->getThumbnailUrl();
            $data['largeThumbnailUrl'] = $person->getLargeThumbnailUrl();
            $data['miniThumbnailUrl'] = $person->getMiniThumbnailUrl();
            $data['profileUrl'] = $person->getProfileUrl();
            
            if ($more) {
                $data['bloodType'] = $person->getField('bloodType');
                $data['address'] = $person->getField('address');
                $data['age'] = $person->getAge();
                $data['dateOfBirth'] = $person->getField('dateOfBirth');
                $data['gender'] = $person->getField('gender');
            }
        }
    }
    
    public static function appendPerson(&$data, $idKey = 'uid', $more = false)
    {
        $person = self::getPerson($data[$idKey]);

        self::appendPersonData($data, $person, $more);
    }

    /*
    public static function appendPeople(&$datas, $idKey = 'uid', $more = false)
    {
        if (empty($datas)) {
            return;
        }
        
        $ids = array();
        foreach ($datas as &$data) {
            $ids[] = $data[$idKey];
            self::appendPersonData($data, null, $more);
        }

        $people = self::getPeople($ids);

        if ($people->count() > 0) {
            foreach ($datas as &$data) {
                $person = self::search($people, $data[$idKey]);
                if ($person) {
                    self::appendPersonData($data, $person, $more);
                }
            }
        }
    }
    */
    
    public static function appendPeople(&$datas, $idKey = 'uid', $more = false)
    {
        if (empty($datas)) {
            return;
        }
        
        foreach ($datas as &$data) {
            $person = self::getPerson($data[$idKey]);
            self::appendPersonData($data, $person, $more);
        }
    }

    public static function search($people, $id)
    {
        foreach($people as $person) {
            if ($person->getId() == $id) {
                return $person;
            }
        }

        return null;
    }
}