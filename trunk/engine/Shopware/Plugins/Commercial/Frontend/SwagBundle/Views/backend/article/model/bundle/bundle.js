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
 * @package    Article
 * @subpackage Bundle
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Model - Bundle models.
 */
//{block name="backend/article/model/bundle/bundle"}
Ext.define('Shopware.apps.Article.model.bundle.Bundle', {
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
        //{block name="backend/article/model/article/fields"}{/block}
        { name: 'id', type: 'int', useNull: true },
        { name: 'name', type: 'string' },
        { name: 'type', type: 'int' },
        { name: 'articleId', type: 'int', useNull: true },
        { name: 'active', type: 'boolean' },
        { name: 'discountType', type: 'string' },
        { name: 'taxId', type: 'int', useNull: true },
        { name: 'number', type: 'string' },
        { name: 'limited', type: 'boolean', defaultValue: false },
        { name: 'quantity', type: 'int' },
        { name: 'validFrom', type: 'date', useNull: true, dateFormat: 'd.m.Y' },
        { name: 'validTo', type: 'date', useNull: true, dateFormat: 'd.m.Y' },
        { name: 'created', type: 'date', useNull: true },
        { name: 'sells', type: 'int' }
    ],

    associations: [
        { type: 'hasMany', model: 'Shopware.apps.Base.model.CustomerGroup', name: 'getCustomerGroups', associationKey: 'customerGroups' },
        { type: 'hasMany', model: 'Shopware.apps.Article.model.bundle.Article', name: 'getArticles', associationKey: 'articles' },
        { type: 'hasMany', model: 'Shopware.apps.Article.model.Detail', name: 'getLimitedDetails', associationKey: 'limitedDetails' },
        { type: 'hasMany', model: 'Shopware.apps.Article.model.Price', name: 'getPrices', associationKey: 'prices' }
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
            create: '{url controller="Bundle" action="createBundle"}',
            update: '{url controller="Bundle" action="updateBundle"}'
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
