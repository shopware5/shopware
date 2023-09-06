<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

use Shopware\Bundle\AttributeBundle\Service\ConfigurationStruct;
use Shopware\Bundle\AttributeBundle\Service\CrudServiceInterface;
use Shopware\Bundle\AttributeBundle\Service\DataLoaderInterface;

class Shopware_Controllers_Backend_AttributeData extends Shopware_Controllers_Backend_ExtJs
{
    public function loadDataAction()
    {
        /** @var DataLoaderInterface $dataLoader */
        $dataLoader = $this->get(DataLoaderInterface::class);

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
            $key = CrudServiceInterface::EXT_JS_PREFIX . $key;
            $result[$key] = $value;
        }

        $this->View()->assign(['success' => true, 'data' => $result]);
    }

    public function saveDataAction()
    {
        /** @var \Shopware\Bundle\AttributeBundle\Service\DataPersisterInterface $dataPersister */
        $dataPersister = $this->get(\Shopware\Bundle\AttributeBundle\Service\DataPersisterInterface::class);

        $data = [];
        foreach ($this->Request()->getParams() as $key => $value) {
            $key = str_replace(CrudServiceInterface::EXT_JS_PREFIX, '', $key);
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

    public function listAction()
    {
        /** @var CrudServiceInterface $crudService */
        $crudService = $this->get(CrudServiceInterface::class);
        $columns = $crudService->getList(
            $this->Request()->getParam('table')
        );

        $columns = array_filter($columns, function (ConfigurationStruct $column) {
            return $column->isIdentifier() == false;
        });

        if ($this->Request()->has('columns')) {
            $whitelist = json_decode($this->Request()->getParam('columns', []), true);
            $columns = array_filter($columns, function (ConfigurationStruct $column) use ($whitelist) {
                return \in_array($column->getColumnName(), $whitelist);
            });
        }

        if (!$this->Request()->getParam('raw')) {
            $this->translateColumns($columns);
        }

        $this->View()->assign([
            'success' => true,
            'data' => array_values($columns),
            'total' => 1,
        ]);
    }

    /**
     * @param ConfigurationStruct[] $columns
     */
    private function translateColumns(array $columns): void
    {
        $snippets = $this->container->get('snippets')->getNamespace('backend/attribute_columns');

        foreach ($columns as $column) {
            $key = $column->getTableName() . '_' . $column->getColumnName() . '_';

            if ($snippet = $snippets->get($key . 'label')) {
                $column->setLabel($snippet);
            }
            if ($snippet = $snippets->get($key . 'supportText')) {
                $column->setSupportText($snippet);
            }
            if ($snippet = $snippets->get($key . 'helpText')) {
                $column->setHelpText($snippet);
            }

            $arrayStore = json_decode($column->getArrayStore(), true);
            if (!empty($arrayStore)) {
                foreach ($arrayStore as &$option) {
                    $optionKey = sprintf('%soptions_store_%s', $key, strtolower($option['key']));
                    $option['value'] = $snippets->get($optionKey, $option['value']);
                }
                unset($option);
                $column->setArrayStore(json_encode($arrayStore));
            }
        }
    }
}
