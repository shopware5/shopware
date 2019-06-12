<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
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

use Doctrine\Common\Collections\Collection;
use Exception;
use RuntimeException;
use Shopware\Components\Api\BatchInterface;
use Shopware\Components\Api\Exception as ApiException;
use Shopware\Components\Api\Exception\BatchInterfaceNotImplementedException;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\DependencyInjection\ContainerAwareInterface;
use Shopware\Components\Model\ModelEntity;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Model\ModelRepository;
use Shopware_Components_Acl as AclComponent;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Abstract API Resource Class
 */
abstract class Resource implements ContainerAwareInterface
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
     * Contains the Shopware model manager
     *
     * @var ModelManager
     */
    protected $manager;

    /**
     * @var bool
     */
    protected $autoFlush = true;

    /**
     * @var int
     */
    protected $resultMode = self::HYDRATE_ARRAY;

    /**
     * @var AclComponent
     */
    protected $acl;

    /**
     * Contains the current role
     *
     * @var string|\Zend_Acl_Role_Interface
     */
    protected $role;

    /**
     * @var Container|null
     */
    protected $container;

    /**
     * @return Container
     */
    public function getContainer()
    {
        if (!$this->container) {
            $this->container = Shopware()->Container();
        }

        return $this->container;
    }

    public function setContainer(Container $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param string $privilege
     *
     * @throws ApiException\PrivilegeException
     */
    public function checkPrivilege($privilege)
    {
        if (!$this->getRole() || !$this->getAcl()) {
            return;
        }

        $calledClass = static::class;
        $calledClass = explode('\\', $calledClass);
        /** @var \Zend_Acl_Resource_Interface|string $resource */
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

    public function setManager(ModelManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return ModelManager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @return \Shopware\Components\Api\Resource\Resource
     */
    public function setAcl(AclComponent $acl)
    {
        $this->acl = $acl;

        return $this;
    }

    /**
     * @return AclComponent|null
     */
    public function getAcl()
    {
        return $this->acl;
    }

    /**
     * @param string|\Zend_Acl_Role_Interface $role
     *
     * @return \Shopware\Components\Api\Resource\Resource
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return string|\Zend_Acl_Role_Interface|null
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param bool $autoFlush
     */
    public function setAutoFlush($autoFlush)
    {
        $this->autoFlush = (bool) $autoFlush;
    }

    /**
     * @return bool
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
        return (int) $this->resultMode;
    }

    /**
     * @param object $entity
     *
     * @throws ApiException\OrmException
     */
    public function flush($entity = null)
    {
        if ($this->getAutoFlush()) {
            $this->getManager()->getConnection()->beginTransaction();
            try {
                $this->getManager()->flush($entity);
                $this->getManager()->getConnection()->commit();
                $this->getManager()->clear();
            } catch (Exception $e) {
                $this->getManager()->getConnection()->rollBack();
                throw new ApiException\OrmException($e->getMessage(), 0, $e);
            }
        }
    }

    /**
     * @param array $data
     *
     * @throws BatchInterfaceNotImplementedException
     *
     * @return array
     */
    public function batchDelete($data)
    {
        if (!$this instanceof BatchInterface) {
            throw new BatchInterfaceNotImplementedException('BatchInterface is not implemented by this resource');
        }

        $results = [];
        foreach ($data as $key => $datum) {
            /** @var BatchInterface $this */
            $id = $this->getIdByData($datum);

            try {
                $results[$key] = [
                    'success' => true,
                    'operation' => 'delete',
                    'data' => $this->delete($id),
                ];
                if ($this->getResultMode() === self::HYDRATE_ARRAY) {
                    $results[$key]['data'] = Shopware()->Models()->toArray(
                        $results[$key]['data']
                    );
                }
            } catch (Exception $e) {
                if (!$this->getManager()->isOpen()) {
                    $this->resetEntityManager();
                }
                $message = $e->getMessage();
                if ($e instanceof ApiException\ValidationException) {
                    $message = implode("\n", $e->getViolations()->getIterator()->getArrayCopy());
                }

                $results[$key] = [
                    'success' => false,
                    'message' => $message,
                    'trace' => $e->getTraceAsString(),
                ];
            }
        }

        return $results;
    }

    /**
     * This method will update/create a whole list of entities.
     * The resource needs to implement BatchInterface for that.
     *
     * @param array $data
     *
     * @throws BatchInterfaceNotImplementedException
     *
     * @return array
     */
    public function batch($data)
    {
        if (!$this instanceof BatchInterface) {
            throw new BatchInterfaceNotImplementedException('BatchInterface is not implemented by this resource');
        }

        $results = [];
        foreach ($data as $key => $datum) {
            /** @var BatchInterface|null $this */
            $id = $this->getIdByData($datum);

            try {
                if ($id) {
                    $results[$key] = [
                        'success' => true,
                        'operation' => 'update',
                        'data' => $this->update($id, $datum),
                    ];
                } else {
                    $results[$key] = [
                        'success' => true,
                        'operation' => 'create',
                        'data' => $this->create($datum),
                    ];
                }
                if ($this->getResultMode() === self::HYDRATE_ARRAY) {
                    $results[$key]['data'] = Shopware()->Models()->toArray(
                        $results[$key]['data']
                    );
                }
            } catch (Exception $e) {
                if (!$this->getManager()->isOpen()) {
                    $this->resetEntityManager();
                }
                $message = $e->getMessage();
                if ($e instanceof ApiException\ValidationException) {
                    $message = implode("\n", $e->getViolations()->getIterator()->getArrayCopy());
                }

                $results[$key] = [
                    'success' => false,
                    'message' => $message,
                    'trace' => $e->getTraceAsString(),
                ];
            }
        }

        return $results;
    }

    /**
     * Returns a new api resource which contains the same configuration for the model manager and ACL
     * as the current resource.
     *
     * @param string $name
     *
     * @return \Shopware\Components\Api\Resource\Resource
     *
     * @deprecated with 5.6, will be removed with 5.8. Inject the resource instead
     */
    protected function getResource($name)
    {
        trigger_error('Using Manager::getResource is deprecated since 5.6 and will be removed with 5.8. Inject the resource instead', E_USER_DEPRECATED);

        try {
            /** @var \Shopware\Components\Api\Resource\Resource $resource */
            $resource = $this->getContainer()->get('shopware.api.' . strtolower($name));
        } catch (ServiceNotFoundException $e) {
            $name = ucfirst($name);
            $class = __NAMESPACE__ . '\\Resource\\' . $name;

            /** @var \Shopware\Components\Api\Resource\Resource $resource */
            $resource = new $class();
        }

        $resource->setManager($this->getManager());

        if ($this->getAcl()) {
            $resource->setAcl($this->getAcl());
        }

        if ($this->getRole()) {
            $resource->setRole($this->getRole());
        }

        return $resource;
    }

    /**
     * Helper function which checks the option configuration for the passed collection.
     *
     * If the data property contains the "__options_$optionName" value and this value contains
     * the "replace" parameter, the collection will be cleared.
     *
     * @param array  $data
     * @param string $optionName
     * @param bool   $defaultReplace
     *
     * @return Collection<\Shopware\Models\Article\Image>
     */
    protected function checkDataReplacement(Collection $collection, $data, $optionName, $defaultReplace)
    {
        $key = '__options_' . $optionName;
        if (isset($data[$key])) {
            if ($data[$key]['replace']) {
                $collection->clear();
            }
        } elseif ($defaultReplace) {
            $collection->clear();
        }

        return $collection;
    }

    /**
     * @param Collection|array $collection
     * @param string           $property
     *
     * @throws Exception
     *
     * @return mixed|null
     */
    protected function getCollectionElementByProperty($collection, $property, $value)
    {
        foreach ($collection as $entity) {
            $method = 'get' . ucfirst($property);

            if (!method_exists($entity, $method)) {
                throw new RuntimeException(
                    sprintf('Method %s not found on entity %s', $method, get_class($entity))
                );
                continue;
            }
            if ($entity->$method() === $value) {
                return $entity;
            }
        }

        return null;
    }

    /**
     * @return mixed|null
     */
    protected function getCollectionElementByProperties(Collection $collection, array $conditions)
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
     * Helper function to execute different `findOneBy` statements with different conditions
     * until a passed entity instance found.
     *
     * @param string $entity
     *
     * @throws Exception
     *
     * @return ModelEntity|null
     */
    protected function findEntityByConditions($entity, array $conditions)
    {
        $repo = $this->getManager()->getRepository($entity);
        if (!$repo instanceof ModelRepository) {
            throw new RuntimeException(sprintf('Passed entity has no configured repository: %s', $entity));
        }

        foreach ($conditions as $condition) {
            /** @var ModelEntity|null $instance */
            $instance = $repo->findOneBy($condition);
            if ($instance) {
                return $instance;
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
     * @param array  $data
     * @param string $entityType
     * @param array  $conditions
     *
     * @throws ApiException\CustomValidationException
     *
     * @return ModelEntity
     */
    protected function getOneToManySubElement(Collection $collection, $data, $entityType, $conditions = ['id'])
    {
        foreach ($conditions as $property) {
            if (!isset($data[$property])) {
                continue;
            }
            $item = $this->getCollectionElementByProperty($collection, $property, $data[$property]);

            if (!$item) {
                throw new ApiException\CustomValidationException(
                    sprintf('%s by %s %s not found', $entityType, $property, $data[$property])
                );
            }

            return $item;
        }

        $item = new $entityType();
        $collection->add($item);

        return $item;
    }

    /**
     * Helper function to resolve many to many associations for an entity. The function does the following:
     *
     * It iterates over all conditions which are passed to it. The conditions contain the property names
     * which can be used as an identifier like array("id", "name", "number", ...).
     *
     * If the property isn't set in the passed data array, the function continues with the next condition.
     * If the property IS defined, the function looks into the passed collection element if an
     * item does already exist in the entity collection.
     *
     * In case the collection doesn't contain the entity, the function creates a `findOneBy`-statement
     * for the passed entity type.
     * In case that the `findOneBy`-statement finds no entity, the function throws an exception.
     * Otherwise the item will be added to the collection and returned.
     *
     * @param array  $data
     * @param string $entityType
     * @param array  $conditions
     *
     * @throws ApiException\CustomValidationException
     *
     * @return object|null
     */
    protected function getManyToManySubElement(Collection $collection, $data, $entityType, $conditions = ['id'])
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

            $item = $repo->findOneBy([$property => $data[$property]]);

            if (!$item) {
                throw new ApiException\CustomValidationException(
                    sprintf('%s by %s %s not found', $entityType, $property, $data[$property])
                );
            }

            $collection->add($item);

            return $item;
        }

        return null;
    }

    /**
     * This helper method will reload the EntityManager.
     *
     * This is useful if the EntityManager was closed due to an error on the PDO connection.
     */
    protected function resetEntityManager()
    {
        $this->getContainer()->reset('models')
            ->reset('dbal_connection')
            ->load('models');

        $this->getContainer()->load('dbal_connection');

        $this->setManager($this->container->get('models'));
    }
}
