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
 */

/**
 * Shopware Model - Filter model
 *
 * The filter model represents a single filter
 */
//{namespace name=backend/article_list/main}
//{block name="backend/article_list/model/filter"}
Ext.define('Shopware.apps.ArticleList.model.Filter', {
    /**
     * Extends the standard Ext Model
     *
     * @string
     */
    extend: 'Ext.data.Model',

    /**
     * The fields used for this model
     *
     * @array
     */
    fields: [
        //{block name="backend/article_list/model/filter/fields"}{/block}
        { name: 'id', type: 'int', useNull: true },
        { name: 'name', type: 'string' },
        { name: 'filterString', type: 'string' },
        { name: 'description', type: 'string' },
        { name: 'create', type: 'datetime' },
        { name: 'isFavorite', type: 'boolean' },
        { name: 'isSimple', type: 'boolean' },
        {
            name : 'groupName',
            type: 'string',
            convert : function(value, record) {
                return record.get('isFavorite') ? '{s name=group_favorite}Favorite{/s}' : '{s name=group_filter}Filter{/s}';
            }
        }
    ],

    /**
     * Configure the data communication
     * @object
     */
    proxy:{
        /**
         * Set proxy type to ajax
         *
         * @string
         */
        type:'ajax',

        /**
         * Configure the url mapping for the different
         * store operations based on
         *
         * @object
         */
        api: {
            create: '{url controller="ArticleList" action="saveFilter"}',
            update: '{url controller="ArticleList" action="saveFilter"}',
            destroy: '{url controller="ArticleList" action="deleteFilter"}'
        },

        /**
         * Configure the data reader
         *
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
