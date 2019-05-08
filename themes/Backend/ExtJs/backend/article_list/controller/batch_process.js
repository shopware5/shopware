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
 * The batchProcess controller handles the batch process
 */
//{namespace name=backend/article_list/main}
//{block name="backend/article_list/controller/batch_process"}
Ext.define('Shopware.apps.ArticleList.controller.BatchProcess', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend: 'Ext.app.Controller',

    refs: [
        { ref:'navigationGrid', selector:'multi-edit-navigation-grid' },
        { ref:'mainGrid', selector:'multi-edit-main-grid' },
        { ref:'batchWindow', selector:'multi-edit-batch-process-window' },
        { ref:'batchGrid', selector:'multi-edit-batch-process-grid' }
    ],

    /**
     * Reference to the progressWindow (if existing)
     */
    progressWindow: undefined,

    /**
     * Canceled flag: Indicated that the current process should be canceled after the next call
     */
    cancel: false,

    /**
     * A template method that is called when your application boots.
     * It is called before the Application's launch function is executed
     * so gives a hook point to run any code before your Viewport is created.
     *
     * @return void
     */
    init: function () {
        var me = this;

        me.control({
            'multi-edit-menu': {
                openBatchProcessWindow: me.onOpenBatchProcessWindow
            },
            'multi-edit-batch-process-grid': {
                editRow: me.onEditRow,
                deleteRow: me.onDeleteRow,
                setEditor: me.onSetEditor,
                addRow: me.onAddRow
            },
            'multi-edit-batch-process-window button[action=addToQueueAndRun]': {
                click: me.onAddToQueueAndRun
            }
        });

        me.callParent(arguments);
    },

    /**
     * Convenience method to show a sticky growl message
     *
     * @param message
     */
    showError: function(message) {
        var me = this;

        Shopware.Notification.createStickyGrowlMessage({
            title: '{s name=error}Error{/s}',
            text: message,
            log: true
        },
        'ArticleList');
    },

    /**
     * Return an array of operations defined in the store
     *
     * @param store
     * @returns Object
     */
    getOperationArray: function(store) {
        var operations = [];

        Ext.each(store.data.items, function(record, index, array) {
            operations.push({ 'column': record.get('column'), 'operator': record.get('operator'), 'value': record.get('value') });
        });

        return operations;
    },

    /**
     * Callback method triggered, after the user presses the 'add to queue and run' button. Will
     * basically first add items to queue and then immidately run the batch processing
     */
    onAddToQueueAndRun: function() {
        var me = this,
            config;

        Ext.MessageBox.confirm(
            '{s name=confirmAddToQueueTitle}Add to queue?{/s}',
            Ext.String.format('{s name=confirmAddToQueueMessage}You are about to add [0] products to the queue. Do you really want to queue those products to be batch processed with the operations you defined?{/s}', me.getBatchProcessWindow().total),
            function (response) {
                if ( response !== 'yes' ) {
                    return;
                }
                config = me.initAddToQueue();
                config.processDirectly = true;

                me.addToQueue(config, 0, 0, 0);
            }
        );
    },

    /**
     * Init queue
     *
     * @returns Object
     */
    initAddToQueue: function() {
        var me = this,
            grid = me.getBatchGrid(),
            store = grid.store,
            operations = me.getOperationArray(store),
            filterArray = me.getBatchWindow().filterArray;

        me.cancel = false;

        me.createQueueWindow();

        return { operations: operations, filterArray: filterArray}
    },

    /**
     * Creates a Ext.MessageBox with a progressbar in order to show a process while adding items to queue
     */
    createQueueWindow: function() {
        var me = this;

        me.progressWindow = Ext.MessageBox.show({
            title        : '{s name=creatingQueue}Creating queue for bulk changes{/s}',
            msg          : '{s name=importPendingMessageQueue}In this step the filtered products are calculated. Additionally a backup will be created, if configured. Depending one the amount of products and the server speed, this might take a while.{/s}',
            width        : 500,
            progress     : true,
            closable     : false,
            buttons      : Ext.MessageBox.CANCEL,
            fn           : function(buttonId, text, opt) {

                if (buttonId !== 'cancel') {
                    return;
                }

                // Set the cancel property to true in order to cancel the migration
                // after the next request
                me.cancel = true;
            }
        });

        // workaround to set the height of the MessageBox
        me.progressWindow.setSize(500, 200);
        me.progressWindow.doLayout();

        me.progressWindow.progressBar.reset();
        me.progressWindow.progressBar.animate = true;
        me.progressWindow.progressBar.updateProgress(0, '{s name=startingAddToQueue}Adding to queue...{/s}');
    },

    /**
     * Creates a Ext.MessageBox with a progressbar for the batch process
     */
    createBatchWindow: function() {
        var me = this;

        me.progressWindow = Ext.MessageBox.show({
            title        : '{s name=creatingBatchWindow}Batch-changing products{/s}',
            msg          : '{s name=importPendingMessageBatch}In this step your changes will be applied to the filtered products. This is quite fast in most cases. If not, try to disable cache invalidation in the ArticleList plugin configuration.{/s}',
            width        : 500,
            progress     : true,
            closable     : false,
            buttons      : Ext.MessageBox.CANCEL,
            fn           : function(buttonId, text, opt) {

                if (buttonId !== 'cancel') {
                    return;
                }

                // Set the cancel property to true in order to cancel the migration
                // after the next request
                me.cancel = true;
            }
        });

        // Workaround to set the height of the MessageBox
        me.progressWindow.setSize(500, 180);
        me.progressWindow.doLayout();

        me.progressWindow.progressBar.reset();
        me.progressWindow.progressBar.animate = true;
        me.progressWindow.progressBar.updateProgress(0, '{s name=startingImport}Starting BatchProcess ...{/s}');
    },

    /**
     * Called recursively until all items have been added to queue.
     *
     * @param config
     * @param offset
     * @param queueId
     * @param startTime
     */
    addToQueue: function(config, offset, queueId, startTime) {
        var me = this;

        if (me.cancel) {
            me.cancel = false;
            return;
        }

        if (!startTime || startTime == 0) {
            startTime = new Date().getTime() / 1000;
        }

        Ext.Ajax.request({
            type: 'POST',
            url: '{url controller="ArticleList" action = "createQueue"}',
            timeout: 4000000,
            params : {
                resource: 'product',
                operations: Ext.JSON.encode(config.operations),
                filterArray: config.filterArray,
                offset: offset,
                limit: '{config name=addToQueuePerRequest}',
                queueId: queueId
            },
            success: function (response, request) {
                if (!response.responseText) {
                    me.showError('{s name=unknownError}An unknown error occurred, please check your server logs{/s}');
                    return;
                }

                var result = Ext.JSON.decode(response.responseText);

                if(!result) {
                    me.progressWindow.close();
                    me.showError(response.responseText);
                }else if(!result.success) {
                    me.progressWindow.close();
                    me.showError(result.message);
                }else{
                    if (result.data.offset < result.data.totalCount) {
                        var eta = me.getETA(startTime, result.data.offset, result.data.totalCount);
                        var progressText =  Ext.String.format(
                            '{s name=processedItems}[0] / [1] processed. [2]:[3]:[4] remaining{/s}',
                            result.data.offset, result.data.totalCount, eta.hours, eta.minutes, eta.seconds
                        );
                        me.progressWindow.progressBar.updateProgress(result.data.offset/result.data.totalCount, progressText);

                        me.addToQueue(config, result.data.offset, result.data.queueId, startTime);
                    }else{
                        Shopware.Notification.createStickyGrowlMessage({
                                title: '{s name=createdQueueTitle}Created queue{/s}',
                                text: Ext.String.format('{s name=createdQueueMessage}Created queue for [0] items for this filter: [1]{/s}', result.data.totalCount, me.filterArrayToString(Ext.JSON.decode(config.filterArray))),
                                log: true
                            },
                            'ArticleList'
                        );

                        me.progressWindow.progressBar.updateProgress(1, "Done");
                        me.progressWindow.close();
                        if (config.processDirectly) {
                            if (me.subApplication.backupStore) {
                                me.subApplication.backupStore.reload();
                            }
                            window.setTimeout(function() {
                                me.onInitBatchProcess(result.data.queueId, config);
                            }, 300);
                            return;
                        }
                    }
                }

            },
            failure: function (response, request) {
                if(response.responseText) {
                    me.showError(response.responseText);
                } else {
                    me.showError('{s name=unknownError}An unknown error occurred, please check your server logs{/s}');
                }
            }
        });
    },

    /**
     * Helper which returns a human readable string for a given filter array
     *
     * @param filterArray
     * @returns string
     */
    filterArrayToString: function(filterArray) {
        var filterLength = filterArray.length,
            result = [];

        for (var i=0;i<filterLength;i++) {
            result.push(filterArray[i]['token']);
        }
        return result.join(' ');
    },

    /**
     * Starts the batch process for a given id.
     *
     * @param queueId The queue ID
     * @param config Config object with infos such as filterArray and the operators to apply
     */
    onInitBatchProcess: function(queueId, config) {
        var me = this;

        me.cancel = false;

        me.createBatchWindow();

        me.runBatchProcess(queueId, config,0 )
    },

    /**
     * Called recursively until the batch process is done
     *
     * @param queueId
     * @param config
     * @param startTime
     */
    runBatchProcess: function(queueId, config, startTime) {
        var me = this;

        if (!startTime || startTime == 0) {
            startTime = new Date().getTime() / 1000;
        }

        if (me.cancel) {
            me.cancel = false;
            return;
        }

        Ext.Ajax.request({
            url: '{url controller="ArticleList" action = "batch"}',
            timeout: 4000000,
            params : {
                resource: 'product',
                limit: 200,
                queueId: queueId
            },
            success: function (response, request) {
                if (!response.responseText) {
                    me.showError('{s name=unknownError}An unknown error occurred, please check your server logs{/s}');
                    return;
                }

                var result = Ext.JSON.decode(response.responseText);

                if(!result) {
                    me.progressWindow.close();
                    me.showError(response.responseText);
                }else if(!result.success) {
                    me.progressWindow.close();
                    me.showError(result.message);
                }else{
                    if (!result.data.done) {
                        var eta = me.getETA(startTime, result.data.processed, result.data.totalCount);
                        var progressText =  Ext.String.format(
                            '{s name=processedItems}[0] / [1] processed. [2]:[3]:[4] remaining{/s}',
                            result.data.processed, result.data.totalCount, eta.hours, eta.minutes, eta.seconds
                        );
                        me.progressWindow.progressBar.updateProgress(result.data.processed/result.data.totalCount, progressText);

                        me.runBatchProcess(queueId, config, startTime);
                    }else{
                        me.progressWindow.close();
                        me.getMainGrid().store.reload();
                        operationString = Ext.JSON.encode(config.operations);
                        Shopware.Notification.createStickyGrowlMessage({
                                title: '{s name=batchDoneTitle}Done{/s}',
                                text: Ext.String.format(
                                        '{s name=batchDoneMessage}Processed [0] items with following rules: [1]{/s}\n',
                                        result.data.totalCount,
                                        me.operationsToString(config.operations)
                                ),
                                log: true
                            },
                            'ArticleList'
                        );
                    }
                }

            },
            failure: function (response, request) {
                if(response.responseText) {
                    me.showError(response.responseText);
                } else {
                    me.showError('{s name=unknownError}An unknown error occurred, please check your server logs{/s}');
                }
            }
        });
    },

    /**
     * Helper method to return a human readable string for a given operators array
     *
     * @param operations
     * @returns string
     */
    operationsToString: function(operations) {
        var operationsLength = operations.length,
            result = [];

        for (var i=0;i<operationsLength;i++) {
            if (operations[i]['column'] != '' && operations[i]['operation'] != '') {
                result.push(Ext.String.format('[0] [1] [2]', operations[i]['column'], operations[i]['operator'], operations[i]['value']));
            }
        }

        return result.join(',\n');
    },

    /**
     * Calculate the estimated remaining time for a process
     *
     * @param startTime
     * @param processedItems
     * @param totalItems
     * @returns Object
     */
    getETA: function(startTime, processedItems, totalItems) {
        var remainingItems = totalItems - processedItems,
            passedSeconds = new Date().getTime()/1000 - startTime,
            perSecond = passedSeconds  / processedItems,
            remainingSeconds = remainingItems * perSecond,
            hours = ~~(remainingSeconds / 3600),
            minutes = ~~((remainingSeconds - hours*3600)/60),
            seconds = ~~(remainingSeconds - -hours*3600 - minutes*60);

        return {
            hours: hours < 10 ? '0' + hours : hours,
            minutes: minutes < 10 ? '0' + minutes : minutes,
            seconds: seconds < 10 ? '0' + seconds : seconds
        };
    },

    /**
     * Callback method triggered, after the user edited a row
     *
     * @param rowIndex
     */
    onEditRow: function(rowIndex) {
        var me = this,
            emptyRecordAt,
            grid = me.getBatchGrid(),
            store = grid.store,
            record = store.getAt(rowIndex);

        if (!record.get('column') || !record.get('operator')) {
            store.remove( [record] );
        }
    },

    /**
     * Callback method triggered, after the user pressed the 'delete' action button
     *
     * @param rowIndex
     */
    onDeleteRow: function(rowIndex) {
        var me = this,
            grid = me.getBatchGrid(),
            store = grid.store,
            record = store.getAt(rowIndex);

        store.remove( [record] );
    },

    /**
     * Fired when the user clicks the "add row" button
     */
    onAddRow: function() {
        var me = this,
            grid = me.getBatchGrid(),
            store = grid.store,
            record,
            emptyRecordAt;

        // Find an existing, empty record
        emptyRecordAt = store.findBy(function(record) {
            if (!record.get('column') || !record.get('operator')) {
                return true;
            }
        });

        // If there is an empty record - use that one
        if (emptyRecordAt >= 0) {
            record = store.getAt(emptyRecordAt);
        // Else create a new one
        } else {
            record = Ext.create('Shopware.apps.ArticleList.model.Operation',  { });
            store.add(record);
        }

        grid.rowEditing.startEdit(record, 1);
    },

    /**
     * Fired when the user changed the selected column in batch grid.
     * We will need to set the value editor here.
     *
     * @param column
     * @param record
     */
    onSetEditor: function(column, record) {
        var me = this,
            columnName = record.get('name'),
            config;

        config = me.subApplication.getController('Main').getConfigForColumn(columnName);

        column.setEditor({
            xtype: me.getEditorForColumn(config),
            allowBlank: false
        });
    },


    /**
     * Helper method which returns a rowEditing.editor for a given column.
     *
     * @param column
     * @returns Object|boolean
     */
    getEditorForColumn: function(column) {
        // Do create editor for columns, which have been configured to be non-editable
        if (!column.editable) {
            return false;
        }

        switch (column.alias) {
            default:
                switch (column.type) {
                    case 'integer':
                    case 'decimal':
                    case 'float':
                        var precision = 0;
                        if (column.precision) {
                            precision = column.precision
                        } else if (column.type === 'float') {
                            precision = 3;
                        } else if (column.type === 'decimal') {
                            precision = 3;
                        }
                        return { xtype: 'numberfield', decimalPrecision: precision };

                    case 'string':
                    case 'text':
                        return {
                            xtype: 'textfield',
                            maxLength: 2700,  // Issue SW-23934: Due to a problem in PHP (https://bugs.php.net/bug.php?id=70110) long values can lead to a problem parsing the DQL
                        };

                    case 'boolean':
                        return {
                            xtype: 'checkbox',
                            inputValue: 1,
                            uncheckedValue: 0
                        };

                    case 'date':
                        return new Ext.form.DateField({
                            disabled : false,
                            format: 'Y-m-d'
                        });

                    case 'datetime':
                        return new Ext.form.DateField({
                            disabled : false,
                            format: 'Y-m-d H:i:s'
                        });

                        return new Shopware.apps.Base.view.element.DateTime({
                            timeCfg: { format: 'H:i:s' },
                            dateCfg: { format: 'Y-m-d' }
                        });

                    default:
                        console.log('Unknown column: ', column.type);
                        break;
                }
            break;
        }
    },

    /**
     * Return an existing instance of the batchProcess window or create a new one
     *
     * @returns Ext.window
     */
    getBatchProcessWindow: function() {
        var me = this;

        if (me.subApplication.batchProcessWindow && !me.subApplication.batchProcessWindow.isDestroyed) {
            return me.subApplication.batchProcessWindow;
        } else {
            me.subApplication.batchProcessWindow = me.getView('BatchProcess.Window').create({
                editableColumnsStore: Ext.create('Shopware.apps.ArticleList.store.EditableColumns').load({ params: { resource: 'product' }})
            });
            me.subApplication.batchProcessWindow.title = me.setWindowTitle();
        }

        return me.subApplication.batchProcessWindow;
    },

    /**
     * Set the window title
     */
    setWindowTitle: function() {
        var me = this,
            window,
            mainGrid = me.getMainGrid(),
            filterArray = mainGrid.store.getProxy().extraParams.ast;

        if (!me.subApplication.batchProcessWindow || me.subApplication.batchProcessWindow.isDestroyed) {
            return;
        }

        window = me.getBatchProcessWindow();

        if (!me.subApplication.currentFilterName) {
            name = '{s name=unknownFilter}Unknown filter{/s}';
        } else {
            name = me.subApplication.currentFilterName;
        }

        window.filterArray = filterArray;
        window.total = mainGrid.store.getProxy().reader.rawData.total;
        window.setTitle(
            Ext.util.Format.stripTags(
                window.titleTemplate + ' - ' + name + ' - ' + Ext.String.format('{s name=batchProcess/window/totalTitle}[0] products{/s}', window.total, window.filter)
            )
        );

    },

    /**
     * Update the batch process window without loosing the current data
     */
    updateBatchProcessWindow: function() {
        var me = this;

        if (!me.subApplication.batchProcessWindow || me.subApplication.batchProcessWindow.isDestroyed) {
            return;
        }

        me.setWindowTitle();
    },

    /**
     * Callback method triggered, after the user clicked the "batch process" button above the main grid
     */
    onOpenBatchProcessWindow: function() {
        var me = this,
            window = me.getBatchProcessWindow(),
            grid = window.down('grid'),
            store =  me.getStore('Shopware.apps.ArticleList.store.Operation');

        me.setWindowTitle();

        store.load({
            params: {
                resource: 'product'
            }
        });

        window.storeLoaded = true;
        grid.reconfigure(store);

        return window.show();
    }
});
//{/block}
