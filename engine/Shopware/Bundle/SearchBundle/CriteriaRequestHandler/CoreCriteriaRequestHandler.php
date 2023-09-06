<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Bundle\SearchBundle\CriteriaRequestHandler;

use Enlight_Controller_Request_RequestHttp as Request;
use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\Condition\CustomerGroupCondition;
use Shopware\Bundle\SearchBundle\Condition\HeightCondition;
use Shopware\Bundle\SearchBundle\Condition\ImmediateDeliveryCondition;
use Shopware\Bundle\SearchBundle\Condition\IsAvailableCondition;
use Shopware\Bundle\SearchBundle\Condition\LengthCondition;
use Shopware\Bundle\SearchBundle\Condition\ManufacturerCondition;
use Shopware\Bundle\SearchBundle\Condition\PriceCondition;
use Shopware\Bundle\SearchBundle\Condition\SearchTermCondition;
use Shopware\Bundle\SearchBundle\Condition\ShippingFreeCondition;
use Shopware\Bundle\SearchBundle\Condition\VoteAverageCondition;
use Shopware\Bundle\SearchBundle\Condition\WeightCondition;
use Shopware\Bundle\SearchBundle\Condition\WidthCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\CriteriaRequestHandlerInterface;
use Shopware\Bundle\SearchBundle\SearchTermPreProcessorInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware_Components_Config;

class CoreCriteriaRequestHandler implements CriteriaRequestHandlerInterface
{
    private Shopware_Components_Config $config;

    private SearchTermPreProcessorInterface $searchTermPreProcessor;

    public function __construct(
        Shopware_Components_Config $config,
        SearchTermPreProcessorInterface $searchTermPreProcessor
    ) {
        $this->config = $config;
        $this->searchTermPreProcessor = $searchTermPreProcessor;
    }

    public function handleRequest(Request $request, Criteria $criteria, ShopContextInterface $context)
    {
        $this->addLimit($request, $criteria);
        $this->addOffset($request, $criteria);

        $this->addCategoryCondition($request, $criteria);
        $this->addIsAvailableCondition($criteria);
        $this->addCustomerGroupCondition($criteria, $context);
        $this->addSearchCondition($request, $criteria);

        $this->addManufacturerCondition($request, $criteria);
        $this->addShippingFreeCondition($request, $criteria);
        $this->addImmediateDeliveryCondition($request, $criteria);
        $this->addRatingCondition($request, $criteria);
        $this->addPriceCondition($request, $criteria);

        $this->addWeightCondition($request, $criteria);
        $this->addHeightCondition($request, $criteria);
        $this->addWidthCondition($request, $criteria);
        $this->addLengthCondition($request, $criteria);
    }

    private function addCategoryCondition(Request $request, Criteria $criteria): void
    {
        if ($request->has('sCategory')) {
            /** @var array<numeric-string> $ids */
            $ids = explode('|', $request->getParam('sCategory')) ?: [];

            $criteria->addBaseCondition(new CategoryCondition($ids));
        } elseif ($request->has('categoryFilter')) {
            /** @var array<numeric-string> $ids */
            $ids = explode('|', $request->getParam('categoryFilter')) ?: [];

            $criteria->addCondition(new CategoryCondition($ids));
        }
    }

    private function addManufacturerCondition(Request $request, Criteria $criteria): void
    {
        if (!$request->has('sSupplier')) {
            return;
        }

        /** @var array<numeric-string> $manufacturerIds */
        $manufacturerIds = explode('|', $request->getParam('sSupplier')) ?: [];

        if (!empty($manufacturerIds)) {
            $criteria->addCondition(new ManufacturerCondition($manufacturerIds));
        }
    }

    private function addShippingFreeCondition(Request $request, Criteria $criteria): void
    {
        $shippingFree = $request->getParam('shippingFree');
        if (!$shippingFree) {
            return;
        }

        $criteria->addCondition(new ShippingFreeCondition());
    }

    private function addImmediateDeliveryCondition(Request $request, Criteria $criteria): void
    {
        $immediateDelivery = $request->getParam('immediateDelivery');
        if (!$immediateDelivery) {
            return;
        }

        $criteria->addCondition(new ImmediateDeliveryCondition());
    }

    private function addRatingCondition(Request $request, Criteria $criteria): void
    {
        $average = $request->getParam('rating');
        if (!$average) {
            return;
        }

        $criteria->addCondition(new VoteAverageCondition($average));
    }

    private function addPriceCondition(Request $request, Criteria $criteria): void
    {
        $min = $request->getParam('priceMin');
        $max = $request->getParam('priceMax');

        if (!$min && !$max) {
            return;
        }

        $condition = new PriceCondition((float) $min, (float) $max);
        $criteria->addCondition($condition);
    }

    private function addSearchCondition(Request $request, Criteria $criteria): void
    {
        $term = $request->getParam('sSearch');
        if (empty($term)) {
            return;
        }
        $term = $this->searchTermPreProcessor->process($term);
        $criteria->addBaseCondition(new SearchTermCondition($term));
    }

    private function addCustomerGroupCondition(Criteria $criteria, ShopContextInterface $context): void
    {
        $condition = new CustomerGroupCondition(
            [$context->getCurrentCustomerGroup()->getId()]
        );
        $criteria->addBaseCondition($condition);
    }

    private function addOffset(Request $request, Criteria $criteria): void
    {
        $page = (int) $request->getParam('sPage', 1);
        $page = ($page > 0) ? $page : 1;
        $request->setParam('sPage', $page);

        $criteria->offset(
            ($page - 1) * $criteria->getLimit()
        );
    }

    private function addLimit(Request $request, Criteria $criteria): void
    {
        $limit = (int) $request->getParam('sPerPage', $this->config->get('articlesPerPage'));
        $max = $this->config->get('maxStoreFrontLimit');
        if ($max) {
            $limit = min($limit, $max);
        }
        $limit = max($limit, 1);
        $criteria->limit($limit);
    }

    private function addIsAvailableCondition(Criteria $criteria): void
    {
        if (!$this->config->get('hideNoInStock')) {
            return;
        }
        $criteria->addBaseCondition(new IsAvailableCondition());
    }

    private function addWeightCondition(Request $request, Criteria $criteria): void
    {
        $min = $request->getParam('minWeight');
        $max = $request->getParam('maxWeight');

        if (!$min && !$max) {
            return;
        }

        $condition = new WeightCondition((float) $min, (float) $max);
        $criteria->addCondition($condition);
    }

    private function addWidthCondition(Request $request, Criteria $criteria): void
    {
        $min = $request->getParam('minWidth');
        $max = $request->getParam('maxWidth');

        if (!$min && !$max) {
            return;
        }

        $condition = new WidthCondition((float) $min, (float) $max);
        $criteria->addCondition($condition);
    }

    private function addLengthCondition(Request $request, Criteria $criteria): void
    {
        $min = $request->getParam('minLength');
        $max = $request->getParam('maxLength');

        if (!$min && !$max) {
            return;
        }

        $condition = new LengthCondition((float) $min, (float) $max);
        $criteria->addCondition($condition);
    }

    private function addHeightCondition(Request $request, Criteria $criteria): void
    {
        $min = $request->getParam('minHeight');
        $max = $request->getParam('maxHeight');

        if (!$min && !$max) {
            return;
        }

        $condition = new HeightCondition((float) $min, (float) $max);
        $criteria->addCondition($condition);
    }
}
