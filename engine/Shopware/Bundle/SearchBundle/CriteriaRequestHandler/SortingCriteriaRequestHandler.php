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

use Enlight_Controller_Request_RequestHttp as Request;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\CriteriaRequestHandlerInterface;
use Shopware\Bundle\StoreFrontBundle\Service\CustomSortingServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class SortingCriteriaRequestHandler implements CriteriaRequestHandlerInterface
{
    /**
     * @var CustomSortingServiceInterface
     */
    private $customSortingService;

    public function __construct(CustomSortingServiceInterface $customSortingService)
    {
        $this->customSortingService = $customSortingService;
    }

    public function handleRequest(
        Request $request,
        Criteria $criteria,
        ShopContextInterface $context
    ) {
        if (!$request->has('sSort')) {
            return;
        }

        $customSortings = $this->customSortingService->getList(
            [(int) $request->getParam('sSort')],
            $context
        );

        if (count($customSortings) === 0) {
            return;
        }

        $customSorting = array_shift($customSortings);
        foreach ($customSorting->getSortings() as $sorting) {
            $criteria->addSorting($sorting);
        }
    }
}
