<?php

/** @see Bll_Email_Abstract */
require_once 'Admin/Bll/Email/Abstract.php';

class Admin_Bll_Email_PC_SetPassword extends Admin_Bll_Email_Abstract
{
    protected function _getEmail($info)
    {
        $xmlEmail = dirname(__FILE__) . '/Xml/setpassword.xml';
        $emailTemplate = $this->_getEmailTemplate($xmlEmail);

        $emailTemplate->to->mail = $info['email'];
        $emailTemplate->to->name = $info['email'];
        $emailTemplate->subject = str_replace('#[Subject]#', $info['title'], $emailTemplate->subject);
        $emailTemplate->content = str_replace('#[uuid]#', $info['uuid'], $emailTemplate->content);
        $emailTemplate->content = str_replace('#[Action]#', $info['action'], $emailTemplate->content);
        $emailTemplate->content = str_replace('#[ServerHost]#', $this->_host, $emailTemplate->content);
        $emailTemplate->signed = str_replace('#[ServerHost]#', $this->_host, $emailTemplate->signed);

        return $emailTemplate;
    }
}