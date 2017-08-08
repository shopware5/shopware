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

use Shopware\Search\Criteria;
use Shopware\Context\Struct\ShopContext;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
interface QueryBuilderFactoryInterface
{
    /**
     * Creates the product number search query for the provided
     * criteria and context.
     *
     * Adds the sortings and conditions of the provided criteria.
     *
     * @param \Shopware\Search\Criteria             $criteria
     * @param \Shopware\Context\Struct\ShopContext $context
     *
     * @return QueryBuilder
     */
    public function createQueryWithSorting(Criteria $criteria, ShopContext $context);

    /**
     * Generates the product selection query of the product number search
     *
     * @param Criteria             $criteria
     * @param \Shopware\Context\Struct\ShopContext $context
     *
     * @return QueryBuilder
     */
    public function createProductQuery(Criteria $criteria, ShopContext $context);

    /**
     * Creates the product number search query for the provided
     * criteria and context.
     *
     * Adds only the conditions of the provided criteria.
     *
     * @param Criteria             $criteria
     * @param \Shopware\Context\Struct\ShopContext $context
     *
     * @return QueryBuilder
     */
    public function createQuery(Criteria $criteria, ShopContext $context);

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder();
}
