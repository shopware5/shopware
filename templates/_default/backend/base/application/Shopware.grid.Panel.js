//{block name="backend/application/grid/panel"}

/**
 * The Shopware.grid.Panel components contains the Shopware boiler plate
 * code for a full featured backend listing.
 *
 * How to use:
 *  - The usage of the Shopware.grid.Panel is really simple.
 *  - The only thing you have to do, is to pass a Ext.data.Store to this component
 *  - Each CRUD operation will be handled by the Shopware.grid.Controller component.
 *  - To configure the different grid features you can use the following source as example:
 *  @example
 *      Ext.define('Shopware.apps.Product.view.list.Grid', {
 *          extend: 'Shopware.grid.Panel',
 *          displayConfig: {
 *              toolbar: false,
 *              ...
 *          }
 *      });
 *  - If you decide to handle all grid events by yourself you can extend the Shopware.grid.Controller
 *    and set the { @link #hasOwnController } property to false. In this case, shopware handles nothing for you for this component.
 *  - If you added some custom components you want to handle by yourself but the CRUD function should be handled,
 *    by shopware, you can add your event handlers normally and set the { @link #hasOwnController } property to false.
 *
 * This components fires the following shopware events:
 *  @event 'eventAlias-add-item'
 *  @event 'eventAlias-delete-items'
 *  @event `eventAlias-search`
 *  @event 'eventAlias-change-page-size'
 *  @event 'eventAlias-edit-item'
 *  @event 'eventAlias-delete-item'
 *
 * The events are documented in the { @link #registerEvents } function
 */
Ext.define('Shopware.grid.Panel', {

    /**
     * The parent class that this class extends
     * @type { String }
     */
    extend: 'Ext.grid.Panel',

    /**
     * Default alias of this component.
     * Used to create the component over an xtype
     * or for the component events.
     *
     * @type { string }
     */
    alias: 'widget.shopware-grid-panel',

    /**
     * List of classes to mix into this class.
     * @type { Object }
     */
    mixins: {
        helper: 'Shopware.model.Helper'
    },

    /**
     * Is defined, when the { @link #displayConfig.toolbar } property is set to true.
     * Created in the { @link #createToolbar } function.
     */
    toolbar: undefined,

    /**
     * Is defined, when the { @link #displayConfig.addButton } property is set to `true`.
     * Created in the { @link #createAddButton } function.
     *
     * @default { undefined }
     * @type { Ext.button.Button }
     */
    addButton: undefined,

    /**
     * Is defined, when the { @link #displayConfig.deleteButton } property is set to true
     * Created in the { @link #createDeleteButton } function.
     *
     * @default { undefined }
     * @type { Ext.button.Button }
     */
    deleteButton: undefined,

    /**
     * Is defined, when the { @link #displayConfig.searchField } property is set to true.
     * Created in the { @link #createSearchField } function.
     *
     * @default { undefined }
     * @type { Ext.form.field.Text }
     */
    searchField: undefined,

    /**
     * Is defined, when the { @link #displayConfig.pageSize } property is set to true.
     * Created in the { @link #createPageSizeCombo } function.
     *
     * @default { undefined }
     * @type { Ext.form.field.ComboBox }
     */
    pageSizeCombo: undefined,

    /**
     * Is defined, when the { @link #displayConfig.pagingbar } property is set to true.
     * created in the { @link #createPagingbar } function.
     *
     * @default { undefined }
     * @type { Ext.toolbar.Paging }
     */
    pagingbar: undefined,

    /**
     * Is defined, when the { @link #displayConfig.hasOwnController } property is set to false.
     *
     * Each grid component requires an own controller.
     * In order to avoid having to create a new controller for every component of an application,
     * the Shopware grid listing components generate their own controllers,
     * which manage the CRUD functions of the components.
     * If you wish to implement your own Shopware controller listing for managing
     * CRUD functions, simply set the property ‘hasOwnController’ to true.
     *
     * @default { undefined }
     * @type { Shopware.grid.Controller }
     */
    controller: undefined,

    /**
     * Contains the class name of the store model.
     * This property is used to create the { @link #eventAlias } for this component.
     * The value will be set automatically
     *
     * @default { undefined }
     * @type { Shopware.data.Model }
     */
    model: undefined,

    /**
     * Contains the event alias of this component.
     * To prevent an event naming conflict, each grid panel has an own
     * eventAlias prefix which added to each component event.
     * @example:
     * The store contains a model named: `Shopware.apps.Product.Model.Product`
     * Shopware creates the event alias: `product`
     * Each event has now the prefix `product`:  `product-add-item`
     *
     * @default { undefined }
     * @type { String }
     */
    eventAlias: undefined,

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
         * It contains properties for the single elements within this component
         * for example: "addButton" => displays an add button which allows the user
         * to add new row items.
         *
         * To override this property you can use the grid.displayConfig object.
         *
         * @example
         * Ext.define('Shopware.apps.Product.view.list.Grid', {
         *     extend: 'Shopware.grid.Panel',
         *     displayConfig: {
         *         toolbar: false,
         *         ...
         *     }
         * });
         */
        displayConfig: {

            /**
             * This is a required configuration property.
             * The detailWindow property contains the class name of your detail window.
             * If the property isn't configured, the add and edit function has no
             * effect.
             *
             * @required
             * @type { string }
             */
            detailWindow: undefined,

            /**
             * The event alias is used to customize the component events for each
             * backend application.
             * The event alias is an optional parameter. If the property is set to
             * undefined, the grid component use the model name as alias.
             * For example:
             *  - A store with Shopware.apps.Product.model.Product is passed to this component
             *  - The model alias will be set to "product"
             *  - All component events have now the prefix "product-..."
             *   - Example "product-add-item".
             *
             * @type { string }
             */
            eventAlias: undefined,

            /**
             * All shopware components works without defining an own application controller
             * for each single component.
             * The component events are handled over the default Shopware.grid.Controller
             * controller.
             * If you have an own application controller that handles all grid events,
             * set this property to "true" to prevent that the Shopware.grid.Controller
             * will handle the events of this component.
             * Additional if you have wrote an own controller that handles only
             * additional events, you can set this property to "false". In this case
             * all CRUD functions will be handled by Shopware.grid.Controller and your
             * own events can be handled in the own application controller.
             *
             * @type { boolean }
             */
            hasOwnController: false,

            /**
             * Enables the grid toolbar.
             * If you want to disable the whole shopware toolbar
             * you can set this property to false.
             * In this case all toolbar function won't be called.
             * If the property is set to true, the toolbar will be
             * created in the { @link #createToolbar } function.
             * If you want to add own components to grid toolbar,
             * you can override the { @link #createToolbarItems } function
             * and insert your items as follow:
             *
             * @example
             *  createToolbarItems: function() {
             *     var me = this, items;
             *
             *     items = me.callParent(arguments);
             *
             *     items = Ext.Array.insert(
             *         items, 2, [
             *            { xtype: 'button', text: 'MyButton', handler: function() { ... } }
             *        ]
             *     );
             *
             *     return items;
             *  },
             *
             *
             * @type { boolean }
             */
            toolbar: true,

            /**
             * Displays an add button within the grid toolbar.
             * Requires that the toolbar property is set to true.
             * If the property is set to true, the add button will be created
             * in the { @link #createAddButton } function and will be set in the internal
             * property { @link addButton }.
             * The add button allows the user to add new grid items over an detail page.
             *
             * @type { boolean }
             * @event 'eventAlias-add-item'
             *      @param { Shopware.grid.Panel } grid - Instance of this component
             *      @param { Ext.button.Button } button - The add button
             */
            addButton: true,

            /**
             * Displays a delete button within the grid toolbar.
             * Requires that the toolbar property is set to true.
             * If the property is set to true, the delete button will be created
             * in the createDeleteButton function and will be set in the internal
             * property "me.deleteButton".
             * The delete button allows the user to remove multiple grid items with a
             * single mouse click.
             *
             * @type { boolean }
             * @event 'eventAlias-delete-items'
             *      @param { Shopware.grid.Panel } grid - Instance of this component
             *      @param { Ext.button.Button } button - The add button
             *      @param { Array } selection - The current grid selection.
             */
            deleteButton: true,

            /**
             * Displays a search field within the grid toolbar.
             * Requires that the toolbar property is set to true.
             * If the property is set to true, the search field will be created
             * in the createSearchField function and will be set in the internal
             * property "me.searchField".
             * The search field allows the user to filter the grid items with a fulltext
             * search.
             *
             * @event `eventAlias-search`
             *      @param { Shopware.grid.Panel } grid - Instance of this component
             *      @param { Ext.form.field.Text } field - The searchField
             *      @param { String } value - The value of the searchField
             *
             * @type { boolean }
             */
            searchField: true,

            /**
             * Displays a Ext.toolbar.Paging bar at the bottom of the grid.
             * Allows to paginate the listing.
             * The paging bar will be created in the { @link #createPagingbar } function.
             *
             * @type { boolean }
             */
            pagingbar: true,

            /**
             * Displays an combo box to change the grid store page size.
             * The combo box will be created in the { @link #createPageSizeCombo } function.
             * If you want to configure your own page sizes, you can override the { @link #createPageSizes } function.
             * This function has to return an array with the allowed page sizes.
             *
             * @example
             *
             * createPageSizes: function() {
             *    return [
             *       { value: 20, name: '20x items' },
             *       { value: 40, name: '40x items' },
             *       { value: 60, name: '60x items' },
             *       ...
             *    ];
             * }
             *
             * @type { boolean }
             * @event 'eventAlias-change-page-size'
             *      @param { Shopware.grid.Panel } grid - Instance of this component
             *      @param { Ext.form.field.ComboBox } combo - The combo box field
             *      @param { Array } records - The selected records.
             */
            pageSize: true,

            /**
             * Defines if the grid contains an additional column at the end for special grid actions.
             * Shopware creates as default an delete and edit action column item.
             * If you set this property to false, the whole action column won't be created.
             * Otherwise the action column will be created in the { @link #createActionColumn } function
             * If you want to add own action column items, you can override the { @link #createActionColumnItems } function.
             *
             * @example
             *
             * createActionColumnItems: function() {
             *     var me = this, items;
             *     items = me.callParent(arguments);
             *     items.push(
             *         { action: 'actionName', iconCls: 'sprite-minus-circle-frame', handler: function() { ... }  }
             *     );
             *     return items;
             * }
             *
             * @type { boolean }
             */
            actionColumn: true,

            /**
             * Displays an edit column within the grid action column.
             * The edit action column item is used to open the detail window of a single record.
             * The column will be created in the { @link #createEditColumn } function.
             * Requires that the { @link #toolbar } property is set to true.
             *
             * @type { boolean }
             * @event 'eventAlias-edit-item'
             *      @param { Shopware.grid.Panel } grid - Instance of this component
             *      @param { Ext.data.Model } record - The record of the row.
             *      @param { int } rowIndex - Row index of the clicked item
             *      @param { int } colIndex - Column index of the clicked item.
             *      @param { object } item - The clicked item (or this Column if multiple items were not configured).
             *      @param { Event } opts - The click event.
             */
            editColumn: true,

            /**
             * Displays an delete column within the grid action column.
             * The delete action column item is used to delete a single grid row.
             * The column will be created in the { @link #createDeleteColumn } function.
             * Requires that the { @link #toolbar } property is set to true.
             *
             * @type { boolean }
             * @event 'eventAlias-delete-item'
             *      @param { Shopware.grid.Panel } grid - Instance of this component
             *      @param { Ext.data.Model } record - The record of the row.
             *      @param { int } rowIndex - Row index of the clicked item
             *      @param { int } colIndex - Column index of the clicked item.
             *      @param { object } item - The clicked item (or this Column if multiple items were not configured).
             *      @param { Event } opts - The click event.
             */
            deleteColumn: true,

            /**
             * Displays the row number of each row.
             *
             * @default { false }
             * @type { boolean }
             */
            rowNumbers: false,


            /**
             * Enables the Ext.grid.plugin.RowEditing plugin.
             * The plugin allows to modify the grid rows over a
             * row editor.
             *
             * @default { false }
             * @type { boolean }
             */
            rowEditing: false,

            /**
             * Column configuration object.
             * This object can contains different configuration for
             * the single grid columns.
             * The configuration will be assigned at least to the
             * generated column.
             * If you want to modify only the header or a single
             * property of each column you can use this property
             * for small and quick changes.
             *
             * The columns will be addressed over their name.
             * For example, you have an model field declared like this:
             *
             * Ext.define('Shopware.apps.Product.model.Product', {
             *     extend: 'Shopware.data.Model',
             *     fields: [
             *         ...
             *         { name: 'description', type: 'string', useNull: true },
             *     ]
             * });
             *
             * To modify the description column you can use the following example:
             *      columns: {
             *          description: { header: 'MyOwnDescription' }
             *      }
             */
            columns: { }
        },

        /**
         * Static function to merge the different configuration values
         * which passed in the class constructor.
         *
         * @param { Object } userOpts
         * @param { Object } displayConfig
         * @returns { Object }
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
         * @param { String } prop - Property which should be in the { @link #displayConfig }
         * @param { String } val - The value of the property (optional)
         * @returns { Boolean }
         */
        setDisplayConfig: function (prop, val) {
            var me = this;

            val = val || '';

            if (!me.displayConfig.hasOwnProperty(prop)) {
                return false;
            }
            me.displayConfig[prop] = val;
            return true;
        }
    },

    /**
     * Helper function to get config access.
     *
     * @param prop string
     * @returns mixed
     */
    getConfig: function (prop) {
        var me = this;
        return me._opts[prop];
    },

    /**
     * Class constructor which merges the different configurations.
     *
     * @param { Object } opts - Passed configuration
     */
    constructor: function (opts) {
        var me = this;

        me._opts = me.statics().getDisplayConfig(opts, this.displayConfig);
        me.callParent(arguments);
    },

    /**
     * Initialisation of this component.
     * Creates all required components for a default shopware listing.
     */
    initComponent: function () {
        var me = this;

        me.model = me.store.model;
        me.eventAlias = me.getConfig('eventAlias');
        if (!me.eventAlias) me.eventAlias = me.getEventAlias(me.model.$className);

        me.columns = me.createColumns();
        me.plugins = me.createPlugins();
        me.features = me.createFeatures();
        me.selModel = me.createSelectionModel();
        me.dockedItems = me.createDockedItems();

        me.registerEvents();
        if (me.getConfig('hasOwnController') === false) {
            me.createDefaultController();
        }

        me.callParent(arguments);
    },

    /**
     * Each grid component requires an own controller.
     *
     * In order to avoid having to create a new controller for every component of an application,
     * the Shopware grid listing components generate their own controllers,
     * which manage the CRUD functions of the components.
     * If you wish to implement your own Shopware controller listing for managing
     * CRUD functions, simply set the property ‘hasOwnController’ to true.
     *
     * @returns { Shopware.grid.Controller }
     */
    createDefaultController: function () {
        var me = this;

        me.controller = Ext.create('Shopware.grid.Controller', {
            displayConfig: {
                gridClass: me.$className,
                eventAlias: me.eventAlias
            },
            subApplication: me.subApp
        });
        me.controller.init();

        return me.controller;
    },

    /**
     * Registers the additional shopware events for this component
     */
    registerEvents: function () {
        var me = this;

        this.addEvents(
            /**
             * Event is fired when the user change the grid selection.
             * This event is event fired, when the selection changed
             * over a grid reload.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component
             * @param { Ext.button.Button } button - The add button
             */
            me.eventAlias + '-selection-changed',

            /**
             * Event is fired when the user clicks on the { @link #addButton }
             * to add new grid rows.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component
             * @param { Ext.button.Button } button - The add button
             */
            me.eventAlias + '-add-item',

            /**
             * Event is fired when the user clicks the { @link #deleteColumn }
             * icon to delete a single grid row.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component
             * @param { Ext.data.Model } record - The record of the row.
             * @param { int } rowIndex - Row index of the clicked item
             * @param { int } colIndex - Column index of the clicked item.
             * @param { object } item - The clicked item (or this Column if multiple items were not configured).
             * @param { Event } opts - The click event.
             */
            me.eventAlias + '-delete-item',

            /**
             * Event is fired when the user clicks on the { @link #deleteButton }
             * to delete all selected grid rows.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component
             * @param { Array } selection - The current grid selection.
             * @param { Ext.button.Button } button - The add button
             */
            me.eventAlias + '-delete-items',

            /**
             * Event is fired when the user clicks on the { @link #editColumn }
             * icon to open the detail window for a single grid row.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component
             * @param { Ext.data.Model } record - The record of the row.
             * @param { int } rowIndex - Row index of the clicked item
             * @param { int } colIndex - Column index of the clicked item.
             * @param { object } item - The clicked item (or this Column if multiple items were not configured).
             * @param { Event } opts - The click event.
             */
            me.eventAlias + '-edit-item',

            /**
             * Event is fired when the user insert a search term into the
             * { @link #searchField } to filter the grid rows.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component
             * @param { Ext.form.field.Text } field - The searchField
             * @param { String } value - The value of the searchField
             */
            me.eventAlias + '-search',

            /**
             * Event is fired when the user change the { @link #pageSizeCombo }
             * value to display more or less grid rows on a single page.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component
             * @param { Ext.form.field.ComboBox } combo - The combo box field
             * @param { Array } records - The selected records.
             */
            me.eventAlias + '-change-page-size'
        );
    },

    /**
     * Creates the grid columns for the grid.
     *
     * Returns an array with all columns which should be displayed
     * in the grid panel.
     *
     * The return value will be assigned to the grid panel property "grid.columns".
     *
     * To modify the result set you can use the following source code as example:
     * @example
     * createColumns: function() {
     *    var me = this, columns = [];
     *
     *    columns = me.callParent(arguments);
     *    columns.push(
     *       { header: 'MyColumn', dataIndex: 'myColumn', ... }
     *    );
     *
     *    return columns;
     * },
     *
     * To insert a column in a special array position you can use this source code as example:
     * @example
     *  createColumns: function() {
     *      var me = this, items;
     *      items = me.callParent(arguments);
     *      items = Ext.Array.insert(
     *          items, 2, [ me.createItem() ]
     *      );
     *      return items;
     *  }
     *
     * You can also override the whole function without a callParent line to
     * specify all grid columns by yourself.
     *
     * @returns { Array }
     */
    createColumns: function () {
        var me = this, model = null,
            column = null,
            columns = [];

        model = me.store.model.$className;

        if (model.length > 0) {
            model = Ext.create(model);
        }

        if (me.getConfig('rowNumbers')) {
            columns.push(me.createRowNumberColumn());
        }

        Ext.each(model.fields.items, function (item) {
            column = me.createColumn(model, item);
            if (column !== null) {
                columns.push(column);
            }
        });

        if (me.getConfig('actionColumn')) {
            column = me.createActionColumn();
            if (column !== null) {
                columns.push(column);
            }
        }

        return columns;
    },

    /**
     * Helper function which creates a grid column for a passed model field.
     * If you want to modify some columns but already want the shopware default configuration
     * you can use the following source as example:
     *
     * @example
     * createColumn: function (model, field) {
     *      var me = this, column;
     *      column = me.callParent(arguments);
     *      if (field.name = 'name') {
     *          field.header = 'MyOwnColumnHeader';
     *      }
     *      return column;
     * }
     *
     * @param { Ext.data.Model } model - The data model which contained in the passed grid store.
     * @param { Ext.data.Field } field - The model field which should be displayed in the grid
     * @returns { Object }
     */
    createColumn: function (model, field) {
        var me = this, column = {}, config, customConfig;

        if (model.idProperty === field.name) {
            return null;
        }

        column.xtype = 'gridcolumn';
        column.dataIndex = field.name;
        column.header = me.camelCaseToWord(field.name);
        column.flex = 1;

        switch (field.type.type) {
            case 'int':
                column = me.applyIntegerColumnConfig(column);
                break;
            case 'string':
                column = me.applyStringColumnConfig(column);
                break;
            case 'bool':
                column = me.applyBooleanColumnConfig(column);
                break;
            case 'date':
                column = me.applyDateColumnConfig(column);
                break;
            case 'float':
                column = me.applyFloatColumnConfig(column);
                break;
        }

        config = me.getConfig('columns');
        customConfig = config[field.name] || {};
        column = Ext.apply(column, customConfig);

        return column;
    },


    /**
     * Creates the action column for the grid panel.
     *
     * The action column item is only a container for the
     * different action column items.
     *
     * If the configuration { @link #actionColumn } is set to
     * false this function isn't called.
     *
     * The function is used from { @link #createColumns } function and the return value
     * will be pushed as last element of the columns array.
     *
     * @returns { Object }
     */
    createActionColumn: function () {
        var me = this, items;

        items = me.createActionColumnItems();

        return {
            xtype: 'actioncolumn',
            width: 30 * items.length,
            items: items
        }
    },

    /**
     * Creates the item array for the grid action column.
     *
     * If the configuration { @link #actionColumn }  is set to
     * false this function isn't called.
     *
     * The function returns an array of all defined action columns like
     * delete column or edit column.
     *
     * To add a new specify action column you can use the following source code:
     *
     * createActionColumnItems: function() {
     *     var me = this, items;
     *     items = me.callParent(arguments);
     *     items.push(
     *         { action: 'actionName', iconCls: 'sprite-minus-circle-frame', handler: function() { ... }  }
     *     );
     *     return items;
     * }
     *
     * @returns { Array }
     */
    createActionColumnItems: function () {
        var me = this, items = [];

        if (me.getConfig('deleteColumn')) {
            items.push(me.createDeleteColumn());
        }
        if (me.getConfig('editColumn')) {
            items.push(me.createEditColumn());
        }
        return items;
    },

    /**
     * Creates the delete action column item of the grid.
     * This column is used to delete a single record.
     *
     * If the configuration { @link #deleteColumn } or { @link #actionColumn } is set to
     * false this function isn't called.
     *
     * @return { Object }
     */
    createDeleteColumn: function () {
        var me = this;

        return {
            action: 'delete',
            iconCls: 'sprite-minus-circle-frame',
            handler: function (view, rowIndex, colIndex, item, opts, record) {
                me.fireEvent(me.eventAlias + '-delete-item', me, record, rowIndex, colIndex, item, opts);
            }
        };
    },

    /**
     * Creates the edit action column item of the grid.
     * This column is used to edit a single record in the detail view.
     *
     * If the configuration { @link #editColumn } or { @link #actionColumn } is set to
     * false this function isn't called.
     *
     * @return { Object }
     */
    createEditColumn: function () {
        var me = this;

        return {
            action: 'edit',
            iconCls: 'sprite-pencil',
            handler: function (view, rowIndex, colIndex, item, opts, record) {
                me.fireEvent(me.eventAlias + '-edit-item', me, record, rowIndex, colIndex, item, opts);
            }
        };
    },

    /**
     * Creates the row number column of the grid.
     *
     * If the configuration { @link #rowNumbers } is set to
     * false this function isn't called.
     *
     * The function is used from { @link #createColumns }  function and the
     * return value will be pushed as first element of the columns array.
     *
     * @return { Object }
     */
    createRowNumberColumn: function () {
        return { xtype: 'rownumberer', width: 30 };
    },

    /**
     * Creates all required grid plugins for a default shopware listing.
     *
     * This function is called from the { @link #initComponent } function and has no configurations which prevents
     * the function call.
     *
     * The return value will be assigned to the grid property grid.plugins
     * http://docs.sencha.com/extjs/4.1.2/#!/api/Ext.grid.Panel-cfg-plugins
     *
     * To add a new plugin which isn't contained in the shopware default you can use the following
     * source code in your own component:
     *
     * createPlugins: function() {
     *    var me = this, plugins;
     *
     *    plugins = me.callParent(arguments)
     *    plugins.push(
     *        {
     *           ptype: 'gridviewdragdrop',
     *           dragText: 'Drag and drop to reorganize'
     *       }
     *    );
     *
     *    return plugins;
     * },
     *
     * @returns { Array }
     */
    createPlugins: function () {
        var me = this, items = [], item;

        if (me.getConfig('rowEditing')) {
            item = Ext.create('Ext.grid.plugin.RowEditing', {
                clicksToEdit: 2
            });
            items.push(item)
        }

        return items;
    },

    /**
     * Creates all required grid features for a default shopware listing.
     *
     * This function is called from the { @link #initComponent } function and has no configurations which prevents
     * the function call.
     *
     * The return value will be assigned to the grid property grid.features
     * http://docs.sencha.com/extjs/4.1.2/#!/api/Ext.grid.Panel-cfg-features
     *
     * To add a new feature which isn't contained in the shopware default you can use the following
     * source code in your own component:
     *
     * createPlugins: function() {
     *    var me = this, features;
     *
     *    features = me.callParent(arguments)
     *    features.push(
     *        { ftype: 'grouping' }
     *    );
     *
     *    return features;
     * },
     * @returns { Array }
     */
    createFeatures: function () {
        return [];
    },

    /**
     * Creates the grid selection model.
     *
     * This function is called from the { @link #initComponent } function and has no configurations which prevents
     * the function call.
     *
     * The function creates an Ext.selection.CheckboxModel instance which is used from shopware as
     * default selection model in a listing.
     *
     * The return value will be assigned to the grid property grid.selModel
     * http://docs.sencha.com/extjs/4.1.2/#!/api/Ext.grid.Panel-cfg-selModel
     *
     * @returns { Ext.selection.CheckboxModel }
     */
    createSelectionModel: function () {
        var me = this;

        return Ext.create('Ext.selection.CheckboxModel', {
            listeners: {
                selectionchange: function (selModel, selection) {
                    return me.fireEvent(me.eventAlias + '-selection-changed', me, selModel, selection);
                }
            }
        });
    },

    /**
     * Wrapper function which has to create all required docked items, like the toolbar
     * or pagingbar.
     *
     * This function is called from the initComponent and has no configurations which prevents
     * the function call.
     *
     * The functions creates an array with the toolbar and the paging bar. This both components
     * can be disabled/enabled over displayConfig.pagingbar and displayConfig.toolbar property.
     *
     * The return value will be assigned to the component property grid.dockedItems.
     * http://docs.sencha.com/extjs/4.1.2/#!/api/Ext.grid.Panel-cfg-dockedItems
     *
     * @returns { Array }
     */
    createDockedItems: function () {
        var me = this, items = [];

        if (me.getConfig('toolbar')) {
            items.push(me.createToolbar());
        }
        if (me.getConfig('pagingbar')) {
            items.push(me.createPagingbar());
        }
        return items;
    },

    /**
     * Creates the grid paging bar.
     *
     * If the configuration { @link #pagingbar } is set to
     * false this function isn't called.
     *
     * The function is used from { @link #createDockedItems } function and sets the component
     * property "me.pagingbar" which is used in subsequently events.
     *
     * @return { Ext.toolbar.Paging }
     */
    createPagingbar: function () {
        var me = this;

        me.pagingbar = Ext.create('Ext.toolbar.Paging', {
            store: me.store,
            dock: 'bottom'
        });

        if (me.getConfig('pageSize')) {
            var pageSizeCombo = me.createPageSizeCombo();
            me.pagingbar.add('->', pageSizeCombo, { xtype: 'tbspacer', width: 6 });
        }

        return me.pagingbar;
    },

    /**
     * Creates the page size combo box for the grid paging bar.
     *
     * If the configurations { @link #pagingbar } or { @link #pageSize } is set to
     * false this function isn't called.
     *
     * The function is used from { @link #createPagingbar } function and sets the component
     * property "me.pageSizeCombo" which is used in subsequently events.
     *
     * @returns { Ext.form.field.ComboBox }
     */
    createPageSizeCombo: function () {
        var me = this, value = 20;

        if (me.store) {
            value = me.store.pageSize;
        }

        me.pageSizeCombo = Ext.create('Ext.form.field.ComboBox', {
            fieldLabel: 'Items per page',
            labelWidth: 110,
            queryMode: 'local',
            value: value,
            width: 200,
            store: Ext.create('Ext.data.Store', {
                fields: [ 'value', 'name' ],
                data: me.createPageSizes()
            }),
            displayField: 'name',
            valueField: 'value',
            listeners: {
                select: function (combo, records) {
                    me.fireEvent(me.eventAlias + '-change-page-size', me, combo, records);
                }
            }
        });
        return me.pageSizeCombo;
    },

    /**
     * Creates the page sizes for the page size combo box of the paging bar.
     *
     * If the configurations { @link #pagingbar } or { @link #pageSize } is set to
     * false this function isn't called.
     *
     * Returns an array with objects. Each object has a value property which contains
     * the integer value for the page size.
     * The name value contains a alphanumeric value which display in the combo box display field.
     *
     * @returns { Array }
     */
    createPageSizes: function () {
        var data = [];

        for (var i = 1; i <= 10; i++) {
            var count = i * 20;
            data.push({ value: count, name: count + ' items' });
        }

        return data;
    },

    /**
     * Creates the grid toolbar.
     *
     * If the configuration { @link #toolbar } is set to
     * false this function isn't called.
     *
     * The function is used from { @link #createDockedItems } function and sets the component
     * property "me.toolbar" which is used in subsequently events.
     *
     * @returns { Ext.toolbar.Toolbar }
     */
    createToolbar: function () {
        var me = this;

        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            dock: 'top',
            items: me.createToolbarItems()
        });

        return me.toolbar;
    },

    /**
     * Creates the toolbar items for the grid toolbar.
     *
     * If the configuration { @link #toolbar } is set to
     * false this function isn't called.
     *
     * The function is used from { @link #createToolbar } function and calls the internal
     * functions { @link #createAddButton }, { @link #createDeleteButton } and { @link #createSearchField }.
     *
     * The Ext.toolbar.Fill element is set on the third position. Each other element
     * after the Fill element will be displayed on the right side of the toolbar.
     *
     * To add an element on the left side of the toolbar, you can use the following source
     * code as example:
     *
     * @example
     *  createToolbarItems: function() {
     *     var me = this, items;
     *
     *     items = me.callParent(arguments);
     *
     *     items = Ext.Array.insert(
     *         items, 2, [
     *            { xtype: 'button', text: 'MyButton', handler: function() { ... } }
     *        ]
     *     );
     *
     *     return items;
     *  },
     *
     * @returns { Array }
     */
    createToolbarItems: function () {
        var me = this, items = [];

        if (me.getConfig('addButton')) {
            items.push(me.createAddButton());
        }
        if (me.getConfig('deleteButton')) {
            items.push(me.createDeleteButton())
        }
        if (me.getConfig('searchField')) {
            items.push('->');
            items.push(me.createSearchField());
        }

        return items;
    },

    /**
     * Creates the add button for the grid toolbar.
     *
     * If the configuration { @link #addButton } is set to
     * false this function isn't called.
     *
     * @returns { Ext.button.Button }
     */
    createAddButton: function () {
        var me = this;

        me.addButton = Ext.create('Ext.button.Button', {
            text: 'Add item',
            cls: 'secondary small',
            iconCls: 'sprite-plus-circle-frame',
            handler: function () {
                me.fireEvent(me.eventAlias + '-add-item', me, this);
            }
        });

        return me.addButton;
    },

    /**
     * Creates the delete button for the grid toolbar.
     *
     * If the configuration { @link #deleteButton } is set to
     * false this function isn't called.
     *
     * @returns { Ext.button.Button }
     */
    createDeleteButton: function () {
        var me = this;

        me.deleteButton = Ext.create('Ext.button.Button', {
            text: 'Delete all selected',
            disabled: true,
            cls: 'secondary small',
            iconCls: 'sprite-minus-circle-frame',
            handler: function () {
                var selModel = me.getSelectionModel();
                me.fireEvent(me.eventAlias + '-delete-items', me, selModel.getSelection(), this);
            }
        });

        return me.deleteButton;
    },

    /**
     * Creates the search field for the grid toolbar.
     *
     * If the configuration { @link #searchField } is set to
     * false this function isn't called.
     *
     * @returns { Ext.form.field.Text }
     */
    createSearchField: function () {
        var me = this;

        me.searchField = Ext.create('Ext.form.field.Text', {
            cls: 'searchfield',
            width: 170,
            emptyText: 'Search ...',
            enableKeyEvents: true,
            checkChangeBuffer: 500,
            listeners: {
                change: function (field, value) {
                    me.fireEvent(me.eventAlias + '-search', me, field, value);
                }
            }
        });

        return me.searchField;
    },


    /**
     * Helper function which is used from the { @link Shopware.detail.Controller }
     * to reload the associated record data.
     * This component requires only a store which is an instance of the Ext.data.Store.
     *
     * @param store
     * @param record
     */
    reloadData: function(store, record) {
        if (store instanceof Ext.data.Store) {
            this.reconfigure(store);
        }
    }


});

//{/block}