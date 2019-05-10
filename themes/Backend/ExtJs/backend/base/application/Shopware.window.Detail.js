
//{namespace name=backend/application/main}
//{block name="backend/application/Shopware.window.Detail"}

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
     * Contains the generated event alias.
     * If the { @link #configure } function returns an eventAlias
     * property, this property contains the configured alias.
     * Otherwise shopware creates an event alias over the model name.
     *
     * @type { String }
     */
    eventAlias: undefined,

    /**
     * Contains the instance of the passed { @link Shopware.data.Model }.
     * The record is passed in the default case from the { @link Shopware.detail.Controller }
     * and is used to generate the detail window components.
     *
     * @type { Shopware.data.Model }
     */
    record: undefined,

    /**
     * Instance of the own { @link Shopware.detail.Controller }.
     * Each { @link Shopware.window.Detail } component requires an own configured controller.
     * If the sub application contains none own implemented controller, the detail window
     * creates his own controller by himself.
     * If you have an own controller in your sub application which extends the
     * { @link Shopware.detail.Controller }, you can set the { @link #hasOwnController } property
     * to true and the detail window won't creates his own controller.
     *
     * @type { Shopware.detail.Controller }
     */
    controller: undefined,

    /**
     * Contains the generated { @link Ext.tab.Panel } .
     * The tab panel will be created, if the { @link #createTabItems } function
     * returns more than one element.
     * Otherwise the one created tab item will be directly set into the { @link #formPanel } as
     * sub element.
     * @type { Ext.tab.Panel }
     */
    tabPanel: undefined,

    /**
     * Contains the created { @link Ext.form.Panel }.
     * The form panel is the outer container of the detail window, because
     * all model fields has to be stored in the same form panel to send the whole
     * model data and association data in one save request.
     *
     * @type { Ext.form.Panel }
     */
    formPanel: undefined,

    /**
     * Contains the { @link Ext.toolbar.Toolbar } instance
     * which created in the { @link #createToolbar } function.
     * The toolbar contains as default the { @link #saveButton } and the { @link #cancelButton }.
     * @type { Ext.toolbar.Toolbar }
     */
    toolbar: undefined,

    /**
     * Contains the instance of the created cancel button.
     * The cancel button, allows the user to revert all changes in the detail view and
     * close the window without saving the modified data.
     * @type { Ext.button.Button }
     */
    cancelButton: undefined,

    /**
     * Contains the instance of the created save button.
     * The save button updates the current displayed record with the { @link #formPanel } data
     * and sends an save request in the { @link Shopware.detail.Controller:onSave } function.
     * @type { Ext.button.Button }
     */
    saveButton: undefined,

    /**
     * Button text for the { @link #cancelButton }.
     * @type { String }
     */
    cancelButtonText: '{s name="detail_window/cancel_button_text"}Cancel{/s}',

    /**
     * Button text for the { @link #saveButton }.
     * @type { String }
     */
    saveButtonText: '{s name="detail_window/save_button_text"}Save{/s}',

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

            /**
             * Allows to enable form translation.
             * Contains the type string for the translations.
             * @string
             * @optional
             */
            translationKey: null
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
     * Override required!
     * This function is used to override the { @link #displayConfig } object of the statics() object.
     *
     * @returns { Object }
     */
    configure: function() {
        return { };
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
     * Helper function which checks all component requirements.
     */
    checkRequirements: function() {
        var me = this;

        if (!(me.record instanceof Shopware.data.Model)) {
            me.throwException(me.$className + ": Component requires a passed Shopware.data.Model in the `record` property.");
        }
        if (me.alias.length <= 0) {
            me.throwException(me.$className + ": Component requires a configured Ext JS widget alias.");
        }
        if (me.alias.length === 1 && me.alias[0] === 'widget.shopware-window-detail') {
            me.throwException(me.$className + ": Component requires a configured Ext JS widget alias.");
        }
    },

    registerEvents: function() {
        var me = this;

        me.addEvents(
            /**
             * Event fired before the main tab changed.
             * Return false to prevent the tab change
             *
             * @param { Shopware.window.Detail } window
             * @param { Ext.tab.Panel } tabPanel
             * @param { Ext.tab.Tab } newCard
             * @param { Ext.tab.Tab } oldCard
             * @param { Object } eventOptions
             */
            me.getEventName('before-tab-changed'),

            /**
             * Fired after the main tab changed.
             * @param { Shopware.window.Detail } window
             * @param { Ext.tab.Panel } tabPanel
             * @param { Ext.tab.Tab } newCard
             * @param { Ext.tab.Tab } oldCard
             * @param { Object } eventOptions
             */
            me.getEventName('after-tab-changed'),

            /**
             * Fired before the passed record will be loaded.
             * Even fired when the detail page will be reloaded.
             *
             * @param { Shopware.window.Detail } window
             * @param { Shopware.data.Model } record
             */
            me.getEventName('before-load-record'),

            /**
             * Fired after the record load into the form panel.
             *
             * @param { Shopware.window.Detail } window
             * @param { Shopware.data.Model } record
             */
            me.getEventName('after-load-record'),

            /**
             * Fired over the saveButton click.
             * Controlled from the Shopware.detail.Controller.
             *
             * @param { Shopware.window.Detail } window
             * @param { Shopware.data.Model } record
             */
            me.getEventName('save'),

            /**
             * Fired when a association component configured
             * as lazy loading component and the component moves into
             * the visible area.
             * Return false to prevent the reload.
             *
             * @param { Shopware.window.Detail } window
             * @param { Object } component
             */
            me.getEventName('before-load-lazy-loading-component'),

            /**
             * Fired after a lazy loading component loaded.
             *
             * @param { Shopware.window.Detail } window
             * @param { Object } component
             * @param { Array } records - The loaded records
             * @param { Ext.data.Operation } operation - The data operation
             */
            me.getEventName('after-load-lazy-loading-component'),

            /**
             * Fired before the reloaded association data
             * will be set into the association components.
             *
             * Return false to prevent the whole reload of the
             * association components.
             *
             * @param { Shopware.window.Detail } window
             * @param { Shopware.data.Model } record
             */
            me.getEventName('before-load-associations'),

            /**
             * Fired after all association components reloaded.
             *
             * @param { Shopware.window.Detail } window
             * @param { Shopware.data.Model } record
             */
            me.getEventName('after-load-associations'),

            /**
             * Fired before a single association component will be
             * reloaded.
             * Return false to prevent the reload of the single component.
             *
             * @param { Shopware.window.Detail } window
             * @param { Shopware.data.Model } record
             * @param { Object } component
             * @param { Ext.data.Store } store
             * @param { Object } association
             */
            me.getEventName('before-load-association-component'),

            /**
             * Fired after a single association was reloaded.
             *
             * @param { Shopware.window.Detail } window
             * @param { Shopware.data.Model } record
             * @param { Object } component
             * @param { Ext.data.Store } store
             * @param { Object } association
             */
            me.getEventName('after-load-association-component'),

            /**
             * Fired before the toolbar elements created.
             * Return false to prevent the default component creation.
             *
             * @param { Shopware.window.Detail } window
             * @param { Array } items
             */
            me.getEventName('before-create-toolbar-items'),

            /**
             * Fired after the shopware toolbar elements created.
             *
             * @param { Shopware.window.Detail } window
             * @param { Array } items
             */
            me.getEventName('after-create-toolbar-items'),

            /**
             * Fired before a single tab item will be created.
             * Return false to prevent the tab item creation.#
             *
             * @param { Shopware.window.Detail } window
             * @param { Object } association
             */
            me.getEventName('before-create-tab-item'),

            /**
             * Fired after a single tab item created.
             *
             * @param { Shopware.window.Detail } window
             * @param { Object } association
             * @param { Object } item
             */
            me.getEventName('after-create-tab-item'),

            /**
             * Fired before all tab items will be created,
             * return false to prevent the default creation.
             *
             * @param { Shopware.window.Detail } window
             * @param { Array } items
             */
            me.getEventName('before-create-tab-items'),

            /**
             * Fired after all tab items created.
             *
             * @param { Shopware.window.Detail } window
             * @param { Array } items
             */
            me.getEventName('after-create-tab-items')
        );
    },

    /**
     * Creates a default controller for this component which adds event listener
     * function for all shopware default events of this component.
     *
     * @returns { Shopware.detail.Controller }
     */
    createDefaultController: function () {
        var me = this,
            id = Ext.id();

        me.controller = Ext.create('Shopware.detail.Controller', {
            application: me.subApp,
            subApplication: me.subApp,
            subApp: me.subApp,
            $controllerId: id,
            id: id,
            configure: function () {
                return {
                    detailWindow: me.$className,
                    eventAlias: me.eventAlias
                }
            }
        });
        me.controller.init();
        me.subApp.controllers.add(me.controller.$controllerId, me.controller);

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
    destroy: function () {
        var me = this;
        if (!me.getConfig('hasOwnController') && me.controller) {
            me.subApp.removeController(me.controller);
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

        var plugins = [];
        if (me.getConfig('translationKey')) {
            plugins.push({
                pluginId: 'translation',
                ptype: 'translation',
                translationType: me.getConfig('translationKey')
            });
        }

        me.formPanel = Ext.create('Ext.form.Panel', {
            items: items,
            flex: 1,
            plugins: plugins,
            defaults: {
                cls: 'shopware-form'
            },
            layout: {
                type: 'hbox',
                align: 'stretch'
            }
        });

        return me.formPanel;
    },

    /**
     * Creates all tab panel items of the outer tab panel.
     * This definitions will be defined in the getTabItemsAssociations function.
     * The function getTabItemsAssociations returns only an array of Ext.association.Association
     * class. For each of this association shopware creates the element over
     * the createTabItem function.
     *
     * @returns Array
     */
    createTabItems: function () {
        var me = this, item, items = [];

        if (!me.fireEvent(me.getEventName('before-create-tab-items'), me, items)) {
            return [];
        }

        Ext.each(me.getTabItemsAssociations(), function (association) {
            item = me.createTabItem(association);
            if (item) items.push(item);
        });

        me.fireEvent(me.getEventName('after-create-tab-items'), me, items);

        return items;
    },

    /**
     * Returns all associations which should be displayed in an own
     * tab item.
     * This function returns all Ext.data.Association which defined
     * in the { @link #associations } property of this component.
     * Additionally the function creates
     * an fake association for the base record with the additional parameter
     * "isBaseRecord".
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

        if (!me.fireEvent(me.getEventName('before-create-tab-item'), me, association)) {
            return false;
        }

        if (association.isBaseRecord) {
            item = me.createAssociationComponent('detail', me.record, null, null, me.record);
        } else {
            item = me.createAssociationComponent(
                me.getComponentTypeOfAssociation(association),
                Ext.create(association.associatedName),
                me.getAssociationStore(me.record, association),
                association,
                me.record
            );
        }
        me.associationComponents[association.associationKey] = item;

        me.fireEvent(me.getEventName('after-create-tab-item'), me, association, item);

        if (item.title === undefined) {
            item.title = me.getModelName(association.associatedName);
        }

        return item;
    },


    /**
     * Helper function which creates all model components.
     *
     * @param type { String }
     * @param model { Shopware.data.Model }
     * @param store { Ext.data.Store }
     * @param association { Ext.data.Association }
     * @param baseRecord { Shopware.data.Model }
     *
     * @returns { Object }
     */
    createAssociationComponent: function(type, model, store, association, baseRecord) {
        var me = this, component = { };

        if (!(model instanceof Shopware.data.Model)) {
            me.throwException(model.$className + ' has to be an instance of Shopware.data.Model');
        }
        if (baseRecord && !(baseRecord instanceof Shopware.data.Model)) {
            me.throwException(baseRecord.$className + ' has to be an instance of Shopware.data.Model');
        }

        var componentType = model.getConfig(type);

        if (!me.fireEvent(me.getEventName('before-association-component-created'), me, component, type, model, store)) {
            return component;
        }

        component = Ext.create(componentType, {
            record: model,
            store: store,
            flex: 1,
            subApp: this.subApp,
            association: association,
            configure: function() {
                var config = { };

                if (association) {
                    config.associationKey = association.associationKey;
                }

                if (baseRecord && baseRecord.getConfig('controller')) {
                    config.controller = baseRecord.getConfig('controller');
                }

                return config;
            }
        });

        //add lazy loading event listener.
        component.on('viewready', function() {
            if (me.isLazyLoadingComponent(component)) {
                if (!(me.fireEvent(me.getEventName('before-load-lazy-loading-component'), me, component))) {
                    return true;
                }

                component.getStore().load({
                    callback: function(records, operation) {
                        me.fireEvent(me.getEventName('after-load-lazy-loading-component'), me, component, records, operation);
                    }
                });
            }
        });

        me.fireEvent(me.getEventName('after-association-component-created'), me, component, type, model, store);


        return component;
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
     * This function creates a toolbar which will be assigned
     * to the property "me.toolbar".
     *
     * @return Ext.toolbar.Toolbar
     */
    createToolbar: function () {
        var me = this;

        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            items: me.createToolbarItems(),
            dock: 'bottom'
        });
        return me.toolbar;
    },

    /**
     * Creates the toolbar items for the detail window toolbar.
     *
     * The function is used from { @link #createToolbar } function and calls the internal
     * functions { @link #createCancelButton } and { @link #createSaveButton }.
     *
     * The Ext.toolbar.Fill element is set on the first position. Each other element
     * after the Fill element will be displayed on the right side of the toolbar.
     *
     * To add an element additional element to the toolbar, you can use the following source
     * code as example:
     *
     * @example
     *  createToolbarItems: function() {
     *     var me = this, items;
     *
     *     items = me.callParent(arguments);
     *
     *     items = Ext.Array.insert(
     *         items, 1, [
     *            { xtype: 'button', text: 'MyButton', handler: function() { ... } }
     *        ]
     *     );
     *
     *     return items;
     *  },
     *
     * @returns { Array }
     */
    createToolbarItems: function() {
        var me = this, items = [];

        me.fireEvent(me.getEventName('before-create-toolbar-items'), me, items);

        items.push({ xtype: 'tbfill' });

        items.push(me.createCancelButton());

        items.push(me.createSaveButton());

        me.fireEvent(me.getEventName('after-create-toolbar-items'), me, items);

        return items;
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
            text: me.cancelButtonText,
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
            text: me.saveButtonText,
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
        var me = this;

        if (!(me.fireEvent(me.getEventName('before-load-record'), me, record))) {
            return false;
        }

        if (this.formPanel instanceof Ext.form.Panel) {
            this.formPanel.loadRecord(record);
        }

        me.fireEvent(me.getEventName('after-load-record'), me, record);

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

        if (!(me.fireEvent(me.getEventName('before-load-associations'), me, record))) {
            return false;
        }

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

            if (!(me.fireEvent(me.getEventName('before-load-association-component'), me, record, component, store, association))) {
                return true;
            }

            if (component && typeof component.reloadData === 'function') {
                component.reloadData(
                    store,
                    record
                );
            }
            if (me.isLazyLoadingComponent(component)) {
                component.getStore().load();
            }

            me.fireEvent(me.getEventName('after-load-association-component'), me, record, component, store, association);
        });

        me.fireEvent(me.getEventName('after-load-associations'), me, record);
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
        var me = this;

        if (!(me.fireEvent(me.getEventName('before-tab-changed'), me, tabPanel, newCard, oldCard, eOpts))) {
            return false;
        }

        me.fireEvent(me.getEventName('after-tab-changed'), me, tabPanel, newCard, oldCard, eOpts);
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
    },



});
//{/block}
