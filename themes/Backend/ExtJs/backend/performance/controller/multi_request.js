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
 * @package    Shopware_Performance
 * @subpackage Cache
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * The multi request dialog controller takes care of actual requests
 */

//{namespace name=backend/performance/main}
//{block name="backend/performance/controller/multi_request"}
Ext.define('Shopware.apps.Performance.controller.MultiRequest', {

    extend: 'Enlight.app.Controller',

    /**
     * Contains all snippets for the component
     * @object
     */
    snippets: {
        process: '{s name=request/process}[0] out of [1] items processed{/s}',
        done: {
            message: '{s name=request/done_message}Operation finished{/s}',
            title: '{s name=request/done_title}Successful{/s}'
        },
        error: {
            message: '{s name=request/error_message}Request failed{/s}',
            title: '{s name=request/error_title}Error{/s}'
        }
    },

    /**
     * Indicates if the operations should be canceled after the next request
     */
    cancelOperation: false,

    requestConfig: {
        topseller: {
            title: '{s name=multi_request/topseller}Build index for TopSeller{/s}',
            totalCountUrl: '{url controller="TopSeller" action="getTopSellerCount"}',
            requestUrl: '{url controller="TopSeller" action="initTopSeller"}',
            batchSize: 100
        },

        search: {
            title: '{s name=multi_request/search}Build index for frontend search{/s}',
            requestUrl: '{url controller="SearchIndex" action="build"}',
            batchSize: 100
        },

        seo: {
            initUrl: '{url controller="Seo" action="initSeo"}',
            title: '{s name=multi_request/sei}Build index for SEO{/s}',
            snippetResource: 'seo',
            totalCountUrl: '{url controller="Seo" action="getCount"}',
            batchSize: 100
        },

        similarShown: {
            title: '{s name=multi_request/viewed}Build index for: Customers also viewed{/s}',
            totalCountUrl: '{url controller="SimilarShown" action="getSimilarShownCount"}',
            requestUrl: '{url controller="SimilarShown" action="initSimilarShown"}',
            batchSize: 100
        },

        alsoBought: {
            title: '{s name=multi_request/bought}Build index for: Customers also bought{/s}',
            totalCountUrl: '{url controller="AlsoBought" action="getAlsoBoughtCount"}',
            requestUrl: '{url controller="AlsoBought" action="initAlsoBought"}',
            batchSize: 100
        },

        category: {
            title: '{s name=multi_request/categories}Repair categories{/s}',
            totalCountUrl: '{url controller="Performance" action="prepareTree"}',
            requestUrl: '{url controller="Performance" action="fixCategories"}',
            batchSize: 100
        },

        httpCacheWarmer: {
            title: '{s name=multi_request/http_cache_warmer/windowTitle}Warm up cache{/s}',
            snippetResource: 'httpCacheWarmer',
            totalCountUrl: '{url controller="Performance" action="getHttpURLs"}',
            batchSize: 10
        },

        sitemap: {
            title: '{s name=multi_request/sitemap}Build cache for sitemap{/s}',
            requestUrl: '{url controller="Performance" action="buildSitemapCache"}',
            batchSize: 1
        },
    },

    /**
     * @var boolean
     */
    requestFailedInformationShown: false,

    init: function () {
        var me = this;

        me.control({
            'performance-multi-request-button': {
                'showMultiRequestDialog': me.onShowMultiRequestDialog,
                'showMultiRequestTasks': me.onShowMultiRequestTasks
            },
            'performance-main-multi-request-tasks': {
                'onShopSelected': me.onShopSelected,
                'startSeoIndex': me.onStartSeoIndex,
                'startHttpCacheWarmUp': me.onStartHttpCacheWarmUp,
                'multiRequestTasksCancelProcess': me.onCancelMultiRequest
            },
            'performance-main-multi-request-dialog': {
                'multiRequestDialogCancelProcess': me.onCancelMultiRequest,
                'multiRequestDialogStartProcess': me.onStartMultiRequest
            }
        });

        me.callParent(arguments);
    },

    onShopSelected: function(window, shopId, taskName) {
        var me = this;

        var taskConfig = window.taskConfig,
            params = {
                shopId: shopId
            };

        if (window.settingsForm) {
            params.config = JSON.stringify(window.settingsForm.getValues());
        }

        Ext.Ajax.request({
            url: taskConfig.totalCountUrl,
            timeout: 4000000,
            params: params,
            success: function(response) {
                var json = Ext.decode(response.responseText);
                taskConfig.totalCounts = json.data.counts;

                me.updateProgressBars(taskName, window);

                window.startButton.enable();
            }
        });
    },

    updateProgressBars: function(taskName, win) {
        var taskConfig = win.taskConfig;

        if (taskName === 'httpCache') {
            win.progressBar.updateProgress(
                0, Ext.String.format('{s name="progress/initialAll"}{/s}', 0, taskConfig.totalCounts.all)
            );
        } else {
            win.iterateConfig(taskName, function (err, config, configName) {
                if (err) {
                    throw err;
                }

                if (!Ext.isEmpty(taskConfig.totalCounts[configName])) {
                    win[configName + 'Bar'].updateProgress(
                        0, Ext.String.format(config.progressText, 0, taskConfig.totalCounts[configName])
                    );
                }
            });
        }
    },

    getRequestConfig: function(win, progress, taskName, resource) {
        var config = {
            batchSize: win.batchSizeCombo.getValue(),
            progress: win[progress],
            requestUrl: win[taskName][resource].requestUrl,
            totalCount: win.taskConfig.totalCounts[resource] * 1,
            snippet: win[taskName][resource].progressText,
            current: 0,
            params: {
                shopId: win.shopCombo.getValue()
            },
            name: resource
        };

        if (win.concurrencySizeCombo) {
            config.concurrencySize = win.concurrencySizeCombo.getValue();
        }

        for (var attrname in win.checkboxValues) {
            config.params[attrname] = win.checkboxValues[attrname];
        }

        return config;
    },

    getSeoInitRequestConfig: function(window) {
        var me = this;

        return {
            totalCount: 1,
            progress: null,
            requestUrl: me.requestConfig.seo.initUrl,
            batchSize: 2,
            params: {
                shopId: window.shopCombo.getValue()
            }
        };
    },

    /**
     * Called after the user hits the 'start' button of the multiRequestDialog
     */
    onStartSeoIndex: function(win) {
        var me = this, configs = [];

        me.updateProgressBars('seo', win);

        configs.push(me.getSeoInitRequestConfig(win, me.requestConfig.seo));

        win.iterateConfig('seo', function (err, seoConfig, seoConfigName) {
            if (err) {
                throw err;
            }

            configs.push(me.getRequestConfig(win, seoConfigName + 'Bar', 'seo', seoConfigName));
        });

        win.startButton.hide();
        win.cancelButton.show();
        win.cancelButton.enable();
        me.cancelOperation = false;

        me.runRequest(0, win, null, configs);
    },

    /**
     * Called after the user hits the 'start' button of the multiRequestDialog
     */
    onStartHttpCacheWarmUp: function(win) {
        var me = this, configs = [];

        Object.keys(win.httpCache).forEach(function (key) {
            win.current[key] = 0;
        });

        win.checkboxValues = win.settingsForm.getValues();

        me.updateProgressBars('httpCache', win);

        win.iterateConfig('httpCache', function (err, seoConfig, seoConfigName) {
            if (err) {
                throw err;
            }

            configs.push(me.getRequestConfig(win, seoConfigName + 'Bar', 'httpCache', seoConfigName));
        });

        win.startButton.hide();
        win.cancelButton.show();
        win.cancelButton.enable();
        me.cancelOperation = false;

        me.runRequest(0, win, null, configs);
    },

    /**
     *
     */
    onShowMultiRequestTasks: function(type) {
        var me = this,
            config = me.requestConfig[type];

        var win = me.getView('main.MultiRequestTasks').create({
            title: config.title,
            currentType: type,
            taskConfig: config,
            batchSize: config.batchSize
        }).show();

        me.cancelOperation = false;

        return win;
    },

    /**
     * Runs the actual request
     * Method is called recursively until all data was processed
     */
    runRequest: function(offset, dialog, currentConfig, configs) {
        var me = this;

        // Support for multiple batch operations
        if (currentConfig === null) {
            // Get next request configuration
            currentConfig = configs.shift();
            me.requestFailedInformationShown = false;
        }

        var params = currentConfig.params;
        if (!(Ext.isObject(params))) {
            params = { };
        }

        // Last batch size processed?
        if (offset >= currentConfig.totalCount) {
            // Is a progress bar configured?
            if (currentConfig.progress) {
                currentConfig.progress.updateProgress(1, me.snippets.done.message, true);
            }

            // Was the request stack processed completely?
            if (configs.length === 0) {
                // Enable close button, set progressBar to 'finish'
                me.resetButtons(dialog);

                // Show 'finished' message
                Shopware.Notification.createGrowlMessage(me.snippets.done.title, me.snippets.done.message);
            } else {
                // Cancel button pushed?
                if (me.cancelOperation) {
                    me.resetButtons(dialog);
                    return;
                }

                // Get next config and call again
                currentConfig = configs.shift();
                me.runRequest(0, dialog, currentConfig, configs);
            }
            return;
        }

        // Cancel button pushed?
        if (me.cancelOperation) {
            me.resetButtons(dialog);
            return;
        }

        var current = offset + currentConfig.batchSize > currentConfig.totalCount ? currentConfig.totalCount : offset + currentConfig.batchSize;

        if (currentConfig.name) {
            dialog.current[currentConfig.name] = current;
        }

        // Does the current request have a progress bar?
        if (currentConfig.progress) {
            // Updates the progress bar value and text, the last parameter is the animation flag
            currentConfig.progress.updateProgress(
                ((offset + currentConfig.batchSize) > currentConfig.totalCount ? currentConfig.totalCount : (offset + currentConfig.batchSize)) / currentConfig.totalCount,
                Ext.String.format(currentConfig.snippet, current, currentConfig.totalCount),
                true
            );
        }

        if (dialog.progressBar && dialog.current) {
            var min = 0;

            Object.keys(dialog.current).forEach(function (key) {
                min += dialog.current[key];
            });

            dialog.progressBar.updateProgress(
                min / dialog.taskConfig.totalCounts.all,
                Ext.String.format('{s name="progress/all"}{/s}', min, dialog.taskConfig.totalCounts.all, dialog.httpCache[currentConfig.name].providerLabel),
                true
            );
        }

        // Set the params single, to support additional request params
        params.offset = offset;
        params.limit = currentConfig.batchSize;
        params.concurrent = currentConfig.concurrencySize;

        Ext.Ajax.request({
            url: currentConfig.requestUrl,
            method: 'POST',
            params: params,
            timeout: 4000000,
            success: function(response) {
                var json = Ext.decode(response.responseText);

                if (json.requestFailed && me.requestFailedInformationShown === false) {
                    Shopware.Notification.createStickyGrowlMessage({
                        title: '{s name="progress/requestFailedGrowlTitle"}{/s}',
                        text: '{s name="progress/requestFailedGrowlContent"}{/s}',
                        btnDetail: {
                            text: '{s name="progress/requestFailedGrowlButton"}{/s}',
                            callback: function () {
                                Shopware.app.Application.addSubApplication({
                                    name: 'Shopware.apps.Log',
                                    params: {
                                        mode: 'systemlogs'
                                    }
                                });
                            }
                        }
                    });

                    me.requestFailedInformationShown = true;
                }

                // Start recursive call here
                me.runRequest((offset + currentConfig.batchSize), dialog, currentConfig, configs);
            },
            failure: function(response) {
                var message = [
                    me.snippets.error.message,
                    response.status,
                    response.statusText
                ].join('<br/>');

                me.resetButtons(dialog);
                Shopware.Notification.createGrowlMessage(me.snippets.error.title, message);
            }
        });
    },

    /**
     * Resets the dialogs buttons to their initial state
     *
     * @param dialog
     */
    resetButtons: function (dialog) {
        dialog.closeButton.enable();
        dialog.startButton.show();
        dialog.cancelButton.hide();
    },

    /**
     * Called after the user hits the 'start' button of the multiRequestDialog
     */
    onStartMultiRequest: function(dialog) {
        var me = this,
            type = dialog.currentType,
            config = me.requestConfig[type];

        dialog.combo.disable();
        config.batchSize = dialog.combo.getValue();
        config.concurrencySize = dialog.combo.getValue();
        config.progress = dialog.progressBar;
        config.snippet = me.snippets.process;

        me.runRequest(0, dialog, config, []);
    },

    /**
     * Called after the user clicks the 'cancel' button of the multiRequestDialog
     */
    onCancelMultiRequest: function() {
        var me = this;

        me.cancelOperation = true;
    },

    /**
     *
     * @param type The actual dialog type (topseller, seo…)
     * @param fieldSet
     */
    onShowMultiRequestDialog: function(type, fieldSet) {
        var me = this,
            config = me.requestConfig[type];

        var window = me.getView('main.MultiRequestDialog').create({
            title: config.title,
            currentType: type,
            batchSize: config.batchSize
        }).show();

        me.cancelOperation = false;

        if (config.totalCountUrl) {
            Ext.Ajax.request({
                url: config.totalCountUrl,
                success: function(response) {
                    var json = Ext.decode(response.responseText);
                    config.totalCount = json.data.count;

                    window.progressBar.updateProgress(0);

                    window.startButton.enable();
                }
            });
        } else {
            if (!config.totalCount) {
                config.totalCount = 1;
            }

            window.progressBar.updateProgress(0);
            window.startButton.enable();
        }
    }

});
//{/block}
