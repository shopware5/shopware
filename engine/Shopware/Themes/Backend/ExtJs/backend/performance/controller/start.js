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
    infoTitleProductiveMode: '{s name=tabs/start/productive_mode}{/s}',
    infoMessageProductiveMode1Success: '{s name=tabs/start/productive_mode_active}{/s}',
    infoMessageProductiveMode0Success: '{s name=tabs/start/productive_mode_inactive}{/s}',
    errorTitle: '{s name=errorTitle}{/s}',
    httpCacheError: '{s name=tabs/start/errorHttpCache}{/s}',

    running: false,

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
                'init-toggle-productive': me.getProductiveMode,
                'toggle-productive': me.toggleProductive
            }
        });

        me.callParent(arguments);
    },

    /**
     * function to set toggle-button-state in the view on startup
     * @param Ext.Component button
     */
    getProductiveMode: function (button) {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller=Performance action=getProductiveMode}',
            success: function (operation, opts) {
                var response = Ext.decode(operation.responseText);

                if (response.success == true) {
                    button.show();

                    me.setToggleButtonState(
                        button,
                        response.productiveMode
                    );
                }
            }
        });
    },

    /**
     * set productive mode active/unactive
     * @param Ext.Component button
     */
    toggleProductive: function (button) {
        var me = this;

        if (!button) {
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
                    me.toggleProductiveCallback(button);
                } else {
                    if (response.state == 'not_found') {
                        Shopware.Notification.createGrowlMessage(
                            me.errorTitle,
                            me.httpCacheError
                        );
                    }
                }
            }
        });
    },

    /**
     * callback function for toggleProductive
     * @param Ext.Component button
     */
    toggleProductiveCallback: function (button) {
        var me = this;

        var message = me.infoMessageProductiveMode1Success;
        if (button.hasCls('active')) {
            message = me.infoMessageProductiveMode0Success;
            me.setToggleButtonState(button, false);
        } else {
            me.setToggleButtonState(button, true);
        }

        Shopware.Notification.createGrowlMessage(
            me.infoTitleProductiveMode,
            message,
            me.infoTitleProductiveMode
        );
    },

    /**
     * set state of toggle button
     * @param Ext.Component button
     * @param boolean state
     */
    setToggleButtonState: function (button, state) {
        if (state == true) {
            button.addCls('active');
        } else {
            button.removeCls('active');
        }
    },

    /**
     * clear whole cache
     */
    clearWholeCache: function () {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller=Cache action=clearDirect}',
            params: {
                cache: 'Config'
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