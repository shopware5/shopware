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
     * The statics object contains the shopware default configuration for
     * this component.
     *
     * @type { object }
     */
    statics: {
        displayConfig: {

            tasks: [ ]
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

        me.items = me.createItems();
        me.dockedItems = [ me.createToolbar() ];
        me.callParent(arguments);
        me.sequentialProcess(undefined, me.getConfig('tasks'));
    },


    createItems: function () {
        var me = this, items = [], item, progressContainer;
        var tasks = me.getConfig('tasks');

        items.push(me.createInfoText());

        Ext.each(tasks, function (task) {
            item = me.createTaskProgressBar(task);
            if (item) {
                items.push(item);
            }
        });

        items.push(me.createResultGrid());

        return items;
    },

    createToolbar: function () {
        var me = this;

        me.cancelButton = Ext.create('Ext.button.Button', {
            cls: 'secondary',
            text: 'Cancel process'
        });

        me.closeButton = Ext.create('Ext.button.Button', {
            cls: 'secondary',
            text: 'Close window',
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
            title: 'Request results',
            border: false,
            columns: [
                { xtype: 'rownumberer', width: 30 },
                { header: 'Success', dataIndex: 'success', width: 60, renderer: me.successRenderer },
                { header: 'Request', dataIndex: 'request', flex: 1, renderer: me.requestRenderer },
                { header: 'Error message', dataIndex: 'error', flex: 1 }
            ],
            store: me.resultStore
        });

        return Ext.create('Ext.form.FieldSet', {
            items: [ me.resultGrid ],
            collapsible: true,
            flex: 1,
            margin: '15 0 0',
            layout: 'fit',
            collapsed: true,
            title: 'Error reporting'
        });
    },


    createInfoText: function () {
        return Ext.create('Ext.container.Container', {
            html: 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et'
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

        if (!current) {
            return;
        }

        record = current.data.shift();

        var index = current.totalCount - current.data.length;
        current.progressBar.updateProgress(
            index / current.totalCount,
            Ext.String.format(current.text, index, current.totalCount),
            true
        );

        me.fireEvent(current.event, current, record, function(result, operation) {
            var model = Ext.create('Shopware.model.Error');

            model.setOperation(operation);
            me.resultStore.add(operation);
            me.sequentialProcess(current, tasks);
        });
    },

    successRenderer: function(value, metaData) {
        metaData.tdAttr = 'style="vertical-align: middle;"';
        var css = 'sprite-cross';
        if (value) {
            css = 'sprite-tick'
        }
        return '<span style="display:block; margin: 0 auto; height:16px; width:16px;" class="' + css + '"></span>';
    },


    requestRenderer: function(value, metaData, record) {
        var params = [], properties = [ 'id', 'number', 'name' ], propertyValue;

        if (record.get('success')) {
            return value.url;
        }
        params.push('<strong>url</strong> = ' + value.url);

        Ext.each(properties, function(property) {
            propertyValue = value.jsonData[property];
            if (propertyValue) {
                params.push('<strong>' + property + '</strong> = ' + propertyValue);
            }
        });

        return params.join('<br>');
    }

});