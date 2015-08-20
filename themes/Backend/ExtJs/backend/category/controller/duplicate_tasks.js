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
 * The duplicate tasks multi request dialog controller takes care of actual requests
 */

//{namespace name=backend/category/main}
//{block name="backend/category/controller/duplicate_tasks"}
Ext.define('Shopware.apps.Category.controller.DuplicateTasks', {
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

    originalCategoryId: null,

    newCategoryId: null,

    pool: [],

    /**
     * Indicates if the operations should be canceled after the next request
     */
    cancelOperation: false,

    init: function () {
        var me = this;

        me.control({
            'category-main-duplicate-tasks': {
                'duplicateTasksWindowReady': me.onWindowReady,
                'duplicateTasksStartProcess': me.startBatchDuplicate
            }
        });

        me.callParent(arguments);
    },

    onWindowReady: function (window, categoryId, parentId, reassignArticleAssociations, callback) {
        var me = this;

        me.originalCategoryId = categoryId;
        me.newCategoryId = categoryId;
        me.reassignArticleAssociations = reassignArticleAssociations;

        me.pool.push(
            {
                categoryId: parentId,
                children: [categoryId],
                reassignArticleAssociations: reassignArticleAssociations
            }
        );

        window.duplicateCategoryProgress.updateProgress(
            0, '{s name=batch/progress/init/duplicateCategories}Duplicate categories{/s}'
        );

        window.rebuildAssignmentsProgress.updateProgress(
            0, '{s name=batch/progress/init/rebuildAssignments}Rebuild assignments{/s}'
        );

        me.startBatchDuplicate(window, callback);
    },

    /**
     * Called after the user hits the 'start' button of the multiRequestDialog
     */
    startBatchDuplicate: function (window, callback) {
        var me = this, configs = [];

        configs.push({
            progress: window.duplicateCategoryProgress,
            requestUrl: '{url controller="Category" action="duplicateCategory"}',
            initUrl: '{url controller="Category" action="getCategoryTreeCount"}',
            snippet: '{s name=batch/progress/duplicateCategory}Duplicating category [0] of [1]{/s}',
            requestHandler: me.runCategoryDuplicateRequest
        });

        configs.push({
            progress: window.rebuildAssignmentsProgress,
            requestUrl: '{url controller="Category" action="rebuildAssignments"}',
            initUrl: '{url controller="Category" action="getRebuildAssignmentsCount"}',
            snippet: '{s name=batch/progress/rebuildAssignments}Rebuild assignments [0] of [1]{/s}',
            requestHandler: me.runRequest
        });

        var currentConfig = configs.shift();

        me.initRequest(currentConfig, window, configs, me, callback);
    },

    initRequest: function (currentConfig, dialog, configs, context, callback) {
        var me = context || this;

        Ext.Ajax.request({
            url: currentConfig.initUrl,
            params: {
                categoryId: me.newCategoryId,
                reassignArticleAssociations: me.reassignArticleAssociations,
                originalCategoryId: me.originalCategoryId
            },
            success: function (response) {
                var json = Ext.decode(response.responseText);

                currentConfig.totalCount = json.data.count;
                currentConfig.batchSize = json.data.batchSize;

                currentConfig.requestHandler(0, dialog, currentConfig, configs, me, callback);
            }
        });
    },

    /**
     * Runs the actual request
     * Method is called recursively until all data was processed
     */
    runCategoryDuplicateRequest: function (offset, dialog, currentConfig, configs, context, callback) {
        var me = context;

        var params = me.pool.shift();

        if (!Ext.isDefined(params)) {
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
                callback();
            } else {

                //cancel button pushed?
                if (me.cancelOperation) {
                    dialog.closeButton.enable();
                    return;
                }

                //get next config and call again
                currentConfig = configs.shift();
                me.initRequest(currentConfig, dialog, configs, context, callback);
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
                (offset + params.children.length) / currentConfig.totalCount,
                Ext.String.format(currentConfig.snippet, ( offset + currentConfig.batchSize), currentConfig.totalCount),
                true
            );
        }

        var str = '?categoryId=' + params.categoryId;
        str += '&reassignArticleAssociations=' + me.reassignArticleAssociations;
        str += '&originalCategoryId=' + me.originalCategoryId;
        Ext.each(params.children, function(child)  {
           str += '&children[]=' + child;
        });

        Ext.Ajax.request({
            url: currentConfig.requestUrl + str,
            timeout: 400000, // increase timeout to 400 seconds
            method: 'POST',
            success: function (response) {
                var json = Ext.decode(response.responseText);

                if (json.result && json.result.length > 0) {
                    if (me.originalCategoryId == me.newCategoryId) {
                        me.newCategoryId = json.result[0].categoryId;
                    }
                    me.pool = Ext.Array.merge([], me.pool, json.result);
                }

                // start recursive call here
                me.runCategoryDuplicateRequest((offset + params.children.length), dialog, currentConfig, configs, context, callback);
            },
            failure: function (response) {
                me.shouldCancel = true;
                me.runCategoryDuplicateRequest((offset + params.children.length), dialog, currentConfig, configs, context, callback);
            }
        });
    },

    /**
     * Runs the actual request
     * Method is called recursively until all data was processed
     */
    runRequest: function (offset, dialog, currentConfig, configs, context, callback) {
        var me = context, params = currentConfig.params;

        if (!(Ext.isObject(params))) {
            params = {};
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
                callback();
            } else {

                //cancel button pushed?
                if (me.cancelOperation) {
                    dialog.closeButton.enable();
                    return;
                }

                //get next config and call again
                currentConfig = configs.shift();
                me.initRequest(currentConfig, dialog, configs, context, callback);
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
        params.limit = currentConfig.batchSize;
        params.categoryId = me.newCategoryId;

        Ext.Ajax.request({
            url: currentConfig.requestUrl,
            timeout: 400000, // increase timeout to 400 seconds
            method: 'POST',
            params: params,
            success: function (response) {
                var json = Ext.decode(response.responseText);

                // start recursive call here
                me.runRequest((offset + currentConfig.batchSize), dialog, currentConfig, configs, context, callback);
            },
            failure: function (response) {
                me.shouldCancel = true;
                me.runRequest((offset + currentConfig.batchSize), dialog, currentConfig, configs, context, callback);
            }
        });
    }
});
//{/block}
