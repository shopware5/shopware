//{block name="backend/component/controller/listing"}

Ext.define('Shopware.grid.Controller', {
    extend: 'Ext.app.Controller',

    /**
     * The statics object contains the shopware default configuration for
     * this component.
     *
     * @type { object }
     */
    statics: {
        /**
         * The statics displayConfig is the shopware default configuration for
         * this component.
         * To override this property you can use the controller.displayConfig object.
         *
         * @example
         * Ext.define('Shopware.apps.Product.controller.Product', {
         *     extend: 'Shopware.grid.Controller',
         *     displayConfig: {
         *         ...
         *     }
         * });
         */
        displayConfig: {
            gridClass: undefined,
            eventAlias: undefined
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
     * Class constructor which merges the different configurations.
     * @param opts
     */
    constructor: function (opts) {
        var me = this;

        me._opts = me.statics().getDisplayConfig(opts, this.displayConfig);
        me.callParent(arguments);
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
     * Initialisation of this component.
     * The function calls the internal function createListingWindow to open
     * the listing window.
     * After the window created the function adds the event controls
     * over the createControls function.
     */
    init: function () {
        var me = this;

        if (me.getConfig('eventAlias')) {
            me.control(me.createControls());
        }

        console.log("Shopware.grid.Controller", me);
        me.callParent(arguments);
    },

    /**
     * Creates the control object which contains all event listener
     * definitions for this controller.
     *
     * This function requires the displayConfig.listingGrid parameter.
     * If this parameter isn't set, the function won't be called.
     *
     * @returns Object
     */
    createControls: function () {
        var me = this, alias, controls = {};

        alias = Ext.ClassManager.getAliasesByName(me.getConfig('gridClass'));
        alias = alias[0];
        alias = alias.replace('widget.', '');
        controls[alias] = me.createListingWindowControls();
        controls['shopware-progress-window'] = me.createProgressWindowControls();
        return controls;
    },

    /**
     * Creates the event controls for the configured listing grid.
     * Adds all shopware default events like addItem or editItem, etc.
     *
     * @returns Object
     */
    createListingWindowControls: function () {
        var me = this, events = {}, alias;

        alias = me.getConfig('eventAlias');

        events[alias + '-selection-changed'] = me.onSelectionChanged;
        events[alias + '-add-item'] = me.onAddItem;
        events[alias + '-delete-item'] = me.onDeleteItem;
        events[alias + '-delete-items'] = me.onDeleteItems;
        events[alias + '-edit-item'] = me.onEditItem;
        events[alias + '-search'] = me.onSearch;
        events[alias + '-change-page-size'] = me.onChangePageSize;

        return events;
    },


    createProgressWindowControls: function(){
        var me = this, events = {};

        events[me.getConfig('eventAlias') + '-batch-delete-item'] = me.onBatchDeleteItem;

        return events;
    },

    onDeleteItems: function (grid, button, records) {
        var me = this;

        var window = Ext.create('Shopware.window.Progress', {
            displayConfig: {
                tasks: [
                    {
                        type: 'Iteration',

                        text: 'Item [0] of [1]',
                        event: me.getConfig('eventAlias') + '-batch-delete-item',
                        size: 1,
                        totalCount: records.length,
                        data: records
                    }
                ]
            }
        });
        window.show();
    },


    onBatchDeleteItem: function(task, record, callback) {
        var me = this, proxy = record.getProxy();

        proxy.on('exception', function(proxy, response, operation, opts) {
            var data = Ext.decode(response.responseText);

            operation.setException(data.error);
            callback(response, operation);

        }, me, { single: true });

        record.destroy({
            success: function(result, operation) {
                callback(result, operation);
            }
        });
    },


    onSelectionChanged: function (grid, selModel, selection) {
        var me = this;

        if (!(grid instanceof Ext.grid.Panel)) {
            return false;
        }
        if (!grid.deleteButton) {
            return false;
        }
        grid.deleteButton.setDisabled(selection.length <= 0);
        return true;
    },


    onAddItem: function (listing) {
        var me = this, store = listing.getStore();
        var record = Ext.create(store.model);

        me.createDetailWindow(
            record,
            listing.getConfig('detailWindow')
        );
    },

    


    onDeleteItem: function (grid, record) {
//        var me = this;
//
//        if (!(record instanceof Ext.data.Model)) {
//            return false;
//        }
//        if (!me.hasModelAction(record, 'destroy')) {
//            grid.getStore().remove(record);
//            return true;
//        }
//
//        Ext.MessageBox.confirm('Delete item', 'Are you sure you want to delete this item?', function (response) {
//            if (response !== 'yes') {
//                return false;
//            }
//            record.destroy({
//                success: function(record, operation) {
//                    Shopware.Notification.createGrowlMessage('Success', 'Item deleted successfully');
//                },
//                failure: function(record, operation) {
//                    var rawData = record.getProxy().getReader().rawData;
//
//                    var message = 'An error occurred while deleting the record';
//                    if (Ext.isString(rawData.error) && rawData.error.length > 0) {
//                        message = message + '<br><br>' + rawData.error;
//                    }
//                    Shopware.Notification.createGrowlMessage('Failure', message);
//                }
//            });
//        });
    },




    onSearch: function (grid, searchField, value) {
        var store = grid.getStore();

        value = Ext.String.trim(value);
        store.filters.clear();
        store.currentPage = 1;

        if (value.length > 0) {
            store.filter({ property: 'search', value: value });
        } else {
            store.load();
        }
    },


    onChangePageSize: function (grid, combo, records) {
        var me = this,
            store = grid.getStore();

        if (combo.getValue() > 0) {
            store.pageSize = combo.getValue();
            store.currentPage = 1;
            store.load();
        }
    },


    /**
     * @param listing
     * @param record
     * @returns { boolean }
     */
    onEditItem: function (listing, record) {
        var me = this;

        if (!(record instanceof Ext.data.Model)) {
            return false;
        }

        if (me.hasModelAction(record, 'detail')) {
            record.reload({
                callback: function (result, operation) {
                    me.createDetailWindow(
                        result,
                        listing.getConfig('detailWindow')
                    );
                }
            });
        } else {
            me.createDetailWindow(
                record,
                listing.getConfig('detailWindow')
            );
        }
    },


    /**
     * Helper function which creates a detail window for the passed record.
     * The second parameter contains the detail window class name.
     *
     * @param record Shopware.data.Model - The record which will be displayed in the detail window
     * @param detailWindowClass string - Class name of the detail window
     */
    createDetailWindow: function (record, detailWindowClass) {
        var me = this;

        if (!detailWindowClass) {
            console.log("no detail window configured");
            return false;
        }

        me.getView(detailWindowClass).create({
            record: record
        }).show();
    },


    hasModelAction: function (model, action) {
        return (model.proxy && model.proxy.api && model.proxy.api[action]);
    },

    getModelName: function (modelName) {
        return modelName.substr(modelName.lastIndexOf(".") + 1);
    }
});
//{/block}
