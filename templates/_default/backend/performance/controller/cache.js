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
 * The cache controller takes care of cache related events and also
 * handles the category fixing
 */

//{namespace name=backend/performance/main}
//{block name="backend/performance/controller/cache"}
Ext.define('Shopware.apps.Performance.controller.Cache', {

    extend: 'Enlight.app.Controller',

    refs: [
        { ref: 'window', selector: 'cache-window' },
        { ref: 'info', selector: 'performance-tabs-cache-info dataview' },
        { ref: 'form', selector: 'performance-tabs-cache-form' },

        { ref: 'progressBar',    selector: 'performance-main-categories progressbar' },
        { ref: 'progressWindow', selector: 'performance-main-categories' },
        { ref: 'startButton',    selector: 'performance-main-categories button[action=start]' },
        { ref: 'closeButton',    selector: 'performance-main-categories button[action=closeWindow]' },
        { ref: 'cancelButton',   selector: 'performance-main-categories button[action=cancel]' }
    ],

    infoTitle: '{s name=form/message_title}Shop cache{/s}',
    infoMessageSuccess: '{s name=form/message}Shop cache has been cleared.{/s}',

    /**
     * Contains all snippets for the component
     * @object
     */
    snippets: {
        process: '{s name=controller/process}Category/Article connection [0] of [1]{/s}',
        done: {
            message: '{s name=controller/done_message}All categories have been fixed{/s}',
            title: '{s name=controller/done_title}Successful{/s}'
        }
    },

    /**
     * Class property which holds the main application if it is created
     *
     * @default null
     * @object
     */
    mainWindow: null,

    shouldCancel: false,
    totalCount: 0,

    /**
     *
     */
    init: function () {
        var me = this;

        me.control({
            'performance-tabs-cache-main button[action=clear]': {
                click: function(button, event) {
                    me.getForm().submit();
                }
            },
            'performance-tabs-cache-main button[action=select-all]': {
                click: function(button, event) {
                    me.getForm().getForm().getFields().each(function(item) {
                        item.setValue(true);
                    });
                }
            },
            'performance-tabs-cache-form': {
                fixCategories: me.onFixCategories,
                actioncomplete: function(form, action) {
                    me.getStore('Info').load({
                        callback: function(records, operation) {
                            Shopware.Notification.createGrowlMessage(
                                me.infoTitle,
                                me.infoMessageSuccess,
                                me.infoTitle
                            );
                        }
                    });
                }
            },

            'performance-main-categories': {
                startProcess:  me.onStartProcess,
                cancelProcess: me.onCancelProcess,
                closeWindow:   me.onCloseProcessWindow
            }
        });

        me.callParent(arguments);
    },

    onFixCategories: function(view) {
        var me = this;

        me.getView('main.Categories').create().show();

        Ext.Ajax.request({
            url: '{url action=prepareTree}',
            success: function(response) {
                var json = Ext.decode(response.responseText);
                me.totalCount = json.total;

                var progressBar = me.getProgressBar();
                progressBar.updateProgress(0);

                me.getStartButton().enable();
            }
        });
    },

    /**
     * @param { Array } selection
     */
    onStartProcess: function(selection) {
        var me = this;
        var progressBar = me.getProgressBar();
        me.executeSingleOrder(0, progressBar);
    },

    /**
     * @param { Integer } index
     * @param { Ext.ProgressBar } progressBar
     */
    executeSingleOrder: function(index, progressBar) {
        var me = this;
        var batchSize = 5000;
        var count = me.totalCount;

        if (index >= count) {
            //display finish update progress bar and display finish message
            progressBar.updateProgress(1, me.snippets.done.message, true);

            me.getCancelButton().disable();
            me.getCloseButton().enable();

            //display shopware notification message that the batch process finished
            Shopware.Notification.createGrowlMessage(me.snippets.done.title, me.snippets.done.message);

            return;
        }

         if (me.shouldCancel) {
            me.getCloseButton().enable();
            return;
        }

        //updates the progress bar value and text, the last parameter is the animation flag
        progressBar.updateProgress((index+batchSize)/count, Ext.String.format(me.snippets.process, (index+batchSize), count), true);

        Ext.Ajax.request({
            url: '{url action=fixCategories}',
            method: 'POST',
            params: {
                offset: index,
                limit: batchSize
            },
            success: function(response) {
                var json = Ext.decode(response.responseText);

                // start recusive call here
                me.executeSingleOrder(index + batchSize, progressBar);
            },

            failure: function(response) {
                me.shouldCancel = true;
                me.executeSingleOrder(index + batchSize, progressBar);
            }
        });
    },


    /**
     * Cancel the order creation.
     */
    onCancelProcess: function() {
        var me = this;
        me.shouldCancel = true;
    },

    /**
     * Cancel the document creation.
     * @param { Enlight.app.Window } window
     */
    onCloseProcessWindow: function(window) {
        var me = this;
        window.destroy();
    }
});
//{/block}
