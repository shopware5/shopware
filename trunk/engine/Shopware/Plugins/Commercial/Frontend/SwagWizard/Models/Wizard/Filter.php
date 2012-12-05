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
 * @package    Shopware_Plugins
 * @subpackage Plugin
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     shopware AG
 */

class Shopware_Models_Wizard_Filter extends Shopware_Models_Wizard_Row
{
    /**
     * Returns values by article ids
     *
     * @param  unknown_type $articleIds
     * @return unknown
     */
    public function getValuesByArticleIds($articleIds)
    {
        $dependentTable = Shopware_Models_Wizard_ValueManager::Instance();
        if (empty($articleIds)) {
            $articleIds = 0;
        }
        switch ($this->typeID) {
            case 7:
                $select = $dependentTable
                    ->select()
                    ->setIntegrityCheck(false)
                    ->from('s_filter_values', array(
                        new Zend_Db_Expr('MIN(`id`) as `id`'),
                        new Zend_Db_Expr('value'),
                        new Zend_Db_Expr('COUNT(*) as `count`')
                    ))
                    ->joinLeft('s_filter_articles', 's_filter_values.id = s_filter_articles.valueId')
                    ->where('value!=?', '')
                    ->where('articleID IN (?)', $articleIds)
                    ->where('optionID=?', $this->storeID)
                    ->group('value')
                    ->order('value');

                return $dependentTable->fetchAll($select);
            case 8:
                $select = $dependentTable
                    ->select()
                    ->setIntegrityCheck(false)
                    ->from('s_articles_details', array(
                        new Zend_Db_Expr('MIN(`id`) as `id`'),
                        new Zend_Db_Expr('additionaltext as `value`'),
                        new Zend_Db_Expr('COUNT(*) as `count`')
                    ))
                    ->where('additionaltext!=?', '')
                    ->where('articleID IN (?)', $articleIds)
                    ->group('value')
                    ->order('value');

                return $dependentTable->fetchAll($select);
            case 9:
                return;
            case 10:
                $select = $dependentTable
                    ->select()
                    ->setIntegrityCheck(false)
                    ->from('s_articles_attributes', array(
                        new Zend_Db_Expr('MIN(`id`) as `id`'),
                        new Zend_Db_Expr('attr'.$this->storeID.' as `value`'),
                        new Zend_Db_Expr('COUNT(*) as `count`')
                    ))
                    ->where('attr'.$this->storeID.'!=?', '')
                    ->where('articleID IN (?)', $articleIds)
                    ->group('value')
                    ->order('value');

                return $dependentTable->fetchAll($select);
            case 2:
            case 4:
                $select = $this->_getTable()
                    ->select()
                    ->setIntegrityCheck(false)
                    ->from(array('wv' => 's_plugin_wizard_values'))
                    ->where('wv.filterID=?', $this->id)
                    ->order('wv.key');

                return $dependentTable->fetchAll($select);
            case 10:
            default:
                $select = $this->_getTable()
                    ->select()
                    ->setIntegrityCheck(false)
                    ->from(array('wv' => 's_plugin_wizard_values'))
                    ->joinInner(array('wr' => 's_plugin_wizard_relations'), null, null)
                    ->where('wv.filterID=?', $this->id)
                    ->where('wr.valueID=wv.id')
                    ->where('wr.articleID IN (?)', $articleIds)
                    ->group('wv.id')
                    ->order('wv.key');

                return $dependentTable->fetchAll($select);
                //return $this->findDependentRowset($dependentTable);
        }
    }
}
