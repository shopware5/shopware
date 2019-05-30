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

namespace Shopware\Bundle\SearchBundleDBAL;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ListingPriceTable implements ListingPriceTableInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @var ListingPriceHelper
     */
    private $listingPriceHelper;

    public function __construct(
        Connection $connection,
        \Shopware_Components_Config $config,
        ListingPriceHelper $listingPriceHelper
    ) {
        $this->connection = $connection;
        $this->config = $config;
        $this->listingPriceHelper = $listingPriceHelper;
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function get(ShopContextInterface $context)
    {
        $priceTable = $this->listingPriceHelper->getPriceTable($context);

        $query = $this->connection->createQueryBuilder();

        $selection = 'MIN(' . $this->listingPriceHelper->getSelection($context) . ') as cheapest_price';

        $query->select(['prices.*', $selection]);
        $query->from('s_articles', 'product');
        $query->innerJoin('product', 's_core_tax', 'tax', 'tax.id = product.taxID');
        $query->innerJoin('product', '(' . $priceTable->getSQL() . ')', 'prices', 'product.id = prices.articleID');

        $this->joinAvailableVariant($query);
        $this->listingPriceHelper->joinPriceGroup($query);

        $query->andWhere('prices.articledetailsID = availableVariant.id');

        if ($this->config->get('useLastGraduationForCheapestPrice')) {
            $query->andWhere("IF(priceGroup.id IS NOT NULL, prices.from = 1, prices.to = 'beliebig')");
        } else {
            $query->andWhere('prices.from = 1');
        }

        $query->groupBy('product.id');

        $query->setParameter(':fallbackCustomerGroup', $context->getFallbackCustomerGroup()->getKey());
        $query->setParameter(':priceGroupCustomerGroup', $context->getCurrentCustomerGroup()->getId());

        if ($this->hasDifferentCustomerGroups($context)) {
            $query->setParameter(':currentCustomerGroup', $context->getCurrentCustomerGroup()->getKey());
        }

        return $query;
    }

    /**
     * @return bool
     */
    private function hasDifferentCustomerGroups(ShopContextInterface $context)
    {
        return $context->getCurrentCustomerGroup()->getId() !== $context->getFallbackCustomerGroup()->getId();
    }

    private function joinAvailableVariant(\Doctrine\DBAL\Query\QueryBuilder $query)
    {
        $stockCondition = '';
        if ($this->config->get('hideNoInstock')) {
            $stockCondition = 'AND (availableVariant.laststock * availableVariant.instock) >= (availableVariant.laststock * availableVariant.minpurchase)';
        }

        $query->innerJoin(
            'product',
            's_articles_details',
            'availableVariant',
            'availableVariant.articleID = product.id
             AND availableVariant.active = 1 ' . $stockCondition
        );
    }
}
