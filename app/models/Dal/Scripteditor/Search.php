<?php

require_once 'Dal/Abstract.php';

class Dal_Scripteditor_Search extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_search = 'scripteditor_search';
    
    protected static $_instance;
    
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
  
    /**
     * insert scripteditor search log
     *
     * @param string $tag
     * @return void
     */
    public function insertSearch($tag)
    {
        $sql = "INSERT INTO $this->table_search (tag) SELECT :tag FROM DUAL 
                WHERE NOT EXISTS (SELECT tag FROM $this->table_search WHERE tag=:tag) ";
        $this->_wdb->query($sql,array('tag'=>$tag));
    }
    
    /**
     * update search log
     *
     * @param integer $sid
     * @return void
     */
    public function updateSearchCount($id)
    {
        $sql = "UPDATE $this->table_search SET search_count=search_count+1 WHERE id=:id ";
        $this->_wdb->query($sql,array('id'=>$id));
    }

    /**
     * get tag search info
     *
     * @param string $tag
     * @return $array
     */
    public function getSearchTag($tag)
    {
        $tag = $this->_rdb->quote($tag);
        
        $sql = "SELECT * FROM $this->table_search WHERE tag=$tag ";

        return $this->_rdb->fetchRow($sql);
    }

    /**
     * get tag search list
     *
     * @param integer $maxNm
     * @return $array
     */
    public function getTagList($maxNm)
    {
        $sql = "SELECT * FROM $this->table_search ORDER BY search_count DESC LIMIT 0,$maxNm ";

        return $this->_rdb->fetchAll($sql);
    }
    
    /**
     * get all search count
     *
     * @return integer
     */
    public function getAllSearchCount()
    {
        $sql = "SELECT SUM(search_count) AS all_count FROM $this->table_search ";

        return $this->_rdb->fetchOne($sql);
    }

    /**
     * search entry
     *
     * @param array $arrSearch
     * @param integer $isSearchAll
     * @param integer $language
     * @param integer $pageIndex
     * @param integer $pagesize
     * @return array
     */
    public function getSearchEntry($arrSearch, $isSearchAll, $language, $pageIndex, $pagesize = 5)
    {
        $start = ($pageIndex - 1) * $pagesize;
                
        $sql = "SELECT e.*,u.nickname FROM scripteditor_entry AS e,scripteditor_user AS u 
                WHERE e.uid=u.uid AND e.status=1 ";
        
        if ( $isSearchAll != '1' ) {
            $sql .= " AND e.language=:language ";
        }
        
        for ( $i=0, $count=count($arrSearch); $i<$count; $i++ ) {

            if ($arrSearch[$i]) {
                if ( $i > 0 ) {
                    $sql .= " OR ";
                }
                else {
                    $sql .= " AND (";
                }

                //$search = $this->_rdb->quote($arrSearch[$i]);
                $search = $arrSearch[$i];
                
                $sql .= " FIND_IN_SET('$search', e.tag) OR e.title LIKE '%$search%' OR e.content LIKE '%$search%' ";
            }
        }
        
        if ( $arrSearch[0] ) {
            $sql .= ") ";
        }
        
        $sql .= " LIMIT $start,$pagesize";
        
        
        $result =  $this->_rdb->fetchAll($sql, array('language'=>$language));
        return $result;
    }    
    
    /**
     * get all search count
     *
     * @param array $arrSearch
     * @param integer $language
     * @return integer
     */
    public function getSearchEntryCount($arrSearch, $language)
    {
        if ($language) {
            $sql = "SELECT count(1) FROM scripteditor_entry WHERE language=:language AND status=1 ";
        }
        else {
            $sql = "SELECT count(1) FROM scripteditor_entry WHERE status=1 ";
        }
        
        
        for ( $i=0, $count=count($arrSearch); $i<$count; $i++ ) {

            if ($arrSearch[$i]) {
                if ( $i > 0 ) {
                    $sql .= " OR ";
                }
                else {
                    $sql .= " AND (";
                }

                //$search = $this->_rdb->quote($arrSearch[$i]);
                $search = $arrSearch[$i];
                
                $sql .= " FIND_IN_SET('$search', tag) OR title LIKE '%$search%' OR content LIKE '%$search%' ";
            }
        }
        
        if ( $arrSearch[0] ) {
            $sql .= ") ";
        }
        
        $result =  $this->_rdb->fetchOne($sql, array('language'=>$language));
        return $result;
    }
}