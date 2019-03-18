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
use Shopware\Bundle\SearchBundle\Condition\PropertyCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\CriteriaRequestHandlerInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class PropertyCriteriaRequestHandler implements CriteriaRequestHandlerInterface
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function handleRequest(Request $request, Criteria $criteria, ShopContextInterface $context)
    {
        $this->addPropertyCondition($request, $criteria);
    }

    private function addPropertyCondition(Request $request, Criteria $criteria)
    {
        $filters = $request->getParam('sFilterProperties', []);
        if (empty($filters)) {
            return;
        }

        $filters = explode('|', $filters);
        $filters = $this->getGroupedFilters($filters);

        if (empty($filters)) {
            return;
        }

        foreach ($filters as $filter) {
            $condition = new PropertyCondition($filter);
            $criteria->addCondition($condition);
        }
    }

    /**
     * Helper function which groups the passed filter option ids
     * by the filter group.
     * Each filter group is joined as own PropertyCondition to the criteria
     * object
     *
     * @param array $filters
     *
     * @return array
     */
    private function getGroupedFilters($filters)
    {
        $sql = "
            SELECT
                optionID,
                GROUP_CONCAT(filterValues.id SEPARATOR '|') as valueIds
            FROM s_filter_values filterValues
            WHERE filterValues.id IN (?)
            GROUP BY filterValues.optionID
        ";

        $data = $this->connection->fetchAll(
            $sql,
            [$filters],
            [Connection::PARAM_INT_ARRAY]
        );

        $result = [];
        foreach ($data as $value) {
            $optionId = $value['optionID'];
            $valueIds = explode('|', $value['valueIds']);

            if (empty($valueIds)) {
                continue;
            }
            $result[$optionId] = $valueIds;
        }

        return $result;
    }
}
