<?php

namespace Shopware\Components\Plugin;

use Enlight_Exception;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Model\ModelRepository;
use Shopware\Models\Plugin\Plugin;
use Shopware\Models\User\Privilege;
use Shopware_Components_Acl;

/**
 * Class PermissionsSynchronizer
 */
class PermissionsSynchronizer
{
    /**
     * @var ModelManager
     */
    private $em;

    /**
     * @var ModelRepository
     */
    private $repository;

    /**
     * @var Shopware_Components_Acl
     */
    private $acl;

    /**
     * CronjobSyncronizer constructor.
     *
     * @param ModelManager $em
     * @param Shopware_Components_Acl $acl
     */
    public function __construct(ModelManager $em, Shopware_Components_Acl $acl)
    {
        $this->em = $em;
        $this->repository = $this->em->getRepository(\Shopware\Models\User\Resource::class);
        $this->acl = $acl;
    }

    /**
     * @param Plugin $plugin
     * @param array $permissions
     */
    public function synchronize(Plugin $plugin, array $permissions)
    {
        $resource = $this->getResource($plugin->getName());

        if (null === $resource) {
            $this->createResource($plugin, $permissions);

            return;
        }

        $this->synchronizePrivileges($resource, $permissions);
        $this->removeNotExistingPrivileges($resource, $permissions);
    }

    /**
     * @param $resourceName
     *
     * @return \Shopware\Models\User\Resource
     */
    private function getResource($resourceName)
    {
        return $this->repository->findOneBy(['name' => $resourceName]);
    }

    /**
     * @param Plugin $plugin
     * @param array $permissions
     *
     * @throws Enlight_Exception
     */
    private function createResource(Plugin $plugin, array $permissions)
    {
        $this->acl->createResource(
            $plugin->getName(),
            $permissions,
            $plugin->getLabel(),
            $plugin->getId()
        );
    }

    /**
     * @param \Shopware\Models\User\Resource $resource
     * @param array $permissions
     */
    private function synchronizePrivileges(\Shopware\Models\User\Resource $resource, array $permissions)
    {
        $existingPrivileges = array_filter($resource->getPrivileges()->toArray(), function (Privilege $privilege) use ($permissions) {
            return in_array($privilege->getName(), $permissions, true);
        });

        $existingPrivileges = array_map(function (Privilege $privilege) {
            return $privilege->getName();
        }, $existingPrivileges);

        $newPrivileges = array_diff($permissions, $existingPrivileges);

        array_walk($newPrivileges, function ($name) use ($resource) {
            $this->acl->createPrivilege($resource->getId(), $name);
        });
    }

    /**
     * @param \Shopware\Models\User\Resource $resource
     * @param array $permissions
     */
    protected function removeNotExistingPrivileges(\Shopware\Models\User\Resource $resource, array $permissions)
    {
        $existingPrivileges = $resource->getPrivileges()->toArray();

        $orphanedPrivileges = array_filter($existingPrivileges, function (Privilege $privilege) use ($permissions) {
            return !in_array($privilege->getName(), $permissions, true);
        });

        if (empty($orphanedPrivileges)) {
            return;
        }

        array_walk($orphanedPrivileges, function (Privilege $privilege) {
            $this->em->remove($privilege);
        });

        $this->em->flush();
    }
}
