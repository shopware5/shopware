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
 * @package    Base
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Model
 *
 * This Model is extended from the Shopware.model.Category to add
 * a proxy configuration.
 *
 */
//{block name="backend/category/model/detail"}
Ext.define('Shopware.apps.Category.model.Detail', {

    /**
     * Extends the standard Ext Model
     * @string
     */
    extend:'Shopware.model.Category',
    /**
     * Configure the data communication
     * @object
     */
    fields:[
        // {block name="backend/category/model/detail/fields"}{/block}
        { name : 'id', type: 'integer', useNull:true },
        { name : 'parentId', type: 'integer' },
        { name : 'streamId', type: 'integer', useNull:true, defaultValue: null },
        { name : 'name', type: 'string' },
        { name : 'position', type: 'integer', useNull:true, defaultValue: null},
        { name : 'metaKeywords', type: 'string', useNull:true, defaultValue: null },
        { name : 'metaTitle', type: 'string', useNull:true, defaultValue: null },
        { name : 'metaDescription', type: 'string', useNull:true, defaultValue: null },
        { name : 'cmsHeadline', type: 'string', useNull:true, defaultValue: null },
        { name : 'cmsText', type: 'string', useNull:true, defaultValue: null },
        {
            name : 'template',
            type: 'string',
            useNull:true,
            defaultValue: null,
            convert: function(v, record) {
                if (v == null) {
                    return '';
                }
                return v;
            },
            serialize: function(v, record) {
                if (v == '') {
                    return null;
                }
                return v;
            }
        },
        { name : 'productBoxLayout', type: 'string', useNull:true, defaultValue: null },
        { name : 'active', type: 'boolean' },
        { name : 'blog', type: 'boolean' },
        { name : 'external', type: 'string' , useNull:true, defaultValue: null},
        { name : 'externalTarget', type: 'string', defaultValue: '' },
        { name : 'hideFilter', type: 'boolean' },
        { name : 'hideTop', type: 'boolean' },
        { name : 'imagePath', type: 'string' , useNull:true, defaultValue: null},
        { name : 'hideSortings', type: 'boolean' },
        { name : 'sortingIds', type: 'string' },
        { name : 'facetIds', type: 'string' },
        { name : 'shops', type: 'auto' },
    ],

    proxy : {
        type : 'ajax',

        /**
         * Configure the url mapping for the different
         * store operations based on
         * @object
         */
        api : {
            read : '{url controller=category action=getDetail}',
            create  : '{url controller=category action=createDetail}',
            update  : '{url controller=category action=updateDetail}'

        },
        /**
         * Configure the data reader
         * @object
         */
        reader : {
            type : 'json',
            root: 'data'
        }
    },
    /**
     * Define the associations of the category model
     * @array
     */
    associations:[
        {
            type:'hasMany',
            model:'Shopware.apps.Category.model.Emotion',
            name:'getEmotion',
            associationKey:'emotion'
        },
        {
            type:'hasMany',
            model:'Shopware.apps.Base.model.CustomerGroup',
            name:'getCustomerGroups',
            associationKey:'customerGroups'
        }
    ]
});
//{/block}
