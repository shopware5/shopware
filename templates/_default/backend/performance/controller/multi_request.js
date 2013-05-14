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
        seo:  {
            title: '{s name=multi_request/sei}Build index for SEO{/s}',
            totalCountUrl: '{url controller="Performance" action="getTopSellerCount"}',
            requestUrl: '{url controller="Performance" action="initTopSeller"}',
            batchSize: 100
        },
        search:  {
            title: '{s name=multi_request/search}Build index for search{/s}',
            totalCountUrl: '{url controller="Performance" action="getTopSellerCount"}',
            requestUrl: '{url controller="Performance" action="initTopSeller"}',
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
            totalCountUrl: '{url controller="Cache" action="prepareTree"}',
            requestUrl: '{url controller="Cache" action="fixCategories"}',
            batchSize: 5000
        }
    },

    init: function () {
        var me = this;

        me.control({
            'performance-multi-request-button': {
                'showMultiRequestDialog': me.onShowMultiRequestDialog
            },
            'performance-main-multi-request-dialog': {
                'multiRequestDialogCancelProcess': me.onCancelMultiRequest,
                'multiRequestDialogStartProcess': me.onStartMultiRequest
            }
       });

        me.callParent(arguments);
    },

    /**
     * Runs the actual request
     * Method is called recursively until all data was processed
     */
    runRequest: function(offset, dialog) {
        var me = this,
            type = dialog.currentType,
            config = me.requestConfig[type],
            batchSize = config.batchSize,
            count = config.totalCount;


        if (offset >= count) {
            // Enable close button, set progressBar to 'finish'
            dialog.progressBar.updateProgress(1, me.snippets.done.message, true);

            dialog.cancelButton.disable();
            dialog.closeButton.enable();

            // Show 'finished' message
            Shopware.Notification.createGrowlMessage(me.snippets.done.title, me.snippets.done.message);

            return;
        }

         if (me.cancelOperation) {
            dialog.closeButton.enable();
            return;
        }

        // updates the progress bar value and text, the last parameter is the animation flag
        dialog.progressBar.updateProgress((offset+batchSize)/count, Ext.String.format(me.snippets.process, (offset+batchSize), count), true);

        Ext.Ajax.request({
            url: config.requestUrl,
            method: 'POST',
            params: {
                offset: offset,
                limit: batchSize
            },
            success: function(response) {
                var json = Ext.decode(response.responseText);

                // start recusive call here
                me.runRequest(offset + batchSize, dialog);
            },

            failure: function(response) {
                me.shouldCancel = true;
                me.runRequest(offset + batchSize, dialog);
            }
        });
    },

    /**
     * Called after the user hits the 'start' button of the multiRequestDialog
     */
    onStartMultiRequest: function(dialog) {
        var me = this,
            type = dialog.currentType;

        me.requestConfig[type].batchSize = dialog.combo.getValue();
        dialog.combo.disable();

        me.runRequest(0, dialog);
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

        Ext.Ajax.request({
            url: config.totalCountUrl,
            success: function(response) {
                var json = Ext.decode(response.responseText);
                config.totalCount = json.data.count;

                window.progressBar.updateProgress(0);

                window.startButton.enable();
            }
        });
    }

});
//{/block}
