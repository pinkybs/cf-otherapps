<?php
/** Bll_Application_Plugin_Interface */
require_once 'Bll/Application/Plugin/Interface.php';

require_once 'Mdal/Quiz/Quiz.php';

class Mbll_Application_Plugin_Quiz implements Bll_Application_Plugin_Interface
{
    public function postUpdatePerson($uid)
    {
    	//insert or update user info
        $mdalQuiz = Mdal_Quiz_Quiz::getDefaultInstance();
        $mbllQuizUser = new Mbll_Quiz_User();
        //check is joined
        $isJoined = $mbllQuizUser->isJoined($uid);

        if (!$isJoined) {
            $mbllQuizUser->join($uid);
        }

        if ($isJoined) {
        	//update status
            $mdalQuiz->updateStatus($uid);
        }
    }

    public function postUpdateFriend($fid)
    {
        //TODO:
    }

    public function postUpdateFriendship($uid, array $fids)
    {
        //TODO:
    }

    public function updateAppFriendship($uid, array $fids)
    {
        //TODO:
    }

    public function postRun(Bll_Application_Interface $application)
    {
        $url = '/mobile/quiz/top/cf_ts/' . time() . '/CF_pos/1';
        $application->redirect($url);
    }
}
