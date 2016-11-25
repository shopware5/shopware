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


//{namespace name=backend/base/listing_filter_mode}

Ext.define('Shopware.apps.Base.store.ListingFilterMode', {
    extend: 'Ext.data.Store',

    alternateClassName: 'Shopware.store.ListingFilterMode',

    storeId: 'base.ListingFilterMode',

    fields: [
        { name: 'key', type: 'string' },
        { name: 'label', type: 'string' },
        { name: 'description', type: 'string' },
        { name: 'image', type: 'string' }
    ],

    pageSize: 1000,

    defaultModes: {
        fullPageReload: true,
        reloadProductsMode: true,
        reloadFiltersMode: true
    },

    snippets: {
        fullPageReload: {
            label: '{s name=listing_mode_reload_label}{/s}',
            description: '{s name=listing_mode_reload_description}{/s}'
        },
        reloadProductsMode: {
            label: '{s name=listing_mode_product_reload_label}{/s}',
            description: '{s name=listing_mode_product_reload_description}{/s}'
        },
        reloadFiltersMode: {
            label: '{s name=listing_mode_filter_reload_label}{/s}',
            description: '{s name=listing_mode_filter_reload_description}{/s}'
        }
    },

    constructor: function(config) {
        var me = this,
            data = [];

        if (this.getConfigValue(config, 'fullPageReload')) {
            data.push({
                key: 'full_page_reload',
                label: me.snippets.fullPageReload.label,
                description: me.snippets.fullPageReload.description,
                image: '{link file="backend/_resources/images/category/layout_box_parent.png"}'
            });
        }
        if (this.getConfigValue(config, 'reloadProductsMode')) {
            data.push({
                key: 'product_ajax_reload',
                label: me.snippets.reloadProductsMode.label,
                description: me.snippets.reloadProductsMode.description,
                image: '{link file="backend/_resources/images/category/layout_box_basic.png"}'
            });
        }
        if (this.getConfigValue(config, 'reloadFiltersMode')) {
            data.push({
                key: 'filter_ajax_reload',
                label: me.snippets.reloadFiltersMode.label,
                description: me.snippets.reloadFiltersMode.description,
                image: '{link file="backend/_resources/images/category/layout_box_minimal.png"}'
            });
        }

        this.data = data;
        this.callParent(arguments);
    },

    getConfigValue: function(config, property) {
        if (!Ext.isObject(config)) {
            return this.defaultModes[property];
        }

        if (!config.hasOwnProperty(property)) {
            return this.defaultModes[property];
        }

        return config[property];
    }
});
