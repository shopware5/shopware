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

/**
 * <code>
 * $menuSave = Shopware_Components_Menu_SaveHandler_DbTable;
 * $menuSave->load($menu);
 * </code>
 */
class Shopware_Components_Menu_SaveHandler_DbTable extends Zend_Db_Table_Abstract
{
    protected $_name = 's_core_menu';

    protected $_primary = 'id';

    protected $_colums = array(
        'id' => 'id',
        'parent' => 'parent',
        'uri' => 'hyperlink',
        'label' => 'name',
        'onclick' => 'onclick',
        'style' => 'style',
        'class' => 'class',
        'position' => 'position',
        'active' => 'active',
        'pluginID' => 'pluginID',
        'controller' => 'controller',
        'action' => 'action',
        'shortcut' => 'shortcut'
    );

    protected $_order = array(
        'parent', 'position', 'id'
    );

    public function __construct($config=array())
    {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        }
        foreach ($config as $key => $value) {
            if ($key=='order') {
                $this->_order = $value;
            } elseif (substr($key, -6)=='Column') {
                $this->_colums[substr($key, 0, -6)] = $value;
            }
        }

        parent::__construct($config);
    }

    public function load(Shopware_Components_Menu $menu)
    {
        $rows = $this->fetchAll(null, $this->_order);
        $pages = array();
        foreach ($rows as $rowKey => $row) {
            $page = array('order'=>$rowKey);
            foreach ($this->_colums as $key => $colum) {
                if (isset($row->{$colum})) {
                    $page[$key] = $row->{$colum};
                }
            }
            $pages[] = $page;
        }
        $menu->addItems($pages);
    }

    public function save(Shopware_Components_Menu $menu)
    {
        $iterator = new RecursiveIteratorIterator($menu, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $page) {
            $data = array();
            $data_id = null;
            foreach ($this->_colums as $key => $colum) {
                $value = $page->get($key);
                if ($key=='parent') {
                    if ($value instanceof Zend_Navigation_Page) {
                        $value = $value->getId();
                    } elseif ($value!==null) {
                        $value = 0;
                    }
                } elseif ($key=='id') {
                    $data_id = $value;
                    $value = null;
                }
                if ($value!==null) {
                    $data[$colum] = $value;
                }
            }

            if (!empty($data_id)) {
                $this->update($data, array($this->_colums['id'].' = ?' => $data_id));
            } else {
                $page->setId($this->insert($data));
            }
        }
    }
}
