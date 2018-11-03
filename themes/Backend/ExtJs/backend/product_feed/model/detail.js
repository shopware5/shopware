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
 * @package    ProductFeed
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Model -  product feed detail backend module.
 *
 * The DetailModel holds all feed data for the detail page
 */
//{block name="backend/product_feed/model/detail"}
Ext.define('Shopware.apps.ProductFeed.model.Detail', {
    /**
    * Extends the standard ExtJS 4
    * @string
    */
    extend : 'Ext.data.Model',
    /**
    * The fields used for this model
    * @array
    */
    fields : [
        //{block name="backend/product_feed/model/detail/fields"}{/block}
        { name : 'id', type : 'int' },
        { name : 'name', type : 'string' },
        { name : 'fileName', type : 'string' },
        { name : 'partnerId', type : 'string' },
        { name : 'hash', type : 'string' },
        { name : 'active', type : 'int' },
        { name : 'variantExport', type : 'int', useNull:true },
        { name : 'customerGroupId', type : 'int', useNull:true },
        { name : 'languageId', type : 'int', useNull:true },
        { name : 'categoryId', type : 'int', useNull:true },
        { name : 'currencyId', type : 'int', useNull:true },
        { name : 'show', type : 'int' },
        { name : 'countArticles', type : 'int' },
        { name : 'expiry', type : 'date' },
        { name : 'interval', type : 'int', useNull: true },
        { name : 'informTemplate', type : 'int' },
        { name : 'informMail', type : 'int' },
        { name : 'encodingId', type : 'int'},
        { name : 'header', type : 'string'},
        { name : 'body', type : 'string'},
        { name : 'footer', type : 'string'},
        { name : 'priceFilter', type : 'double'},
        { name : 'instockFilter', type : 'int'},
        { name : 'countFilter', type : 'int'},
        { name : 'stockMinFilter', type : 'int'},
        { name : 'activeFilter', type : 'int'},
        { name : 'imageFilter', type : 'int'},
        { name : 'ownFilter', type : 'string'},
        { name : 'formatId', type : 'int'},
        { name : 'lastExport' },
        { name : 'cacheRefreshed' }
    ],
    /**
    * If the name of the field is 'id' extjs assumes autmagical that
    * this field is an unique identifier.
    */
    idProperty : 'id',
    /**
    * Configure the data communication
    * @object
    */
    proxy : {
        type : 'ajax',
        api:{
            read:   '{url action=getDetailFeed}',
            create: '{url action=saveFeed}',
            update: '{url action=saveFeed}',
            destroy:'{url action=deleteFeed}'
        },
        reader : {
            type : 'json',
            root : 'data'
        }
    },
    /**
     * Define the associations of the export model.
     * One customer has a billing, shipping address and a debit information.
     * @array
     */
    associations:[
        {
            type:'hasMany',
            model:'Shopware.apps.ProductFeed.model.Category',
            name:'getCategories',
            associationKey:'categories'
        },
        {
            type:'hasMany',
            model:'Shopware.apps.Base.model.Supplier',
            name:'getSuppliers',
            associationKey:'suppliers'
        },
        {
            type:'hasMany',
            model:'Shopware.apps.Base.model.Article',
            name:'getArticles',
            associationKey:'articles'
        }
    ]
});
//{/block}
