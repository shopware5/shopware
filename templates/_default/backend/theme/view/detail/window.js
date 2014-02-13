

Ext.define('Shopware.apps.Theme.view.detail.Window', {
    extend: 'Enlight.app.Window',
    alias: 'widget.theme-detail-window',
    title : '{s name=title}Theme details{/s}',
    height: 420,
    width: 1080,
    layout: 'fit',
    cls: 'theme-config-window',

    initComponent: function() {
        var me = this;

        me.items = [ me.createFormPanel() ];
        me.dockedItems = [ me.createToolbar() ];

        me.callParent(arguments);
    },

    /**
     * Creates the form panel for the window.
     * @returns { Ext.form.Panel }
     */
    createFormPanel: function() {
        var me = this;

        var tabs = me.createTabs(me.elements);

        me.formPanel = Ext.create('Ext.form.Panel', {
            layout: 'fit',
            items: [{
                xtype: 'tabpanel',
                items: tabs
            }]
        });
        return me.formPanel;
    },

    /**
     * Creates the tab panels for the config window.
     *
     * @param elements
     * @returns { Array }
     */
    createTabs: function(elements) {
        var me = this, tabs = [],
            collection = {};

        Ext.each(elements, function(element) {
            var tab = collection[element.tab];

            if (!tab) {
                tab = {
                    padding: 20,
                    layout: 'column',
                    xtype: 'container',
                    autoScroll: true,
                    title: element.tab,
                    items: [ ]
                };
            }

            tab.items.push(element);
            collection[element.tab] = tab;
        });

        //iterate tab collection to create tabs
        for (var key in collection) {
            var tab = collection[key],
                fields = tab.items;

            var counter = Math.round(fields.length / 2);

            tab.items = [
                me.createContainer(fields.slice(0, counter)),
                me.createContainer(fields.slice(counter))
            ];

            tabs.push(tab);
        }

        return tabs;
    },

    /**
     * Creates a column container for the tab panels.
     * @param fields
     * @returns { Ext.container.Container }
     */
    createContainer: function(fields) {
        return Ext.create('Ext.container.Container', {
            columnWidth: 0.5,
            defaults: {
                labelWidth: 150,
                anchor: '95%'
            },
            layout: 'anchor',
            items: fields
        });
    },


    /**
     * Creates the window toolbar.
     *
     * @returns { Ext.toolbar.Toolbar }
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
     * Creates all toolbar elements.
     *
     * @returns { Array }
     */
    createToolbarItems: function() {
        var me = this, items = [];

        items.push({ xtype: 'tbfill' });

        items.push(me.createCancelButton());

        items.push(me.createSaveButton());

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
            text: 'Cancel',
            handler: function () {
                me.destroy();
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
                me.fireEvent(
                    'saveConfig',
                    me.theme,
                    me.shop,
                    me.formPanel,
                    me
                );
            }
        });
        return me.saveButton;
    }

});
