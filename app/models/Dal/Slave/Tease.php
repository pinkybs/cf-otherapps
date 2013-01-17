<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Slave Tease
 * MixiApp Slave Tease Data Access Layer
 *
 * @package    Dal/Slave
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/07/06    xiali
 */
class Dal_Slave_Tease extends Dal_Abstract
{

	/**
	 * class default instance
	 * @var self instance
	 */
	protected static $_instance;

	/**
	 * return self's default instance
	 *
	 * @return self instance
	 */
	public static function getDefaultInstance()
	{
		if (self::$_instance == null) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}



	//*****************************************************************************

	/**
	 * insert tease
	 * @param : $info array
	 * @return: integer
	 */
	public function insertTease($info)
	{
		$this->_wdb->insert('slave_tease', $info);
		return $this->_wdb->lastInsertId();
	}

	/**
	 * insert nb tease
	 *@param :$info array
	 * @return :void
	 */
	public function insertNbTease($info)
	{
		return $this->_wdb->insert('slave_nb_tease', $info);
	}

	/**
	 * select nb tease list
	 * @return :array
	 */
	public function listNbTease()
	{
		$sql = "SELECT n.tid,n.action,n.pic_small,n.level FROM slave_nb_tease AS n WHERE iscustom = 0";
		return $this->_rdb->fetchAll($sql);
	}

	/**
	 * get tease by Id
	 * @param :$tid integer
	 * @return :array
	 */
	public function getTeaseById($tid)
	{
		$sql = "SELECT n.tid,n.action,n.pic_small,n.level FROM slave_nb_tease AS n WHERE tid =:tid";
		return $this->_rdb->fetchRow($sql,array('tid' =>$tid));
	}
}
?>