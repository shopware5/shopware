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

namespace Shopware\Bundle\SearchBundleDBAL\SearchTerm;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\SearchBundle\Condition\SearchTermCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class SearchTermLogger implements SearchTermLoggerInterface
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Traces the search result into the s_statistic_search
     *
     * @param Criteria                  $criteria
     * @param ProductNumberSearchResult $result
     * @param Shop                      $shop
     */
    public function logResult(
        Criteria $criteria,
        ProductNumberSearchResult $result,
        Shop $shop
    ) {
        if (!$criteria->hasCondition('search')) {
            return;
        }

        /* @var $condition SearchTermCondition */
        $condition = $criteria->getCondition('search');

        $now = new \DateTime();
        $this->connection->insert('s_statistics_search', [
            'datum' => $now->format('Y-m-d H:i:s'),
            'searchterm' => $condition->getTerm(),
            'results' => $result->getTotalCount(),
            'shop_id' => $shop->getId(),
        ]);
    }
}
