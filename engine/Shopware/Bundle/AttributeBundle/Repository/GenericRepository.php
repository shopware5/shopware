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

use Shopware\Bundle\AttributeBundle\Repository\Reader\ReaderInterface;
use Shopware\Bundle\AttributeBundle\Repository\Searcher\SearcherInterface;
use Shopware\Components\Model\ModelManager;

class GenericRepository implements RepositoryInterface
{
    /**
     * @var ReaderInterface
     */
    protected $reader;

    /**
     * @var SearcherInterface
     */
    protected $searcher;

    /**
     * @var ModelManager
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $entity;

    /**
     * @param string $entity
     */
    public function __construct(
        $entity,
        ModelManager $entityManager,
        ReaderInterface $reader,
        SearcherInterface $searcher
    ) {
        $this->entityManager = $entityManager;
        $this->reader = $reader;
        $this->searcher = $searcher;
        $this->entity = $entity;
    }

    /**
     * @param string $entity
     *
     * @return bool
     */
    public function supports($entity)
    {
        return $entity == $this->entity;
    }

    /**
     * @return SearchResult
     */
    public function search(SearchCriteria $criteria)
    {
        if ($criteria->ids) {
            $data = $this->getList($criteria->ids);

            return new SearchResult(count($data), $data);
        }

        $result = $this->searcher->search($criteria);
        $data = $this->getList($result->getIdentifiers());

        return new SearchResult($result->getCount(), $data);
    }

    /**
     * @param int[]|string[] $identifiers
     *
     * @return array[]
     */
    public function getList($identifiers)
    {
        return $this->reader->getList($identifiers);
    }

    /**
     * @param int|string $identifier
     *
     * @return array
     */
    public function get($identifier)
    {
        return $this->reader->get($identifier);
    }
}
