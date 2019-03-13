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

namespace Shopware\Components\Cart;

use Doctrine\DBAL\Connection;
use Shopware\Components\Cart\Struct\DiscountContext;

class BasketQueryHelper implements BasketQueryHelperInterface
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
    public function getPositionPricesQuery(DiscountContext $discountContext)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select([
            self::BASKET_TABLE_ALIAS . '.price as end_price',
            self::BASKET_TABLE_ALIAS . '.netprice as net_price',
            self::BASKET_TABLE_ALIAS . '.tax_rate',
            self::BASKET_TABLE_ALIAS . '.quantity',
        ]);

        $query->from(self::BASKET_TABLE_NAME, self::BASKET_TABLE_ALIAS);
        $query->andWhere(self::BASKET_TABLE_ALIAS . '.modus = 0');
        $query->andWhere(self::BASKET_TABLE_ALIAS . '.sessionID = :session');
        $query->andWhere(self::BASKET_TABLE_ALIAS . '.tax_rate != 0');
        $query->setParameter(':session', $discountContext->getSessionId());

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function getInsertDiscountQuery(DiscountContext $discountContext)
    {
        $query = $this->connection->createQueryBuilder();
        $price = $discountContext->getPrice();

        $query->insert(self::BASKET_TABLE_NAME)
            ->setValue('sessionID', ':sessionId')
            ->setValue('articlename', ':articleName')
            ->setValue('articleID', ':articleName')
            ->setValue('ordernumber', ':ordernumber')
            ->setValue('quantity', ':quantity')
            ->setValue('price', ':price')
            ->setValue('netprice', ':netPrice')
            ->setValue('tax_rate', ':taxRate')
            ->setValue('datum', ':datum')
            ->setValue('modus', ':mode')
            ->setValue('currencyFactor', ':currencyFactor')
            ->setParameters([
                'sessionId' => $discountContext->getSessionId(),
                'articleName' => $discountContext->getDiscountName(),
                'ordernumber' => $discountContext->getOrderNumber(),
                'price' => $price->getPrice(),
                'netPrice' => $price->getNetPrice(),
                'taxRate' => $price->getTaxRate(),
                'mode' => $discountContext->getBasketMode(),
                'currencyFactor' => $discountContext->getCurrencyFactor(),
                'datum' => (new \DateTime())->format('Y-m-d H:i:s'),
                'articleID' => 0,
                'quantity' => 1,
            ]);

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function getInsertDiscountAttributeQuery(DiscountContext $discountContext)
    {
        $query = $this->connection->createQueryBuilder();

        $query->insert(self::BASKET_ATTRIBUTE_TABLE_NAME)
            ->setValue('basketID', ':basketId')
            ->setParameter('basketId', $discountContext->getBasketId());

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastInsertId()
    {
        return (int) $this->connection->lastInsertId(self::BASKET_TABLE_NAME);
    }
}
