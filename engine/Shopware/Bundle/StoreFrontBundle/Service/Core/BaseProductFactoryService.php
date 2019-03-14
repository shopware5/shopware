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

namespace Shopware\Bundle\StoreFrontBundle\Service\Core;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Service\BaseProductFactoryServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;

class BaseProductFactoryService implements BaseProductFactoryServiceInterface
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function createBaseProduct($number)
    {
        $products = $this->createBaseProducts([$number]);

        return array_shift($products);
    }

    /**
     * {@inheritdoc}
     */
    public function createBaseProducts($numbers)
    {
        if (!count($numbers)) {
            return [];
        }

        $query = $this->connection->createQueryBuilder();
        $query->select([
            'variant.id as variantId',
            'variant.ordernumber as number',
            'variant.articleID as productId',
        ]);
        $query->from('s_articles_details', 'variant')
            ->where('variant.ordernumber IN(:numbers)')
            ->setParameter(':numbers', $numbers, Connection::PARAM_STR_ARRAY);

        /** @var \PDOStatement $statement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $products = [];
        foreach ($data as $row) {
            $product = new BaseProduct(
                (int) $row['productId'],
                (int) $row['variantId'],
                $row['number']
            );
            $products[$product->getNumber()] = $product;
        }

        return $products;
    }
}
