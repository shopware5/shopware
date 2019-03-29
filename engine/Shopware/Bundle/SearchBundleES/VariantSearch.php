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

namespace Shopware\Bundle\SearchBundleES;

use Shopware\Bundle\SearchBundle\Condition\MainVariantCondition;
use Shopware\Bundle\SearchBundle\Condition\VariantCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\ProductNumberSearchInterface;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\StoreFrontBundle\Struct;

class VariantSearch implements ProductNumberSearchInterface
{
    /**
     * @var ProductNumberSearchInterface
     */
    private $productNumberSearch;

    public function __construct(ProductNumberSearchInterface $service)
    {
        $this->productNumberSearch = $service;
    }

    /**
     * Creates a product search result for the passed criteria object.
     * The criteria object contains different core conditions and plugin conditions.
     * This conditions has to be handled over the different condition handlers.
     *
     * The search gateway has to implement an event which plugin can be listened to,
     * to add their own handler classes.
     *
     * @return ProductNumberSearchResult
     */
    public function search(Criteria $criteria, Struct\ShopContextInterface $context)
    {
        if (!$criteria->hasConditionOfClass(VariantCondition::class)) {
            $criteria->addCondition(new MainVariantCondition());
        }

        return $this->productNumberSearch->search($criteria, $context);
    }
}
