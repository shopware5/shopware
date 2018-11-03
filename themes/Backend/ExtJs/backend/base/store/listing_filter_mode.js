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
        displayFullPageReload: true,
        displayReloadProductsMode: true,
        displayReloadFiltersMode: true
    },

    fullPageReload: {
        key: 'full_page_reload',
        label: '{s name=listing_mode_reload_label}{/s}',
        description: '{s name=listing_mode_reload_description}{/s}',
        image: '{link file="backend/_resources/images/listing_mode/full_page_reload.jpg"}'
    },

    reloadProductsMode: {
        key: 'product_ajax_reload',
        label: '{s name=listing_mode_product_reload_label}{/s}',
        description: '{s name=listing_mode_product_reload_description}{/s}',
        image: '{link file="backend/_resources/images/listing_mode/product_ajax_reload.jpg"}'
    },

    reloadFiltersMode: {
        key: 'filter_ajax_reload',
        label: '{s name=listing_mode_filter_reload_label}{/s}',
        description: '{s name=listing_mode_filter_reload_description}{/s}',
        image: '{link file="backend/_resources/images/listing_mode/filter_ajax_reload.jpg"}'
    },

    constructor: function(config) {
        var me = this,
            data = [];

        if (this.getConfigValue(config, 'displayFullPageReload')) {
            data.push(me.fullPageReload);
        }
        if (this.getConfigValue(config, 'displayReloadProductsMode')) {
            data.push(me.reloadProductsMode);
        }
        if (this.getConfigValue(config, 'displayReloadFiltersMode')) {
            data.push(me.reloadFiltersMode);
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
