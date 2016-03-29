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
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Bundle\AttributeBundle\Service\DataLoader;
use Shopware\Bundle\AttributeBundle\Service\SchemaOperator;
use Shopware\Bundle\AttributeBundle\Service\TableMapping;
use Shopware\Bundle\AttributeBundle\Service\TypeMapping;
use Shopware\Components\Model\ModelManager;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\AttributeBundle\Controllers\Backend
 * @copyright Copyright (c) shopware AG (http://www.shopware.com)
 */
class Shopware_Controllers_Backend_Attributes extends Shopware_Controllers_Backend_ExtJs
{
    protected function addAclPermission($action, $privilege, $errorMessage = '')
    {
        $this->addAclPermission("navigate", "read", "Insufficient Permissions");
        $this->addAclPermission("create", "update", "Insufficient Permissions");
        $this->addAclPermission("update", "update", "Insufficient Permissions");
        $this->addAclPermission("delete", "update", "Insufficient Permissions");
        $this->addAclPermission("generateModels", "update", "Insufficient Permissions");
        $this->addAclPermission("resetData", "update", "Insufficient Permissions");
    }

    const EXT_JS_PREFIX = '__attribute_';

    public function loadDataAction()
    {
        /** @var DataLoader $dataLoader */
        $dataLoader = $this->get('shopware_attribute.data_loader');

        try {
            $data = $dataLoader->load(
                $this->Request()->getParam('_table'),
                $this->Request()->getParam('_foreignKey')
            );
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
            return;
        }

        if (empty($data)) {
            $data = [];
        }

        $result = [];
        foreach ($data as $key => $value) {
            $key = self::EXT_JS_PREFIX . $key;
            $result[$key] = $value;
        }

        $this->View()->assign(['success' => true, 'data' => $result]);
    }

    public function saveDataAction()
    {
        /** @var \Shopware\Bundle\AttributeBundle\Service\DataPersister $dataPersister */
        $dataPersister = $this->get('shopware_attribute.data_persister');

        $data = [];
        foreach ($this->Request()->getParams() as $key => $value) {
            $key = str_replace(self::EXT_JS_PREFIX, '', $key);
            $data[$key] = $value;
        }

        try {
            $dataPersister->persist(
                $data,
                $this->Request()->getParam('_table'),
                $this->Request()->getParam('_foreignKey')
            );
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
            return;
        }
        $this->View()->assign('success', true);
    }

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
            'data' => array_values($mapping->getTypes())
        ]);
    }

    public function getEntitiesAction()
    {
        /** @var TypeMapping $mapping */
        $mapping = $this->get('shopware_attribute.type_mapping');

        $this->View()->assign([
            'success' => true,
            'data' => $mapping->getEntities()
        ]);
    }

    public function resetDataAction()
    {
        $table = $this->Request()->getParam('tableName');
        $column = $this->Request()->getParam('columnName');

        if (!$table || !$column) {
            throw new Exception("Required parameter not found");
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

    public function listAction()
    {
        /** @var CrudService $crudService */
        $crudService = $this->get('shopware_attribute.crud_service');
        $columns = $crudService->getList(
            $this->Request()->getParam('table')
        );

        $columns = array_filter($columns, function (ConfigurationStruct $column) {
            return $column->isIdentifier() == false;
        });

        $this->View()->assign([
            'success' => true,
            'data' => array_values($columns),
            'total' => 1
        ]);
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
            $service->create(
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
}
