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
use Shopware\Bundle\SearchBundle\Condition\VariantCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\CriteriaRequestHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\VariantHelperInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class VariantCriteriaRequestHandler implements CriteriaRequestHandlerInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var VariantHelperInterface
     */
    private $variantHelper;

    public function __construct(Connection $connection, VariantHelperInterface $variantHelper)
    {
        $this->connection = $connection;
        $this->variantHelper = $variantHelper;
    }

    public function handleRequest(Request $request, Criteria $criteria, ShopContextInterface $context)
    {
        $this->addVariantCondition($request, $criteria);
    }

    private function addVariantCondition(Request $request, Criteria $criteria)
    {
        $filters = $request->getParam('variants', []);
        if (empty($filters)) {
            return;
        }

        $filters = explode('|', $filters);
        $filters = $this->getGroupedFilters($filters);

        if (empty($filters)) {
            return;
        }

        $facet = $this->variantHelper->getVariantFacet();
        $groups = [];
        if ($facet) {
            $groups = $facet->getExpandGroupIds();
        }

        foreach ($filters as $groupId => $filter) {
            $condition = new VariantCondition($filter, in_array($groupId, $groups), $groupId);
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
                group_id,
                GROUP_CONCAT(variantOptions.id SEPARATOR '|') as optionIds
            FROM s_article_configurator_options variantOptions
            WHERE variantOptions.id IN (?)
            GROUP BY variantOptions.group_id
        ";

        $data = $this->connection->fetchAll(
            $sql,
            [$filters],
            [Connection::PARAM_INT_ARRAY]
        );

        $result = [];
        foreach ($data as $value) {
            $groupId = $value['group_id'];
            $optionIds = explode('|', $value['optionIds']);

            if (empty($optionIds)) {
                continue;
            }
            $result[$groupId] = $optionIds;
        }

        return $result;
    }
}
