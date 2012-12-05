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
 * @package    BonusSystem
 * @subpackage Main
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     shopware AG
 */

//{block name="backend/bonus_system/model/setting"}
Ext.define('Shopware.apps.BonusSystem.model.Setting', {
    /**
     * Extends the standard Ext Model
     * @string
     */
    extend: 'Ext.data.Model',

    idProperty: 'shopID',

    fields: [
        { name: 'shopID', type: 'int' },
        { name: 'bonus_maintenance_mode', type: 'string' },
        { name: 'bonus_articles_active', type: 'string' },
        { name: 'test', type: 'string' },
        { name: 'bonus_voucher_active', type: 'string' },
        { name: 'bonus_point_conversion_factor', type: 'string' },
        { name: 'bonus_voucher_conversion_factor', type: 'string' },
        { name: 'bonus_voucher_limitation_type', type: 'string' },
        { name: 'bonus_voucher_limitation_value', type: 'string' },
        { name: 'bonus_point_unlock_type', type: 'string' },
        { name: 'bonus_point_unlock_day', type: 'string' },
        { name: 'bonus_listing_text', type: 'string' },
        { name: 'bonus_listing_banner', type: 'string' },
        { name: 'display_banner', type: 'string' },
        { name: 'display_accordion', type: 'string' },
        { name: 'display_article_slider', type: 'string' },
        { name: 'banner_preview', type: 'string' }
    ],

    /**
     * Configure the data communication
     * @object
     */
    proxy: {
        type: 'ajax',

        /**
         * Configure the url mapping for the different
         * store operations based on
         * @object
         */
        api: {
            read:   '{url controller="BonusSystem" action="getSettings"}',
            create: '{url controller="BonusSystem" action="saveSettings"}',
            update: '{url controller="BonusSystem" action="saveSettings"}'
        },

        /**
         * Configure the data reader
         * @object
         */
        reader: {
            type: 'json',
            root: 'data'
        }
    }
});
//{/block}
