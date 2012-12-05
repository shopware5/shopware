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
 * @package    LiveShopping
 * @subpackage ExtJs
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware ExtJs model
 */
//{block name="backend/article/model/live_shopping/live_shopping"}
Ext.define('Shopware.apps.Article.model.live_shopping.LiveShopping', {
    /**
     * Extends the standard Ext Model
     * @string
     */
    extend: 'Ext.data.Model',

    /**
     * The fields used for this model
     * @array
     */
    fields: [
        //{block name="backend/article/model/live_shopping/live_shopping/fields"}{/block}
        { name: 'id', type: 'int', useNull: true },
        { name: 'name', type: 'string' },
        { name: 'type', type: 'int' },
        { name: 'articleId', type: 'int', useNull: true },
        { name: 'active', type: 'boolean' },
        { name: 'number', type: 'string' },
        { name: 'limited', type: 'boolean', defaultValue: false },
        { name: 'quantity', type: 'int' },
        { name: 'validFrom', type: 'date', useNull: true, dateFormat: 'd.m.Y' },
        { name: 'validTo', type: 'date', useNull: true, dateFormat: 'd.m.Y' },
        { name: 'validFromTime', type: 'string', useNull: true },
        { name: 'validToTime', type: 'string', useNull: true },
        { name: 'created', type: 'date', useNull: true },
        { name: 'sells', type: 'int' },
        { name: 'frontpageDisplay', type: 'boolean' },
        { name: 'categoriesDisplay', type: 'boolean' }
    ],

    associations: [
        { type: 'hasMany', model: 'Shopware.apps.Base.model.CustomerGroup', name: 'getCustomerGroups', associationKey: 'customerGroups' },
        { type: 'hasMany', model: 'Shopware.apps.Base.model.Shop', name: 'getShops', associationKey: 'shops' },
        { type: 'hasMany', model: 'Shopware.apps.Article.model.Detail', name: 'getLimitedVariants', associationKey: 'limitedVariants' },
        { type: 'hasMany', model: 'Shopware.apps.Article.model.live_shopping.Price', name: 'getPrices', associationKey: 'prices' }
    ],

    /**
     * Configure the data communication
     * @object
     */
    proxy:{
        /**
         * Set proxy type to ajax
         * @string
         */
        type:'ajax',

        /**
         * Configure the url mapping for the different
         * store operations based on
         * @object
         */
        api: {
            create: '{url controller="LiveShopping" action="createLiveShopping"}',
            update: '{url controller="LiveShopping" action="updateLiveShopping"}',
            destroy: '{url controller="LiveShopping" action="deleteLiveShopping"}'
        },

        /**
         * Configure the data reader
         * @object
         */
        reader:{
            type:'json',
            root:'data',
            totalProperty:'total'
        }
    }

});
//{/block}
