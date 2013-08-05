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
            /**
             * Final class of the Shopware.grid.Panel.
             * This class is required to get the alias of the component.
             *
             * @required
             * @type { string }
             */
            gridClass: undefined,

            /**
             * Suffix alias for the different component events.
             * This alias must the same alias of the { @link Shopware.grid.Panel:eventAlias }  component.
             * If you don't know the alias you can output the alias of the grid panel as follow:
             * console.log("alias", me.eventAlias);
             *
             * If you haven't configured a custom event alias, the { @link Shopware.grid.Panel } creates
             * the event alias over the configured model.
             * @example
             * If you passed a store with an model named: 'Shopware.apps.Product.model.Product'
             * the { @link Shopware.grid.Panel } use "product" as event alias.
             *
             * @required
             * @type { string }
             */
            eventAlias: undefined
        },

        /**
         * Static function to merge the different configuration values
         * which passed in the class constructor.
         *
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

        me.callParent(arguments);
    },

    /**
     * Creates the control object which contains all event listener
     * definitions for this controller.
     *
     * This function requires the displayConfig.listingGrid parameter.
     * If this parameter isn't set, the function won't be called.
     *
     * @returns { Object }
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
     * @returns { Object }
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


    /**
     * Creates all controls for the { @link Shopware.window.Progress } component.
     * This component is used as default for multiple item deletion.
     *
     * @returns { Object }
     */
    createProgressWindowControls: function () {
        var me = this, events = {};

        events[me.getConfig('eventAlias') + '-batch-delete-item'] = me.onBatchDeleteItem;

        return events;
    },

    /**
     * Event listener function of the { @link Shopware.grid.Panel } component.
     * This event is fired when the user uses the "delete items" button within the grid toolbar
     * to delete multiple items.
     *
     * The function creates an { @link Shopware.window.Progress } which deletes the items
     * in an batch mode.
     *
     * @param grid { Shopware.grid.Panel }
     * @param button { Ext.button.Button }
     * @param records { Array }
     */
    onDeleteItems: function (grid, button, records) {
        var me = this;

        var window = Ext.create('Shopware.window.Progress', {
            displayConfig: {
                infoText: '<b>The records will be deleted.</b> <br>To cancel the process, you can use the <b><i>`Cancel process`</i></b> Button. Depending on the selected volume of data may take several seconds to complete this process.',
                tasks: [
                    {
                        text: 'Item [0] of [1]',
                        event: me.getConfig('eventAlias') + '-batch-delete-item',
                        totalCount: records.length,
                        data: records
                    }
                ]
            }
        });

        if (!Shopware.app.Application.fireEvent(me.getEventName('before-open-delete-window'), me, window, grid, records)) {
            return;
        }

        window.show();
    },


    /**
     * Event listener function of the { @link Shopware.window.Progress:sequentialProcess } function.
     * This event fired for each record which passed to the progess window.
     *
     * @param task { Object }
     * @param record { Ext.data.Model }
     * @param callback { Function }
     */
    onBatchDeleteItem: function (task, record, callback) {
        var me = this, proxy = record.getProxy();

        proxy.on('exception', function (proxy, response, operation, opts) {
            var data = Ext.decode(response.responseText);

            operation.setException(data.error);

            if (!Shopware.app.Application.fireEvent(me.getEventName('batch-delete-exception'), me, task, record, response, operation)) {
                return;
            }

            callback(response, operation);

        }, me, { single: true });

        record.destroy({
            success: function (result, operation) {

                if (!Shopware.app.Application.fireEvent(me.getEventName('batch-delete-success'), me, task, record, result, operation)) {
                    return;
                }

                callback(result, operation);
            }
        });
    },


    /**
     * Event listener function of the { @link Shopware.grid.Panel:selectionModel } component.
     *
     *
     * @param grid { Shopware.grid.Panel }
     * @param selModel { Ext.selection.CheckboxModel }
     * @param selection { Array }
     * @returns { boolean }
     */
    onSelectionChanged: function (grid, selModel, selection) {
        var me = this;

        if (!(grid instanceof Ext.grid.Panel)) {
            return false;
        }
        if (!grid.deleteButton) {
            return false;
        }
        grid.deleteButton.setDisabled(selection.length <= 0);

        return Shopware.app.Application.fireEvent(me.getEventName('after-selection-changed'), me, grid, selModel, selection);
    },


    /**
     * Event listener function of the { @link Shopware.grid.Panel:addButton }.
     *
     * @param listing { Shopware.grid.Panel }
     */
    onAddItem: function (listing) {
        var me = this, record, store = listing.getStore();

        record = Ext.create(store.model);

        if (!Shopware.app.Application.fireEvent(me.getEventName('before-add-item'), me, listing, record)) {
            return;
        }

        me.createDetailWindow(
            record,
            listing.getConfig('detailWindow')
        );
    },


    onDeleteItem: function (grid, record) {
        var me = this;

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


    /**
     * Event listener function of the { @link Shopware.grid.Panel:createSearchField }
     * The event is fired when the user insert a search string into the grid toolbar.
     * The search field can be enabled or disabled over the { @link Shopware.grid.Panel:searchField } property.
     *
     * @param grid { Shopware.grid.Panel }
     * @param searchField { Ext.form.field.Text }
     * @param value { String }
     */
    onSearch: function (grid, searchField, value) {
        var me = this, store = grid.getStore();

        value = Ext.String.trim(value);
        store.filters.clear();
        store.currentPage = 1;

        if (!Shopware.app.Application.fireEvent(me.getEventName('before-search'), me, grid, store, searchField, value)) {
            return;
        }

        if (value.length > 0) {
            store.filter({ property: 'search', value: value });
        } else {
            store.load();
        }
    },

    /**
     * Event listener function of the { @link Shopware.grid.Panel:pageSizeCombo }.
     * The event is fired when the user change the combo box value to change the
     * grid store page size.
     *
     * @param grid { Shopware.grid.Panel }
     * @param combo { Ext.form.field.ComboBox }
     * @param records { Array }
     */
    onChangePageSize: function (grid, combo, records) {
        var me = this,
            store = grid.getStore();

        if (!Shopware.app.Application.fireEvent(me.getEventName('before-page-size-changed'), me, grid, combo, records)) {
            return;
        }

        if (combo.getValue() > 0) {
            store.pageSize = combo.getValue();
            store.currentPage = 1;
            store.load();
        }
    },


    /**
     * Event listener function of the { @link Shopware.grid.Panel:editColumn }.
     * The event is fired when the user clicks the action edit column
     * @param listing
     * @param record
     */
    onEditItem: function (listing, record) {
        var me = this;

        if (!(record instanceof Ext.data.Model)) {
            return;
        }

        if (!Shopware.app.Application.fireEvent(me.getEventName('before-page-size-changed'), me, listing, record)) {
            return;
        }

        if (me.hasModelAction(record, 'detail')) {
            record.reload({
                callback: function (result) {
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
        var me = this, window;

        if (!detailWindowClass) {
            return;
        }

        if (!Shopware.app.Application.fireEvent('before-create-detail-window', me, record)) {
            return;
        }

        window = me.getView(detailWindowClass).create({
            record: record
        });

        if (!Shopware.app.Application.fireEvent(me.getEventName('after-create-detail-window'), me, record, window)) {
            return;
        }

        if (window) {
            window.show();
        }
    },


    getEventName: function (name) {
        return this.getConfig('eventAlias') + '-' + name;
    },


    hasModelAction: function (model, action) {
        return (model.proxy && model.proxy.api && model.proxy.api[action]);
    },

    getModelName: function (modelName) {
        return modelName.substr(modelName.lastIndexOf(".") + 1);
    }
});
//{/block}
