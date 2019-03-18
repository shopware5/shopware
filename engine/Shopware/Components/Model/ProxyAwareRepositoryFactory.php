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

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Repository\RepositoryFactory;

class ProxyAwareRepositoryFactory implements RepositoryFactory
{
    /**
     * The list of EntityRepository instances.
     *
     * @var ObjectRepository[]
     */
    private $repositoryList = [];

    /**
     * @var \Enlight_Hook_HookManager
     */
    private $hookManager;

    public function __construct(\Enlight_Hook_HookManager $hookManager)
    {
        $this->hookManager = $hookManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository(EntityManagerInterface $entityManager, $entityName)
    {
        $entityName = ltrim($entityName, '\\');
        $repositoryHash = $entityManager->getClassMetadata($entityName)->getName() . spl_object_hash($entityManager);

        if (isset($this->repositoryList[$repositoryHash])) {
            return $this->repositoryList[$repositoryHash];
        }

        return $this->repositoryList[$repositoryHash] = $this->createRepository($entityManager, $entityName);
    }

    /**
     * @param string $entityName
     *
     * @return ObjectRepository
     */
    private function createRepository(EntityManagerInterface $entityManager, $entityName)
    {
        /* @var \Doctrine\ORM\Mapping\ClassMetadata $metadata */
        $metadata = $entityManager->getClassMetadata($entityName);

        /** @var string|null $repositoryClassName */
        $repositoryClassName = $metadata->customRepositoryClassName;
        if ($repositoryClassName === null) {
            $repositoryClassName = $entityManager->getConfiguration()->getDefaultRepositoryClassName();
        }

        $repositoryClassName = $this->hookManager->getProxy($repositoryClassName);

        return new $repositoryClassName($entityManager, $metadata);
    }
}
