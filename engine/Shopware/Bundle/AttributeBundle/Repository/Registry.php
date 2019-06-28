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

namespace Shopware\Bundle\AttributeBundle\Repository;

use IteratorAggregate;
use Shopware\Bundle\AttributeBundle\Repository\Reader\GenericReader;
use Shopware\Bundle\AttributeBundle\Repository\Searcher\GenericSearcher;
use Shopware\Components\Model\ModelManager;

class Registry implements RegistryInterface
{
    /**
     * @var RepositoryInterface[]
     */
    private $repositories;

    /**
     * @var ModelManager
     */
    private $entityManager;

    public function __construct(IteratorAggregate $repositories, ModelManager $entityManager)
    {
        $this->repositories = iterator_to_array($repositories, false);
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository(SearchCriteria $criteria)
    {
        foreach ($this->repositories as $repository) {
            if ($repository->supports($criteria->entity)) {
                return $repository;
            }
        }

        return new GenericRepository(
            $criteria->entity,
            $this->entityManager,
            new GenericReader($criteria->entity, $this->entityManager),
            new GenericSearcher($criteria->entity, $this->entityManager)
        );
    }
}
