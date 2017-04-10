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

use Enlight_Controller_Request_Request as Request;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
interface StoreFrontCriteriaFactoryInterface
{
    /**
     * @param int[]                $categoryIds
     * @param ShopContextInterface $context
     *
     * @return Criteria
     */
    public function createBaseCriteria($categoryIds, ShopContextInterface $context);

    /**
     * @param Request                                                        $request
     * @param \Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface $context
     *
     * @return Criteria
     */
    public function createSearchCriteria(Request $request, ShopContextInterface $context);

    /**
     * @param Request              $request
     * @param ShopContextInterface $context
     *
     * @return Criteria
     */
    public function createListingCriteria(Request $request, ShopContextInterface $context);

    /**
     * @param Request                                                        $request
     * @param \Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface $context
     *
     * @return Criteria
     */
    public function createAjaxSearchCriteria(Request $request, ShopContextInterface $context);

    /**
     * @param Request                                                        $request
     * @param \Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface $context
     *
     * @return Criteria
     */
    public function createAjaxListingCriteria(Request $request, ShopContextInterface $context);

    /**
     * @param Request                                                        $request
     * @param \Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface $context
     *
     * @return Criteria
     */
    public function createAjaxCountCriteria(Request $request, ShopContextInterface $context);

    /**
     * @param Request                                                        $request
     * @param \Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface $context
     * @param int                                                            $categoryId
     *
     * @return \Shopware\Bundle\SearchBundle\Criteria
     */
    public function createProductNavigationCriteria(Request $request, ShopContextInterface $context, $categoryId);
}
