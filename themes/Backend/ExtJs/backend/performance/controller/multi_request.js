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
        }
    },

    /**
     * Indicates if the operations should be canceled after the next request
     */
    cancelOperation: false,

    requestConfig: {
        topseller:  {
            title: '{s name=multi_request/topseller}Build index for TopSeller{/s}',
            totalCountUrl: '{url controller="TopSeller" action="getTopSellerCount"}',
            requestUrl: '{url controller="TopSeller" action="initTopSeller"}',
            batchSize: 100
        },

        search:  {
            title: '{s name=multi_request/search}Build index for frontend search{/s}',
            requestUrl: '{url controller="SearchIndex" action="build"}',
            batchSize: 100
        },

        seo:  {
            title: '{s name=multi_request/sei}Build index for SEO{/s}',
            snippetResource: 'seo',
            totalCountUrl: '{url controller="Seo" action="getCount"}',
            requestUrls: {
                init: '{url controller="Seo" action="initSeo"}',
                article: '{url controller="Seo" action="seoArticle"}',
                category: '{url controller="Seo" action="seoCategory"}',
                emotion: '{url controller="Seo" action="seoEmotion"}',
                blog: '{url controller="Seo" action="seoBlog"}',
                static: '{url controller="Seo" action="seoStatic"}',
                content: '{url controller="Seo" action="seoContent"}',
                supplier: '{url controller="Seo" action="seoSupplier"}'
            },
            batchSize: 100
        },

        similarShown:  {
            title: '{s name=multi_request/viewed}Build index for: Customers also viewed{/s}',
            totalCountUrl: '{url controller="SimilarShown" action="getSimilarShownCount"}',
            requestUrl: '{url controller="SimilarShown" action="initSimilarShown"}',
            batchSize: 100
        },
        alsoBought:  {
            title: '{s name=multi_request/bought}Build index for: Customers also bought{/s}',
            totalCountUrl: '{url controller="AlsoBought" action="getAlsoBoughtCount"}',
            requestUrl: '{url controller="AlsoBought" action="initAlsoBought"}',
            batchSize: 100
        },
        category:  {
            title: '{s name=multi_request/categories}Repair categories{/s}',
            totalCountUrl: '{url controller="Performance" action="prepareTree"}',
            requestUrl: '{url controller="Performance" action="fixCategories"}',
            batchSize: 100
        },
        httpCacheWarmer:  {
            title: '{s name=multi_request/http_cache_warmer/windowTitle}Warm up cache{/s}',
            snippetResource: 'httpCacheWarmer',
            totalCountUrl: '{url controller="Performance" action="getHttpURLs"}',
            requestUrls: {
                article: '{url controller="Performance" action="warmUpCache" resource=article}',
                category: '{url controller="Performance" action="warmUpCache" resource=category}',
                blog: '{url controller="Performance" action="warmUpCache" resource=blog}',
                static: '{url controller="Performance" action="warmUpCache" resource=static}',
                supplier: '{url controller="Performance" action="warmUpCache" resource=supplier}'
            },
            batchSize: 10
        }
    },

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

    onShopSelected: function(window, shopId) {
        var me = this;

        var taskConfig = window.taskConfig;

        Ext.Ajax.request({
            url: taskConfig.totalCountUrl,
            timeout: 4000000,
            params: {
                shopId: shopId
            },
            success: function(response) {
                var json = Ext.decode(response.responseText);
                taskConfig.totalCounts = json.data.counts;

                me.updateProgressBars(window);

                window.startButton.enable();
            }
        });
    },

    updateProgressBars: function(window) {
        var taskConfig = window.taskConfig;

        if (!Ext.isEmpty(taskConfig.totalCounts.article)) {
            window.articleProgress.updateProgress(
                0, Ext.String.format(window.snippets[taskConfig.snippetResource].article, 0, taskConfig.totalCounts.article)
            );
        }
        if (!Ext.isEmpty(taskConfig.totalCounts.category)) {
            window.categoryProgress.updateProgress(
                0, Ext.String.format(window.snippets[taskConfig.snippetResource].category, 0, taskConfig.totalCounts.category)
            );
        }
        if (!Ext.isEmpty(taskConfig.totalCounts.emotion)) {
            window.emotionProgress.updateProgress(
                0, Ext.String.format(window.snippets[taskConfig.snippetResource].emotion, 0, taskConfig.totalCounts.emotion)
            );
        }
        if (!Ext.isEmpty(taskConfig.totalCounts.static)) {
            window.staticProgress.updateProgress(
                0, Ext.String.format(window.snippets[taskConfig.snippetResource].static, 0, taskConfig.totalCounts.static)
            );
        }
        if (!Ext.isEmpty(taskConfig.totalCounts.blog)) {
            window.blogProgress.updateProgress(
                0, Ext.String.format(window.snippets[taskConfig.snippetResource].blog, 0, taskConfig.totalCounts.blog)
            );
        }
        if (!Ext.isEmpty(taskConfig.totalCounts.content)) {
            window.contentProgress.updateProgress(
                0, Ext.String.format(window.snippets[taskConfig.snippetResource].content, 0, taskConfig.totalCounts.content)
            );
        }
        if (!Ext.isEmpty(taskConfig.totalCounts.supplier)) {
            window.supplierProgress.updateProgress(
                0, Ext.String.format(window.snippets[taskConfig.snippetResource].supplier, 0, taskConfig.totalCounts.supplier)
            );
        }
    },

    getRequestConfig: function(window, progress, taskName, resource) {
        var me = this;

        return {
            batchSize: window.batchSizeCombo.getValue(),
            progress: window[progress],
            requestUrl: me.requestConfig[taskName].requestUrls[resource],
            totalCount: window.taskConfig.totalCounts[resource] * 1,
            snippet: window.snippets[taskName][resource],
            params: {
                shopId: window.shopCombo.getValue()
            }
        };
    },


    getSeoInitRequestConfig: function(window) {
        var me = this;

        return {
            totalCount: 1,
            progress: null,
            requestUrl: me.requestConfig.seo.requestUrls.init,
            batchSize: 2,
            params: {
                shopId: window.shopCombo.getValue()
            }
        };
    },

    /**
     * Called after the user hits the 'start' button of the multiRequestDialog
     */
    onStartSeoIndex: function(window) {
        var me = this, configs = [];

        me.updateProgressBars(window);

        configs.push(me.getSeoInitRequestConfig(window, me.requestConfig.seo));

        configs.push(me.getRequestConfig(window, 'articleProgress', 'seo', 'article'));
        configs.push(me.getRequestConfig(window, 'categoryProgress', 'seo', 'category'));
        configs.push(me.getRequestConfig(window, 'emotionProgress', 'seo', 'emotion'));
        configs.push(me.getRequestConfig(window, 'blogProgress', 'seo', 'blog'));
        configs.push(me.getRequestConfig(window, 'staticProgress', 'seo', 'static'));
        configs.push(me.getRequestConfig(window, 'contentProgress', 'seo', 'content'));
        configs.push(me.getRequestConfig(window, 'supplierProgress', 'seo', 'supplier'));

        window.startButton.hide();
        window.cancelButton.show();
        window.cancelButton.enable();
        me.cancelOperation = false;

        me.runRequest(0, window, null, configs);

    },
    /**
     * Called after the user hits the 'start' button of the multiRequestDialog
     */
    onStartHttpCacheWarmUp: function(window) {
        var me = this, configs = [];

        me.updateProgressBars(window);

        configs.push(me.getRequestConfig(window, 'articleProgress', 'httpCacheWarmer', 'article'));
        configs.push(me.getRequestConfig(window, 'categoryProgress', 'httpCacheWarmer', 'category'));
        configs.push(me.getRequestConfig(window, 'blogProgress', 'httpCacheWarmer', 'blog'));
        configs.push(me.getRequestConfig(window, 'staticProgress', 'httpCacheWarmer', 'static'));
        configs.push(me.getRequestConfig(window, 'supplierProgress', 'httpCacheWarmer', 'supplier'));

        window.startButton.hide();
        window.cancelButton.show();
        window.cancelButton.enable();
        me.cancelOperation = false;

        me.runRequest(0, window, null, configs);
    },


    /**
     *
     */
    onShowMultiRequestTasks: function(type) {
        var me = this,
            config = me.requestConfig[type];

        var window = me.getView('main.MultiRequestTasks').create({
            title: config.title,
            currentType: type,
            taskConfig: config,
            batchSize: config.batchSize
        }).show();

        me.cancelOperation = false;
    },

    /**
     * Runs the actual request
     * Method is called recursively until all data was processed
     */
    runRequest: function(offset, dialog, currentConfig, configs) {
        var me = this;

        //support for multiple batch operation.
        if (currentConfig === null) {
            //get next request configuration
            currentConfig = configs.shift();
        }

        var params = currentConfig.params;
        if (!(Ext.isObject(params))) {
            params = { };
        }

        //last batch size processed?
        if (offset >= currentConfig.totalCount) {

            //is progress bar configured?
            if (currentConfig.progress) {
                currentConfig.progress.updateProgress(1, me.snippets.done.message, true);
            }

            //no more request configurations exists?
            if (configs.length === 0) {
                // Enable close button, set progressBar to 'finish'
                dialog.closeButton.enable();
                dialog.startButton.show();
                dialog.cancelButton.hide();

                // Show 'finished' message
                Shopware.Notification.createGrowlMessage(me.snippets.done.title, me.snippets.done.message);
            } else {
                //cancel button pushed?
                if (me.cancelOperation) {
                    dialog.closeButton.enable();
                    dialog.startButton.show();
                    dialog.cancelButton.hide();
                    return;
                }

                //get next config and call again
                currentConfig = configs.shift();
                me.runRequest(0, dialog, currentConfig, configs);
            }
            return;
        }

        //cancel button pushed?
        if (me.cancelOperation) {
            dialog.closeButton.enable();
            dialog.startButton.show();
            dialog.cancelButton.hide();
            return;
        }

        //has the current request a progress bar?
        if (currentConfig.progress) {
            // updates the progress bar value and text, the last parameter is the animation flag
            currentConfig.progress.updateProgress(
                (offset + currentConfig.batchSize) / currentConfig.totalCount,
                Ext.String.format(currentConfig.snippet, ( offset + currentConfig.batchSize), currentConfig.totalCount),
                true
            );
        }

        //set the params single, to support additional request params
        params.offset = offset;
        params.limit  = currentConfig.batchSize;

        Ext.Ajax.request({
            url: currentConfig.requestUrl,
            method: 'POST',
            params: params,
            timeout: 4000000,
            success: function(response) {
                var json = Ext.decode(response.responseText);

                // start recusive call here
                me.runRequest((offset + currentConfig.batchSize), dialog, currentConfig, configs);
            },
            failure: function(response) {
                me.shouldCancel = true;
                me.runRequest((offset + currentConfig.batchSize), dialog, currentConfig, configs);
            }
        });
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
