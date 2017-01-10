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

namespace Shopware\Bundle\SearchBundle;

use Doctrine\Common\Collections\ArrayCollection;
use Enlight_Controller_Request_Request as Request;
use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\Condition\CustomerGroupCondition;
use Shopware\Bundle\SearchBundle\Condition\IsAvailableCondition;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundle
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class StoreFrontCriteriaFactory implements StoreFrontCriteriaFactoryInterface
{
    const SORTING_RELEASE_DATE = 1;
    const SORTING_POPULARITY = 2;
    const SORTING_CHEAPEST_PRICE = 3;
    const SORTING_HIGHEST_PRICE = 4;
    const SORTING_PRODUCT_NAME_ASC = 5;
    const SORTING_PRODUCT_NAME_DESC = 6;
    const SORTING_SEARCH_RANKING = 7;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @var CriteriaRequestHandlerInterface[]
     */
    private $requestHandlers;

    /**
     * @param \Shopware_Components_Config $config
     * @param \Enlight_Event_EventManager $eventManager
     * @param CriteriaRequestHandlerInterface[] $requestHandlers
     */
    public function __construct(
        \Shopware_Components_Config $config,
        \Enlight_Event_EventManager $eventManager,
        $requestHandlers = []
    ) {
        $this->config = $config;
        $this->eventManager = $eventManager;

        $this->requestHandlers = $requestHandlers;
        $this->requestHandlers = $this->registerRequestHandlers();
    }

    /**
     * @param int[] $categoryIds
     * @param ShopContextInterface $context
     * @return Criteria
     */
    public function createBaseCriteria($categoryIds, ShopContextInterface $context)
    {
        $criteria = new Criteria();

        $criteria->addBaseCondition(new CategoryCondition($categoryIds));

        if ($this->config->get('hideNoInStock')) {
            $criteria->addBaseCondition(new IsAvailableCondition());
        }

        $criteria->addBaseCondition(
            new CustomerGroupCondition([$context->getCurrentCustomerGroup()->getId()])
        );

        $this->eventManager->notify('Shopware_SearchBundle_Create_Base_Criteria', [
            'criteria' => $criteria,
            'context' => $context,
            'categoryIds' => $categoryIds
        ]);
        return $criteria;
    }

    /**
     * @param Request $request
     * @param ShopContextInterface $context
     * @return Criteria
     */
    public function createSearchCriteria(Request $request, ShopContextInterface $context)
    {
        $criteria = $this->getSearchCriteria($request, $context);

        $this->eventManager->notify('Shopware_SearchBundle_Create_Search_Criteria', [
            'criteria' => $criteria,
            'request'  => $request,
            'context'  => $context
        ]);

        return $criteria;
    }

    /**
     * @param Request $request
     * @param ShopContextInterface $context
     * @return Criteria
     */
    public function createListingCriteria(Request $request, ShopContextInterface $context)
    {
        $criteria = $this->createCriteriaFromRequest($request, $context);

        $this->eventManager->notify('Shopware_SearchBundle_Create_Listing_Criteria', [
            'criteria' => $criteria,
            'request'  => $request,
            'context'  => $context
        ]);

        $criteria->removeFacet('category');

        return $criteria;
    }

    /**
     * @param Request $request
     * @param ShopContextInterface $context
     * @return Criteria
     */
    public function createAjaxSearchCriteria(Request $request, ShopContextInterface $context)
    {
        $criteria = $this->getSearchCriteria($request, $context);

        $criteria->limit($this->config->get('MaxLiveSearchResults', 6));

        $this->eventManager->notify('Shopware_SearchBundle_Create_Ajax_Search_Criteria', [
            'criteria' => $criteria,
            'request'  => $request,
            'context'  => $context
        ]);

        $criteria->resetFacets();

        return $criteria;
    }

    /**
     * @param Request $request
     * @param ShopContextInterface $context
     * @return Criteria
     */
    public function createAjaxListingCriteria(Request $request, ShopContextInterface $context)
    {
        $criteria = $this->createCriteriaFromRequest($request, $context);

        $this->eventManager->notify('Shopware_SearchBundle_Create_Ajax_Listing_Criteria', [
            'criteria' => $criteria,
            'request'  => $request,
            'context'  => $context
        ]);

        $criteria->resetFacets();

        return $criteria;
    }

    /**
     * @param Request $request
     * @param ShopContextInterface $context
     * @return Criteria
     */
    public function createAjaxCountCriteria(Request $request, ShopContextInterface $context)
    {
        $criteria = $this->createCriteriaFromRequest($request, $context);

        $this->eventManager->notify('Shopware_SearchBundle_Create_Ajax_Count_Criteria', [
            'criteria' => $criteria,
            'request'  => $request,
            'context'  => $context
        ]);

        $criteria
            ->offset(0)
            ->limit(1)
            ->resetSorting()
            ->resetFacets();

        return $criteria;
    }

    /**
     * @param Request $request
     * @param ShopContextInterface $context
     * @param int $categoryId
     * @return \Shopware\Bundle\SearchBundle\Criteria
     */
    public function createProductNavigationCriteria(
        Request $request,
        ShopContextInterface $context,
        $categoryId
    ) {
        $criteria = $this->createCriteriaFromRequest($request, $context);

        $criteria
            ->offset(0)
            ->limit(null);

        $criteria->removeCondition('category');
        $criteria->addBaseCondition(new CategoryCondition([$categoryId]));

        $this->eventManager->notify('Shopware_SearchBundle_Create_Product_Navigation_Criteria', [
            'criteria' => $criteria,
            'request'  => $request,
            'context'  => $context
        ]);

        $criteria->resetFacets();

        return $criteria;
    }

    /**
     * @param Request $request
     * @param ShopContextInterface $context
     * @return Criteria
     */
    private function getSearchCriteria(Request $request, ShopContextInterface $context)
    {
        if (!$request->has('sSort')) {
            $request->setParam('sSort', StoreFrontCriteriaFactory::SORTING_SEARCH_RANKING);
        }

        $criteria = $this->createCriteriaFromRequest($request, $context);

        $systemId = $context->getShop()->getCategory()->getId();

        if (!$criteria->hasBaseCondition('category')) {
            $criteria->addBaseCondition(new CategoryCondition([$systemId]));
            return $criteria;
        }

        /** @var CategoryCondition $condition */
        $condition = $criteria->getBaseCondition('category');

        if (!in_array($systemId, $condition->getCategoryIds())) {
            $criteria->removeBaseCondition('category');
            $criteria->addCondition($condition);
            $criteria->addBaseCondition(new CategoryCondition([$systemId]));
        }

        return $criteria;
    }

    /**
     * @param Request $request
     * @param ShopContextInterface $context
     * @return Criteria
     */
    private function createCriteriaFromRequest(Request $request, ShopContextInterface $context)
    {
        $criteria = new Criteria();

        foreach ($this->requestHandlers as $handler) {
            $handler->handleRequest($request, $criteria, $context);
        }

        return $criteria;
    }

    /**
     * @return array
     * @throws \Enlight_Event_Exception
     */
    private function registerRequestHandlers()
    {
        $requestHandlers = new ArrayCollection();
        $requestHandlers = $this->eventManager->collect(
            'Shopware_SearchBundle_Collect_Criteria_Request_Handlers',
            $requestHandlers
        );

        return array_merge($this->requestHandlers, $requestHandlers->toArray());
    }
}
