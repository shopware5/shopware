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

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL;

use Doctrine\DBAL\Query\QueryBuilder;
use PDO;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\ProductHydrator;
use Shopware\Bundle\StoreFrontBundle\Gateway\ListProductGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\ListProductQueryHelperInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ListProductGateway implements ListProductGatewayInterface
{
    /**
     * @var ProductHydrator
     */
    protected $hydrator;

    private ListProductQueryHelperInterface $queryHelper;

    public function __construct(
        ProductHydrator $hydrator,
        ListProductQueryHelperInterface $queryHelper
    ) {
        $this->hydrator = $hydrator;
        $this->queryHelper = $queryHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function get($number, ShopContextInterface $context)
    {
        $products = $this->getList([$number], $context);

        return array_shift($products);
    }

    /**
     * {@inheritdoc}
     */
    public function getList(array $numbers, ShopContextInterface $context)
    {
        $data = $this->getQuery($numbers, $context)->execute()->fetchAll(PDO::FETCH_ASSOC);
        $products = [];
        foreach ($data as $product) {
            $key = $product['__variant_ordernumber'];
            $products[$key] = $this->hydrator->hydrateListProduct($product);
        }

        return $products;
    }

    /**
     * @param array<string> $numbers
     *
     * @return QueryBuilder
     */
    protected function getQuery(array $numbers, ShopContextInterface $context)
    {
        return $this->queryHelper->getQuery($numbers, $context);
    }
}
