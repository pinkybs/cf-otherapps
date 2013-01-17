<?php

require_once 'Mbll/Abstract.php';

/**
 * Mixi App School Class Topic logic Operation
 *
 * @package    Mbll/School
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/11/19
 */
final class Mbll_School_Topic extends Mbll_Abstract
{

    /**
     * add class topic
     *
     * @param array $info
     * @param integer $cid
     * @param integer $uid
     * @return boolean
     */
    public function addTopic($info, $cid, $uid)
    {
        try {
            require_once 'Mdal/School/Topic.php';
            $mdalTopic = Mdal_School_Topic::getDefaultInstance();
            require_once 'Mdal/School/Class.php';
            $mdalClass = Mdal_School_Class::getDefaultInstance();
            require_once 'Mdal/School/AssistNewestTopic.php';
            $mdalAssist = Mdal_School_AssistNewestTopic::getDefaultInstance();

            if (!$mdalClass->isClassMember($cid, $uid)) {
                return 0;
            }

            $this->_wdb->beginTransaction();
            $rowClass = $mdalClass->getClassInfoLock($cid);
            if (empty($rowClass)) {
                $this->_wdb->rollBack();
                return false;
            }
            //insert topic
            $info['cid'] = $cid;
            $info['uid'] = $uid;
            $info['create_time'] = time();
            $info['update_time'] = time();
            $result = $mdalTopic->insertTopic($info);
            if (empty($result)) {
                $this->_wdb->rollBack();
                return false;
            }
            //update class
            $mdalClass->updateClass(array('topic_count' => ((int)$rowClass['topic_count'] + 1), 'last_new_update_time' => time()), $cid);
            $this->_wdb->commit();
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Mbll/School/Topic/addTopic:' . $e->getMessage());
        }

        try {
            //update newest topic assist table
            $rowAssist = $mdalAssist->getNewestTopic($cid);
            if (empty($rowAssist)) {
                $mdalAssist->insertNewestTopic(array('cid' =>$cid, 'tid' => $result, 'update_time' => $info['update_time']));
            }
            else {
                $mdalAssist->updateNewestTopic(array('tid' => $result, 'update_time' => $info['update_time']), $cid);
            }
        }
        catch (Exception $e2) {
            debug_log('Mbll/School/Topic/addTopic-assist:' . $e2->getMessage());
        }

        return $result;
    }

/******************************************************/

/**
* xial ****************************************************
*/

    public function insertTopicCommentGood($info)
    {
		try {
            require_once 'Mdal/School/Topic.php';
            $mdalTopic = Mdal_School_Topic::getDefaultInstance();

            require_once 'Mdal/School/User.php';
            $mdalUser = Mdal_School_User::getDefaultInstance();

            $this->_wdb->beginTransaction();

			$rowTopicComment = $mdalTopic->getTopicCommentLock($info['comment_id']);
			$rowUser = $mdalUser->getUserLock($rowTopicComment['uid']);
			if ($rowTopicComment && $rowUser && empty($rowTopicComment['isdelete'])) {
				$mdalTopic->insertCommentGood($info);
				$mdalTopic->updateTopicComment(array('good_count' => $rowTopicComment['good_count'] + 1), $info['comment_id']);
				$mdalUser->updateUser(array('star_count' => $rowUser['star_count'] + 10), $rowTopicComment['uid']);
				$mdalUser->insertUserStarChangeHistory(array('actor_uid' => $info['uid'],
															 'target_uid' => $rowTopicComment['uid'],
															 'star' => 10,
															 'create_time' => time()));
			} else {
				$this->_wdb->rollBack();
				return false;
			}

            $this->_wdb->commit();
            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Mbll/School/Topic/insertTopicCommentGood:' . $e->getMessage());
            return false;
        }
    }

    public function insertTopicComment($info)
    {
		try {
            require_once 'Mdal/School/Topic.php';
            $mdalTopic = Mdal_School_Topic::getDefaultInstance();
            $this->_wdb->beginTransaction();

			$rowTopic = $mdalTopic->getClassTopicLock($info['tid']);
			if ($rowTopic && empty($rowTopic['isdelete'])) {
				$cnt = $mdalTopic->getCntCommentById($info['tid']);
				$info['no'] = $cnt + 1;
				$mdalTopic->insertTopicComment($info);
				$mdalTopic->updateClassTopic(array('comment_count' => $rowTopic['comment_count'] + 1), $info['tid']);
			} else {
				$this->_wdb->rollBack();
				return false;
			}

            $this->_wdb->commit();
            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Mbll/School/Topic/insertTopicComment:' . $e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * update topic comment
     *
     * @param integer $info
     * @param integer $commentid
     * @return boolean
     */
    public function updateTopicComment($info, $commentid, $tid)
    {
    	try {
            require_once 'Mdal/School/Topic.php';
            $mdalTopic = Mdal_School_Topic::getDefaultInstance();
            $this->_wdb->beginTransaction();

            $rowComment = $mdalTopic->getTopicCommentLock($commentid);
            //good_count >= 1 not delete
            if ($info['isDelete'] && $rowComment['good_count'] >= 1) {
            	$this->_wdb->rollBack();
            	return false;
            }

            $rowTopic = $mdalTopic->getClassTopicLock($tid);
			if ($rowTopic) {
				$mdalTopic->updateTopicComment($info, $commentid);
				$mdalTopic->updateClassTopic(array('comment_count' => $rowTopic['comment_count'] - 1), $tid);
			}
            $this->_wdb->commit();
            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Mbll/School/Topic/updateTopicComment:' . $e->getMessage());
            return false;
        }
        return true;
    }
}