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
        licence_upgrade_warning: '[0]x plugin(s) require a licence upgrade.<br /><br /><b>Required upgrades:</b><br />[1]',
        subscription_warning: 'Subscription(s) for [0]x plugin(s) are expired. <br /><br /><b>Expired plugins:</b><br />[1]',
        expired_soon_subscription_warning: 'Subscription(s) for [0]x plugin(s) expire in a few days.<br /><br /><b>Soon expired plugins:</b><br />[1]',
        expired_soon_subscription_days_warning: ' days',
        invalid_licence: 'Licence(s) of [0]x plugin(s) are invalid.<br /><br /><b>Invalid licence(s):</b><br />[1]',
        shop_license_upgrade : 'The license upgrade for the shop hasn\'t been executed yet.'
    },

    /**
     * Check if any plugins need to be upgraded
     */
    check: function () {
        var me = this;

        me.getPluginsSubscriptionState(function (data) {
            data.expiredPluginSubscriptions.sort(me.sortPluginsByDaysLeftCallback);
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

        var expiredPlugins = me.filterExpiredPlugins(data.expiredPluginSubscriptions, true);
        if (expiredPlugins && expiredPlugins.length > 0) {
            me.displaySubscriptionNotice(expiredPlugins);
        }

        var soonExpiredPlugins = me.filterExpiredPlugins(data.expiredPluginSubscriptions, false);
        if (soonExpiredPlugins && soonExpiredPlugins.length > 0) {
            me.displayExpiredSoonSubscriptionNotice(soonExpiredPlugins);
        }
    },

    displayWrongVersionNotice: function (plugins) {
        var me          = this,
            pluginNames = me.getPluginNamesMessage(plugins, '<br />');

        Shopware.Notification.createStickyGrowlMessage({
            text: Ext.String.format(me.snippets.invalid_licence, plugins.length, pluginNames),
            width: 440
        });
    },

    displayNotUpgradedNotice: function (plugins) {
        var me          = this,
            pluginNames = me.getPluginNamesMessage(plugins, '<br />');

        Shopware.Notification.createStickyGrowlMessage({
            text: Ext.String.format(me.snippets.licence_upgrade_warning, plugins.length, pluginNames),
            width: 440
        });

    },

    displaySubscriptionNotice: function (plugins) {
        var me          = this,
            pluginNames = me.getPluginNamesMessage(plugins, '<br />');

        Shopware.Notification.createStickyGrowlMessage({
            text: Ext.String.format(me.snippets.subscription_warning, plugins.length, pluginNames),
            width: 440
        });
    },

    displayExpiredSoonSubscriptionNotice: function(plugins) {
        var me          = this,
            pluginNames = me.getSoonExpiredPluginNamesMessage(plugins, '<br />');

        Shopware.Notification.createStickyGrowlMessage({
            text: Ext.String.format(me.snippets.expired_soon_subscription_warning, plugins.length, pluginNames),
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

    /**
     * Creates a string with all plugin names as a string separated by a defined separator, the default value for the
     * separator is a ','
     * @param plugins
     * @param separator
     * @returns [string]
     */
    getPluginNamesMessage: function (plugins, separator) {
        separator = (typeof separator == 'undefined' ? ',' : separator);

        var pluginNameList = plugins.map(function (plugin) {
            return plugin.label;
        });
        return pluginNameList.join(separator);
    },

    /**
     * Filters the plugin array for expired plugins or soon expired plugins
     * @param plugins
     * @param expired
     * @returns [object]
     */
    filterExpiredPlugins: function (plugins, expired) {
        if (expired) {
            return plugins.filter(function (plugin) {
                if (plugin.expired && plugin.daysLeft == 0) {
                    return plugin;
                }
            });
        } else {
            return plugins.filter(function (plugin) {
                if (!plugin.expired && plugin.daysLeft > 0) {
                    return plugin;
                }
            });
        }
    },

    /**
     * Returns an array with all plugins names including the days left until the subscription will expire
     * @param plugins
     * @param separator
     * @returns [string]
     */
    getSoonExpiredPluginNamesMessage: function (plugins, separator) {
        var me = this;
        separator = (typeof separator == 'undefined' ? ',' : separator);

        var pluginNameList = plugins.map(function (plugin) {
            return plugin.label + ' (' + plugin.daysLeft + me.snippets.expired_soon_subscription_days_warning + ')';
        });

        return pluginNameList.join(separator);
    },

    /**
    * Sorts the plugins by days left ascending
    * @param a
    * @param b
    * @returns [number]
    */
    sortPluginsByDaysLeftCallback: function(a, b) {
        if (a.daysLeft < b.daysLeft) {
            return -1;
        }
        if (a.daysLeft > b.daysLeft) {
            return 1;
        }
        return 0;
    }
});