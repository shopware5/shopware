
//{block name="backend/application/window/progress"}
Ext.define('Shopware.window.Progress', {
    extend: 'Ext.window.Window',
    title: 'Delete items',
    alias: 'widget.shopware-progress-window',
    layout: {
        type: 'vbox',
        align: 'stretch'
    },
    width: 600,
    modal: true,
    bodyPadding: 20,
    height: 360,
    closable: false,

    /**
     * Internal flag which will be set when the user clicks on the
     * cancel button at the bottom of the window.
     *
     * @type { boolean }
     */
    cancelProcess: false,

    /**
     * The statics object contains the shopware default configuration for
     * this component.
     *
     * @type { object }
     */
    statics: {
        displayConfig: {

            /**
             * Contains an info text which will be displayed at top of the window.
             * @type { String }
             */
            infoText: undefined,

            /**
             * List of tasks which will be executed.
             * @example
             *  records = [ recordA, recordB, ... ];
             *
             *  window = Ext.create('Shopware.window.Progress', {
             *      displayConfig: {
             *          infoText: 'Delete products in a batch mode. Each product will be deleted in a single request. This task requires some minutes, please wait ...',
             *          tasks: [
             *              {
             *                  text: 'Delete product [0] of [1]',
             *                  event: 'delete-product-item',
             *                  totalCount: records.length,
             *                  data: records
             *              }
             *          ]
             *      }
             *  });
             *
             * @type { Array }
             */
            tasks: [ ],

            /**
             * Array of fields which will be displayed in the result grid.
             *
             * @type { Array }
             */
            outputProperties: [ 'id', 'number', 'name' ],

            /**
             * Flag to hide or display the result grid of the batch window.
             *
             * @type { boolean }
             */
            displayResultGrid: true
        },

        /**
         * Static function to merge the different configuration values
         * which passed in the class constructor.
         * @param userOpts Object
         * @param displayConfig Object
         * @returns Object
         */
        getDisplayConfig: function (userOpts, displayConfig) {
            var config;

            config = Ext.apply({ }, userOpts.displayConfig, displayConfig);
            config = Ext.apply({ }, config, this.displayConfig);

            return config;
        },

        /**
         * Static function which sets the property value of
         * the passed property and value in the display configuration.
         *
         * @param prop
         * @param val
         * @returns boolean
         */
        setDisplayConfig: function (prop, val) {
            var me = this;

            if (!me.displayConfig.hasOwnProperty(prop)) {
                return false;
            }

            me.displayConfig[prop] = val;
            return true;
        }
    },


    /**
     * Helper function to get config access.
     * @param prop string
     * @returns mixed
     * @constructor
     */
    getConfig: function (prop) {
        var me = this;
        return me._opts[prop];
    },

    /**
     * Class constructor which merges the different configurations.
     * @param opts
     */
    constructor: function (opts) {
        var me = this;

        me._opts = me.statics().getDisplayConfig(opts, this.displayConfig);
        me.callParent(arguments);
    },


    /**
     * Initialisation of this component.
     */
    initComponent: function () {
        var me = this;

        //reset the cancel proccess flag
        me.cancelProcess = false;

        me.items = me.createItems();

        me.dockedItems = [ me.createToolbar() ];

        me.callParent(arguments);

        //starts the batch process.
        me.sequentialProcess(undefined, me.getConfig('tasks'));
    },


    /**
     * Creates all required elements for this component.
     * Shopware create a info text container, result grid for the task results and
     * a progress bar foreach task definition.
     *
     * @returns { Array }
     */
    createItems: function () {
        var me = this, items = [], item;
        var tasks = me.getConfig('tasks');

        if (me.getConfig('infoText')) {
            items.push(me.createInfoText());
        }

        Ext.each(tasks, function (task) {
            item = me.createTaskProgressBar(task);
            if (item) {
                items.push(item);
            }
        });

        if (me.getConfig('displayResultGrid')) {
            items.push(me.createResultGrid());
        }

        return items;
    },


    /**
     * Creates the bottom toolbar for the batch window.
     * The toolbar contains as default a cancel button to cancel the batch process
     * and a close window button to close the bath window.
     *
     * @returns { Ext.toolbar.Toolbar }
     */
    createToolbar: function () {
        var me = this;

        me.cancelButton = Ext.create('Ext.button.Button', {
            cls: 'secondary',
            text: 'Cancel process',
            handler: function() {
                me.cancelProcess = true;
            }
        });

        me.closeButton = Ext.create('Ext.button.Button', {
            cls: 'secondary',
            text: 'Close window',
            disabled: true,
            handler: function() { me.destroy() }
        });

        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            items: [ '->', me.cancelButton, me.closeButton ]
        });


        return me.toolbar;
    },


    /**
     * Creates the result grid which displays each request and response of each single task.
     * The result grid will be displayed within a field set which can be collapsed.
     *
     * @returns { Ext.form.FieldSet }
     */
    createResultGrid: function () {
        var me = this;

        me.resultStore = Ext.create('Ext.data.Store', {
            model: 'Shopware.model.Error'
        });

        me.resultGrid = Ext.create('Ext.grid.Panel', {
            border: false,
            columns: [
                { xtype: 'rownumberer', width: 30 },
                { header: 'Success', dataIndex: 'success', width: 60, renderer: me.successRenderer },
                { header: 'Request', dataIndex: 'request', flex: 1, renderer: me.requestRenderer, scope: me },
                { header: 'Error message', dataIndex: 'error', flex: 1 }
            ],
            store: me.resultStore
        });

        me.resultFieldSet = Ext.create('Ext.form.FieldSet', {
            items: [ me.resultGrid ],
            layout: 'fit',
            collapsible: true,
            collapsed: false,
            flex: 1,
            margin: '20 0 0',
            title: 'Request results'
        });

        return me.resultFieldSet;
    },


    /**
     * Creates an Ext.container.Container for the info text which will
     * be displayed at top of the window.
     *
     * @returns { Ext.container.Container }
     */
    createInfoText: function () {
        return Ext.create('Ext.container.Container', {
            html: this.getConfig('infoText'),
            style: 'line-height:20px;'
        });
    },


    /**
     * Creates a progress bar for the passed task object.
     * The task can contains a text which will be displayed within the
     * progress bar.
     *
     * @param { Object } task - The current task
     * @returns { Ext.ProgressBar }
     */
    createTaskProgressBar: function (task) {
        task.progressBar = Ext.create('Ext.ProgressBar', {
            animate: true,
            text: Ext.String.format(task.text, 0, task.totalCount),
            value: 0,
            height: 20,
            margin: '15 0 0'
        });

        return task.progressBar;
    },

    /**
     * Recursive helper function which executes the different tasks and iterates
     * the task records.
     *
     * @param current
     * @param tasks
     * @returns {boolean}
     */
    sequentialProcess: function (current, tasks) {
        var me = this, record;

        //no current task passed? Take the first task in the tasks array
        if (!current && tasks.length > 0) {
            current = tasks.shift();
        }

        //contains the current task no more record/data? Then get the next from the array
        if (current.data && current.data.length <= 0) {
            current = tasks.shift();
        }

        //no more current task configured? Or the process was canceled over the button?
        if (!current || me.cancelProcess) {

            //disabled cancel button and enable the close window button
            me.closeButton.enable();
            me.cancelButton.disable();

            //if the process canceled over the button, set an info text at which position the process canceled.
            if (me.cancelProcess) {
                me.updateProgressBar(current, 'Process canceled at position [0] of [1]');
            }
            return false;
        }

        //get next record of the data array of the current task.
        record = current.data.shift();
        me.updateProgressBar(current, current.text);


        /**
         * The progress window don't know, how to handle the data operation. Delete, update, create, save?
         * Or an Ext.Ajax Request? The event can be defined for each task definition.
         *
         * IMPORTANT: The event listener has the "callback" parameter, you have to call the callback function manuel!
         * @example:
         *
         * onBatchProcess: function (task, record, callback) {
         *     record.destroy({
         *         success: function (result, operation) {
         *             callback(result, operation);
         *         }
         *     });
         * },
         *
         * If you have no request result or Ext.data.Operation, you can fake this object:
         * @example
         * onBatchProcess: function (task, record, callback) {
         *     var me = this;
         *
         *     operation = {
         *          wasSuccessful: function() { return true; },
         *          getError: function() { return 'No error ...' },
         *          request: { url: "MY_REQUEST_URL" }
         *     };
         *
         *     callback({  }, operation);
         * },
         *
         */
        me.fireEvent(current.event, current, record, function(result, operation) {

            if (me.getConfig('displayResultGrid')) {
                me.resultStore.add(
                    me.createResponseRecord(operation)
                );
                if (!operation.wasSuccessful()) {
                    me.resultFieldSet.expand();
                }
            }

            //recursive call!
            me.sequentialProcess(current, tasks);
        });
    },

    /**
     * Helper function to update the progress bar text and process.
     *
     * @param { Object } task
     * @param { String } text
     */
    updateProgressBar: function(task, text) {
        var index = task.totalCount - task.data.length;

        task.progressBar.updateProgress(
            index / task.totalCount,
            Ext.String.format(text, index, task.totalCount),
            true
        );
    },


    /**
     * Creates a response record for the result grid.
     *
     * @param {Ext.data.Operation } operation - The request operation.
     * @returns { Shopware.model.Error }
     */
    createResponseRecord: function(operation) {
        return Ext.create('Shopware.model.Error', {
            success: operation.wasSuccessful(),
            error: operation.getError(),
            request: operation.request,
            operation: operation
        });
    },


    /**
     * Success renderer function of the result grid which displayed
     * an tick icon if the request was successfully and a red cross
     * if the request contains an exception.
     *
     * @param value
     * @param metaData
     * @returns {string}
     */
    successRenderer: function(value, metaData) {
        metaData.tdAttr = 'style="vertical-align: middle;"';
        var css = 'sprite-cross-small';
        if (value) {
            css = 'sprite-tick-small'
        }
        return '<span style="display:block; margin: 0 auto; height:16px; width:16px;" class="' + css + '"></span>';
    },


    /**
     * Grid column renderer function for the request column.
     * This function combines the request url and the request parameter to
     * a list which will be displayed in the grid.
     *
     * @param value
     * @param metaData
     * @param record
     * @returns {string}
     */
    requestRenderer: function(value, metaData, record) {
        var me = this, operation, propertyValue,
            params = [], requestRecord,
            properties = me.getConfig('outputProperties');

        operation = record.get('operation');
        requestRecord = operation.getRecords();
        requestRecord = requestRecord[0];

        params.push('<strong>url</strong> = ' + value.url);
        Ext.each(properties, function(property) {
            propertyValue = requestRecord.get(property);
            if (propertyValue) {
                params.push('<strong>' + property + '</strong> = ' + propertyValue);
            }
        });

        return params.join('<br>');
    }

});
//{/block}
