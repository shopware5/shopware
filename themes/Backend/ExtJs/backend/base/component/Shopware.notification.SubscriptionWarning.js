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

//{namespace name=backend/index/view/menu}

Ext.define('Shopware.notification.SubscriptionWarning', {

    snippets: {
        plugin_not_upgraded : '{s name="shop_license_upgrade"}The license upgrade for plugin [0] is pending.{/s}',
        plugins_not_upgraded : '{s name="shop_license_upgrade"}The license upgrades for the following plugins are pending:{/s}',
        plugin_wrong_version : '{s name="shop_license_upgrade"}The license for plugin [0] is invalid.{/s}',
        plugins_wrong_version : '{s name="shop_license_upgrade"}The licenses for the following plugins are invalid:{/s}',
        plugin_subscription_warning : '{s name="shop_license_upgrade"}The subscription for plugin [0] has expired.{/s}',
        plugin_subscription_warning_days : '{s name="shop_license_upgrade"}The subscription for plugin [1] expires in [0] days.{/s}',
        shop_license_upgrade : '{s name="shop_license_upgrade"}The license upgrade for the shop hasn\'t been executed yet.{/s}'
    },

    /**
     * Check if any plugins need to be upgraded
     */
    check: function () {
        var me = this;

        me.getPluginsSubscriptionState(function (data) {
            me.displayNotices(data);
        });
    },

    checkSecret: function () {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller="PluginManager" action="checkSecret"}'
        });
    },

    getPluginsSubscriptionState: function (callback) {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller="PluginManager" action="getPluginsSubscriptionState"}',
            success: function (response) {
                var responseData = Ext.decode(response.responseText);

                if (!responseData || Ext.isEmpty(responseData.data)) {
                    return;
                }

                if (responseData.success === true && responseData.data) {
                    callback(responseData.data);
                }
            }
        });
    },

    displayNotUpgradedNotice: function (plugins) {
        var me = this,
            text = '',
            len = plugins.length,
            x = 0;

        if (len === 1) {
            text += Ext.String.format('<b>' + me.snippets.plugin_not_upgraded + '</b></br>', '</b><i>' + plugins[0].label + '</i><b>');
        } else {
            text += '<b>' + me.snippets.plugins_not_upgraded + '</b><br/>';

            for (; x < len; x++) {
                text += '<i>' + plugins[x].label + '</i>';

                if (x < len - 1) {
                    text += '<br/>';
                }
            }
        }

        Shopware.Notification.createStickyGrowlMessage({
            text: text,
            width: 440
        });
    },

    getPluginList: function (plugins) {
        return '<i>' + plugins.join('</i><br/><i>') + '</i>';
    },

    displayWrongVersionNotice: function (plugins) {
        var me = this,
            text = '',
            len = plugins.length,
            x = 0;

        if (len === 1) {
            text += Ext.String.format('<b>' + me.snippets.plugin_wrong_version + '</b></br>', '</b><i>' + plugins[0].label + '</i><b>');
        } else {
            text += '<b>' + me.snippets.plugins_wrong_version + '</b><br/>';

            for (; x < len; x++) {
                text += '<i>' + plugins[x].label + '</i>';
                if (x < len - 1) {
                    text += '<br/>';
                }
            }
        }

        Shopware.Notification.createStickyGrowlMessage({
            text: text,
            width: 440
        });
    },

    displaySubscriptionNotice: function (plugins) {
        var me = this,
            text = '',
            len = plugins.length,
            x = 0;

        for (; x < len; x++) {
            if (plugins[x].expired == true) {
                text += Ext.String.format('<b>' + me.snippets.plugin_subscription_warning + '</b></br>', '</b><i>' + plugins[0].label + '</i><b>');
            } else {
                text += Ext.String.format('<b>' + me.snippets.plugin_subscription_warning_days + '</b></br>', '</b><i>' + plugins[0].daysLeft + '</i><b>', '</b><i>' + plugins[0].label + '</i><b>');
            }

            if (x < len - 1) {
                text += '<br/>';
            }
        }

        Shopware.Notification.createStickyGrowlMessage({
            text: text,
            width: 440
        });
    },

    displayShopNotUpgradedShopMessage: function (data) {
        var me = this;

        Shopware.Notification.createStickyGrowlMessage({
            text: '<b>' + me.snippets.shop_license_upgrade + '</b>',
            width: 440
        });
    },

    displayNotices: function (data) {
        var me = this;

        if(data.isShopUpgraded == false) {
            me.displayShopNotUpgradedShopMessage();
        }

        if (data.notUpgradedPlugins && data.notUpgradedPlugins.length > 0) {
            me.displayNotUpgradedNotice(data.notUpgradedPlugins);
        }

        if (data.wrongVersionPlugins && data.wrongVersionPlugins.length > 0) {
            me.displayWrongVersionNotice(data.wrongVersionPlugins);
        }

        if (data.expiredPluginSubscriptions && data.expiredPluginSubscriptions.length > 0) {
            me.displaySubscriptionNotice(data.expiredPluginSubscriptions);
        }
    }
});