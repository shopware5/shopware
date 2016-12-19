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

use Shopware\Bundle\AttributeBundle\Service\ConfigurationStruct;
use Shopware\Models\Search\CustomSorting;
use Enlight_Controller_Request_Request as Request;
use Shopware\Bundle\AttributeBundle\Repository\SearchCriteria;

class Shopware_Controllers_Backend_CustomSorting extends Shopware_Controllers_Backend_Application
{
    protected $model = CustomSorting::class;

    public function copyCategorySettingsAction()
    {
        $categoryId = (int) $this->Request()->getParam('categoryId');

        $connection = $this->container->get('dbal_connection');

        $data = $connection->fetchAssoc(
            'SELECT `hide_sortings`, `sorting_ids` FROM s_categories WHERE id = :id',
            [':id' => $categoryId]
        );

        $connection->executeUpdate(
            "UPDATE s_categories SET `hide_sortings` = :hideSortings, `sorting_ids` = :sortingIds WHERE path LIKE :path",
            [
                ':hideSortings' => (int) $data['hide_sortings'],
                ':sortingIds' => (string) $data['sorting_ids'],
                ':path' => '%|'. $categoryId .'|%'
            ]
        );

        $this->View()->assign('success', true);
    }

    public function listAttributesAction()
    {
        $service = Shopware()->Container()->get('shopware_attribute.crud_service');

        if ($column = $this->Request()->getParam('columnName')) {
            $attribute = $service->get('s_articles_attributes', $column);
            $this->View()->assign(['success' => true, 'data' => $attribute]);
            return;
        }

        $attributes = $service->getList('s_articles_attributes');
        $total = count($attributes);

        $term = $this->Request()->getParam('query', null);

        $attributes = array_filter(
            $attributes,
            function (ConfigurationStruct $attribute) {
                return !$attribute->isIdentifier();
            }
        );

        if ($term) {
            $attributes = array_filter(
                $attributes,
                function (ConfigurationStruct $attribute) use ($term) {
                    return stripos($term, $attribute->getLabel()) > 0;
                }
            );
        }

        $offset = $this->Request()->getParam('start', 0);
        $limit = $this->Request()->getParam('limit', 30);
        if ($offset && $limit) {
            $attributes = array_slice($attributes, $offset, $limit);
        }

        usort(
            $attributes,
            function (ConfigurationStruct $a, ConfigurationStruct $b) {
                if (!$b->getId() && $a->getId()) {
                    return -1;
                }
                if ($b->getId() && !$a->getId()) {
                    return 1;
                }
                if (empty($b->getLabel()) && $a->getLabel()) {
                    return -1;
                }
                if ($b->getLabel() && empty($a->getLabel())) {
                    return 1;
                }
                return strnatcmp($a->getLabel(), $b->getLabel());
            }
        );

        $this->View()->assign([
            'data' => array_values($attributes),
            'totalCount' => $total
        ]);
    }

    public function changePositionAction()
    {
        $id = (int) $this->Request()->getParam('id');
        $position = (int) $this->Request()->getParam('position');

        $connection = $this->container->get('dbal_connection');

        $connection->executeUpdate(
            "UPDATE s_search_custom_sorting SET position = position - 1 WHERE position <= :position",
            [':position' => $position]
        );

        $connection->executeUpdate(
            "UPDATE s_search_custom_sorting SET position = :position WHERE id = :id",
            [':position' => $position, ':id' => $id]
        );

        $this->View()->assign('success', true);
    }
}
