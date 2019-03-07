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

use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Bundle\AttributeBundle\Service\SchemaOperator;
use Shopware\Bundle\AttributeBundle\Service\TableMapping;
use Shopware\Bundle\AttributeBundle\Service\TypeMapping;
use Shopware\Components\Model\ModelManager;

class Shopware_Controllers_Backend_Attributes extends Shopware_Controllers_Backend_ExtJs
{
    public function getTablesAction()
    {
        /** @var TableMapping $mapping */
        $mapping = $this->get('shopware_attribute.table_mapping');
        $tables = $mapping->getAttributeTables();

        foreach ($tables as $name => &$table) {
            $table['name'] = $name;
        }

        $this->View()->assign(['success' => true, 'data' => array_values($tables)]);
    }

    public function getTypesAction()
    {
        /** @var TypeMapping $mapping */
        $mapping = $this->get('shopware_attribute.type_mapping');

        $this->View()->assign([
            'success' => true,
            'data' => array_values($mapping->getTypes()),
        ]);
    }

    public function getEntitiesAction()
    {
        /** @var TypeMapping $mapping */
        $mapping = $this->get('shopware_attribute.type_mapping');

        $this->View()->assign([
            'success' => true,
            'data' => $mapping->getEntities(),
        ]);
    }

    public function resetDataAction()
    {
        $tableParamName = 'tableName';
        $columnParamName = 'columnName';
        $table = $this->Request()->getParam($tableParamName);
        $column = $this->Request()->getParam($columnParamName);

        if (!$table) {
            throw new Exception(sprintf('Required parameter "%s" not found', $tableParamName));
        }

        if (!$column) {
            throw new Exception(sprintf('Required parameter "%s" not found', $columnParamName));
        }

        /** @var SchemaOperator $schemaOperator */
        $schemaOperator = $this->get('shopware_attribute.schema_operator');

        try {
            $schemaOperator->resetColumn($table, $column);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);

            return;
        }
        $this->View()->assign('success', true);
    }

    public function getColumnAction()
    {
        /** @var CrudService $crudService */
        $crudService = $this->get('shopware_attribute.crud_service');
        $columnName = $this->Request()->getParam('columnName');

        try {
            $column = $crudService->get(
                $this->Request()->getParam('table'),
                $columnName
            );
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);

            return;
        }

        $this->View()->assign(['success' => true, 'data' => $column]);
    }

    public function createAction()
    {
        $data = $this->Request()->getParams();
        $data['custom'] = true;

        /** @var CrudService $service */
        $service = $this->get('shopware_attribute.crud_service');

        try {
            $service->update(
                $this->Request()->getParam('tableName'),
                $this->Request()->getParam('columnName'),
                $this->Request()->getParam('columnType'),
                $data
            );
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);

            return;
        }

        $this->View()->assign(['success' => true]);
    }

    public function deleteAction()
    {
        /** @var CrudService $service */
        $service = $this->get('shopware_attribute.crud_service');

        try {
            $service->delete(
                $this->Request()->getParam('tableName'),
                $this->Request()->getParam('columnName')
            );
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);

            return;
        }

        $this->View()->assign(['success' => true]);
    }

    public function updateAction()
    {
        $data = $this->Request()->getParams();

        /** @var CrudService $service */
        $service = $this->get('shopware_attribute.crud_service');

        try {
            $service->update(
                $this->Request()->getParam('tableName'),
                $this->Request()->getParam('originalName'),
                $this->Request()->getParam('columnType'),
                $data,
                $this->Request()->getParam('columnName'),
                false,
                $this->Request()->getParam('defaultValue')
            );
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);

            return;
        }

        $this->View()->assign(['success' => true]);
    }

    public function generateModelsAction()
    {
        $table = $this->Request()->getParam('tableName');

        /** @var TableMapping $mapping */
        $mapping = $this->get('shopware_attribute.table_mapping');

        if (!$mapping->isAttributeTable($table) || !$table) {
            $this->View()->assign(['success' => false, 'message' => 'Table not supported']);

            return;
        }

        if (!$model = $mapping->getTableModel($table)) {
            $this->View()->assign(['success' => false, 'message' => 'Table has no model']);

            return;
        }

        try {
            /** @var ModelManager $entityManager */
            $entityManager = $this->get('models');
            $entityManager->generateAttributeModels([$table]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);

            return;
        }

        $this->View()->assign('success', true);
    }

    public function columnNameExistsAction()
    {
        $name = $this->Request()->getParam('columnName');
        $table = $this->Request()->getParam('tableName');

        /** @var TableMapping $mapping */
        $mapping = $this->get('shopware_attribute.table_mapping');

        $tables = array_merge([$table], $mapping->getDependingTables($table));

        $table = null;
        foreach ($tables as $attributeTable) {
            if ($mapping->isTableColumn($attributeTable, $name)) {
                $table = $attributeTable;
                break;
            }
        }

        $this->View()->assign([
            'exists' => ($table !== null),
            'table' => $table,
        ]);
    }

    protected function addAclPermission($action, $privilege, $errorMessage = '')
    {
        $this->addAclPermission('create', 'update', 'Insufficient Permissions');
        $this->addAclPermission('update', 'update', 'Insufficient Permissions');
        $this->addAclPermission('delete', 'update', 'Insufficient Permissions');
        $this->addAclPermission('generateModels', 'update', 'Insufficient Permissions');
        $this->addAclPermission('resetData', 'update', 'Insufficient Permissions');
    }
}
