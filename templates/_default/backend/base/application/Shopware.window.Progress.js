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
    height: 300,
    closable: false,


    cancelProcess: false,

    /**
     * The statics object contains the shopware default configuration for
     * this component.
     *
     * @type { object }
     */
    statics: {
        displayConfig: {
            infoText: '',
            tasks: [ ],
            outputProperties: [ 'id', 'number', 'name' ],
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


    initComponent: function () {
        var me = this;

        me.cancelProcess = false;
        me.items = me.createItems();
        me.dockedItems = [ me.createToolbar() ];
        me.callParent(arguments);
        me.sequentialProcess(undefined, me.getConfig('tasks'));
    },


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
            collapsed: true,
            flex: 1,
            margin: '20 0 0',
            title: 'Request results'
        });

        return me.resultFieldSet;
    },


    createInfoText: function () {
        return Ext.create('Ext.container.Container', {
            html: this.getConfig('infoText'),
            style: 'line-height:20px;'
        });
    },


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

    sequentialProcess: function (current, tasks) {
        var me = this, record;

        if (!current && tasks.length > 0) {
            current = tasks.shift();
        }

        if (current.data && current.data.length <= 0) {
            current = tasks.shift();
        }

        if (!current || me.cancelProcess) {
            me.closeButton.enable();
            me.cancelButton.disable();
            if (me.cancelProcess) {
                me.updateProgressBar(current, 'Process canceled at position [0] of [1]');
            }
            return;
        }

        record = current.data.shift();
        me.updateProgressBar(current, current.text);

        me.fireEvent(current.event, current, record, function(result, operation) {

            if (me.getConfig('displayResultGrid')) {
                me.resultStore.add(
                    me.createResponseRecord(operation)
                );
                if (!operation.wasSuccessful()) {
                    me.resultFieldSet.expand();
                }
            }

            me.sequentialProcess(current, tasks);
        });
    },


    updateProgressBar: function(task, text) {
        var index = task.totalCount - task.data.length;

        task.progressBar.updateProgress(
            index / task.totalCount,
            Ext.String.format(text, index, task.totalCount),
            true
        );
    },


    createResponseRecord: function(operation) {
        return Ext.create('Shopware.model.Error', {
            success: operation.wasSuccessful(),
            error: operation.getError(),
            request: operation.request,
            operation: operation
        });
    },


    successRenderer: function(value, metaData) {
        metaData.tdAttr = 'style="vertical-align: middle;"';
        var css = 'sprite-cross-small';
        if (value) {
            css = 'sprite-tick-small'
        }
        return '<span style="display:block; margin: 0 auto; height:16px; width:16px;" class="' + css + '"></span>';
    },


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