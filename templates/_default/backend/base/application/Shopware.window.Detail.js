
//{block name="backend/component/window/detail"}
Ext.define('Shopware.window.Detail', {
    extend: 'Enlight.app.Window',

    layout: {
        type: 'hbox',
        align: 'stretch'
    },

    width: 990,
    height: '90%',
    alias : 'widget.shopware-window-detail',
    associationComponents: [],

    statics: {
        displayConfig: {
            searchController: '',
            searchUrl: '{url controller="placeholder" action="searchAssociation"}',

            oneToManyGrid: {
                searchField: false,
                pagingbar: false,
                editColumn: false
            },
            manyToManyGrid: {
                searchField: false,
                pagingbar: false,
                editColumn: false,
                toolbar: false
            }
        },

        /**
         * Static function to merge the different configuration values
         * which passed in the class constructor.
         * @param userOpts Object
         * @param displayConfig Object
         * @returns Object
         */
        getDisplayConfig: function(userOpts, displayConfig) {
            var config = { };

            if (userOpts && userOpts.displayConfig) {
                config = Ext.apply({ }, config, userOpts.displayConfig);
            }
            config = Ext.apply({ }, config, displayConfig);
            config = Ext.apply({ }, config, this.displayConfig);

            console.log("get config", config);
            if (config.searchController) {
                config.searchUrl = config.searchUrl.replace(
                    '/placeholder/', '/' + config.searchController.toLowerCase()  + '/'
                );
            }

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
        },
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
     * Initialisation of this component.
     */
    initComponent: function() {
        var me = this;

        me.items = [ me.createFormPanel() ];
        me.dockedItems = me.createDockedItems();
        me.callParent(arguments);
        if (me.record) {
            me.loadRecord(me.record);
        }
    },



    /**
     * FormPanel
     *      TabPanel
     *              HauptElement
     *                  -   der Eigentliche Record
     *                      - FieldSet
     *                  -   1:1 Ohne Associationen
     *                      - FieldSet
     *              1:1
     *                  -   Mit Assocationen
     *                      -   N:M => Accordion
     *                      -   1:1 => Fieldset
     *                      -   1:N => Grid
     *              1:N
     *                  -   Wenn weitere Associationen:
     *                      -   Grid hat detailseite
     *                  -   Wenn keine Associationen
     *                      -   Grid hat Inline-Editierung
     *              N:M
     *                  -   Einfaches Grid
     */
    createFormPanel: function() {
        var me = this;

        me.formPanel = Ext.create('Ext.form.Panel', {
            items: [ me.createTabPanel() ],
            flex: 1,
            layout: {
                type: 'hbox',
                align: 'stretch'
            }
        });

        return me.formPanel;
    },

    /**
     * Creates the outer tab panel of the detail panel.
     * The items of the tab panel will be created in the
     * createTabItems function.
     *
     * @eventListeners
     *  -   tabchange => Calls onTabChange function of this component
     *
     * @returns Ext.tab.Panel
     */
    createTabPanel: function() {
        var me = this;

        return Ext.create('Ext.tab.Panel', {
            flex: 1,
            items: me.createTabItems(),
            listeners: {
                tabchange: function(tabPanel, newCard, oldCard, eOpts ) {
                    me.onTabChange(tabPanel, newCard, oldCard, eOpts);
                }
            }
        });
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
     *  @returns Array
     */
    createTabItems: function() {
        var me = this;
        var associations = me.getTabItemsAssociations();
        var items = [];

        Ext.each(associations, function(association) {
            var item = me.createTabItem(association);
            if (item) {
                items.push(item);
            }
        });

        return items;
    },

    /**
     * Gibt alle associationen zurück, für die ein eigener Tab
     * erstellt werden soll. Damit für den Hauptrecord auch ein
     * tab erstellt werden muss, wird ein fake object erstellt
     * mit der zusätlichen Konfiguration isBaseRecord
     * Standardmäßig wird für folgende associationen ein eigener
     * tab erstellt:
     *  - OneToOne ohne eigene Associationen
     *  - OneToMany
     *  - ManyToMany
     *
     * Wenn sie möchten dass einige Associationen nicht in eigenen
     * tabs dargestellt werden geben filtern sie diese hier einfach heraus
     * und fügen sie an eine andere Stelle wieder ein.
     *
     * @returns array
     */
    getTabItemsAssociations: function() {
        var me = this, associations;

        associations = me.getAssociations(me.record.$className, [
            { relation: 'OneToOne',  hasAssociations: true },
            { relation: 'OneToMany' },
            { relation: 'ManyToMany' }
        ]);

        associations = Ext.Array.insert(associations, 0, [
            { isBaseRecord: true }
        ]);

        return associations;
    },

    /**
     * Create the component for a single association.
     * Sollten sie eine eigene Komponente verwenden wollen,
     * können Sie hier ganz einfach eine eigene Componente instanzieren
     * und als Rückgabe setzen.
     *
     * Die entsprechenden Daten werden automatisch in die Komponente gesetzt.
     *
     * @param association
     * @returns Ext.container.Container|Ext.grid.Panel
     */
    createTabItem: function(association) {
        var me = this, item;

        if (association.isBaseRecord) {
            item = me.createBaseItem();
        } else {
            switch (association.relation.toLowerCase()) {
                case 'onetoone':
                    item = me.createOneToOneItem(association, me.record);
                    break;
                case 'onetomany':
                    item = me.createOneToManyItem(association, me.record);
                    break;
                case 'manytomany':
                    item = me.createManyToManyItem(association, me.record);
                    break;
            }
        }
        return item;
    },



    createBaseItem: function() {
        var me = this, container, items = [],
            fieldSet, associations,
            modelName = me.record.$className;

        items.push(me.createModelFieldSet(modelName, ''));
        associations = me.getAssociations(modelName, [
            { relation: 'OneToOne',  hasAssociations: false }
        ]);

        Ext.each(associations, function(association) {
            fieldSet = me.createModelFieldSet(
                association.associatedName,
                association.associationKey
            );
            if (fieldSet !== null) {
                items.push(fieldSet);
            }
        });

        return Ext.create('Ext.container.Container', {
            flex: 1,
            items: items,
            autoScroll: true,
            padding: 20,
            title: me.getModelName(me.record.$className)
        });
    },

    createOneToOneItem: function(association, record) {
        var me = this, model, items = [],
            modelName, associations;

        modelName = association.associatedName;
        console.log("modelName", modelName);
        var store = me.getAssociationStore(record, association);
        model = Ext.create(modelName);
        if (store instanceof Ext.data.Store && store.getCount() > 0) {
            model = store.first();
        }

        items.push(me.createModelFieldSet(modelName, modelName.toLowerCase()));
        associations = me.getAssociations(modelName, [
            { relation: 'OneToMany' }
        ]);

        Ext.each(associations, function(assoc) {
            var store = me.getAssociationStore(model, assoc);
            var grid = me.createGrid(model, me.Config('oneToManyGrid'));
            if (grid) {
                items.push(grid);
            }
        });

        return Ext.create('Ext.container.Container', {
            flex: 1,
            items: items,
            autoScroll: true,
            padding: 20,
            title: me.getModelName(modelName)
        });
    },

    createOneToManyItem: function(association, record) {
        var me = this;

        var store = me.getAssociationStore(record, association);
        var grid = me.createGrid(store, me.Config('oneToManyGrid'));

        grid.title = me.getModelName(association.associatedName);
        return grid;
    },

    createManyToManyItem: function(association, record) {
        var me = this;
        
        var title = me.getModelName(association.associatedName);
        var gridStore = me.getAssociationStore(record, association);
        var grid = me.createGrid(gridStore, me.Config('manyToManyGrid'));
        var comboStore = me.createSearchComboStore(association, me.Config('searchUrl'));
        var combo = me.createSearchCombo(comboStore, grid);

        return Ext.create('Ext.container.Container', {
            items: [ combo, grid ],
            layout: { type: 'vbox', align: 'stretch' },
            title: title,
            autoScroll: true
        });
    },




    getAssociationStore: function(record, association) {
        var store;

        store = record[association.storeName];
        if (!(store instanceof Ext.data.Store)) {
            store = Ext.create('Ext.data.Store', {
                model: association.associatedName
            })
        }

        return store;
    },

    createGrid: function(store, displayConfig) {
        var config = { };
        config = Ext.apply({ }, config, displayConfig);

        return Ext.create('Shopware.grid.Listing', {
            store: store,
            minHeight: 300,
            flex: 1,
            displayConfig: config
        });
    },





    createSearchCombo: function(store, grid) {
        var me = this, listConfig;

        listConfig = me.createSearchComboListConfig();

        return Ext.create('Ext.form.field.ComboBox', {
            name: 'associationSearchField',
            queryMode: 'remote',
            store: store,
            grid: grid,
            valueField: 'id',
            displayField: 'name',
            minChars: 2,
            fieldLabel: 'Search for',
            margin: 10,
            listConfig: me.createSearchComboListConfig(),
            listeners: {
                select: function(combo, records) {
                    me.onSelectSearchItem(combo, records, combo.grid);
                }
            }
        });
    },

    createSearchComboListConfig: function() {
        return {
            getInnerTpl: function() {
                return '{literal}<a class="search-item">' +
                    '<h4>{name}</h4><span><br />{[Ext.util.Format.ellipsis(values.description, 150)]}</span>' +
                    '</a>{/literal}';
            }
        }
    },

    createSearchComboStore: function(association, searchUrl) {
        return Ext.create('Ext.data.Store', {
            model: association.associatedName,
            proxy: {
                type: 'ajax',
                url: searchUrl,
                reader: { type: 'json', root: 'data', totalProperty: 'total' },
                extraParams: { association: association.associationKey }
            }
        });
    },

    onSelectSearchItem: function(combo, records, grid) {
        var inStore;

        if (!grid) {
            return;
        }
        Ext.each(records, function(record) {
            inStore = grid.getStore().getById(record.get('id'));
            if (inStore === null) {
                grid.getStore().add(record);
                combo.setValue('');
            }
        });
    },






    /**
     * Erstellt für den übergebene Modelnamen ein Ext.form.FieldSet.
     * Die Elemente dieses Fieldsets werden über createModelFields erstellt.
     * Formfelder die zu einer Association gehören müssen einen Alias im Feldnamen
     * besitzen damit die Daten richtig geladen werden können.
     * Beispiel:
     *  -  Hauptmodel: 'Shopware.apps.Product.model.Product'
     *  -  Eine Association:
     *       {
     *           model: 'Shopware.apps.Product.model.Attribute',
     *           associationKey: 'attribute',
     *           ...
     *       }
     *  -> Wenn die Felder des Attribute models nun im selben Formular
     *     angezeigt werden sollen benötigen diese dafür folgenden alias:
     *     'attribute[name]', 'attribute[street]'
     *
     *
     * @param modelName Ext.data.Model
     * @param alias Additional alias for the field names (example: 'attribute' => 'attibute[name]')
     *
     * @return Ext.form.FieldSet
     */
    createModelFieldSet: function(modelName, alias) {
        var me = this, fields, model;

        model = Ext.create(modelName);
        fields = me.createModelFields(model, alias);
        console.log("fields", fields);
        return Ext.create('Ext.form.FieldSet', {
            flex: 1,
            items: fields,
            title: me.getModelName(modelName)
        });
    },

    /**
     * Creates all Ext.form.Fields for the passed model.
     * The alias can be used to prefix the field names.
     * For example: 'attribute[name]'.
     *
     * @return Array
     */
    createModelFields: function(model, alias) {
        var me = this, fields = [], field;

        Ext.each(model.fields.items, function(item) {
            field = me.createModelField(model, item, alias);
            if (field !== null) {
                fields.push(field);
            }
        });

        return fields;
    },

    /**
     * This function creates the form field element
     * for a single model field.
     * This functions use different helper function like
     * 'applyIntegerFieldConfig' to set different shopware
     * default configurations for a form field.
     * The id property of the model won't be displayed.
     *
     * @param model Ext.data.Model
     * @param field Ext.data.Field
     * @param alias string
     * @return Ext.form.field.Field
     */
    createModelField: function(model, field, alias) {
        var me = this, formField = {};

        if (model.idProperty === field.name) {
            return null;
        }

        formField.xtype = 'displayfield';
        formField.anchor = '100%';

        formField.labelWidth = 130;
        formField.name = field.name;

        alias += '';
        if (alias !== undefined && Ext.isString(alias) && alias.length > 0) {
            formField.name = alias + '[' + field.name + ']';
        }
        formField.fieldLabel = me.camelCaseToWord(field.name);

        switch (field.type.type) {
            case 'int':
                formField = me.applyIntegerFieldConfig(formField);
                break;
            case 'string':
                formField = me.applyStringFieldConfig(formField);
                break;
            case 'bool':
                formField = me.applyBooleanFieldConfig(formField);
                break;
            case 'date':
                formField = me.applyDateFieldConfig(formField);
                break;
            case 'float':
                formField = me.applyFloatFieldConfig(formField);
                break;
        }

        return formField;
    },




    /**
     * Creates all docked items for the detail window
     * component.
     * Shopware creates as default a dock bottom
     * toolbar with a cancel and save button.
     *
     * @return Array
     */
    createDockedItems: function() {
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
    createToolbar: function() {
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
    createCancelButton: function() {
        var me = this;

        me.cancelButton = Ext.create('Ext.button.Button', {
            cls:  'secondary',
            name: 'cancel-button',
            text: 'Cancel',
            handler: function() {
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
    createSaveButton: function() {
        var me = this;

        me.saveButton = Ext.create('Ext.button.Button', {
            cls:  'primary',
            name: 'detail-save-button',
            text: 'Save',
            handler: function() {
                me.onSave();
            }
        });
        return me.saveButton;
    },





    /**
     * Helper function to load the
     */
    loadRecord: function() {
        if (this.formPanel instanceof Ext.form.Panel) {
            this.formPanel.loadRecord(this.record);
        }
    },


    onTabChange: function(tabPanel, newCard, oldCard, eOpts ) {
        this.fireEvent('tabChange', this, tabPanel, newCard, oldCard, eOpts );
    },

    onSave: function() {
        this.destroy();
    },

    onCancel: function() {
        this.destroy();
    },





    /**
     * Helfer function um associationen eines Models einfacher ermitteln zu können.
     * Übergeben wird der Klassenname des Models von dem die Associationen ermittelt werden sollen.
     * Für die Filterung spezifischer Associationen kann der conditions Parameter genutzt werden.
     * Dieser Parameter kann ein Array mit bestimmten Filter Kriterien beinhalten.
     * Die Funktion überprüft pro Associationen jeden Filter. Sollte einer der Filter zutreffen,
     * wird die Association am Ende in einem Array zurückgegben.
     *
     * Eine Filter Kriterie kann die folgenden Eigenschaften haben:
     *  - [string] relation
     *      -   Überprüft einen bestimmten Typen der association
     *      -   Wenn weg gelassen wird der Associationtyp nicht überprüft
     *      -   Mögliche Typen: 'OneToOne', 'OneToMany', 'ManyToMany'
     *  - [boolean] hasAssociations
     *      -   Überprüft ob die Association eigene Associationen besitzt
     *  - [string] associationTypes
     *      -   Überprüft ob die Associationen eine eigene Association eines bestimmtes Typen besitzt
     *      -   Mögliche Typen: 'OneToOne', 'OneToMany', 'ManyToMany'
     *
     * @param className
     * @param conditions
     */
    getAssociations: function(className, conditions) {
        var me = this,
            associations = [],
            model = Ext.create(className);

        if (model.associations .lenght <= 0) {
            return associations;
        }
        Ext.each(model.associations.items, function(association) {
            if (me.matchAssociationConditions(association, conditions)) {
                associations.push(association);
            }
        });
        return associations;
    },

    /**
     * Helfer Funktion für die getAssociations Funktion.
     * Überprüft eine einzelne Association ob einer der übergebenen
     * Filter Kriterien entspricht.
     *
     * Sollten ein Filter Kriterium zutreffen oder es werden keine Filter Kriterien übergeben,
     * liefert die Funktion als Ergebnis true zurück.
     *
     * @param association
     * @param conditions
     * @returns boolean
     */
    matchAssociationConditions: function(association, conditions) {
        var me = this;
        var associationInstance = Ext.create(association.associatedName);
        var match = false;

        //if no conditions passed, the loop won't be accessed
        if (conditions && conditions.length <= 0) {
            match = true;
        }

        Ext.each(conditions, function(condition) {
            //relation type has been set? if isn't matched continue with next condition
            if (condition.relation && condition.relation.toLowerCase() !== association.relation.toLowerCase()) {
                return true;
            }

            //filter condition for association with own associations
            if (condition.hasAssociations === true && associationInstance.associations.length <= 0) {
                return true;
            }

            //filter condition for association without own associations
            if (condition.hasAssociations === false && associationInstance.associations.length > 0) {
                return true;
            }

            if (condition.associationTypes && associationInstance.association.length <= 0) {
                return true;
            }

            //filter condition for association
            if (condition.associationTypes) {
                var typeMatch = false;

                Ext.each(associationInstance.associations.items, function(item) {
                    Ext.each(condition.associationTypes, function(type) {
                        if (type.toLowerCase() === item.relation.toLowerCase()) {
                            typeMatch = true;
                        }
                    });
                });

                if (typeMatch === false) {
                    return true;
                }
            }

            match = true;
            return false;   //cancel foreach
        });

        return match;
    },

    /**
     * Helper function to create the form field label
     * for the passed model field.
     *
     * @param word
     * @return string
     */
    camelCaseToWord: function(word) {
        word = word.split(/(?=[A-Z])/).map(function(p) {
            return p.charAt(0).toLowerCase() + p.slice(1);
        }).join(' ');

        word = word.charAt(0).toUpperCase() + word.slice(1);

        return word;
    },

    /**
     * Helper function which creates the model field set
     * title.
     * Shopware use as default the model name of
     * the passed record.
     *
     * @param modelName
     * @return String
     */
    getModelName: function(modelName) {
        return modelName.substr(modelName.lastIndexOf(".")+1);
    },



    /**
     * Adds the shopware default form field configuration for integer form field.
     * The field configuration will be applied to the passed field object.
     *
     * @param field
     * @return Ext.form.field.Number
     */
    applyIntegerFieldConfig: function(field) {
        field.xtype = 'numberfield';
        field.align = 'right';
        return field;
    },

    /**
     * Adds the shopware default form field configuration for string form field.
     * The field configuration will be applied to the passed field object.
     *
     * @param field
     * @return Ext.form.field.Number
     */
    applyStringFieldConfig: function(field) {
        field.xtype = 'textfield';
        return field;
    },

    /**
     * Adds the shopware default form field configuration for boolean form field.
     * The field configuration will be applied to the passed field object.
     *
     * @param field
     * @return Ext.form.field.Number
     */
    applyBooleanFieldConfig: function(field) {
        field.xtype = 'checkbox';
        field.uncheckedValue = false;
        field.inputValue = true;
        return field;
    },

    /**
     * Adds the shopware default form field configuration for date form field.
     * The field configuration will be applied to the passed field object.
     *
     * @param field
     * @return Ext.form.field.Number
     */
    applyDateFieldConfig: function(field) {
        field.xtype = 'datefield';
        return field;
    },

    /**
     * Adds the shopware default form field configuration for float form field.
     * The field configuration will be applied to the passed field object.
     *
     * @param field
     * @return Ext.form.field.Number
     */
    applyFloatFieldConfig: function(field) {
        field.xtype = 'numberfield';
        field.align = 'right';
        return field;
    },
});
//{/block}
