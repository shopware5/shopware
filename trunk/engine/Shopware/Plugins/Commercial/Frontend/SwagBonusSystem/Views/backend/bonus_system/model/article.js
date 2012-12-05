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

//{block name="backend/bonus_system/model/article"}
Ext.define('Shopware.apps.BonusSystem.model.Article', {

    /**
     * Extends the standard Ext Model
     * @string
     */
    extend: 'Ext.data.Model',

    fields: [
        'id',
        'articleID',
        'articleName',
        'ordernumber',
        'required_points',
        'position'
    ],

    /**
     * Configure the data communication
     * @object
     */
    proxy: {
        type:'ajax',

        /**
         * Configure the url mapping for the different
         * store operations based on
         * @object
         */
        api: {
            read: "{url controller='BonusSystem' action='getBonusArticles'}",
            update: "{url controller='BonusSystem' action='updateBonusArticle'}",
            create: "{url controller='BonusSystem' action='insertBonusArticle' }",
            destroy: "{url controller='BonusSystem' action='deleteBonusArticle' targetField='details'}"
        },

        /**
         * Configure the data reader
         * @object
         */
        reader:{
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    }
});
//{/block}
