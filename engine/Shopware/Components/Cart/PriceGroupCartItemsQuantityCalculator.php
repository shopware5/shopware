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

use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Query\QueryBuilder;
use Enlight_Components_Session_Namespace;

class PriceGroupCartItemsQuantityCalculator implements PriceGroupCartItemsQuantityCalculatorInterface
{
    /**
     * @var DbalConnection
     */
    private $dbalConnection;

    /**
     * @var Enlight_Components_Session_Namespace
     */
    private $session;

    public function __construct(DbalConnection $dbalConnection, Enlight_Components_Session_Namespace $session)
    {
        $this->dbalConnection = $dbalConnection;
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function calculateCartItemsQuantityForPriceGroup(int $priceGroupId): int
    {
        $queryBuilder = $this->getCalculateCartItemsQuantityForPriceGroupQueryBuilder($priceGroupId);
        $queryResult = $queryBuilder->execute()->fetchColumn();

        return (int) $queryResult;
    }

    protected function getCalculateCartItemsQuantityForPriceGroupQueryBuilder(int $priceGroupId): QueryBuilder
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();
        $queryBuilder
            ->select('SUM(basket.quantity)')
            ->from('s_order_basket', 'basket')
            ->join('basket', 's_articles', 'articles', 'articles.id = basket.articleID')
            ->where('articles.pricegroupID = :priceGroupId')
            ->andWhere('sessionID = :sessionId')
            ->setParameters([
                'priceGroupId' => $priceGroupId,
                'sessionId' => $this->session->get('sessionId'),
            ]);

        return $queryBuilder;
    }
}
