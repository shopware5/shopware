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

namespace Shopware\Bundle\ContentTypeBundle\Services;

use Doctrine\ORM\EntityRepository;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\User\Privilege;
use Shopware\Models\User\Resource as AclResource;

class AclSynchronizer implements AclSynchronizerInterface
{
    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var ModelManager
     */
    private $manager;

    public function __construct(ModelManager $manager)
    {
        $this->manager = $manager;
        $this->repository = $manager->getRepository(AclResource::class);
    }

    /**
     * {@inheritdoc}
     */
    public function update(array $typeNames): void
    {
        foreach ($typeNames as $name) {
            /** @var AclResource|null $item */
            $item = $this->repository->findOneBy(['name' => $name]);

            if (!$item) {
                $item = new AclResource();
                $this->manager->persist($item);
            }

            $item->setName($name);

            $this->addPrivilegeIfMissing($item, 'read');
            $this->addPrivilegeIfMissing($item, 'create');
            $this->addPrivilegeIfMissing($item, 'edit');
            $this->addPrivilegeIfMissing($item, 'delete');
        }

        $this->manager->flush();
    }

    public function remove(string $name): void
    {
        $item = $this->repository->findOneBy(['name' => $name]);

        if (!$item) {
            return;
        }

        $this->manager->remove($item);
        $this->manager->flush($item);
    }

    private function addPrivilegeIfMissing(AclResource $resource, string $name): void
    {
        foreach ($resource->getPrivileges() as $privilege) {
            if ($privilege->getName() === $name) {
                return;
            }
        }

        $newPriv = new Privilege();
        $newPriv->setName($name);
        $newPriv->setResource($resource);
        $this->manager->persist($newPriv);

        $resource->getPrivileges()->add($newPriv);
    }
}
