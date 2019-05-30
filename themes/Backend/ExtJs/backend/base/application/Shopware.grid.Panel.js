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
 *          configure: function() {
 *              return {
 *                  toolbar: false,
 *                  ...
 *              }
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
 *  @event 'eventAlias-before-init-component'
 *  @event 'eventAlias-after-init-component'
 *  @event 'eventAlias-after-create-columns'
 *  @event 'eventAlias-column-created'
 *  @event 'eventAlias-action-column-created'
 *  @event 'eventAlias-before-create-action-column-items'
 *  @event 'eventAlias-after-create-action-column-items'
 *  @event 'eventAlias-delete-action-column-created'
 *  @event 'eventAlias-edit-action-column-created'
 *  @event 'eventAlias-before-create-plugins'
 *  @event 'eventAlias-after-create-plugins'
 *  @event 'eventAlias-selection-model-created'
 *  @event 'eventAlias-before-create-docked-items'
 *  @event 'eventAlias-after-create-docked-items'
 *  @event 'eventAlias-paging-bar-created'
 *  @event 'eventAlias-page-size-combo-created'
 *  @event 'eventAlias-before-create-page-sizes'
 *  @event 'eventAlias-after-create-page-sizes'
 *  @event 'eventAlias-toolbar-created'
 *  @event 'eventAlias-before-create-toolbar-items'
 *  @event 'eventAlias-before-create-right-toolbar-items'
 *  @event 'eventAlias-after-create-toolbar-items'
 *  @event 'eventAlias-add-button-created'
 *  @event 'eventAlias-delete-button-created'
 *  @event 'eventAlias-search-field-created'
 *  @event 'eventAlias-before-reload-data'
 *  @event 'eventAlias-after-reload-data'
 *  @event 'eventAlias-before-create-features'
 *  @event 'eventAlias-after-create-features'
 *
 * The events are documented in the { @link #registerEvents } function
 */
// {namespace name=backend/application/main}
// {block name="backend/application/Shopware.grid.Panel"}
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

    cls: 'shopware-grid-panel',

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
     * Contains the field label for the { @link #pageSizeCombo }.
     * The page size combo box is displayed in the grid pagingbar.
     *
     * @type { String }
     */
    pageSizeLabel: '{s name="grid_panel/page_size_label"}Items per page{/s}',

    /**
     * Contains the text for the { @link #addButton }.
     * The add button is displayed in the grid toolbar.
     * @type { String }
     */
    addButtonText: '{s name="grid_panel/add_button_text"}Add item{/s}',

    /**
     * Contains the text for the { @link #deleteButton }
     * @type { String }
     */
    deleteButtonText: '{s name="grid_panel/delete_button_text"}Delete all selected{/s}',

    /**
     * Contains the emptyText value for the { @link #searchField }.
     * @type { String }
     */
    searchFieldText: '{s name="grid_panel/search_field_text"}Search ...{/s}',

    /**
     * Contains the text for the { @link createPageSizes } method.
     * @type { String }
     */
    pagingItemText: '{s name="grid_panel/paging_toolbar/item_text"}items{/s}',

    /**
     * Get the reference to the class from which this object was instantiated. Note that unlike self, this.statics()
     * is scope-independent and it always returns the class from which it was called, regardless of what
     * this points to during run-time.
     *
     * The statics object contains the shopware default configuration for
     * this component. The different shopware configurations are stored
     * within the displayConfig object.
     *
     * @type { object }
     */
    statics: {
        /**
         * The statics displayConfig contains the default shopware configuration for
         * this component.
         * To set the shopware configuration, you can use the configure function and set an object as return value
         *
         * @example
         *      Ext.define('Shopware.apps.Product.view.list.Product', {
         *          extend: 'Shopware.grid.Panel',
         *          configure: function() {
         *              return {
         *                  detailWindow: 'Shopware.apps.Product.view.detail.Window',
         *                  eventAlias: 'product',
         *                  hasOwnController: true,
         *                  ...
         *              }
         *          }
         *      });
         */
        displayConfig: {

            /**
             * @required - For the add and edit action
             *
             * This is a required configuration property.
             * The detailWindow property contains the class name of your detail window.
             * If the property isn't configured, the add and edit function has no
             * effect.
             *
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
             * If enabled, shows progress window when delete column will be used to delete a single item
             */
            displayProgressOnSingleDelete: true,

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
            columns: { },

            /**
             * Shows the id property as a column
             *
             * @default { false }
             * @type { Boolean }
             */
            showIdColumn: false
        },

        /**
         * Static function to merge the different configuration values
         * which passed in the class constructor.
         * @param { Object } userOpts
         * @param { Object } definition
         * @returns Object
         */
        getDisplayConfig: function (userOpts, definition) {
            var config = { };

            if (userOpts && typeof userOpts.configure == 'function') {
                config = Ext.apply({ }, config, userOpts.configure());
            }
            if (definition && typeof definition.configure === 'function') {
                config = Ext.apply({ }, config, definition.configure());
            }
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
     * Override required!
     * This function is used to override the { @link #displayConfig } object of the statics() object.
     *
     * @returns { Object }
     */
    configure: function() {
        return { };
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

        me._opts = me.statics().getDisplayConfig(opts, this);

        me.callParent(arguments);
    },

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first, with each initComponent method up the hierarchy
     * to Ext.Component being called thereafter. This makes it easy to implement and, if needed, override the constructor
     * logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class'
     * initComponent method is also called.
     * All config options passed to the constructor are applied to this before initComponent is called, so you
     * can simply access them with this.someOption.
     */
    initComponent: function () {
        var me = this;

        me.checkRequirements();

        me.model = me.store.model;
        me.eventAlias = me.getConfig('eventAlias');
        if (!me.eventAlias) me.eventAlias = me.getEventAlias(me.model.$className);

        me.fieldAssociations = me.getAssociations(me.model.$className, [
            { relation: 'ManyToOne' }
        ]);

        me.registerEvents();
        me.fireEvent(me.eventAlias + '-before-init-component', me);

        me.columns = me.createColumns();
        me.plugins = me.createPlugins();
        me.features = me.createFeatures();
        me.selModel = me.createSelectionModel();
        me.dockedItems = me.createDockedItems();

        if (me.getConfig('hasOwnController') === false) {
            me.createDefaultController();
        }

        me.fireEvent(me.eventAlias + '-after-init-component', me);

        me.callParent(arguments);
    },

    /**
     * Helper function which checks all component requirements.
     */
    checkRequirements: function() {
        var me = this;

        if (!(me.store instanceof Ext.data.Store)) {
            me.throwException(me.$className + ': Component requires a configured store, which has to been passed in the class constructor.');
        }
        if (me.alias.length <= 0) {
            me.throwException(me.$className + ': Component requires a configured Ext JS widget alias.');
        }
        if (me.alias.length === 1 && me.alias[0] === 'widget.shopware-grid-panel') {
            me.throwException(me.$className + ': Component requires a configured Ext JS widget alias.');
        }
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
        var me = this,
            id = Ext.id();

        me.controller = Ext.create('Shopware.grid.Controller', {
            application: me.subApp,
            subApplication: me.subApp,
            subApp: me.subApp,
            $controllerId: id,
            id: id,
            configure: function () {
                return {
                    gridClass: me.$className,
                    eventAlias: me.eventAlias
                };
            }
        });
        me.controller.init();
        me.subApp.controllers.add(me.controller.$controllerId, me.controller);

        return me.controller;
    },

    /**
     * Event bus workaround.
     * The grid controller isn't assigned to any sub application.
     * To prevent a duplicate event handling, the controller event listeners
     * has to be destroyed if the detail window will be destroyed.
     *
     * @returns { Object }
     */
    destroy: function () {
        var me = this;
        if (!me.getConfig('hasOwnController') && me.controller) {
            me.subApp.removeController(me.controller);
        }
        return me.callParent(arguments);
    },

    /**
     * Registers the additional shopware events for this component
     */
    registerEvents: function () {
        var me = this;

        this.addEvents(
            /**
             * Event fired when the user change the grid selection.
             * This event is event fired, when the selection changed
             * over a grid reload.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component
             * @param { Ext.selection.Model } selModel - The selection model of the grid panel
             * @param { Array } records - The selected records.
             */
            me.eventAlias + '-selection-changed',

            /**
             * Event fired when the user clicks on the { @link #addButton }
             * to add new grid rows.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component
             * @param { Ext.button.Button } button - The add button
             */
            me.eventAlias + '-add-item',

            /**
             * Event fired when the user clicks the { @link #deleteColumn }
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
             * Event fired when the user clicks on the { @link #deleteButton }
             * to delete all selected grid rows.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component
             * @param { Array } selection - The current grid selection.
             * @param { Ext.button.Button } button - The add button
             */
            me.eventAlias + '-delete-items',

            /**
             * Event fired when the user clicks on the { @link #editColumn }
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
             * Event fired when the user insert a search term into the
             * { @link #searchField } to filter the grid rows.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component
             * @param { Ext.form.field.Text } field - The searchField
             * @param { String } value - The value of the searchField
             */
            me.eventAlias + '-search',

            /**
             * Event fired when the user change the { @link #pageSizeCombo }
             * value to display more or less grid rows on a single page.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component
             * @param { Ext.form.field.ComboBox } combo - The combo box field
             * @param { Array } records - The selected records.
             */
            me.eventAlias + '-change-page-size',

            /**
             * Event fired before the component will be initialed. The event
             * alias is at this point already defined.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component.
             */
            me.eventAlias + '-before-init-component',

            /**
             * Event fired after all default elements of this component created.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component.
             */
            me.eventAlias + '-after-init-component',

            /**
             * Event fired before the grid columns created. This event can be used
             * to add additional column over the Ext JS event system.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component.
             * @param { Array } columns - An empty array which will be returned as column array.
             */
            me.eventAlias + '-before-create-columns',

            /**
             * Event fired before the grid action column created. This event can be used
             * to add additional column over the Ext JS event system.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component.
             * @param { Array } columns - An empty array which will be returned as column array.
             */
            me.eventAlias + '-before-create-action-columns',

            /**
             * Event fired after the grid columns created. This event can be used
             * to add additional column over the Ext JS event system.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component.
             * @param { Array } columns - The filled columns array contains all generated columns.
             */
            me.eventAlias + '-after-create-columns',

            /**
             * Event fired after a single grid column was created.
             * This event can be used to modify a single grid column over the Ext JS event system.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component.
             * @param { Object } column - The created column object.
             * @param { Shopware.data.Model } model - The model which contains the field for the column
             * @param { Ext.data.Field } field - The current model field which used to generate the column
             */
            me.eventAlias + '-column-created',

            /**
             * Event fired after the grid action column created. This event can be used
             * to modify the action column over the Ext JS event system.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component.
             * @param { Object } column - The created action column object.
             */
            me.eventAlias + '-action-column-created',

            /**
             * Event fired before the default shopware action column items will be created.
             * The items parameter contains an empty array which will be returned as action column items.
             * This event can be used to add some action column items at the beginning of the column.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component.
             * @param { Array } items - An empty array
             */
            me.eventAlias + '-before-create-action-column-items',

            /**
             * Event fired after the default shopware action column items created.
             * The passed items array contains the created action column items.
             * This event can be used to add some action column items at the end of the column.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component.
             * @param { Array } items - Contains the generated action column items.
             */
            me.eventAlias + '-after-create-action-column-items',

            /**
             * Event fired after the default shopware delete action colunn item was created.
             * This event can be used to modify the action column over the Ext JS event system.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component.
             * @param { Object } column - The generated delete action column item.
             */
            me.eventAlias + '-delete-action-column-created',

            /**
             * Event fired after the default shopware edit action column item was created.
             * This event can be used to modify the default edit column over the Ext JS event system.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component.
             * @param { Object } column - The generated edit action column item.
             */
            me.eventAlias + '-edit-action-column-created',

            /**
             * Event fired before the default shopware grid plugins will be created.
             * The passed items array can be used to add additional plugins.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component.
             * @param { Array } items - An empty array which can be used to add additional plugins.
             */
            me.eventAlias + '-before-create-plugins',

            /**
             * Event fired after the default shopware grid plugins created.
             * This event can be used to add additional plugins at the end of the plugins array.
             * Some plugins requires additional plugins which initialed before the own plugin can be created.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component.
             * @param { Array } items - Contains the generated plugins.
             */
            me.eventAlias + '-after-create-plugins',

            /**
             * Event fired after the default shopware selection model created.
             * This event can be used to modify the selection model or add some event listeners
             * on the component.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component.
             * @param { Ext.selection.Model } selModel - The created grid selection model
             */
            me.eventAlias + '-selection-model-created',

            /**
             * Event fired before the default shopware docked items created.
             * This event can be used to insert some items at the beginning of the docked items array.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component.
             * @param { Array } items - An empty array which returned as docked items definition.
             */
            me.eventAlias + '-before-create-docked-items',

            /**
             * Event fired after the default shopware docked items created.
             * This event can be used to add some items at the end of the docked items array.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component.
             * @param { Array } items - Contains the created docked items which returned as docked items definition.
             */
            me.eventAlias + '-after-create-docked-items',

            /**
             * Event fired after the default shopware paging bar created.
             * This event can be used to add some event listeners or modify the view of the paging bar.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component.
             * @param { Ext.toolbar.Paging } pagingbar - The created paging bar.
             */
            me.eventAlias + '-paging-bar-created',

            /**
             * Event fired after the page size combo box of the paging bar created.
             * This event can be used to add some event listeners or modify the view of the page size combo.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component.
             * @param { Ext.form.field.ComboBox } combo - The created combo box.
             */
            me.eventAlias + '-page-size-combo-created',

            /**
             * Event fired before the different page sizes of the paging bar created.
             * This event can be used to insert some sizes at the beginning of the array.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component.
             * @param { Array } data - An empty array which used as page size combo store data definition.
             */
            me.eventAlias + '-before-create-page-sizes',

            /**
             * Event fired after the default shopware page sizes created.
             * This event can be used to push some sizes at the end of the array.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component.
             * @param { Array } data - Contains the already generated page sizes, which used as page size combo store data definition.
             */
            me.eventAlias + '-after-create-page-sizes',

            /**
             * Event fired after the default shopware toolbar created.
             * This event can be used add some even listeners to the toolbar or to modify the view of the toolbar.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component.
             * @param { Ext.toolbar.Toolbar } toolbar - The created toolbar instance.
             */
            me.eventAlias + '-toolbar-created',

            /**
             * Event fired before the shopware default toolbar items created.
             * This event can be used to insert some toolbar items at the beginning of the array.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component.
             * @param { Array } items - Empty array at this point, used as definition for the toolbar items.
             */
            me.eventAlias + '-before-create-toolbar-items',

            /**
             * Event fired before the toolbar fill element added to the toolbar.
             * This event can be used to add some toolbar items before the fill element to display
             * them after the default shopware items but not on the right side of the toolbar.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component.
             * @param { Array } items - Contains the already created toolbar items like the add or delete button
             */
            me.eventAlias + '-before-create-right-toolbar-items',

            /**
             * Event fired after all toolbar items created. This event can be used
             * to push some toolbar items at the end of the array.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component.
             * @param { Array } items - Contains the created toolbar items.
             */
            me.eventAlias + '-after-create-toolbar-items',

            /**
             * Event fired after the default shopware toolbar add button created.
             * This event can be used to modify the button view or to add some event listeners
             * to the add button.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component.
             * @param { Ext.button.Button } button - Contains the button instance
             */
            me.eventAlias + '-add-button-created',

            /**
             * Event fired after the default shopware toolbar delete button created.
             * This event can be used to modify the button view or to add some event listeners
             * to the button.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component.
             * @param { Ext.button.Button } button - Instance of the created delete button
             */
            me.eventAlias + '-delete-button-created',

            /**
             * Event fired after the default shopware search field created.
             * This event can be used to modify the text field view or to add some event listeners
             * to the search field.
             * @param { Shopware.grid.Panel } grid - Instance of this component.
             * @param { Ext.form.field.Text } field - Instance of the created search field.
             */
            me.eventAlias + '-search-field-created',

            /**
             * Event fired before the grid panel data will be reloaded. To cancel the reload
             * add an event listener to this event and set the return value to false.
             * The { @link #reloadData } function is an interface function which is used in the detail
             * view to reload associated data of a record.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component.
             * @param { Ext.data.Store } store - Instance of the grid store.
             * @param { Shopware.data.Model } record - Contains null in this component
             */
            me.eventAlias + '-before-reload-data',

            /**
             * Event fired after the grid store reconfigured.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component.
             * @param { Ext.data.Store } store - Instance of the grid store.
             * @param { Shopware.data.Model } record - Contains null in this component
             */
            me.eventAlias + '-after-reload-data',

            /**
             * Event fired before the default shopware grid features created.
             * This event can be used to insert some grid features at the beginning of the feature definition.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component.
             * @param { Array } items - An empty array which is used as feature definition.
             */
            me.eventAlias + '-before-create-features',

            /**
             * Event fired after the default shopware grid features created.
             * Some features requires additional features or has collision with some grid plugins, so
             * the { @link #createFeatures } function contains an before and after event for the feature
             * definition.
             *
             * @param { Shopware.grid.Panel } grid - Instance of this component.
             * @param { Array } items - Contains the created grid features.
             */
            me.eventAlias + '-after-create-features'
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
            configColumns = me.getConfig('columns'),
            columns = [];

        model = me.store.model.$className;

        if (model.length > 0) {
            model = Ext.create(model);
        }

        me.fireEvent(me.eventAlias + '-before-create-columns', me, columns);

        if (me.getConfig('rowNumbers')) {
            columns.push(me.createRowNumberColumn());
        }

        var keys = model.fields.keys;
        if (Object.keys(configColumns).length > 0) keys = Object.keys(configColumns);

        Ext.each(keys, function(key) {
            var modelField = me.getFieldByName(model.fields.items, key);
            column = me.createColumn(model, modelField);

            // column created? then push it into the columns array
            if (column !== null) columns.push(column);
        });

        me.fireEvent(me.eventAlias + '-before-create-action-columns', me, columns);

        if (me.getConfig('actionColumn')) {
            column = me.createActionColumn();
            if (column !== null) {
                columns.push(column);
            }
        }

        me.fireEvent(me.eventAlias + '-after-create-columns', me, columns);

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

        if (model.idProperty === field.name && !me.getConfig('showIdColumn')) {
            return null;
        }

        column.xtype = 'gridcolumn';
        column.dataIndex = field.name;
        column.header = me.getHumanReadableWord(field.name);

        var fieldAssociation = me.getFieldAssociation(field.name);

        if (fieldAssociation === undefined) {
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
                case 'datetime':
                    column = me.applyDateColumnConfig(column, field.dateFormat);
                    break;
                case 'float':
                    column = me.applyFloatColumnConfig(column);
                    break;
            }
        } else {
            column.association = fieldAssociation;
            column.renderer = me.associationColumnRenderer;
        }

        config = me.getConfig('columns');
        customConfig = config[field.name] || {};

        if (Ext.isString(customConfig)) customConfig = { header: customConfig };

        if (Ext.isObject(customConfig)) {
            column = Ext.apply(column, customConfig);
        } else if (Ext.isFunction(customConfig)) {
            column = customConfig.call(this, model, column, field, fieldAssociation);
        }

        if (!column.flex && !column.width) {
            column.flex = 1;
        }

        me.fireEvent(me.eventAlias + '-column-created', me, column, model, field);

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
        var me = this, items, column;

        items = me.createActionColumnItems();

        column = {
            xtype: 'actioncolumn',
            width: 30 * items.length,
            items: items
        };

        me.fireEvent(me.eventAlias + '-action-column-created', me, column);

        return column;
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

        me.fireEvent(me.eventAlias + '-before-create-action-column-items', me, items);

        if (me.getConfig('deleteColumn')) {
            items.push(me.createDeleteColumn());
        }
        if (me.getConfig('editColumn')) {
            items.push(me.createEditColumn());
        }

        me.fireEvent(me.eventAlias + '-after-create-action-column-items', me, items);

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
        var me = this, column;

        column = {
            action: 'delete',
            iconCls: 'sprite-minus-circle-frame',
            handler: Ext.bind(me._onDelete, me)
        };

        me.fireEvent(me.eventAlias + '-delete-action-column-created', me, column);

        return column;
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
        var me = this, column;

        column = {
            action: 'edit',
            iconCls: 'sprite-pencil',
            handler: Ext.bind(me._onEdit, me)
        };

        me.fireEvent(me.eventAlias + '-edit-action-column-created', me, column);

        return column;
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
        var me = this, items = [];

        me.fireEvent(me.eventAlias + '-before-create-plugins', me, items);

        if (me.getConfig('rowEditing')) {
            me.rowEditor = Ext.create('Ext.grid.plugin.RowEditing', {
                clicksToEdit: 2
            });
            items.push(me.rowEditor);
        }

        me.fireEvent(me.eventAlias + '-after-create-plugins', me, items);

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
        var me = this, items = [];

        me.fireEvent(me.eventAlias + '-before-create-features', me, items);

        me.fireEvent(me.eventAlias + '-after-create-features', me, items);

        return items;
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
        var me = this, selModel;

        selModel = Ext.create('Ext.selection.CheckboxModel', {
            listeners: {
                selectionchange: Ext.bind(me.onSelectionChange, me)
            }
        });

        me.fireEvent(me.eventAlias + '-selection-model-created', me, selModel);

        return selModel;
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

        me.fireEvent(me.eventAlias + '-before-create-docked-items', me, items);

        if (me.getConfig('toolbar')) {
            items.push(me.createToolbar());
        }
        if (me.getConfig('pagingbar')) {
            items.push(me.createPagingbar());
        }

        me.fireEvent(me.eventAlias + '-after-create-docked-items', me, items);

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
            dock: 'bottom',
            displayInfo: true
        });

        if (me.getConfig('pageSize')) {
            var pageSizeCombo = me.createPageSizeCombo();
            me.pagingbar.add(pageSizeCombo, { xtype: 'tbspacer', width: 6 });
        }

        me.fireEvent(me.eventAlias + '-paging-bar-created', me, me.pagingbar);

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
            fieldLabel: me.pageSizeLabel,
            labelWidth: 110,
            cls: 'page-size-combo',
            queryMode: 'local',
            value: value,
            width: 220,
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

        me.fireEvent(me.eventAlias + '-page-size-combo-created', me, me.pageSizeCombo);

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
        var me = this, data = [];

        me.fireEvent(me.eventAlias + '-before-create-page-sizes', me, data);

        for (var i = 1; i <= 10; i++) {
            var count = i * 20;
            data.push({ value: count, name: count + ' ' + me.pagingItemText });
        }

        me.fireEvent(me.eventAlias + '-after-create-page-sizes', me, data);

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
            ui: 'shopware-ui',
            items: me.createToolbarItems()
        });

        me.fireEvent(me.eventAlias + '-toolbar-created', me, me.toolbar);

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

        me.fireEvent(me.eventAlias + '-before-create-toolbar-items', me, items);

        if (me.getConfig('addButton')) {
            items.push(me.createAddButton());
        }
        if (me.getConfig('deleteButton')) {
            items.push(me.createDeleteButton());
        }

        me.fireEvent(me.eventAlias + '-before-create-right-toolbar-items', me, items);

        if (me.getConfig('searchField')) {
            items.push('->');
            items.push(me.createSearchField());
        }

        me.fireEvent(me.eventAlias + '-after-create-toolbar-items', me, items);

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
            text: me.addButtonText,
            iconCls: 'sprite-plus-circle-frame',
            handler: Ext.bind(me.onAddItem, me)
        });

        me.fireEvent(me.eventAlias + '-add-button-created', me, me.addButton);

        return me.addButton;
    },

    createNewRecord: function() {
        return Ext.create(this.store.model);
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
            text: me.deleteButtonText,
            disabled: true,
            iconCls: 'sprite-minus-circle-frame',
            handler: Ext.bind(me._onMultiDelete, me)
        });

        me.fireEvent(me.eventAlias + '-delete-button-created', me, me.deleteButton);

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
            emptyText: me.searchFieldText,
            enableKeyEvents: true,
            checkChangeBuffer: 500,
            listeners: {
                change: function (field, value) {
                    me.searchEvent(field, value);
                }
            }
        });

        me.fireEvent(me.eventAlias + '-search-field-created', me, me.searchField);

        return me.searchField;
    },

    searchEvent: function(field, value) {
        var me = this;

        me.fireEvent(me.eventAlias + '-search', me, field, value);
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
        var me = this;

        if (store instanceof Ext.data.Store) {
            if (!me.fireEvent(me.eventAlias + '-before-reload-data', me, store, record)) {
                return false;
            }

            me.reconfigure(store);

            me.fireEvent(me.eventAlias + '-after-reload-data', me, store, record);
        }
    },

    /**
     * Helper function which checks if an many to one association is configured for
     * the passed field.
     *
     * @param fieldName { String }
     * @returns { undefined|Ext.data.association.Association }
     */
    getFieldAssociation: function(fieldName) {
        var me = this, fieldAssociation = undefined;

        Ext.each(me.fieldAssociations, function(association) {
            if (association.field === fieldName) {
                fieldAssociation = association;
                return false;
            }
        });
        return fieldAssociation;
    },

    /**
     * Renderer function of an association column field.
     * This function is used for foreign key columns which contains
     * initial the numeric foreign key.
     * This renderer function converts the foreign key value to
     * a human readable value.
     *
     * @param { mixed } value - The foreign key value
     * @param { Object } metaData - Cell meta data
     * @param { Ext.data.Model } record - The record of the grid row
     * @param { int } rowIndex - Index of the grid row
     * @param { int } colIndex - Index of the grid column
     * @returns { String }
     */
    associationColumnRenderer: function(value, metaData, record, rowIndex, colIndex) {
        var column = this.columns[colIndex], result;

        // check if the association was assigned to the grid column
        if (!column.association) {
            return value;
        }
        // if the association assigned, we can get the association store of the record
        var associationStore = record[column.association.storeName];

        // check if the association was loaded through the listing query
        if (!(associationStore instanceof Ext.data.Store) || associationStore.getCount() <= 0) {
            return value;
        }

        // get the first record of the store to display the human readable data
        var associationRecord = associationStore.first();
        if (!(associationRecord instanceof Ext.data.Model)) {
            return value;
        }

        result = associationRecord.get('name');
        if (result) return result;

        result = associationRecord.get('description');
        if (result) return result;

        return value;
    },

    onAddItem: function() {
        var me = this;
        me.fireEvent(me.eventAlias + '-add-item', me, me.createNewRecord());
    },

    _onMultiDelete: function () {
        var me = this;
        var selModel = me.getSelectionModel();
        me.fireEvent(me.eventAlias + '-delete-items', me, selModel.getSelection(), this);
    },

    /**
     * @param selModel
     * @param selection
     */
    onSelectionChange: function(selModel, selection) {
        var me = this;
        return me.fireEvent(me.eventAlias + '-selection-changed', me, selModel, selection);
    },

    _onDelete: function (view, rowIndex, colIndex, item, opts, record) {
        var me = this;
        me.fireEvent(me.eventAlias + '-delete-item', me, record, rowIndex, colIndex, item, opts);
    },

    _onEdit: function (view, rowIndex, colIndex, item, opts, record) {
        var me = this;
        me.fireEvent(me.eventAlias + '-edit-item', me, record, rowIndex, colIndex, item, opts);
    }
});

// {/block}
