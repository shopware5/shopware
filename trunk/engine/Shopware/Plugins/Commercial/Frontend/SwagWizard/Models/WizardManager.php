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

class Shopware_Models_WizardManager extends Shopware_Models_Wizard_Table
{
    protected $_name = 's_plugin_wizard';
    protected $_rowClass = 'Shopware_Models_Wizard';
    protected $_dependentTables = array('Shopware_Models_Wizard_FilterManager');

    /**
     * Returns active wizard by id
     *
     * @param  int                    $id
     * @return Shopware_Models_Wizard
     */
    public static function getActiveWizardById($id)
    {
        $manager = self::Instance();
        $select = $manager->select()
            ->where('active=1')
            ->where('id=?', $id)
            ->where('shopID=? OR shopID IS NULL', Shopware()->Shop()->getId());
        $row = $manager->fetchRow($select);

        return $row;
    }

    /**
     * Returns wizard manager instance
     *
     * @return Shopware_Models_WizardManager
     */
    public static function Instance()
    {
        return Enlight_Class::Instance(__CLASS__);
    }
}
