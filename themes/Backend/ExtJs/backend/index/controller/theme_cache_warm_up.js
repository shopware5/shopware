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
 * Shopware Core - Theme cache warm up controller
 *
 * This class handles all theme cache warm up actions
 */
//{namespace name=backend/index/controller/theme_cache_warm_up}
//{block name="backend/index/controller/theme_cache_warm_up"}
Ext.define('Shopware.apps.Index.controller.ThemeCacheWarmUp', {
    extend: 'Ext.app.Controller',

    /**
     * Shop store
     */
    shopStore: null,

    /**
     * Url to which the theme cache warm up requests are sent
     */
    requestUrl: '{url controller="cache" action="themeCacheWarmUp"}',

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the subapplication.
     *
     * @public
     * @constructor
     * @return void
     */
    init: function() {
        var me = this;

        me.control({
            'theme-cache-warm-up-window': {
                'themeCacheWarmUpStartProcess': me.onThemeCacheWarmUpStartProcess,
                'themeCacheWarmUpCancelProcess': me.onThemeCacheWarmUpCancelProcess
            }
        });

        Shopware.app.Application.on('shopware-theme-cache-warm-up-request', function(shopId, forceShow) {
            me.forceShow = forceShow;

            me.window = Ext.create('Shopware.apps.Index.view.themeCache.ThemeCacheWarmUp');

            me.shopStore = Ext.create('Shopware.apps.Index.store.ThemeCacheWarmUp');

            if (Ext.isNumber(shopId)) {
                me.shopStore.getProxy().extraParams.shopId = shopId;

                me.window.setSingleShopId(shopId);
                me.window.shopSelector.hide();
            }

            me.shopStore.load({
                callback: function(records, operation, success) {
                    if (records.length == 0 && forceShow !== true) {
                        me.window.close();
                    } else {
                        me.window.setShops(records);
                        if (me.window.shopSelector.isVisible()) {
                            me.window.shopSelector.bindStore(me.shopStore);
                        }
                        me.window.show();
                    }
                }
            });
        });
    },

    onThemeCacheWarmUpStartProcess: function() {
        var me = this,
            data = [];

        me.cancelOperation = false;

        me.shopStore.each(function(elem) {
            data.push(elem);
        });

        me.runRequest(data, 0);
    },

    onThemeCacheWarmUpCancelProcess: function() {
        var me = this;

        me.cancelOperation = true;

        me.window.progressBar.updateText(
            Ext.String.format('{s name=progress_bar/cancelling}Cancelling process ...{/s}')
        );
    },

    /**
     * Runs the actual request
     * Method is called recursively until all data was processed
     */
    runRequest: function(shops, offset) {
        var me = this,
            shop = shops[offset],
            batchSize = shops.length + 1;

        //cancel button pushed?
        if (me.cancelOperation) {
            me.shopStore.load({
                callback: function(records, operation, success) {
                    me.window.setShops(records);
                }
            });

            Shopware.Notification.createGrowlMessage(
                '{s name=response/cancelled/title}Cancelled{/s}',
                Ext.String.format('{s name=response/cancelled/detail}The process was cancelled. [0] of [1] caches were correctly warmed up{/s}', offset, batchSize)
            );

            return;
        }

        if (me.window.progressBar) {
            // updates the progress bar value and text, the last parameter is the animation flag
            me.window.progressBar.updateProgress(
                (offset) / batchSize,
                Ext.String.format('{s name=progress_bar/processing}Processing [0] ...{/s}', shop.get('name')),
                true
            );
        }

        Ext.Ajax.request({
            url: me.requestUrl,
            method: 'POST',
            params: {
                shopId: shop.get('id')
            },
            timeout: 4000000,
            callback: function(options, success, response) {
                var json, message;

                if (!Ext.isEmpty(response.responseText)) {
                    json = Ext.decode(response.responseText)
                } else {
                    json = {
                        success: success
                    }
                }

                if (success && json.success) {
                    if (offset+2 == batchSize) {
                        if (me.window.progressBar) {
                            me.window.progressBar.updateProgress(
                                (offset+1) / batchSize,
                                Ext.String.format('{s name=progress_bar/clearing_http_cache}Clearing HTTP cache{/s}'),
                                true
                            );
                        }

                        me.moveThemeFiles(function() {
                            me.clearHttpCache();
                        });
                    } else {
                        me.runRequest(shops, offset+1);
                    }
                } else {
                    message = Ext.String.format('{s name=response/error/detail}A server error occurred while processing your request for shop [0]{/s}', shop.get('name'));
                    if (!Ext.isEmpty(json.message)) {
                        message = message + ': ' + json.message;
                    }
                    Shopware.Notification.createGrowlMessage(
                        '{s name=response/error/title}An error occurred{/s}',
                        message
                    );

                    me.window.hide();
                }
            }
        });
    },

    moveThemeFiles: function(callback) {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller=Cache action=moveThemeFiles}',
            method: 'POST',
            timeout: 4000000,
            success: function(response) {
                callback();
            },
            failure: function(response) {
            }
        });
    },

    clearHttpCache: function() {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller=Cache action=clearCache}',
            method: 'POST',
            params: {
                'cache[http]' : 'on'
            },
            timeout: 4000000,
            success: function(response) {
                if (Ext.isNumber(me.window.singleShopId)) {
                    Shopware.Notification.createGrowlMessage(
                        '{s name=response/success/title}Theme shop cache warm up{/s}',
                        '{s name=response/success/detail_single}Theme shop cache has been successfully warmed up{/s}'
                    );
                } else {
                    Shopware.Notification.createGrowlMessage(
                        '{s name=response/success/title}Theme shop cache warm up{/s}',
                        '{s name=response/success/detail_multiple}All theme shop caches have been successfully warmed up{/s}'
                    );
                }

                me.window.hide();
            },
            failure: function(response) {
                //has the current request a progress bar?
                if (me.window.progressBar) {
                    me.window.progressBar.updateProgress(
                        0,
                        Ext.String.format('{s name=response/error/progress_bar}Error{/s}'),
                        true
                    );
                }

                Shopware.Notification.createGrowlMessage(
                    '{s name=response/error/title}An error occurred{/s}',
                    Ext.String.format('{s name=response/error/detail}A server error occurred while processing your request for shop [0]{/s}', shop.get('name'))
                );
            }
        });
    }

});
//{/block}
