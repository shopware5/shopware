//{block name="backend/component/window/detail"}
Ext.define('Shopware.window.Detail', {
    extend: 'Enlight.app.Window',

    layout: {
        type: 'hbox',
        align: 'stretch'
    },

    /**
     * List of classes to mix into this class.
     * @type { Object }
     */
    mixins: {
        helper: 'Shopware.model.Helper'
    },

    width: 990,
    height: '90%',
    alias: 'widget.shopware-window-detail',
    associationComponents: [],

    statics: {
        displayConfig: {
            eventAlias: undefined,

            /**
             * Name of the php controller with extends the Shopware_Controllers_Backend_Application.
             */
            searchController: undefined,

            components: {

            }
        },

        /**
         * Static function to merge the different configuration values
         * which passed in the class constructor.
         * @param userOpts Object
         * @param displayConfig Object
         * @returns Object
         */
        getDisplayConfig: function (userOpts, displayConfig) {
            var config = { };

            if (userOpts && userOpts.displayConfig) {
                config = Ext.apply({ }, config, userOpts.displayConfig);
            }
            config = Ext.apply({ }, config, displayConfig);
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
     */
    initComponent: function () {
        var me = this;

        me.eventAlias = me.getConfig('eventAlias');
        if (!me.eventAlias) me.eventAlias = me.getEventAlias(me.record.$className);

        me.items = [ me.createFormPanel() ];
        me.dockedItems = me.createDockedItems();
        me.callParent(arguments);
//        me.loadRecord(me.record);
    },


    createFormPanel: function () {
        var me = this;

        me.tabPanel = Ext.create('Ext.tab.Panel', {
            flex: 1,
            items: me.createTabItems(),
            listeners: {
                tabchange: function (tabPanel, newCard, oldCard, eOpts) {
                    me.onTabChange(tabPanel, newCard, oldCard, eOpts);
                }
            }
        });

        me.formPanel = Ext.create('Ext.form.Panel', {
            items: [ me.tabPanel ],
            flex: 1,
            layout: {
                type: 'hbox',
                align: 'stretch'
            }
        });

        return me.formPanel;
    },

    /**
     * Creates all tab panel items of the outer tab panel.
     * Shopware creates for the following definitions a single tab item:
     *
     * 1. Base record (which passed to the me.record property)
     * 2. OneToOne associations which has no own associations
     * 3. OneToMany associations
     * 4. ManyToMany associations
     *
     * This definitions will be defined in the getTabItemsAssociations function.
     * The function getTabItemsAssociations returns only an array of Ext.association.Association
     * class. For each of this association shopware creates the element over
     * the createTabItem function.
     *
     * @returns Array
     */
    createTabItems: function () {
        var me = this, item, items = [];

        Ext.each(me.getTabItemsAssociations(), function (association) {
            item = me.createTabItem(association);
            if (item) items.push(item);
        });

        return items;
    },

    /**
     * Returns all records associations, which will have an own tab item.
     * To create an own tab item for the base record, the function creates
     * an fake association for the base record with the additional parameter
     * "isBaseRecord".
     * Shopware creates for the following definitions a single tab item:
     *
     * 1. Base record (which passed to the me.record property)
     * 2. OneToOne associations which has no own associations
     * 3. OneToMany associations
     * 4. ManyToMany associations
     *
     * @returns array
     */
    getTabItemsAssociations: function () {
        var me = this, associations, config = me.getConfig('tabItemAssociations') || [];

        associations = me.getAssociations(me.record.$className, [
            { associationKey: config }
        ]);
        associations = Ext.Array.insert(associations, 0, [
            { isBaseRecord: true }
        ]);

        return associations;
    },

    /**
     * Create the component for a single association.
     * To display an own component for a single association,
     * override this function, create an instance of your own component
     * and set it as return value.
     *
     * The association data will be loaded automatically.
     *
     * @param association
     * @returns Ext.container.Container|Ext.grid.Panel
     */
    createTabItem: function (association) {
        var me = this, item, model, store;

        if (association.isBaseRecord) {
            item = me.createAssociationComponent('detail', me.record, null);
        } else {
            model = Ext.create(association.associatedName);
            store = me.getAssociationStore(me.record, association);

            switch (association.relation.toLowerCase()) {
                case 'onetoone':
                    item = me.createAssociationComponent('detail', model, store);
                    break;
                case 'onetomany':
                    item = me.createAssociationComponent('listing', model, store);
                    break;
                case 'manytomany':
                    item = me.createAssociationComponent('related', model, store);
                    break;
            }
        }
        return item;
    },


    /**
     * Helper function which creates all model components.
     *
     * @param type { String }
     * @param model { Shopware.data.Model }
     * @param store { Ext.data.Store }
     * @returns { Object }
     */
    createAssociationComponent: function(type, model, store) {
        var componentType = model.getConfig(type);

        return Ext.create(componentType, {
            record: model,
            store: store,
            flex: 1
        });
    },

    /**
     * Creates all docked items for the detail window
     * component.
     * Shopware creates as default a dock bottom
     * toolbar with a cancel and save button.
     *
     * @return Array
     */
    createDockedItems: function () {
        var me = this;

        return [
            me.createToolbar()
        ];
    },

    /**
     * Creates the bottom toolbar of the detail window.
     * The shopware toolbar contains as default a cancel and
     * save button.
     * This function creates a toolbar wich will be assigned
     * to the property "me.toolbar".
     *
     * @return Ext.toolbar.Toolbar
     */
    createToolbar: function () {
        var me = this, items = [];

        items.push({ xtype: 'tbfill' });
        items.push(me.createCancelButton());
        items.push(me.createSaveButton());

        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            items: items,
            dock: 'bottom'
        });
        return me.toolbar;
    },

    /**
     * Creates the cancel button which will be displayed
     * in the bottom toolbar of the detail window.
     * The button handler will be raised to the internal
     * function me.onCancel
     *
     * @return Ext.button.Button
     */
    createCancelButton: function () {
        var me = this;

        me.cancelButton = Ext.create('Ext.button.Button', {
            cls: 'secondary',
            name: 'cancel-button',
            text: 'Cancel',
            handler: function () {
                me.onCancel();
            }
        });
        return me.cancelButton;
    },

    /**
     * Creates the save button which will be displayed
     * in the bottom toolbar of the detail window.
     * The button handler will be raised to the internal
     * function me.onSave
     *
     * @return Ext.button.Button
     */
    createSaveButton: function () {
        var me = this;

        me.saveButton = Ext.create('Ext.button.Button', {
            cls: 'primary',
            name: 'detail-save-button',
            text: 'Save',
            handler: function () {
                me.onSave();
            }
        });
        return me.saveButton;
    },




    /**
     * Helper function to load the detail window record.
     */
    loadRecord: function (record) {
        if (this.formPanel instanceof Ext.form.Panel) {
            this.formPanel.loadRecord(record);
        }
        this.loadAssociationData(record);
    },

    loadAssociationData: function(record) {
        var me = this, component,
            associations = me.getAssociations(record.$className);

        Ext.each(associations, function(association) {
            component = me.associationComponents[association.associationKey];
            if (component) {
                component.reconfigure(me.getAssociationStore(record, association));
            }
        });
    },

    onTabChange: function (tabPanel, newCard, oldCard, eOpts) {
        this.fireEvent('tabChange', this, tabPanel, newCard, oldCard, eOpts);
    },

    onSave: function () {
        this.destroy();
    },

    onCancel: function () {
        this.destroy();
    }

});
//{/block}
