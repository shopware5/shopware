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

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.com)
 */
class Shopware_Controllers_Backend_AttributeData extends Shopware_Controllers_Backend_ExtJs
{
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
            $key = CrudService::EXT_JS_PREFIX . $key;
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
            $key = str_replace(CrudService::EXT_JS_PREFIX, '', $key);
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
            'total' => 1,
        ]);
    }
}
