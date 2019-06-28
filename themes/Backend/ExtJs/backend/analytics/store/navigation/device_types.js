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
 * Analytics Device Types Store
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 */
//{block name="backend/analytics/store/navigation/device_types"}
Ext.define('Shopware.apps.Analytics.store.navigation.DeviceTypes', {
    extend: 'Ext.data.Store',
    alias: 'widget.analytics-store-navigation-device-types',
    remoteSort: true,
    fields: [
        { name: 'deviceType', type: 'string' },
        {
            name : 'deviceTypeHuman',
            type: 'string',
            convert: function(value, record) {
                var deviceType = record.get('deviceType');

                if (deviceType.length) {
                    return deviceType.charAt(0).toUpperCase() + deviceType.slice(1);
                } else {
                    return deviceType;
                }
            }
        },
        { name: 'turnover', type: 'float' }
    ],
    proxy: {
        type: 'ajax',
        url: '{url controller=analytics action=getDevice}',
        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    },

    constructor: function (config) {
        var me = this;
        config.fields = me.fields;

        if (config.shopStore) {
            config.shopStore.each(function (shop) {
                config.fields.push('turnover' + shop.data.id);
            });
        }

        me.callParent(arguments);
    }
});
//{/block}
