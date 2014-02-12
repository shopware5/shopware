
Ext.define('Shopware.apps.Theme.view.list.Window', {
    extend: 'Shopware.window.Listing',
    alias: 'widget.theme-list-window',
    height: 450,
    title : '{s name=window_title}Theme manager{/s}',
    minWidth: 600,

    configure: function() {
        return {
            listingGrid: 'Shopware.apps.Theme.view.list.Theme',
            listingStore: 'Shopware.apps.Theme.store.Theme',

            extensions: [
                { xtype: 'theme-listing-info-panel' }
            ]
        };
    },

    initComponent: function() {
        var me = this;

        me.dockedItems = [
            me.createToolbar()
        ];

        me.callParent(arguments);
    },

    /**
     * Following functions creates the toolbar elements
     * @returns { Ext.toolbar.Toolbar }
     */
    createToolbar: function () {
        var me = this;

        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            items: me.createToolbarItems(),
            ui: 'shopware-ui',
            dock: 'top'
        });

        return me.toolbar;
    },

    createToolbarItems: function () {
        var me = this,
            items = [];

        items.push({ xtype: 'tbspacer', width: 6 });
        items.push(me.createShopCombo());
        items.push('-');
        items.push(me.createAssignButton());
        items.push(me.createPreviewButton());
        items.push('-');
        items.push(me.createConfigureButton());
        items.push(me.createAddButton());
        items.push('->')
        items.push(me.createSearchField());

        return items;
    },

    createShopCombo: function () {
        var me = this;

        me.shopStore = Ext.create('Shopware.apps.Base.store.Shop').load({
            callback: function(records) {
                var first = records.shift();
                me.shopCombo.select(first);
            }
        });

        me.shopCombo = Ext.create('Ext.form.field.ComboBox', {
            name: 'shop',
            editable: false,
            store: me.shopStore,
            displayField: 'name',
            valueField: 'id'
        });

        return me.shopCombo;
    },

    createAssignButton: function () {
        var me = this;

        me.assignButton = Ext.create('Ext.button.Button', {
            text: 'Select theme',
            cls: 'small',
            disabled: true,
            handler: function() {
                me.fireEvent('assign-theme', me);
            }
        });

        return me.assignButton;
    },

    createPreviewButton: function () {
        var me = this;

        me.previewButton = Ext.create('Ext.button.Button', {
            text: 'Preview theme',
            disabled: true,
            cls: 'small',
            handler: function() {
                me.fireEvent('preview-theme', me);
            }
        });

        return me.previewButton;
    },

    createConfigureButton: function() {
        var me = this;

        me.configureButton = Ext.create('Ext.button.Button', {
            text: 'Configure theme',
            disabled: true,
            cls: 'small',
            handler: function() {
                me.fireEvent('configure-theme', me);
            }
        });

        return me.configureButton;
    },

    createAddButton: function() {
        var me = this;

        me.addButton = Ext.create('Ext.button.Button', {
            text: 'Add theme',
            cls: 'small',
            handler: function() {
                me.fireEvent('add-theme', me);
            }
        });

        return me.addButton;
    },

    createSearchField: function() {
        var me = this;

        me.searchField = Ext.create('Ext.form.field.Text', {
            cls: 'searchfield',
            width: 170,
            emptyText: 'Search ...',
            enableKeyEvents: true,
            checkChangeBuffer: 500,
            listeners: {
                change: function (field, value) {
                    me.fireEvent('search-theme', me, field, value);
                }
            }
        });

        return me.searchField;
    }

});