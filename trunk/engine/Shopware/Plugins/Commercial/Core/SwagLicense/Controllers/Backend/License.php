<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    Shopware_Controllers
 * @subpackage Article
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Shopware Config Controller
 *
 * todo@all: Documentation
 */
class Shopware_Controllers_Backend_License extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * @return string
     */
    protected function getTable()
    {
        return 's_core_licenses';
    }

    /**
     * Return a list of values for extended forms
     */
    public function getListAction()
    {
        $table = $this->getTable();
        $filter = $this->Request()->get('filter');
        if (isset($filter[0]['property']) && $filter[0]['property'] == 'name') {
            $search = $filter[0]['value'];
        }

        $select = Shopware()->Db()->select();
        $select->from(array('t' => $table));
        $data = Shopware()->Db()->fetchAll($select);
        foreach($data as &$row) {
            $row['active'] = (bool) $row['active'];
            $row['added'] = new DateTime($row['added']);
            $row['creation'] = $row['creation'] !== null ? new DateTime($row['creation']) : $row['creation'];
            $row['expiration'] = $row['expiration'] !== null ? new DateTime($row['expiration']) : $row['expiration'];

            $row['license'] = preg_replace('#--.+?--#', '', (string) $row['license']);
            $row['license'] = preg_replace('#[^A-Za-z0-9+/=]#', '', $row['license']);
            $row['license'] = chunk_split($row['license'], 32);
            $row['license'] = "-------- LICENSE BEGIN ---------\r\n"
                            .  $row['license']
                            . "--------- LICENSE END ----------\r\n";
        }

        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => count($data)));
    }

    /**
     * Save the custom table values
     */
    public function saveAction()
    {
        $db = Shopware()->Db();
        $data = $this->Request()->getPost();
        $data = isset($data[0]) ? array_pop($data) : $data;
        $info = Shopware_Components_License::readLicenseInfo($data['license']);

        if(empty($info)) {
            $this->View()->assign(array(
                'success' => false,
                'result' => 'No valid license found.'
            ));
            return;
        }

        $info['creation'] = $this->fixDate($info['creation']);
        $info['expiration'] = $this->fixDate($info['expiration']);
        $info['label'] = !empty($info['label']) ? $info['label'] : $info['module'];
        $info['label'] = !empty($data['label']) ? $data['label'] : $info['label'];
        $info['active'] = !empty($data['active']) ? 1 : 0;

        $table = $this->getTable();
        $columns = array_keys($db->describeTable($table));
        $diff = array_diff(array_keys($info), $columns);
        foreach($diff as $column) {
            unset($info[$column]);
        }

        if (!empty($data['id'])) {
            $result = $db->update($table, $info, array('id=?' => $data['id']));
        } else {
            $info['added'] = new Zend_Date();
            $result = $db->insert($table, $info);
        }

        $this->View()->assign(array(
            'success' => true,
            'result' => $result
        ));
    }

    /**
     * @param   string $date
     * @return  null|string
     */
    protected function fixDate($date)
    {
        if(empty($date)) {
            return null;
        }
        $date = substr($date, 0, 4) . '-' . substr($date, 4, 2). '-' . substr($date, 6, 2);
        return $date;
    }

    /**
     * Save the custom table values
     */
    public function deleteAction()
    {
        $data = $this->Request()->getPost();
        $data = isset($data[0]) ? array_pop($data) : $data;

        $table = $this->getTable();

        if ($table !== null && !empty($data['id'])) {
            Shopware()->Db()->delete($table, array('id=?' => $data['id']));
            $this->View()->assign(array('success' => true));
        } else {
            $this->View()->assign(array('success' => false));
        }
    }
}