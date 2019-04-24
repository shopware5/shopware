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

namespace Shopware\Bundle\SearchBundle\CriteriaRequestHandler;

use Doctrine\DBAL\Connection;
use Enlight_Controller_Request_RequestHttp as Request;
use Shopware\Bundle\SearchBundle\Condition\CombinedCondition;
use Shopware\Bundle\SearchBundle\Condition\ProductAttributeCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\CriteriaRequestHandlerInterface;
use Shopware\Bundle\SearchBundle\Facet\CombinedConditionFacet;
use Shopware\Bundle\SearchBundle\Facet\ProductAttributeFacet;
use Shopware\Bundle\StoreFrontBundle\Service\CustomFacetServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Search\CustomFacet;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class FacetCriteriaRequestHandler implements CriteriaRequestHandlerInterface
{
    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @var CustomFacetServiceInterface
     */
    private $facetService;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(
        \Shopware_Components_Config $config,
        CustomFacetServiceInterface $facetService,
        Connection $connection
    ) {
        $this->config = $config;
        $this->facetService = $facetService;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(
        Request $request,
        Criteria $criteria,
        ShopContextInterface $context
    ) {
        if ($this->isSearchPage($request)) {
            $ids = $this->config->get('searchFacets', '');
            /** @var int[] $ids */
            $ids = array_filter(explode('|', $ids));
            $customFacets = $this->facetService->getList($ids, $context);
        } elseif ($this->isCategoryListing($request)) {
            $categoryId = (int) $request->getParam('sCategory');
            $customFacets = $this->facetService->getFacetsOfCategories([$categoryId], $context);
            $customFacets = array_shift($customFacets);
        } else {
            $customFacets = $this->facetService->getAllCategoryFacets($context);
        }

        /** @var CustomFacet[] $customFacets */
        foreach ($customFacets as $customFacet) {
            if (!$customFacet->getFacet()) {
                continue;
            }
            $facet = $customFacet->getFacet();
            $criteria->addFacet($facet);

            if ($facet instanceof ProductAttributeFacet) {
                $this->handleProductAttributeFacet($request, $criteria, $facet);
            } elseif ($facet instanceof CombinedConditionFacet) {
                $this->handleCombinedConditionFacet($request, $criteria, $facet);
            }
        }
    }

    /**
     * @return bool
     */
    private function isCategoryListing(Request $request)
    {
        return strtolower($request->getControllerName()) === 'listing';
    }

    /**
     * @return bool
     */
    private function isSearchPage(Request $request)
    {
        $params = $request->getParams();

        return array_key_exists('sSearch', $params);
    }

    private function handleProductAttributeFacet(
        Request $request,
        Criteria $criteria,
        ProductAttributeFacet $facet
    ) {
        if (!$this->isAttributeInRequest($facet, $request)) {
            return;
        }
        $data = $request->getParam($facet->getFormFieldName());

        switch ($facet->getMode()) {
            case ProductAttributeFacet::MODE_BOOLEAN_RESULT:

                $criteria->addCondition(
                    new ProductAttributeCondition(
                        $facet->getField(),
                        ProductAttributeCondition::OPERATOR_NOT_IN,
                        [false]
                    )
                );

                return;

            case ProductAttributeFacet::MODE_RADIO_LIST_RESULT:

                $criteria->addCondition(
                    new ProductAttributeCondition(
                        $facet->getField(),
                        ProductAttributeCondition::OPERATOR_EQ,
                        $data
                    )
                );

                return;

            case ProductAttributeFacet::MODE_RANGE_RESULT:

                $range = [];
                if ($request->has('min' . $facet->getFormFieldName())) {
                    $range['min'] = $request->getParam('min' . $facet->getFormFieldName());
                }
                if ($request->has('max' . $facet->getFormFieldName())) {
                    $range['max'] = $request->getParam('max' . $facet->getFormFieldName());
                }
                $condition = new ProductAttributeCondition(
                    $facet->getField(),
                    ProductAttributeCondition::OPERATOR_BETWEEN,
                    $range
                );
                $criteria->addCondition($condition);

                return;

            case ProductAttributeFacet::MODE_VALUE_LIST_RESULT:

                $criteria->addCondition(
                    new ProductAttributeCondition(
                        $facet->getField(),
                        ProductAttributeCondition::OPERATOR_IN,
                        explode('|', $data)
                    )
                );

                return;
            default:
                return;
        }
    }

    private function handleCombinedConditionFacet(
        Request $request,
        Criteria $criteria,
        CombinedConditionFacet $facet
    ) {
        if (!$request->has($facet->getRequestParameter())) {
            return;
        }
        $criteria->addCondition(
            new CombinedCondition(
                $facet->getConditions()
            )
        );
    }

    /**
     * @return bool
     */
    private function isAttributeInRequest(ProductAttributeFacet $facet, Request $request)
    {
        $params = $request->getParams();

        if (array_key_exists($facet->getFormFieldName(), $params)) {
            return true;
        }
        if ($facet->getMode() !== ProductAttributeFacet::MODE_RANGE_RESULT) {
            return false;
        }

        return array_key_exists('min' . $facet->getFormFieldName(), $params)
            || array_key_exists('max' . $facet->getFormFieldName(), $params)
        ;
    }
}
