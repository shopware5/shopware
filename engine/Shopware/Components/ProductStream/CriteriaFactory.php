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

namespace Shopware\Components\ProductStream;

use Enlight_Controller_Request_Request as Request;
use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\StoreFrontCriteriaFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class CriteriaFactory implements CriteriaFactoryInterface
{
    /**
     * @var StoreFrontCriteriaFactoryInterface
     */
    private $criteriaFactory;

    public function __construct(StoreFrontCriteriaFactoryInterface $criteriaFactory)
    {
        $this->criteriaFactory = $criteriaFactory;
    }

    /**
     * @return Criteria
     */
    public function createCriteria(Request $request, ShopContextInterface $context)
    {
        $criteria = $this->criteriaFactory->createListingCriteria($request, $context);

        $criteria->removeBaseCondition('category');
        $criteria->resetFacets();

        $category = $context->getShop()->getCategory()->getId();
        $criteria->addBaseCondition(new CategoryCondition([$category]));

        return $criteria;
    }
}
