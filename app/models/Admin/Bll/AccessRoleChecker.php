<?php

require_once 'Zend/Acl.php';
require_once 'Zend/Acl/Role.php';
require_once 'Zend/Acl/Resource.php';

/**
 * AccessRoleChecker
 * authenticate check user's role and allowed resource
 *
 * @package    Admin/Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/02/25    zhangxin
 */
class Admin_Bll_AccessRoleChecker
{

    /**
     * ACL,access control list
     * @var object Zend_Acl
     */
    protected $_acl;

    /**
     * init the Checker's variables
     *
     * @param array $roles
     * @param array $resource
     * @param array $assertMap
     */
    public function __construct($roles, $resource, $assertMap)
    {
        $acl = new Zend_Acl();

        //ACL add roles
        foreach ($roles as $role) {
            $acl->addRole(new Zend_Acl_Role($role['role_name']));
        }

        //ACL add resource
        foreach ($resource as $res) {
            $acl->add(new Zend_Acl_Resource($res['page_url']));
        }

        //ACL assert resource to role
        foreach ($assertMap as $assert) {
            $acl->allow($assert['role'], $assert['resource']);
        }

        $this->_acl = $acl;
    }

    /**
     * check user role is allow to visit resource
     * @param string $roleName
     * @param string $resourceName
     *
     * @return integer (0 -not found ; 1 - not allow; 2- allow)
     */
    public function checkAllowed($roleName, $resourceName)
    {
        if ($this->_acl->has($resourceName)) {
            $result = $this->_acl->isAllowed($roleName, $resourceName) ? 2 : 1;
        }
        else {
            $result = 0;
        }
        return $result;
    }

    /**
     * append a role
     * @param string $roleName
     *
     * @return void
     */
    public function appendRole($roleName)
    {
        $this->_acl->addRole(new Zend_Acl_Role($roleName));
    }

	/**
     * append a resource
     * @param string $resourceName
     *
     * @return void
     */
    public function appendResource($resourceName)
    {
        $this->_acl->add(new Zend_Acl_Resource($resourceName));
    }

    /**
     * assert a resource to a role
     * @param string $roleName
     * @param string $resourceName
     *
     * @return void
     */
    public function assert($roleName, $resourceName = null)
    {
        $this->_acl->allow($roleName, $resourceName);
    }

}