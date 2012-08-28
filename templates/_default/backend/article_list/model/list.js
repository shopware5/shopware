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
 * @package    ArticleList
 * @subpackage Article
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * shopware AG (c) 2012. All rights reserved.
 *
 * todo@all: Documentation
 */
//{block name="backend/article_list/model/list"}
Ext.define('Shopware.apps.ArticleList.model.List', {
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
		//{block name="backend/article_list/model/list/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'articleId', type: 'int' },

        { name: 'number',   type: 'string' },
        { name: 'name',     type: 'string' },
        { name: 'supplier', type: 'string' },
        { name: 'additionalText', type: 'string' },

        { name: 'tax',      type: 'string' },
        { name: 'price',    type: 'string' },

        { name: 'active',   type: 'boolean' },
        { name: 'inStock',  type: 'int' },
        { name: 'imageSrc', type: 'string' },

        { name: 'hasVariants',      type: 'boolean' },
        { name: 'hasConfigurator',  type: 'boolean' },
        { name: 'hasCategories',    type: 'boolean' }
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
            read:    '{url action="list"}',
            update:  '{url action="update"}',
            destroy: '{url action="delete"}'
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
