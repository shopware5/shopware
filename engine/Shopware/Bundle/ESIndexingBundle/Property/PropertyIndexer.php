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

namespace Shopware\Bundle\ESIndexingBundle\Property;

use Elasticsearch\Client;
use Shopware\Bundle\ESIndexingBundle\Console\ProgressHelperInterface;
use Shopware\Bundle\ESIndexingBundle\DataIndexerInterface;
use Shopware\Bundle\ESIndexingBundle\ProviderInterface;
use Shopware\Bundle\ESIndexingBundle\Struct\ShopIndex;
use Shopware\Bundle\StoreFrontBundle\Struct\Property\Group;

class PropertyIndexer implements DataIndexerInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var ProviderInterface
     */
    private $provider;

    /**
     * @var PropertyQueryFactory
     */
    private $queryFactory;

    public function __construct(
        Client $client,
        PropertyQueryFactory $queryFactory,
        ProviderInterface $provider
    ) {
        $this->client = $client;
        $this->provider = $provider;
        $this->queryFactory = $queryFactory;
    }

    public function populate(ShopIndex $index, ProgressHelperInterface $progress)
    {
        $query = $this->queryFactory->createQuery(100);
        $progress->start($query->fetchCount(), 'Indexing properties');

        while ($ids = $query->fetch()) {
            $this->indexProperties($index, $ids);
            $progress->advance(count($ids));
        }
        $progress->finish();
    }

    /**
     * @param int[] $groupIds
     */
    public function indexProperties(ShopIndex $index, $groupIds)
    {
        if (empty($groupIds)) {
            return;
        }

        /** @var Group[] $properties */
        $properties = $this->provider->get($index->getShop(), $groupIds);
        $remove = array_diff($groupIds, array_keys($properties));

        $documents = [];
        foreach ($properties as $property) {
            $documents[] = ['index' => ['_id' => $property->getId()]];
            $documents[] = $property;
        }

        foreach ($remove as $id) {
            $documents[] = ['delete' => ['_id' => $id]];
        }

        $this->client->bulk([
            'index' => $index->getName(),
            'type' => PropertyMapping::TYPE,
            'body' => $documents,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function supports()
    {
        return PropertyMapping::TYPE;
    }
}
