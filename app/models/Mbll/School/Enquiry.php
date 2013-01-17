<?php

require_once 'Mdal/Abstract.php';

class Mbll_School_Enquiry extends Mdal_Abstract
{
	/**
	 * insert enquiry comment
	 *
	 * @param array $info
	 * @return boolean
	 */
 	public function insertEnquiryComment($info)
    {
		try {
            require_once 'Mdal/School/Enquiry.php';
        	$mdalEnquiry = Mdal_School_Enquiry::getDefaultInstance();

            $this->_wdb->beginTransaction();

			$rowEnquiry = $mdalEnquiry->getEnquiryLock($info['qid']);
			if ($rowEnquiry) {
				$commentId = $mdalEnquiry->isUserHaveComment($info['uid'], $info['qid']);
				if (empty($commentId)) {
					$cnt = $mdalEnquiry->getCntEnquiryCommentById($info['qid']);
					$info['no'] = $cnt + 1;
					$mdalEnquiry->insertEnquiryComment($info);
					$mdalEnquiry->updateEnquiry(array('answer_count' => $rowEnquiry['answer_count'] + 1, 'update_time' => time()), $info['qid']);
				} else {
					$mdalEnquiry->updateEnquiryComment($info, $commentId);
					$mdalEnquiry->updateEnquiry(array('update_time' => time()), $info['qid']);
				}
			}
            $this->_wdb->commit();
            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Mbll/School/Enquiry/insertEnquiryComment:' . $e->getMessage());
            return false;
        }
    }

    /**
     * update enquiry comment
     *
     * @param array $info
     * @param integer $commentid
     * @param integer $qid
     * @param integer $isDel 1:delete, 0:update
     * @return boolean
     */
    public function updateEnquiryComment($info, $commentid, $qid)
    {
    	try {
            require_once 'Mdal/School/Enquiry.php';
        	$mdalEnquiry = Mdal_School_Enquiry::getDefaultInstance();

            $this->_wdb->beginTransaction();

            $rowEnquiry = $mdalEnquiry->getEnquiryLock($qid);
    		if ($rowEnquiry) {
				$mdalEnquiry->updateEnquiryComment($info, $commentid);
				$mdalEnquiry->updateEnquiry(array('update_time' => time()), $qid);
				if ($info['isDelete']) {
					$commentInfo = $mdalEnquiry->getEnquiryComment($commentid);
					if ($commentInfo['good_count'] > 0) {
						$this->_wdb->rollBack();
						return false;
					} else {
						$mdalEnquiry->updateEnquiry(array('answer_count' => $rowEnquiry['answer_count'] - 1), $qid);
					}
				}
			}
            $this->_wdb->commit();
			return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Mbll/School/Enquiry/updateEnquiryComment:' . $e->getMessage());
            return false;
        }
    }

    /**
     * insert enquiry comment good
     *
     * @param array $info
     * @return integer
     */
 	public function insertEnquiryCommentGood($info)
    {
		try {
			$ownerId = '';
            require_once 'Mdal/School/Enquiry.php';
        	$mdalEnquiry = Mdal_School_Enquiry::getDefaultInstance();
        	require_once 'Mdal/School/User.php';
            $mdalUser = Mdal_School_User::getDefaultInstance();

            $this->_wdb->beginTransaction();
			$rowEnquiryComment = $mdalEnquiry->getEnquiryCommentLock($info['comment_id']);
			if ($rowEnquiryComment['isdelete']) {
				$this->_wdb->rollBack();
	            return false;
			}

			$rowUser = $mdalUser->getUserLock($rowEnquiryComment['uid']);
			if ($rowEnquiryComment && $rowUser) {
				$mdalEnquiry->insertEnquiryCommentGood($info);
				$mdalEnquiry->updateEnquiryComment(array('good_count' => $rowEnquiryComment['good_count'] + 1), $info['comment_id']);
				$mdalUser->updateUser(array('star_count' => $rowUser['star_count'] + 1), $rowEnquiryComment['uid']);
				$mdalUser->insertUserStarChangeHistory(array('actor_uid' => $info['uid'],
															 'target_uid' => $rowEnquiryComment['uid'],
															 'star' => 1,
															 'create_time' => time()));
			}

			$ownerId = $rowEnquiryComment['uid'];
            $this->_wdb->commit();

            return $ownerId;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Mbll/School/Enquiry/insertEnquiryCommentGood:' . $e->getMessage());
            return false;
        }
    }
}