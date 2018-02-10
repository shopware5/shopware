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
 *
 * @category   Shopware
 * @package    Article
 * @subpackage Batch
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Model - Article backend module.
 */
//{block name="backend/article/model/detail"}
Ext.define('Shopware.apps.Article.model.Detail', {
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
        //{block name="backend/article/model/detail/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'articleId', type: 'int' },
        { name: 'number', type: 'string' },
        { name: 'additionalText', type: 'string' },
        { name: 'supplierNumber', type: 'string' },
        { name: 'active', type: 'int' },
        { name: 'inStock', type: 'int', defaultValue: 0 },
        { name: 'stockMin', type: 'int' },
        { name: 'lastStock', type: 'boolean'},

        { name: 'ean', type: 'string', useNull: true },
        { name: 'weight', type: 'float', useNull: true },
        { name: 'width', type: 'float', useNull: true },
        { name: 'height', type: 'float', useNull: true },
        { name: 'len', type: 'float', useNull: true },

        { name: 'active', type: 'boolean' },
        { name: 'kind', type: 'int' },
        { name: 'position', type: 'int' },
        { name: 'releaseDate', type: 'date', useNull: true, dateFormat: 'd.m.Y' },
        { name: 'shippingTime', type: 'string', useNull: true },
        { name: 'shippingFree', type: 'boolean' },
        { name: 'purchaseSteps', type: 'int', useNull: true },
        { name: 'minPurchase', type: 'int', useNull: false, defaultValue: 1 },
        { name: 'maxPurchase', type: 'int', useNull: true },
        { name: 'unitId', type: 'int', useNull: true },
        { name: 'purchaseUnit', type: 'float', useNull: true },
        { name: 'referenceUnit', type: 'float', useNull: true},
        { name: 'packUnit', type: 'string', useNull: true },
        { name: 'purchasePrice', type: 'float', useNull: false },
        {
            name: 'price',
            type: 'float',
            convert: function(value, record) {
                if (value) {
                    return value;
                }
                if (record && record.raw && record.raw.prices && record.raw.prices[0]) {
                    return Ext.Number.toFixed(record.raw.prices[0].price, 2);
                }
                return 0;
            }
        },
        {
            name: 'pseudoPrice',
            type: 'float',
            persist: false,
            convert: function(value, record) {
                if (value) {
                    return value;
                }
                if (record && record.getPrice() && record.getPrice().count() > 0) {
                    return Ext.Number.toFixed(record.getPrice().first().get('pseudoPrice'), 2);
                }
                if (record && record.raw && record.raw.prices && record.raw.prices[0]) {
                    return Ext.Number.toFixed(record.raw.prices[0].pseudoPrice, 2);
                }
                return 0;
            }
        },
        {
            name: 'standard',
            type: 'boolean',
            convert: function(value, record) {
                if (Ext.isBoolean(value)) {
                    return value;
                }
                return (record.get('kind') == 1)
            }
        }
    ],
    associations: [
        { type: 'hasMany', model: 'Shopware.apps.Article.model.Price', name: 'getPrice', associationKey: 'prices' },
        { type: 'hasMany', model: 'Shopware.apps.Article.model.ConfiguratorOption', name: 'getConfiguratorOptions', associationKey: 'configuratorOptions' },
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
            destroy: '{url action="deleteDetail"}',
            update: '{url action="saveDetail"}',
            create: '{url action="saveDetail"}'
        }
    }
});
//{/block}
