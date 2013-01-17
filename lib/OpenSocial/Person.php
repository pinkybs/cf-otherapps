<?php

/**
 * Represents information about an OpenSocial Person account.
 * @package OpenSocial
 */
class OpenSocial_Person
{
    private $fields = null;

    /**
     * Constructor
     */
    public function __construct($fields = array())
    {
        $this->fields = $fields;
    }

    /**
     * Returns the value of the requested field, if it exists on this Person.
     */
    public function getField($key, $check = true)
    {
        if ($check) {
            if (array_key_exists($key, $this->fields)) {
                return $this->fields[$key];
            }
            else {
                return null;
            }
        }
        else {
            return $this->fields[$key];
        }
    }

    /**
     * Returns the ID number of this Person.
     */
    public function getId()
    {
        return $this->getField("id", false);
    }

    /**
     * Returns a human-readable name for this Person.
     */
    public function getDisplayName()
    {
        return $this->getField("displayName", false);
    }
        
    public function getThumbnailUrl()
    {
        return $this->getField("thumbnailUrl", false);
    }
    
    public function getProfileUrl()
    {
        return $this->getField("profileUrl", false);
    }
    
    public function isDifferentWith($person)
    {
        if ($person == null) {
            return false;
        }
        
        $hasApp = $person->getField('hasApp') == 'true';
        $profileFields = array(
            'address',
            'dateOfBirth',
            'gender'
        );
        
        foreach($this->fields as $key => $value) {
            $newValue = $person->getField($key);
            if (empty($value) && !empty($newValue)) {
                return true;
            }
            if($newValue != $value) {
                //
                if ($hasApp == true || ($hasApp == false && !in_array($key, $profileFields))) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    public function getUnescapeDisplayName()
    {
        $str = $this->getField("displayName", false);
        return MyLib_String::unescapeString($str);
    }

    public function getMobileProfileUrl()
    {
        $profileUrl = $this->getField("profileUrl", false);
        return str_replace('mixi.jp', 'm.mixi.jp', $profileUrl);
    }
    
    public function getLargeThumbnailUrl()
    {
        $thumbnailUrl = $this->getThumbnailUrl();
        if ($thumbnailUrl == 'http://img.mixi.jp/img/basic/common/noimage_member76.gif') {
            return 'http://img.mixi.jp/img/basic/common/noimage_member180.gif';
        }
        return str_replace('s.jpg', '.jpg', $thumbnailUrl);
    }
    
    public function getMiniThumbnailUrl()
    {
        $thumbnailUrl = $this->getThumbnailUrl();
        if ($thumbnailUrl == 'http://img.mixi.jp/img/basic/common/noimage_member76.gif') {
            return 'http://img.mixi.jp/img/basic/common/noimage_member40.gif';
        }
        return str_replace('s.jpg', 'm.jpg', $thumbnailUrl);       
    }
    
    public function getAge()
    {
        $dateOfBirth = $this->getField("dateOfBirth", true);
        if ($dateOfBirth) {
            list($y, $m, $d) = explode('-', $dateOfBirth);
            $today = getdate();
            $age = $today['year'] - $y - 1;
            if ($today['mon'] > $m || ($today['mon'] == $m && $today['mday'] >= $d)) {
                $age++;
            }
            return $age;
        }
        
        return null;
    }

    /**
     * Returns a string representation of this person.
     */
    public function __toString()
    {
        return sprintf("%s [%s]", $this->getDisplayName(), $this->getId());
    }

    /**
     * Converts a JSON response containing a single person's data into an
     * OpenSocial_Person object.
     */
    public static function parseJson($data)
    {
        return new self($data);
    }

    /**
     * Converts a JSON response containing people data into an 
     * OpenSocial_Collection of OpenSocial_Person objects.
     */
    public static function parseJsonCollection($start, $total, $data)
    {
        $items = array();
        foreach ($data as $persondata) {
            $items[] = new self($persondata);
        }
        return new OpenSocial_Collection($start, $total, $items);
    }
}