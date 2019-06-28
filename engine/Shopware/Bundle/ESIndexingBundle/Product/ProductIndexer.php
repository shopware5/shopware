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

namespace Shopware\Bundle\ESIndexingBundle\Product;

use Elasticsearch\Client;
use Shopware\Bundle\ESIndexingBundle\Console\ProgressHelperInterface;
use Shopware\Bundle\ESIndexingBundle\DataIndexerInterface;
use Shopware\Bundle\ESIndexingBundle\ProviderInterface;
use Shopware\Bundle\ESIndexingBundle\Struct\ShopIndex;
use Shopware\Bundle\SearchBundleDBAL\VariantHelperInterface;

class ProductIndexer implements DataIndexerInterface
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
     * @var ProductQueryFactoryInterface
     */
    private $queryFactory;

    /**
     * @var VariantHelperInterface
     */
    private $variantHelper;

    public function __construct(
        Client $client,
        ProviderInterface $provider,
        ProductQueryFactoryInterface $queryFactory,
        VariantHelperInterface $variantHelper
    ) {
        $this->client = $client;
        $this->provider = $provider;
        $this->queryFactory = $queryFactory;
        $this->variantHelper = $variantHelper;
    }

    public function populate(ShopIndex $index, ProgressHelperInterface $progress)
    {
        $categoryId = $index->getShop()->getCategory()->getId();
        $idQuery = $this->queryFactory->createCategoryQuery($categoryId, 100);
        $progress->start($idQuery->fetchCount(), 'Indexing products');

        while ($ids = $idQuery->fetch()) {
            if (!$this->variantHelper->getVariantFacet()) {
                $query = $this->queryFactory->createProductIdQuery($ids);
                $numbers = $query->fetch();
            } else {
                $numbers = $ids;
            }

            $this->indexProducts($index, $numbers);
            $progress->advance(count(array_unique($ids)));
        }
        $progress->finish();
    }

    /**
     * {@inheritdoc}
     */
    public function supports()
    {
        return ProductMapping::TYPE;
    }

    /**
     * @param string[] $numbers
     */
    public function indexProducts(ShopIndex $index, $numbers)
    {
        if (empty($numbers)) {
            return;
        }

        $products = $this->provider->get($index->getShop(), $numbers);
        $remove = array_diff($numbers, array_keys($products));

        $documents = [];
        foreach ($products as $product) {
            $documents[] = ['index' => ['_id' => $product->getNumber()]];
            $documents[] = $product;
        }

        foreach ($remove as $number) {
            $documents[] = ['delete' => ['_id' => $number]];
        }

        $this->client->bulk([
            'index' => $index->getName(),
            'type' => ProductMapping::TYPE,
            'body' => $documents,
        ]);
    }
}
