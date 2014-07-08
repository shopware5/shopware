/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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
 * @copyright  Copyright (c) 2013, shopware AG (http://www.shopware.de)
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
            totalCountUrl: '{url controller="Seo" action="getCount"}',
            requestUrls: {
                init: '{url controller="Seo" action="initSeo"}',
                article: '{url controller="Seo" action="seoArticle"}',
                category: '{url controller="Seo" action="seoCategory"}',
                emotion: '{url controller="Seo" action="seoEmotion"}',
                blog: '{url controller="Seo" action="seoBlog"}',
                statistic: '{url controller="Seo" action="seoStatic"}',
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

                window.articleProgress.updateProgress(
                    0, Ext.String.format(window.snippets.seo.article, 0, taskConfig.totalCounts.article)
                );
                window.categoryProgress.updateProgress(
                    0, Ext.String.format(window.snippets.seo.category, 0, taskConfig.totalCounts.category)
                );
                window.emotionProgress.updateProgress(
                    0, Ext.String.format(window.snippets.seo.emotion, 0, taskConfig.totalCounts.emotion)
                );
                window.statisticProgress.updateProgress(
                    0, Ext.String.format(window.snippets.seo.statistic, 0, taskConfig.totalCounts.statistic)
                );
                window.blogProgress.updateProgress(
                    0, Ext.String.format(window.snippets.seo.blog, 0, taskConfig.totalCounts.blog)
                );
                window.contentProgress.updateProgress(
                    0, Ext.String.format(window.snippets.seo.content, 0, taskConfig.totalCounts.content)
                );
                window.supplierProgress.updateProgress(
                    0, Ext.String.format(window.snippets.seo.supplier, 0, taskConfig.totalCounts.supplier)
                );

                window.startButton.enable();
            }
        });
    },

    getSeoArticleRequestConfig: function(window) {
        var me = this;

        return {
            batchSize: window.batchSizeCombo.getValue(),
            progress: window.articleProgress,
            requestUrl: me.requestConfig.seo.requestUrls.article,
            totalCount: window.taskConfig.totalCounts.article * 1,
            snippet: window.snippets.seo.article,
            params: {
                shopId: window.shopCombo.getValue()
            }
        };
    },

    getSeoCategoryRequestConfig: function(window) {
        var me = this;

        return {
            batchSize: window.batchSizeCombo.getValue(),
            progress: window.categoryProgress,
            requestUrl: me.requestConfig.seo.requestUrls.category,
            totalCount: window.taskConfig.totalCounts.category * 1,
            snippet: window.snippets.seo.category,
            params: {
                shopId: window.shopCombo.getValue()
            }
        };
    },

    getSeoEmotionRequestConfig: function(window) {
        var me = this;

        return {
            batchSize: window.batchSizeCombo.getValue(),
            progress: window.emotionProgress,
            requestUrl: me.requestConfig.seo.requestUrls.emotion,
            totalCount: window.taskConfig.totalCounts.emotion * 1,
            snippet: window.snippets.seo.emotion,
            params: {
                shopId: window.shopCombo.getValue()
            }
        };
    },

    getSeoBlogRequestConfig: function(window) {
        var me = this;

        return {
            batchSize: window.batchSizeCombo.getValue(),
            progress: window.blogProgress,
            requestUrl: me.requestConfig.seo.requestUrls.blog,
            totalCount: window.taskConfig.totalCounts.blog * 1,
            snippet: window.snippets.seo.blog,
            params: {
                shopId: window.shopCombo.getValue()
            }
        };
    },

    getSeoStatisticRequestConfig: function(window) {
        var me = this;

        return {
            batchSize: window.batchSizeCombo.getValue(),
            progress: window.statisticProgress,
            requestUrl: me.requestConfig.seo.requestUrls.statistic,
            totalCount: window.taskConfig.totalCounts.statistic * 1,
            snippet: window.snippets.seo.statistic,
            params: {
                shopId: window.shopCombo.getValue()
            }
        };
    },

    getSeoContentRequestConfig: function(window) {
        var me = this;


        return {
            batchSize: window.batchSizeCombo.getValue(),
            progress: window.contentProgress,
            requestUrl: me.requestConfig.seo.requestUrls.content,
            totalCount: window.taskConfig.totalCounts.content * 1,
            snippet: window.snippets.seo.content,
            params: {
                shopId: window.shopCombo.getValue()
            }
        };
    },

    getSeoSupplierRequestConfig: function(window) {
        var me = this;

        return {
            batchSize: window.batchSizeCombo.getValue(),
            progress: window.supplierProgress,
            requestUrl: me.requestConfig.seo.requestUrls.supplier,
            totalCount: window.taskConfig.totalCounts.supplier * 1,
            snippet: window.snippets.seo.supplier,
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

        configs.push(me.getSeoInitRequestConfig(window));

        configs.push(me.getSeoArticleRequestConfig(window));
        configs.push(me.getSeoCategoryRequestConfig(window));
        configs.push(me.getSeoEmotionRequestConfig(window));
        configs.push(me.getSeoBlogRequestConfig(window));
        configs.push(me.getSeoStatisticRequestConfig(window));
        configs.push(me.getSeoContentRequestConfig(window));
        configs.push(me.getSeoSupplierRequestConfig(window));

        me.runRequest(0, window, null, configs);

        window.startButton.show();
        window.cancelButton.hide();
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
                dialog.cancelButton.disable();
                dialog.closeButton.enable();

                // Show 'finished' message
                Shopware.Notification.createGrowlMessage(me.snippets.done.title, me.snippets.done.message);
            } else {
                //cancel button pushed?
                if (me.cancelOperation) {
                    dialog.closeButton.enable();
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
