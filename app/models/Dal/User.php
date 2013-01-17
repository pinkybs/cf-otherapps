<?php

require_once 'OpenSocial/Collection.php';
require_once 'OpenSocial/Person.php';

require_once 'Dal/Abstract.php';

class Dal_User extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_user = 'mixi_user';
    
    protected static $_instance;
    
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    public function getTableName($id)
    {
        $n = $id % 10;
        return $this->table_user . '_' . $n;
    }

    public function getPerson($id)
    {
        $tname = $this->getTableName($id);
        
        $sql = "SELECT id,displayName,thumbnailUrl,profileUrl,bloodType,address,dateOfBirth,gender FROM $tname WHERE id=:id";
        
        $row = $this->_rdb->fetchRow($sql, array('id' => $id));        
        
        if ($row) {
            return OpenSocial_Person::parseJson($row);
        }
        
        return null;
    }
    
    /*
    public function getPeople(array $ids)
    {
        $ids = $this->_rdb->quote($ids);
        $sql = "SELECT * FROM $this->table_user WHERE id in ($ids)";
        
        $rows = $this->_rdb->fetchAll($sql);
        
        if ($rows) {
            $start = 0;
            $count = count($rows);
        }
        else {
            $count = 0;
            $rows = array();
        }
        
        return OpenSocial_Person::parseJsonCollection($start, $count, $rows);
    }
    */
    
    public function updatePerson(OpenSocial_Person $person)
    {
        $time = time();
        $id = $this->_wdb->quote($person->getId());
        $displayName = $this->_wdb->quote($person->getDisplayName());
        $thumbnailUrl = $this->_wdb->quote($person->getThumbnailUrl());
        $profileUrl = $this->_wdb->quote($person->getProfileUrl());
        
        $bloodType = $person->getField('bloodType');
        $bloodType = $bloodType ? $this->_wdb->quote($bloodType) : 'null';
        
        $address = $person->getField('address');
        $address = $address ? $this->_wdb->quote($address) : 'null';
                
        $dateOfBirth = $person->getField('dateOfBirth');
        $dateOfBirth = $dateOfBirth ? $this->_wdb->quote($dateOfBirth) : 'null';
        
        $gender = $person->getField('gender');
        $gender = $gender ? $this->_wdb->quote($gender) : 'null';
        
        $tname = $this->getTableName($person->getId());
        
        $sql = "INSERT INTO $tname (id, displayName, thumbnailUrl, profileUrl, bloodType, address, dateOfBirth, gender, time) VALUES"
              . '(' . $id . ',' . $displayName . ',' . $thumbnailUrl . ',' . $profileUrl . ','
              . $bloodType . ',' . $address . ',' . $dateOfBirth . ',' . $gender . ',' . $time .')'
              . ' ON DUPLICATE KEY UPDATE '
              . 'displayName=' . $displayName
              . ',thumbnailUrl=' . $thumbnailUrl
              . ',profileUrl=' . $profileUrl
              . ',bloodType=' . $bloodType
              . ',address=' . $address
              . ',dateOfBirth=' . $dateOfBirth
              . ',gender=' . $gender
              . ',time=' . $time;
        
        return $this->_wdb->query($sql, $params);
    }
    
    public function deletePerson($id)
    {
        $tname = $this->getTableName($id);
        
        $sql = "DELETE FROM $tname WHERE id=:id";
        
        return $this->_wdb->query($sql, array('id' => $id));
    }
    
    /*
    public function deletePeople(array $ids)
    {
        $ids = $this->_rdb->quote($ids);
        $sql = "DELETE FROM $this->table_user WHERE id in ($ids)";
        
        return $this->_wdb->query($sql);
    }
    */
    
    /*
    public function updatePeople($people)
    {
        $count = count($people);
        if ($count == 0) {
            return;
        }
        
        $time = time();
        $person = $people[0];
        $id = $this->_wdb->quote($person->getId());
        $displayName = $this->_wdb->quote($person->getDisplayName());
        $thumbnailUrl = $this->_wdb->quote($person->getThumbnailUrl());
        $profileUrl = $this->_wdb->quote($person->getProfileUrl());
        
        $bloodType = $person->getField('bloodType');
        $bloodType = $bloodType ? $this->_wdb->quote($bloodType) : 'null';
        
        $address = $person->getField('address');
        $address = $address ? $this->_wdb->quote($address) : 'null';
        
        $dateOfBirth = $person->getField('dateOfBirth');
        $dateOfBirth = $dateOfBirth ? $this->_wdb->quote($dateOfBirth) : 'null';
        
        $gender = $person->getField('gender');
        $gender = $gender ? $this->_wdb->quote($gender) : 'null';
        
        $sql = "INSERT INTO $this->table_user (id, displayName, thumbnailUrl, profileUrl, bloodType, address, dateOfBirth, gender, time) VALUES"
              . '(' . $id . ',' . $displayName . ',' . $thumbnailUrl . ',' . $profileUrl . ','
              . $bloodType . ',' . $address . ',' . $dateOfBirth . ',' . $gender . ',' . $time .')';
             
        for($i = 1; $i < $count; $i++) {
            $person = $people[$i];
            $id = $this->_wdb->quote($person->getId());
            $displayName = $this->_wdb->quote($person->getDisplayName());
            $thumbnailUrl = $this->_wdb->quote($person->getThumbnailUrl());
            $profileUrl = $this->_wdb->quote($person->getProfileUrl());
            
            $bloodType = $person->getField('bloodType');
            $bloodType = $bloodType ? $this->_wdb->quote($bloodType) : 'null';
            
            $address = $person->getField('address');
            $address = $address ? $this->_wdb->quote($address) : 'null';
            
            $dateOfBirth = $person->getField('dateOfBirth');
            $dateOfBirth = $dateOfBirth ? $this->_wdb->quote($dateOfBirth) : 'null';
            
            $gender = $person->getField('gender');
            $gender = $gender ? $this->_wdb->quote($gender) : 'null';
            
            $sql .= ',(' . $id . ',' . $displayName . ',' . $thumbnailUrl . ',' . $profileUrl . ','
                  . $bloodType . ',' . $address . ',' . $age . ',' . $dateOfBirth . ',' . $gender . ',' . $time .')';
        }
        
        return $this->_wdb->query($sql);
    }
    */
}