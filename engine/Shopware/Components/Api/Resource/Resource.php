<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Components\Api\Resource;

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Components\Api\Exception as ApiException;

/**
 * Abstract API Resource Class
 *
 * @category  Shopware
 * @package   Shopware\Components\Api\Resource
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
abstract class Resource
{
    /* Hydration mode constants */
    /**
     * Hydrates an object graph. This is the default behavior.
     */
    const HYDRATE_OBJECT = 1;
    /**
     * Hydrates an array graph.
     */
    const HYDRATE_ARRAY = 2;

    /**
     * Contains the shopware model manager
     *
     * @var \Shopware\Components\Model\ModelManager
     */
    protected $manager = null;

    /**
     * @var bool
     */
    protected $autoFlush = true;

    /**
     * @var int
     */
    protected $resultMode = self::HYDRATE_ARRAY;

    /**
     * @var \Shopware_Components_Acl
     */
    protected $acl = null;

    /**
     * Contains the current role
     *
     * @var string|\Zend_Acl_Role_Interface
     */
    protected $role = null;

    /**
     * @param string $privilege
     * @throws \Shopware\Components\Api\Exception\PrivilegeException
     */
    public function checkPrivilege($privilege)
    {
        if (!$this->getRole() || !$this->getAcl()) {
            return;
        }

        $calledClass = get_called_class();
        $calledClass = explode('\\', $calledClass);
        $resource = strtolower(end($calledClass));

        if (!$this->getAcl()->has($resource)) {
            return;
        }

        $role = $this->getRole();

        if (!$this->getAcl()->isAllowed($role, $resource, $privilege)) {
            $message = sprintf(
                'Role "%s" is not allowed to "%s" on resource "%s"',
                is_string($role) ? $role : $role->getRoleId(),
                $privilege,
                is_string($resource) ? $resource : $resource->getResourceId()
            );
            throw new ApiException\PrivilegeException($message);
        }
    }

    /**
     * @param \Shopware\Components\Model\ModelManager $manager
     */
    public function setManager(\Shopware\Components\Model\ModelManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return \Shopware\Components\Model\ModelManager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @param \Shopware_Components_Acl $acl
     * @return Resource
     */
    public function setAcl(\Shopware_Components_Acl $acl)
    {
        $this->acl = $acl;

        return $this;
    }

    /**
     * @return \Shopware_Components_Acl
     */
    public function getAcl()
    {
        return $this->acl;
    }

    /**
     * @param string|\Zend_Acl_Role_Interface $role
     * @return Resource
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return string|\Zend_Acl_Role_Interface
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param boolean $autoFlush
     */
    public function setAutoFlush($autoFlush)
    {
        $this->autoFlush = (bool) $autoFlush;
    }

    /**
     * @return boolean
     */
    public function getAutoFlush()
    {
        return $this->autoFlush;
    }

    /**
     * @param int $resultMode
     */
    public function setResultMode($resultMode)
    {
        $this->resultMode = $resultMode;
    }

    /**
     * @return int
     */
    public function getResultMode()
    {
        return $this->resultMode;
    }

    /**
     * @param object $entity
     * @throws \Shopware\Components\Api\Exception\OrmException
     */
    public function flush($entity = null)
    {
        if ($this->getAutoFlush()) {
            $this->getManager()->getConnection()->beginTransaction();
            try {
                $this->getManager()->flush($entity);
                $this->getManager()->getConnection()->commit();
                $this->getManager()->clear();
            } catch (\Exception $e) {
                $this->getManager()->getConnection()->rollBack();
                throw new ApiException\OrmException($e->getMessage(), 0, $e);
            }
        }
    }

    /**
     * Helper function which checks the option configuration for the passed collection.
     * If the data property contains the "__options_$optionName" value and this value contains
     * the "replace" parameter the collection will be cleared.
     *
     * @param ArrayCollection $collection
     * @param $data
     * @param $optionName
     */
    protected function checkDataReplacement(ArrayCollection $collection, $data, $optionName)
    {
        $key = '__options_' . $optionName;
        if (isset($data[$key]) && $data[$key]['replace']) {
            $collection->clear();
        }
    }

}
