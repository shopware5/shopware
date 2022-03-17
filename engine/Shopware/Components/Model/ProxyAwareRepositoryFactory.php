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

namespace Shopware\Components\Model;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Repository\RepositoryFactory;
use Doctrine\Persistence\ObjectRepository;
use Enlight_Hook_HookManager;

class ProxyAwareRepositoryFactory implements RepositoryFactory
{
    /**
     * The list of EntityRepository instances.
     *
     * @var ObjectRepository[]
     */
    private $repositoryList = [];

    /**
     * @var Enlight_Hook_HookManager
     */
    private $hookManager;

    public function __construct(Enlight_Hook_HookManager $hookManager)
    {
        $this->hookManager = $hookManager;
    }

    /**
     * @template TEntityClass of object
     *
     * @param class-string<TEntityClass> $entityName
     *
     * @return ObjectRepository<TEntityClass>
     */
    public function getRepository(EntityManagerInterface $entityManager, $entityName)
    {
        /** @var class-string<TEntityClass> $entityName */
        $entityName = ltrim($entityName, '\\');
        $repositoryHash = $entityManager->getClassMetadata($entityName)->getName() . spl_object_hash($entityManager);

        if (isset($this->repositoryList[$repositoryHash])) {
            return $this->repositoryList[$repositoryHash];
        }

        return $this->repositoryList[$repositoryHash] = $this->createRepository($entityManager, $entityName);
    }

    /**
     * @template TEntityClass of object
     *
     * @param class-string<TEntityClass> $entityName
     *
     * @return ObjectRepository<TEntityClass>
     */
    private function createRepository(EntityManagerInterface $entityManager, $entityName)
    {
        $metadata = $entityManager->getClassMetadata($entityName);

        /** @var class-string<ObjectRepository<TEntityClass>>|null $repositoryClassName */
        $repositoryClassName = $metadata->customRepositoryClassName;
        if ($repositoryClassName === null) {
            $repositoryClassName = $entityManager->getConfiguration()->getDefaultRepositoryClassName();
        }

        /** @var class-string<ObjectRepository<TEntityClass>> $repositoryClassName */
        $repositoryClassName = $this->hookManager->getProxy($repositoryClassName);

        return new $repositoryClassName($entityManager, $metadata);
    }
}
