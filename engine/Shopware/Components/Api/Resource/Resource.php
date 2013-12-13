<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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
use Shopware\Components\Api\BatchInterface;
use Shopware\Components\DependencyInjection\ResourceLoader;

/**
 * Abstract API Resource Class
 *
 * @category  Shopware
 * @package   Shopware\Components\Api\Resource
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
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

    /** @var ResourceLoader */
    protected $resourceLoader = null;

    /**
     * @return ResourceLoader
     */
    public function getResourceLoader()
    {
        if (!$this->resourceLoader) {
            $this->resourceLoader = Shopware()->ResourceLoader();
        }
        return $this->resourceLoader;

    }

    /**
     * @param $resourceLoader
     */
    public function setResourceLoader($resourceLoader)
    {
        $this->resourceLoader = $resourceLoader;
    }

    /**
     * Returns a new api resource which contains the
     * same configuration for the model manager and acl
     * as the current resource.
     *
     * @param $name
     * @return Resource
     */
    protected function getResource($name)
    {
        $name = ucfirst($name);
        $class = __NAMESPACE__ . '\\' . $name;

        /** @var $resource Resource\Resource */
        $resource = new $class();

        $resource->setManager($this->getManager());
        $resource->setAcl($this->getAcl());
        $resource->setRole($this->getRole());

        return $resource;
    }

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
     * @param $defaultReplace
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    protected function checkDataReplacement(ArrayCollection $collection, $data, $optionName, $defaultReplace)
    {
        $key = '__options_' . $optionName;
        if (isset($data[$key])) {
            if ($data[$key]['replace']) {
                $collection->clear();
            }
        } else if ($defaultReplace) {
            $collection->clear();
        }

        return $collection;
    }

    /**
     * @param ArrayCollection $collection
     * @param $property
     * @param $value
     * @return null
     */
    protected function getCollectionElementByProperty(ArrayCollection $collection, $property, $value)
    {
        foreach ($collection->getIterator() as $entity) {
            $method = 'get' . ucfirst($property);

            if (!$entity && !method_exists($entity, $method)) {
                continue;
            }
            if ($entity->$method() == $value) {
                return $entity;
            }
        }
        return null;
    }

    /**
     * @param ArrayCollection $collection
     * @param array $conditions
     * @return null
     */
    protected function getCollectionElementByProperties(ArrayCollection $collection, array $conditions)
    {
        foreach ($conditions as $property => $value) {
            $entity = $this->getCollectionElementByProperty(
                $collection,
                $property,
                $value
            );
            if ($entity) {
                return $entity;
            }
        }

        return null;
    }

    /**
     * Helper function to resolve one to many associations for an entity.
     * The function do the following thinks:
     * It iterates all conditions which passed. The conditions contains the property names
     * which can be used as identifier like array("id", "name", "number", ...).
     * If the property isn't set in the passed data array the function continue with the next condition.
     * If the property is set, the function looks into the passed collection element if
     * the item is already exist in the entity collection.
     * In case that the collection don't contains the entity, the function throws an exception.
     * If no property is set, the function creates a new entity and adds the instance into the
     * passed collection and persist the entity.
     *
     * @param ArrayCollection $collection
     * @param $data
     * @param $entityType
     * @param array $conditions
     * @return null|object
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     */
    protected function getOneToManySubElement(ArrayCollection $collection, $data, $entityType, $conditions = array('id'))
    {
        foreach ($conditions as $property) {
            if (!isset($data[$property])) {
                continue;
            }
            $item = $this->getCollectionElementByProperty($collection, $property, $data[$property]);

            if (!$item) {
                throw new ApiException\CustomValidationException(
                    sprintf("%s by %s %s not found", $entityType, $property, $data[$property])
                );
            }
            return $item;
        }

        $item = new $entityType();
        $this->getManager()->persist($item);
        $collection->add($item);

        return $item;
    }

    /**
     * Helper function to resolve many to many associations for an entity.
     * The function do the following thinks:
     * It iterates all conditions which passed. The conditions contains the property names
     * which can be used as identifier like array("id", "name", "number", ...).
     * If the property isn't set in the passed data array the function continue with the next condition.
     * If the property is set, the function looks into the passed collection element if
     * the item is already exist in the entity collection.
     * In case that the collection don't contains the entity, the function creates a findOneBy
     * statement for the passed entity type.
     * In case that the findOneBy statement finds no entity, the function throws an exception.
     * Otherwise the item will be
     *
     * @param ArrayCollection $collection
     * @param $data
     * @param $entityType
     * @param array $conditions
     * @return null|object
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     */
    protected function getManyToManySubElement(ArrayCollection $collection, $data, $entityType, $conditions = array('id'))
    {
        $repo = $this->getManager()->getRepository($entityType);
        foreach ($conditions as $property) {
            if (!isset($data[$property])) {
                continue;
            }

            $item = $this->getCollectionElementByProperty($collection, $property, $data[$property]);
            if ($item) {
                return $item;
            }

            $item = $repo->findOneBy(array($property => $data[$property]));

            if (!$item) {
                throw new ApiException\CustomValidationException(
                    sprintf("%s by %s %s not found", $entityType, $property, $data[$property])
                );
            }

            $collection->add($item);
            return $item;
        }

        return null;
    }

    public function batchDelete($data)
    {
        if (!$this instanceof BatchInterface) {
            throw new \RuntimeException('BatchInterface is not implemented by this resource');
        }

        $results = array();
        foreach ($data as $key => $datum) {
            $id = $this->getIdByData($datum);

            try {
                $results[$key] = array(
                    'success' => true,
                    'operation' => 'delete',
                    'data' => $this->delete($id)
                );
                if ($this->getResultMode() == self::HYDRATE_ARRAY) {
                    $results[$key]['data'] = Shopware()->Models()->toArray(
                        $results[$key]['data']
                    );
                }
            } catch (\Exception $e) {
                if (!$this->getManager()->isOpen()) {
                    $this->resetEntityManager();
                }
                $message = $e->getMessage();
                if ($e instanceof ApiException\ValidationException) {
                    $message = implode("\n", $e->getViolations()->getIterator()->getArrayCopy());
                }

                $results[$key] = array(
                    'success' => false,
                    'message' => $message,
                    'trace' => $e->getTraceAsString()
                );
            }
        }

        return $results;

    }

    /**
     * This method will update/create a whole list of entities.
     * The resource needs to implement BatchInterface for that.
     *
     * @param $data
     * @return array
     * @throws \RuntimeException
     */
    public function batch($data)
    {
        if (!$this instanceof BatchInterface) {
            throw new \RuntimeException('BatchInterface is not implemented by this resource');
        }

        $results = array();
        foreach ($data as $key => $datum) {
            $id = $this->getIdByData($datum);

            try {
                if ($id) {
                    $results[$key] = array(
                        'success' => true,
                        'operation' => 'update',
                        'data' => $this->update($id, $datum)
                    );
                } else {
                    $results[$key] = array(
                        'success' => true,
                        'operation' => 'create',
                        'data' => $this->create($datum)
                    );
                }
                if ($this->getResultMode() == self::HYDRATE_ARRAY) {
                    $results[$key]['data'] = Shopware()->Models()->toArray(
                        $results[$key]['data']
                    );
                }
            } catch (\Exception $e) {
                if (!$this->getManager()->isOpen()) {
                    $this->resetEntityManager();
                }
                $message = $e->getMessage();
                if ($e instanceof ApiException\ValidationException) {
                    $message = implode("\n", $e->getViolations()->getIterator()->getArrayCopy());
                }

                $results[$key] = array(
                    'success' => false,
                    'message' => $message,
                    'trace' => $e->getTraceAsString()
                );
            }
        }

        return $results;
    }

    /**
     * This helper method will reload the EntityManager.
     * This is useful if the EntityManager was closed due to an error on the
     * PDO connection.
     */
    protected function resetEntityManager()
    {
        $this->getResourceLoader()->reset('models')
                                  ->reset('db_connection')
                                  ->load('models');

        $this->getResourceLoader()->load('db_connection');

        $this->setManager($this->resourceLoader->get('models'));
    }
}
