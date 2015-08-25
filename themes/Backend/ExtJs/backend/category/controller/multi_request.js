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
 * @package    Category
 * @subpackage Controller
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * The multi request dialog controller takes care of actual requests
 */

//{namespace name=backend/category/main}
//{block name="backend/category/controller/multi_request"}
Ext.define('Shopware.apps.Category.controller.MultiRequest', {
    extend: 'Enlight.app.Controller',

    /**
     * Contains all snippets for the component
     * @object
     */
    snippets: {
        done: {
            message: '{s name=batch/done_message}Operation finished{/s}',
            title: '{s name=batch/done_title}Successful{/s}'
        }
    },

    categoryId: null,

    /**
     * Indicates if the operations should be canceled after the next request
     */
    cancelOperation: false,

    init: function () {
        var me = this;

        me.control({
            'category-main-multi-request-tasks': {
                'multiRequestTasksWindowReady': me.onWindowReady,
                'multiRequestTasksStartProcess': me.startBatchMove
            }
        });

        me.callParent(arguments);
    },

    onWindowReady: function(window, categoryId) {
        var me = this;

        me.categoryId = categoryId;

        window.rebuildCategoryProgress.updateProgress(
            0, '{s name=batch/progress/init/rebuildCategoryPath}Rebuild category path{/s}'
        );

        window.removeOldAssignmentsProgress.updateProgress(
            0, '{s name=batch/progress/init/removeOldAssignments}Remove old assignments{/s}'
        );

        window.rebuildAssignmentsProgress.updateProgress(
            0, '{s name=batch/progress/init/rebuildAssignments}Rebuild assignments{/s}'
        );

        me.startBatchMove(window);
    },

    /**
     * Called after the user hits the 'start' button of the multiRequestDialog
     */
    startBatchMove: function(window) {
        var me = this, configs = [];

        configs.push({
            progress: window.rebuildCategoryProgress,
            requestUrl: '{url controller="Category" action="rebuildCategoryPath"}',
            initUrl:    '{url controller="Category" action="getRebuildCategoryPathCount"}',
            snippet:    '{s name=batch/progress/rebuildCategoryPath}Rebuild category path [0] of [1]{/s}'
        });

        configs.push({
            progress: window.removeOldAssignmentsProgress,
            requestUrl: '{url controller="Category" action="removeOldAssignments"}',
            initUrl:    '{url controller="Category" action="getRemoveOldAssignmentsCount"}',
            snippet:    '{s name=batch/progress/removeOldAssignments}Remove old assignments [0] of [1]{/s}'
        });

        configs.push({
            progress: window.rebuildAssignmentsProgress,
            requestUrl: '{url controller="Category" action="rebuildAssignments"}',
            initUrl:    '{url controller="Category" action="getRebuildAssignmentsCount"}',
            snippet:    '{s name=batch/progress/rebuildAssignments}Rebuild assignments [0] of [1]{/s}'
        });

        var currentConfig = configs.shift();

        me.initRequest(currentConfig, window, configs);
    },

    initRequest: function(currentConfig, dialog, configs) {
        var me = this;

        Ext.Ajax.request({
            url: currentConfig.initUrl,
            params: {
                categoryId: me.categoryId
            },
            success: function(response) {
                var json = Ext.decode(response.responseText);

                currentConfig.totalCount = json.data.count;
                currentConfig.batchSize  = json.data.batchSize;

                me.runRequest(0, dialog, currentConfig, configs);
            }
        });
    },

     /**
     * Runs the actual request
     * Method is called recursively until all data was processed
     */
    runRequest: function(offset, dialog, currentConfig, configs) {
        var me = this;

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
                // Show 'finished' message
                Shopware.Notification.createGrowlMessage(me.snippets.done.title, me.snippets.done.message);

                // Enable close button, set progressBar to 'finish'
                dialog.closeButton.enable();
                dialog.hide();
            } else {

                //cancel button pushed?
                if (me.cancelOperation) {
                    dialog.closeButton.enable();
                    return;
                }

                //get next config and call again
                currentConfig = configs.shift();
                me.initRequest(currentConfig, dialog, configs);
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
        params.categoryId = me.categoryId;

        Ext.Ajax.request({
            url: currentConfig.requestUrl,
            timeout: 400000, // increase timeout to 400 seconds
            method: 'POST',
            params: params,
            success: function(response) {
                var json = Ext.decode(response.responseText);

                // start recursive call here
                me.runRequest((offset + currentConfig.batchSize), dialog, currentConfig, configs);
            },
            failure: function(response) {
                me.shouldCancel = true;
                me.runRequest((offset + currentConfig.batchSize), dialog, currentConfig, configs);
            }
        });
    }
});
//{/block}
