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
 * The cache controller takes care of cache related events and also
 * handles the category fixing
 */

//{namespace name=backend/performance/main}
//{block name="backend/performance/controller/start"}
Ext.define('Shopware.apps.Performance.controller.Start', {
    extend: 'Enlight.app.Controller',

    infoTitle: '{s name=form/message_title}Shop cache{/s}',
    infoMessageSuccess: '{s name=form/message}Shop cache has been cleared.{/s}',
    infoTitlePerformanceMode: '{s name=tabs/start/performance_mode}{/s}',
    infoMessageProductionMode: '{s name=tabs/start/production_mode_active}{/s}',
    infoMessageDevelopmentMode: '{s name=tabs/start/development_mode_active}{/s}',
    errorTitle: '{s name=errorTitle}{/s}',
    httpCacheError: '{s name=tabs/start/errorHttpCache}{/s}',

    running: false,
    state: null,

    /**
     * init events
     */
    init: function () {
        var me = this;

        me.control({
            'performance-tabs-start-main button[action=clear-all]': {
                click: me.clearWholeCache
            },
            'performance-tabs-start-main': {
                'init-toggle-productive': me.getPerformanceMode,
                'toggle-productive': me.toggleProductive
            }
        });

        me.callParent(arguments);
    },

    /**
     * function to set toggle-button-state in the view on startup
     * @param Shopware.apps.Performance.view.tabs.start.Main window
     */
    getPerformanceMode: function (window) {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller=Performance action=getProductiveMode}',
            success: function (operation, opts) {
                var response = Ext.decode(operation.responseText);

                if (response.success == true) {
                    me.state = response.productiveMode;
                    window.setState(me.state);
                }
            }
        });
    },

    /**
     * set productive mode active/unactive
     * @param Shopware.apps.Performance.view.tabs.start.Main window
     */
    toggleProductive: function (window) {
        var me = this;

        if (!window) {
            return;
        }

        if (me.running) {
            return;
        }

        me.running = true;

        Ext.Ajax.request({
            url: '{url controller=Performance action=toggleProductiveMode}',
            success: function (operation, opts) {
                var response = Ext.decode(operation.responseText);
                me.running = false;

                if (response.success == true) {
                    me.toggleProductiveCallback(window);
                } else {
                    if (response.state == 'not_found') {
                        Shopware.Notification.createGrowlMessage(
                            me.errorTitle,
                            me.httpCacheError
                        );
                        window.resetState();
                    }
                }
            }
        });
    },

    /**
     * callback function for toggleProductive
     * @param Shopware.apps.Performance.view.tabs.start.Main window
     */
    toggleProductiveCallback: function (window) {
        var me = this,
            message;

        if (me.state) {
            message = me.infoMessageDevelopmentMode;
        } else {
            message = me.infoMessageProductionMode;
        }

        me.state = !me.state;

        Shopware.Notification.createGrowlMessage(
            me.infoTitlePerformanceMode,
            message,
            me.infoTitlePerformanceMode
        );
    },

    /**
     * clear whole cache
     */
    clearWholeCache: function () {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller=Cache action=clearCache}?cache=Config',
            params:{
              'cache[config]'   : 'on',
              'cache[template]' : 'on',
              'cache[search]'   : 'on',
              'cache[router]'   : 'on'
            },
            success: function () {
                me.reloadInfoStore();
            }
        });
    },

    /**
     * reload cache-info-store and give success-message
     */
    reloadInfoStore: function () {
        var me = this;

        Ext.getStore('Info').load({
            callback: function (records, operation) {
                Shopware.Notification.createGrowlMessage(
                    me.infoTitle,
                    me.infoMessageSuccess,
                    me.infoTitle
                );
            }
        });
    }
});
//{/block}
