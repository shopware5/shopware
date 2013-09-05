
//{namespace name=backend/application/main}

//{block name="backend/application/window/detail"}

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


    /**
     * Internal collection of all created association components
     * which created in this component.
     * The array elements are indexed with the association key.
     * The base component for the main record is indexed with "baseRecord".
     *
     * @type { Array }
     */
    associationComponents: [],

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
         *      Ext.define('Shopware.apps.Product.view.detail.Window', {
         *          extend: 'Shopware.detail.Window',
         *          configure: function() {
         *              return {
         *                  eventAlias: 'product',
         *                  tabItemAssociations: [ 'categories', 'variants' ],
         *                  ...
         *              }
         *          }
         *      });
         */
        displayConfig: {

            /**
             * Alias for the component events.
             *
             * @optional
             */
            eventAlias: undefined,

            /**
             * Array of associations which has an own tab item.
             * To display an association in an own tab item, add the associationKey to this array.
             *
             * @optional
             */
            associations: [],


            /**
             * Flag if the component is already controlled by an application controller.
             *
             * @optional
             */
            hasOwnController: false,

            cancelButtonText: '{s name="detail_window/cancel_button_text"}Cancel{/s}',
            saveButtonText: '{s name="detail_window/save_button_text"}Save{/s}'

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
     *
     * @param opts
     */
    constructor: function (opts) {
        var me = this;

        me._opts = me.statics().getDisplayConfig(opts, this);
        me.callParent(arguments);
    },

    /**
     * Helper function to get config access.
     *
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

        me.associationComponents = [];

        me.eventAlias = me.getConfig('eventAlias');
        if (!me.eventAlias) me.eventAlias = me.getEventAlias(me.record.$className);

        me.items = [ me.createFormPanel() ];
        me.dockedItems = me.createDockedItems();

        if (me.getConfig('hasOwnController') === false) {
            me.createDefaultController();
        }

        me.callParent(arguments);
        me.loadRecord(me.record);
    },

    /**
     * Creates a default controller for this component which adds event listener
     * function for all shopware default events of this component.
     *
     * @returns { Shopware.detail.Controller }
     */
    createDefaultController: function() {
        var me = this;

        me.controller = me.subApp.getController('Shopware.detail.Controller');
        me.controller._opts.detailWindow = me.$className;
        me.controller._opts.eventAlias = me.eventAlias;
        me.controller.reloadControls();

        return me.controller;
    },

    /**
     * Event bus workaround.
     * The detail controller isn't assigned to any sub application.
     * To prevent a duplicate event handling, the controller event listeners
     * has to be destroyed if the detail window will be destroyed.
     *
     * @returns { Object }
     */
    destroy: function() {
        var me = this;
        if (!me.getConfig('hasOwnController') && me.controller) {
            me.subApp.eventbus.uncontrol([me.controller.$className]);
        }
        return me.callParent(arguments);
    },


    /**
     * Creates the form and tab panel for the window. The form panel
     * are used to send the model data back to the php controller.
     * For this reason, the form panel has to be the outer container
     * in the detail window.
     * The tab panel will be the only child element of the form panel.
     * Items of the tab panel created in the { @link #createTabItems } function.
     *
     * @returns { Ext.form.Panel }
     */
    createFormPanel: function () {
        var me = this, items;

        items = me.createTabItems();

        //check if more than one tab was created
        if (items.length > 1) {
            //in this case, we have to display a tab panel.
            me.tabPanel = Ext.create('Ext.tab.Panel', {
                flex: 1,
                items: items,
                listeners: {
                    tabchange: function (tabPanel, newCard, oldCard, eOpts) {
                        me.onTabChange(tabPanel, newCard, oldCard, eOpts);
                    }
                }
            });
            //otherwise, the created item would be displayed directly in the form panel.
            items = [ me.tabPanel ];
        }

        me.formPanel = Ext.create('Ext.form.Panel', {
            items: items,
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
        var me = this, associations, config = me.getConfig('associations') || [];

        associations = me.getAssociations(me.record.$className, [
            { associationKey: config }
        ]);

        associations = Ext.Array.insert(associations, 0, [
            {  isBaseRecord: true, associationKey: 'baseRecord' }
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
        var me = this, item;

        if (association.isBaseRecord) {
            item = me.createAssociationComponent('detail', me.record, null);
        } else {
            item = me.createAssociationComponent(
                me.getComponentTypeOfAssociation(association),
                Ext.create(association.associatedName),
                me.getAssociationStore(me.record, association),
                association.associationKey
            );
        }
        me.associationComponents[association.associationKey] = item;
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
    createAssociationComponent: function(type, model, store, associationKey) {
        var componentType = model.getConfig(type);

        return Ext.create(componentType, {
            record: model,
            store: store,
            flex: 1,
            subApp: this.subApp,
            configure: function() {
                return {
                    associationKey: associationKey
                };
            }
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
            text: me.getConfig('cancelButtonText'),
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
            text: me.getConfig('saveButtonText'),
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


    /**
     * Helper function to reload the associated data of the passed record.
     * Associations can be displayed within a Shopware.model.Container or
     * within the detail window as own tab item.
     * The associations which will be displayed in own tab items are defined
     * in the { @link #associations } property of the displayConfig.
     * All created association tab items are stored in the { @link #associationComponents }
     * object.
     * This components will be iterated and if the components include the reloadDate function
     * the association data can be reloaded over this function.
     *
     * @param record
     */
    loadAssociationData: function(record) {
        var me = this, association, component, store;

        Object.keys(me.associationComponents).forEach(function(key) {
            component = me.associationComponents[key];
            store = null;
            association = null;

            //check if the association key is the base record of the detail window.
            if (key != 'baseRecord') {
                //In this case we have no association store
                association = me.getAssociations(record.$className, [ { associationKey: [ key ] } ]);
                store = me.getAssociationStore(record, association[0]);
            }

            if (component && typeof component.reloadData === 'function') {
                component.reloadData(
                    store,
                    record
                );
            }
        });
    },


    /**
     * Event listener which called when the detail window tab panel changes
     * the active tab item.
     *
     * @param tabPanel
     * @param newCard
     * @param oldCard
     * @param eOpts
     */
    onTabChange: function (tabPanel, newCard, oldCard, eOpts) {
        this.fireEvent('tabChange', this, tabPanel, newCard, oldCard, eOpts);
    },

    /**
     * Event listener function of the save button in the bottom toolbar.
     */
    onSave: function () {
        this.fireEvent(
            this.getEventName('save'), this, this.record
        );
    },

    onCancel: function () {
        this.destroy();
    },

    /**
     * Helper function to prefix the passed event name with the event alias.
     *
     * @param name
     * @returns { string }
     */
    getEventName: function (name) {
        return this.eventAlias + '-' + name;
    }

});
//{/block}
