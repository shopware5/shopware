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

namespace Shopware\Bundle\SearchBundleDBAL\ConditionHandler;

use Shopware\Bundle\SearchBundle\Condition\HasPriceCondition;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\PriceHelper;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundleDBAL\ConditionHandler
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class HasPriceConditionHandler implements ConditionHandlerInterface
{
    /**
     * @var PriceHelper
     */
    private $priceHelper;

    /**
     * @param PriceHelper $priceHelper
     */
    public function __construct(PriceHelper $priceHelper)
    {
        $this->priceHelper = $priceHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsCondition(ConditionInterface $condition)
    {
        return ($condition instanceof HasPriceCondition);
    }

    /**
     * Extends the query with two inner joins which validates
     * that the selected products has a defined price for the fallback customer group.
     *
     * @param ConditionInterface|HasPriceCondition $condition
     * @param QueryBuilder $query
     * @param ShopContextInterface $context
     * @return void
     */
    public function generateCondition(
        ConditionInterface $condition,
        QueryBuilder $query,
        ShopContextInterface $context
    ) {
        $query->innerJoin(
            'product',
            's_articles_prices',
            'hasPrice',
            'hasPrice.articledetailsID = product.main_detail_id
             AND hasPrice.pricegroup = :fallbackCustomerGroup
             AND hasPrice.from = 1'
        );

        $query->setParameter(
            ':fallbackCustomerGroup',
            $context->getFallbackCustomerGroup()->getKey()
        );
    }
}
