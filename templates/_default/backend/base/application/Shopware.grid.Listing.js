
//{block name="backend/component/grid/panel"}
Ext.define('Shopware.grid.Listing', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.shopware-grid-panel',

    statics: {
        /**
         * The statics display config is the shopware default configuration for
         * this component.
         * It contains properties for the single elements within this component
         * for example: "addButton" => displays an add button which allows the user
         * to add new row items.
         */
        displayConfig: {

            //toolbar configurations
            toolbar: true,
            addButton: true,
            deleteButton: true,
            searchField: true,

            //paging bar configuration
            pagingbar: true,
            pageSize: true,

            //action column configuration
            actionColumn: true,
            editColumn: true,
            deleteColumn: true,

            //additional configurations
            rowNumbers: false
        },

        /**
         * Static function to merge the different configuration values
         * which passed in the class constructor.
         * @param userOpts Object
         * @param displayConfig Object
         * @returns Object
         */
        getDisplayConfig: function(userOpts, displayConfig) {
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
        setDisplayConfig: function(prop, val) {
            var me = this;

            if(!me.displayConfig.hasOwnProperty(prop)) {
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
    Config: function(prop) {
        var me = this;
        return me._opts[prop];
    },

    /**
     * Class constructor which merges the different configurations.
     * @param opts
     */
    constructor: function(opts) {
        var me = this;

        me._opts = me.statics().getDisplayConfig(opts, this.displayConfig);
        me.callParent(arguments);
    },

    /**
     * Initialisation of this component.
     *
     * Creates all required components for a default shopware listing.
     */
    initComponent: function() {
        var me = this;

        me.columns = me.createColumns();
        me.plugins = me.createPlugins();
        me.features = me.createFeatures();
        me.selModel = me.createSelectionModel();
        me.dockedItems = me.createDockedItems();
        me.registerEvents();
        me.callParent(arguments);
    },

    /**
     * Registers the additional shopware events for this component
     */
    registerEvents: function() {
        this.addEvents('selectionChanged','addItem','deleteItem','editItem','search','changePageSize');
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
     *
     * createColumns: function() {
     *    var me = this, columns = [];
     *
     *    columns = me.callParent(arguments);
     *    columns.push(me.createAdditionalColumn();
     *    return columns;
     * },
     *
     * You can also override the whole function without a callParent line to
     * specify all grid columns by yourself.
     *
     * @returns Array
     */
    createColumns: function() {
        var me = this, model = null,
            column = null,
            columns = [];

        model = me.store.model.$className;

        if (model.length > 0) {
            model = Ext.create(model);
        }

        if (me.Config('rowNumbers')) {
            columns.push(me.createRowNumberColumn());
        }
        Ext.each(model.fields.items, function(item, index) {
            column = me.createFieldColumn(model, item);
            if (column !== null) {
                columns.push(column);
            }
        });

        if (me.Config('actionColumn')) {
            column = me.createActionColumn();
            if (column !== null) {
                columns.push(column);
            }
        }

        return columns;
    },

    /**
     * Creates the action column for the grid panel.
     *
     * The action column item is only a container for the
     * different action column items.
     *
     * If the configuration displayConfig.actionColumn is set to
     * false this function isn't called.
     *
     * The function is used from createColumns function and return value will
     * be pushed as last element of the columns array.
     */
    createActionColumn: function() {
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
     * If the configuration displayConfig.actionColumn is set to
     * false this function isn't called.
     *
     * The function returns an array of all defined action columns like
     * delete column or edit column.
     *
     * To add a new specify action column you can use the following source code:
     *
     *
     * createActionColumnItems: function() {
     *    var me = this, items = [];
     *
     *    items = me.callParent(arguments)
     *    items.push(me.createMyActionColumnItem);
     *    return items;
     * }
     *
     */
    createActionColumnItems: function() {
        var me = this, items = [];

        if (me.Config('deleteColumn')) {
            items.push(me.createDeleteColumn());
        }
        if (me.Config('editColumn')) {
            items.push(me.createEditColumn());
        }
        return items;
    },

    /**
     * Creates the delete action column item of the grid.
     * This column is used to delete a single record.
     *
     * If the configuration displayConfig.deleteColumn is set to
     * false this function isn't called.
     *
     * @return Object
     */
    createDeleteColumn: function() {
        var me = this;

        return {
            action:'delete',
            iconCls:'sprite-minus-circle-frame',
            handler: function (view, rowIndex, colIndex, item, opts, record) {
                me.onDeleteItem(view, rowIndex, colIndex, item, opts, record);
            }
        };
    },

    /**
     * Creates the edit action column item of the grid.
     * This column is used to edit a single record in the detail view.
     *
     * If the configuration displayConfig.editColumn is set to
     * false this function isn't called.
     *
     * @return Object
     */
    createEditColumn: function() {
        var me = this;

        return {
            action: 'edit',
            iconCls: 'sprite-pencil',
            handler: function (view, rowIndex, colIndex, item, opts, record) {
                me.onEditItem(view, rowIndex, colIndex, item, opts, record);
            }
        };
    },

    /**
     * Creates the row number column of the grid.
     *
     * If the configuration displayConfig.rowNumbers is set to
     * false this function isn't called.
     *
     * The function is used from createColumns function and return value will
     * be pushed as first element of the columns array.
     *
     * @return Object
     */
    createRowNumberColumn: function() {
        return { xtype: 'rownumberer', width: 30 };
    },

    /**
     * Helper function which creates a grid column for a passed model field.
     *
     * Override this function to prevent the field
     *
     * @param field Ext.data.Field
     * @returns Object
     * @param model Ext.data.Model
     */
    createFieldColumn: function(model, field) {
        var me = this, column = {};

        if (model.idProperty === field.name) {
            return null;
        }

        column.xtype = 'gridcolumn';
        column.dataIndex = field.name;
        column.header = me.createColumnHeader(model, field);
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

        return column;
    },

    /**
     * Helper function to create the grid column header
     * for the passed model field.
     *
     * @param field
     * @param model
     */
    createColumnHeader: function(model, field) {
        var name = field.name;

        name = name.split(/(?=[A-Z])/).map(function(p) {
            return p.charAt(0).toLowerCase() + p.slice(1);
        }).join(' ');

        name = name.charAt(0).toUpperCase() + name.slice(1);

        return name;
    },

    /**
     * Creates all required grid plugins for a default shopware listing.
     *
     * This function is called from the initComponent function and has no configurations which prevents
     * the function call.
     *
     * The return value will be assigned to the grid property grid.plguins
     * http://docs.sencha.com/extjs/4.1.2/#!/api/Ext.grid.Panel-cfg-plugins
     *
     * To add a new plugin which isn't contained in the shopware default you can use the following
     * source code in your own component:
     *
     * createPlugins: function() {
     *    var me = this, plugins = [];
     *
     *    plugins = me.callParent(arguments)
     *    plugins.push(me.createMyPlugin());
     *
     *    return plugins;
     * },
     *
     *
     * @returns Array
     */
    createPlugins: function() {
        var me = this, plugins = [];

        return plugins;
    },

    /**
     * Creates all required grid features for a default shopware listing.
     *
     * This function is called from the initComponent function and has no configurations which prevents
     * the function call.
     *
     * The return value will be assigned to the grid property grid.features
     * http://docs.sencha.com/extjs/4.1.2/#!/api/Ext.grid.Panel-cfg-features
     *
     * To add a new feature which isn't contained in the shopware default you can use the following
     * source code in your own component:
     *
     * createPlugins: function() {
     *    var me = this, features = [];
     *
     *    features = me.callParent(arguments)
     *    features.push(me.createMyFeature());
     *
     *    return features;
     * },
     * @returns Array
     */
    createFeatures: function() {
        var me = this, features = [];

        return features;
    },

    /**
     * Creates the grid selection model.
     *
     * This function is called from the initComponent function and has no configurations which prevents
     * the function call.
     *
     * The function creates an Ext.selection.CheckboxModel instance which is used from shopware as
     * default selection model in a listing.
     *
     * The return value will be assigned to the grid property grid.selModel
     * http://docs.sencha.com/extjs/4.1.2/#!/api/Ext.grid.Panel-cfg-selModel
     *
     *
     * @returns Ext.selection.CheckboxModel
     */
    createSelectionModel: function() {
        var me = this;

        return Ext.create('Ext.selection.CheckboxModel', {
            listeners: {
                selectionchange: function(selModel, selection) {
                    me.onSelectionChanged(selModel, selection);
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
     * @returns Array
     */
    createDockedItems: function() {
        var me = this, items = [];

        if (me.Config('toolbar')) {
            items.push(me.createToolbar());
        }
        if (me.Config('pagingbar')) {
            items.push(me.createPagingbar());
        }
        return items;
    },

    /**
     * Creates the grid paging bar.
     *
     * If the configuration displayConfig.pagingbar is set to
     * false this function isn't called.
     *
     * The function is used from createDockedItems function and sets the component
     * property "me.pagingbar" which is used in subsequently events.
     *
     * @return Ext.toolbar.Paging
     */
    createPagingbar: function() {
        var me = this;

        me.pagingbar = Ext.create('Ext.toolbar.Paging', {
            store: me.store,
            dock: 'bottom'
        });

        if (me.Config('pageSize')) {
            var pageSizeCombo = me.createPageSizeCombo();
            me.pagingbar.add('->', pageSizeCombo, { xtype: 'tbspacer', width: 6 });
        }

        return me.pagingbar;
    },

    /**
     * Creates the page size combo box for the grid paging bar.
     *
     * If the configurations displayConfig.pagingbar or displayConfig.pageSize is set to
     * false this function isn't called.
     *
     * The function is used from createPagingbar function and sets the component
     * property "me.pageSizeCombo" which is used in subsequently events.
     *
     * @returns Ext.form.field.ComboBox
     */
    createPageSizeCombo: function() {
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
                select: function(combo, records) {
                    me.onChangePageSize(combo, records);
                }
            }
        });
        return me.pageSizeCombo;
    },

    /**
     * Creates the page sizes for the page size combo box of the pagingbar.
     *
     * If the configuration displayConfig.pageSize is set to
     * false this function isn't called.
     *
     * Returns an array with objects. Each object has a value property which contains
     * the integer value for the page size.
     * The name value contains a alphanumeric value which display in the combo box display field.
     *
     * @returns Array
     */
    createPageSizes: function() {
        var me = this, data = [];

        for (var i = 1; i <= 10; i++) {
            var count = i * 20;
            data.push({ value: count, name: count + ' items' });
        }

        return data;
    },

    ////////////////////////////
    //// Toolbar components ////
    ////////////////////////////

    /**
     * Creates the grid toolbar.
     *
     * If the configuration displayConfig.toolbar is set to
     * false this function isn't called.
     *
     * The function is used from createDockedItems function and sets the component
     * property "me.toolbar" which is used in subsequently events.
     *
     * @returns Ext.toolbar.Toolbar
     */
    createToolbar: function() {
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
     * If the configuration displayConfig.toolbar is set to
     * false this function isn't called.
     *
     * The function is used from createToolbar function and calls the internal
     * functions createAddButton, createDeleteButton and createSearchField.
     *
     * @returns Array
     */
    createToolbarItems: function() {
        var me = this, items = [];

        if (me.Config('addButton')) {
            items.push(me.createAddButton());
        }
        if (me.Config('deleteButton')) {
            items.push(me.createDeleteButton())
        }
        if (me.Config('searchField')) {
            items.push('->');
            items.push(me.createSearchField());
        }

        return items;
    },

    /**
     * Creates the add button for the grid toolbar.
     *
     * If the configuration displayConfig.addButton is set to
     * false this function isn't called.
     *
     * @returns Ext.button.Button
     */
    createAddButton: function() {
        var me = this;

        me.addButton = Ext.create('Ext.button.Button', {
            text: 'Add item',
            cls: 'secondary small',
            iconCls: 'sprite-plus-circle-frame',
            handler: function() {
                me.onAddItem();
            }
        });

        return me.addButton;
    },

    /**
     * Creates the delete button for the grid toolbar.
     *
     * If the configuration displayConfig.deleteButton is set to
     * false this function isn't called.
     *
     * @returns Ext.button.Button
     */
    createDeleteButton: function() {
        var me = this;

        me.deleteButton = Ext.create('Ext.button.Button', {
            text: 'Delete all selected',
            disabled: true,
            cls: 'secondary small',
            iconCls: 'sprite-minus-circle-frame',
            handler: function() {
                me.onDeleteItems(arguments);
            }
        });

        return me.deleteButton;
    },

    /**
     * Creates the search field for the grid toolbar.
     *
     * If the configuration displayConfig.searchField is set to
     * false this function isn't called.
     *
     * @returns Ext.form.field.Text
     */
    createSearchField: function() {
        var me = this;

        me.searchField = Ext.create('Ext.form.field.Text', {
            cls:'searchfield',
            width:170,
            emptyText: 'Search ...',
            enableKeyEvents:true,
            checkChangeBuffer:500,
            listeners: {
                change: function(field, value) {
                    me.onSearch(field, value);
                }
            }
        });

        return me.searchField;
    },

    ////////////////////////////
    ////  Event listeners   ////
    ////////////////////////////

    /**
     * Event listener function of the selectionchange event of the grid component.
     * This event will be raised and controled in the listing controller of this component.
     * @Event selectionChanged
     */
    onSelectionChanged: function(selModel, selection) {
        this.fireEvent('selectionChanged', this, selModel, selection);
    },

    onAddItem: function() {
        this.fireEvent('addItem', this);
    },
    onDeleteItems: function() {
        var me = this, selModel = me.getSelectionModel();
        this.fireEvent('deleteItems', me, selModel.getSelection());
    },
    onDeleteItem: function (view, rowIndex, colIndex, item, opts, record) {
        this.fireEvent('deleteItem', this, record);
    },
    onEditItem: function(view, rowIndex, colIndex, item, opts, record) {
        this.fireEvent('editItem', this, record, rowIndex, colIndex, item);
    },
    onSearch: function(field, value) {
        this.fireEvent('search', this, field, value);
    },
    onChangePageSize: function(combo, records) {
        this.fireEvent('changePageSize', this, combo, records);
    },






    ////////////////////////////
    /// Column configuration ///
    ////////////////////////////
    
    /**
     * Adds the shopware default column configuration for a listing integer
     * column.
     * The column configuration will be applied to the passed column object.
     *
     * @param column
     * @return Ext.grid.column.Number
     */
    applyIntegerColumnConfig: function(column) {
        var me = this;

        column.xtype = 'numbercolumn';
        column.renderer = me.integerColumnRenderer;
        column.align = 'right';

        return column;
    },

    /**
     * Adds the shopware default column configuration for a listing string
     * column.
     * The column configuration will be applied to the passed column object.
     *
     * @param column
     * @return Ext.grid.column.Column
     */
    applyStringColumnConfig: function(column) {
        var me = this;
        return column;
    },

    /**
     * Adds the shopware default column configuration for a listing boolean
     * column.
     * The column configuration will be applied to the passed column object.
     *
     * @param column
     * @return Ext.grid.column.Boolean
     */
    applyBooleanColumnConfig: function(column) {
        var me = this;

        column.xtype = 'booleancolumn';
        column.renderer = me.booleanColumnRenderer;
        return column;
    },

    /**
     * Adds the shopware default column configuration for a listing date
     * column.
     * The column configuration will be applied to the passed column object.
     *
     * @param column
     * @return Ext.grid.column.Date
     */
    applyDateColumnConfig: function(column) {
        var me = this;

        column.xtype = 'datecolumn';
        return column;
    },

    /**
     * Adds the shopware default column configuration for a listing float
     * column.
     * The column configuration will be applied to the passed column object.
     *
     * @param column
     * @return Ext.grid.column.Number
     */
    applyFloatColumnConfig: function(column) {
        var me = this;

        column.xtype = 'numbercolumn';
        column.align = 'right';
        return column;
    },


    ///////////////////////////////
    /// Grid renderer functions ///
    ///////////////////////////////

    /**
     * Shopware default renderer function for a boolean listing column.
     * This functions expects a boolean value as first parameter.
     * The function returns a span tag with a css class for a checkbox
     * sprite.
     *
     * @param value boolean
     * @param record Ext.data.Model
     * @return String
     */
    booleanColumnRenderer: function(value, record) {
        var checked = 'sprite-ui-check-box-uncheck';
        if (value === true) {
            checked = 'sprite-ui-check-box';
        }
        return '<span style="display:block; margin: 0 auto; height:16px; width:16px;" class="'+checked+'"></span>';
    },

    /**
     * Shopware default renderer function for a integer listing column.
     * Grid number columns will be displayed with two precisions so this function
     * converts the passed value parameter to an integer value.
     *
     * @param value integer|float
     * @param record Ext.data.Model
     * @return integer
     */
    integerColumnRenderer: function(value, record) {
        return Ext.util.Format.number(value, '0');
    }
});

//{/block}