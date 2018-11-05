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
 * @subpackage Store
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Store - Global Stores and Models
 */
//{namespace name=backend/base/product_box_layout}
//{block name="backend/base/store/product_box_layout"}
Ext.define('Shopware.apps.Base.store.ProductBoxLayout', {
    extend: 'Ext.data.Store',

    alternateClassName: 'Shopware.store.ProductBoxLayout',

    storeId: 'base.ProductBoxLayout',

    model: 'Shopware.apps.Base.model.ProductBoxLayout',

    pageSize: 1000,

    defaultLayouts: {
        displayExtendLayout: false,
        displayBasicLayout: true,
        displayMinimalLayout: true,
        displayImageLayout: true,
        displayListLayout: false
    },

    snippets: {
        displayExtendLayout: {
            label: '{s name=settings_box_layout_parent_title}Parent setting{/s}',
            description: '{s name=settings_box_layout_parent_description}The layout of the product box will be set by the value of the parent category.{/s}'
        },
        displayBasicLayout: {
            label: '{s name=settings_box_layout_basic_title}Detailed information{/s}',
            description: '{s name=settings_box_layout_basic_description}The layout of the product box will show very detailed information.{/s}'
        },
        displayMinimalLayout: {
            label: '{s name=settings_box_layout_minimal_title}Only important information{/s}',
            description: '{s name=settings_box_layout_minimal_description}The layout of the product box will only show the most important information.{/s}'
        },
        displayImageLayout: {
            label: '{s name=settings_box_layout_image_title}Big image{/s}',
            description: '{s name=settings_box_layout_image_description}The layout of the product box is based on a big image of the product.{/s}'
        },
        displayListLayout: {
            label: '{s name=settings_box_layout_list_title}Product list{/s}',
            description: '{s name=settings_box_layout_list_description}The layout of the product box shows a small image and only one product in a row.{/s}'
        }
    },

    constructor: function(config) {
        var me = this,
            data = [];

        data = me.createLayoutData(config);
        me.data = data;

        me.callParent(arguments);
    },

    createLayoutData: function(config) {
        var me = this,
            data = [];

        if (me.getConfigValue(config, 'displayExtendLayout')) {
            data.push({
                key: 'extend',
                label: me.snippets.displayExtendLayout.label,
                description: me.snippets.displayExtendLayout.description,
                image: '{link file="backend/_resources/images/category/layout_box_parent.png"}'
            });
        }
        if (me.getConfigValue(config, 'displayBasicLayout')) {
            data.push({
                key: 'basic',
                label: me.snippets.displayBasicLayout.label,
                description: me.snippets.displayBasicLayout.description,
                image: '{link file="backend/_resources/images/category/layout_box_basic.png"}'
            });
        }
        if (me.getConfigValue(config, 'displayMinimalLayout')) {
            data.push({
                key: 'minimal',
                label: me.snippets.displayMinimalLayout.label,
                description: me.snippets.displayMinimalLayout.description,
                image: '{link file="backend/_resources/images/category/layout_box_minimal.png"}'
            });
        }
        if (me.getConfigValue(config, 'displayImageLayout')) {
            data.push({
                key: 'image',
                label: me.snippets.displayImageLayout.label,
                description: me.snippets.displayImageLayout.description,
                image: '{link file="backend/_resources/images/category/layout_box_image.png"}'
            });
        }
        if (me.getConfigValue(config, 'displayListLayout')) {
            data.push({
                key: 'list',
                label: me.snippets.displayListLayout.label,
                description: me.snippets.displayListLayout.description,
                image: '{link file="backend/_resources/images/category/layout_box_list.png"}'
            });
        }

        return data;
    },

    getConfigValue: function(config, property) {
        if (!Ext.isObject(config)) {
            return this.defaultLayouts[property];
        }

        if (!config.hasOwnProperty(property)) {
            return this.defaultLayouts[property];
        }

        return config[property];
    }

});
//{/block}
