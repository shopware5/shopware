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
 * Shopware Model - Article models.
 * The article model contains all data about the article. Additional the model
 * contains the definition of the model associations. The article model
 * contains a proxy to define which request has to been sent to save, update and delete
 * the article.
 */
//{block name="backend/article/model/article"}
Ext.define('Shopware.apps.Article.model.Article', {
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
        { name: 'mainDetailId', type: 'int', useNull: true },
        { name: 'configuratorSetId', type: 'integer', useNull: true },
        { name: 'supplierId', type: 'int', useNull: true },
        { name: 'supplierName', type: 'string' },
        { name: 'name', type: 'string' },
        { name: 'description', type: 'string', useNull: true },
        { name: 'descriptionLong', type: 'string' },
        { name: 'metaTitle', type: 'string', useNull: true },
        { name: 'keywords', type: 'string', useNull: true },
        { name: 'added', type: 'date', dateFormat: 'd.m.Y' },
        { name: 'changed', type: 'date', dateFormat: 'c' },
        { name: 'active', type: 'boolean', defaultValue: true },
        { name: 'taxId', type: 'int', useNull: true },
        { name: 'highlight', type: 'boolean' },
        { name: 'pseudoSales', type: 'int' },
        { name: 'priceGroupId', type: 'int', useNull: true },
        { name: 'priceGroupActive', type: 'boolean' },
        { name: 'filterGroupId', type: 'int', useNull: true },
        { name: 'lastStock', type: 'boolean' },
        { name: 'notification', type: 'boolean' },
        { name: 'template', type: 'string', useNull: true },
        { name: 'autoNumber', type: 'string' },
        {
            name: 'avoidCustomerGroups',
            type: 'string',
            useNull: true,
            convert: function(value, record) {
                if (value) {
                    return value;
                }
                if (record && record.raw && record.raw.customerGroups) {
                    var customerGroups = [];
                    Ext.each(record.raw.customerGroups, function(customerGroup) {
                        customerGroups.push(customerGroup.id);
                    });
                    return customerGroups;
                }
                return null;
            }
        },
        {
            name: 'isConfigurator',
            type: 'boolean',
            convert: function(value, record) {
                if (value === true || value === false) {
                    return value;
                }
                return (record.get('configuratorSetId') !== null);
            }
        }
    ],
    associations: [
        //{block name="backend/article/model/article/associations"}{/block}
        { type: 'hasMany', model: 'Shopware.apps.Article.model.Detail', name: 'getMainDetail', associationKey: 'mainDetail' },
        { type: 'hasMany', model: 'Shopware.apps.Article.model.Category', name: 'getCategory', associationKey: 'categories' },
        { type: 'hasMany', model: 'Shopware.apps.Article.model.SeoCategory', name: 'getSeoCategories', associationKey: 'seoCategories' },
        { type: 'hasMany', model: 'Shopware.apps.Article.model.Accessory', name: 'getAccessory', associationKey: 'related' },
        { type: 'hasMany', model: 'Shopware.apps.Article.model.Similar', name: 'getSimilar', associationKey: 'similar' },
        { type: 'hasMany', model: 'Shopware.apps.Article.model.Stream', name: 'getStreams', associationKey: 'streams'},
        { type: 'hasMany', model: 'Shopware.apps.Article.model.Link', name: 'getLink', associationKey: 'links' },
        { type: 'hasMany', model: 'Shopware.apps.Article.model.Media', name: 'getMedia', associationKey: 'images' },
        { type: 'hasMany', model: 'Shopware.apps.Article.model.Download', name: 'getDownload', associationKey: 'downloads' },
        { type: 'hasMany', model: 'Shopware.apps.Article.model.Price', name: 'getPrice', associationKey: 'mainPrices' },
        { type: 'hasMany', model: 'Shopware.apps.Article.model.ConfiguratorTemplate', name: 'getConfiguratorTemplate', associationKey: 'configuratorTemplate' },
        { type: 'hasMany', model: 'Shopware.apps.Article.model.ConfiguratorSet', name:'getConfiguratorSet', associationKey:'configuratorSet' },
        { type: 'hasMany', model: 'Shopware.apps.Article.model.Dependency', name: 'getDependencies', associationKey: 'dependencies' },
        { type: 'hasMany', model: 'Shopware.apps.Article.model.PriceSurcharge', name: 'getPriceSurcharges', associationKey: 'priceSurcharges' },
        { type: 'hasMany', model: 'Shopware.apps.Base.model.CustomerGroup', name: 'getCustomerGroups', associationKey: 'customerGroups' }
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
            create: '{url action="save"}',
            update: '{url action="save"}',
            destroy: '{url action="delete"}'
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
