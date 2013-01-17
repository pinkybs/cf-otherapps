<?php

/**
 * @see Zend_Mail_Transport_Abstract
 */
require_once 'Zend/Mail/Transport/Abstract.php';

/**
 * email send manager
 * Abstract class
 *
 * @package    Bll_Email
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2008/08/27    HLJ
 */
abstract class Admin_Bll_Email_Abstract
{
    /**
     * web host url
     *
     * @var string
     */
    protected $_host;

    /**
     * Construct function
     * init data
     *
     * @param string $host
     */
    public function __construct($host = null)
    {
        if ($host === null) {
            $this->_host = Zend_Registry::get('host');
        }
        else {
            $this->_host = $host;
        }
    }

    /**
     * get email template
     *
     * @param string $xmlEmail xml path
     * @return Zend_Config_Xml object
     */
    protected function _getEmailTemplate($xmlEmail)
    {
        return new Zend_Config_Xml($xmlEmail, 'email', true);
    }

    /**
     * truncate input string
     *
     * @param string $string
     * @param integer $length
     * @param string $ext
     * @return string
     */
    public function truncate($string, $length = 100, $ext = '……')
    {
        if ($length == 0) {
            return $ext;
        }

        if (mb_strlen($string, 'utf-8') > $length) {
            return mb_substr($string, 0, $length, 'utf-8') . $ext;
        }
        else {
            return $string;
        }
    }

    /**
     * get mail charset by email
     *
     * @param string $email
     * @return string
     */
    protected function _getCharset($email)
    {
        $pieces = explode('@', $email);
        $domain = array_pop($pieces);

        //if is aol
        if (strcmp(strtolower($domain), 'aol.com') == 0) {
            return 'utf-8';
        }

        return 'iso-2022-jp';
    }

    /**
     * get mail encoding by email
     *
     * @param string $email
     * @return string
     */
    protected function _getEncoding($email)
    {
        $pieces = explode('@', $email);
        $domain = array_pop($pieces);

        require_once 'Zend/Mime.php';

        //waseda.jp
        //This might be better to use in regular expression.
        if (stristr($domain, 'waseda.jp') != false) {
           return Zend_Mime::ENCODING_7BIT;
        }

        //Use Base64 for other mail addresses.
        return Zend_Mime::ENCODING_BASE64;
    }

    /**
     * get email template order by input info
     *
     * @abstract
     * @param array $info
     * @return Zend_Config_Xml object
     */
    abstract protected function _getEmail($info);

    /**
     * send email
     *
     * @param Zend_Config_Xml $emailTemplate
     * @param Zend_Mail_Transport_Abstract $transport
     */
    protected function _sendEmail($emailTemplate, Zend_Mail_Transport_Abstract $transport = null)
    {
        require_once 'Zend/Mail.php';

        $charset = $this->_getCharset($emailTemplate->to->mail);

        $encoding = $this->_getEncoding($emailTemplate->to->mail);

        $mail = new Zend_Mail($charset);

        $mail->setFrom($emailTemplate->from->mail, mb_convert_encoding($emailTemplate->from->name, $charset, 'auto'));
        $mail->addTo($emailTemplate->to->mail, mb_convert_encoding($emailTemplate->to->name, $charset, 'auto'));
        $mail->setSubject(mb_convert_encoding($emailTemplate->subject, $charset, 'auto'));
        $mail->setBodyText(mb_convert_encoding($emailTemplate->content . $emailTemplate->signed, $charset, 'auto'), $charset, $encoding);
        $mail->setReturnPath($emailTemplate->returnpath);

        $mail->send($transport);
    }

    /**
     * main function to send mail
     *
     * @param array $info
     * @param Zend_Mail_Transport_Abstract $transport
     * @return boolean
     */
    public function send($info, Zend_Mail_Transport_Abstract $transport = null)
    {
        try {
            $this->_sendEmail($this->_getEmail($info), $transport);
            return true;
        }
        catch (Exception $e) {
            return false;
        }
    }
}