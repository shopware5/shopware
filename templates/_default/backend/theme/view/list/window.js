
//{namespace name=backend/theme/main}

Ext.define('Shopware.apps.Theme.view.list.Window', {
    extend: 'Shopware.window.Listing',
    alias: 'widget.theme-list-window',
    height: '80%',
    width: '75%',
    title : '{s name=listing}Theme manager 2.0{/s}',
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
        items.push(me.createAddButton());
        items.push(me.createRefreshButton());
        items.push('->');
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
            fieldLabel: '{s name=shop_combo}Template-Auswahl für Shop{/s}',
            editable: false,
            labelWidth: 150,
            store: me.shopStore,
            displayField: 'name',
            valueField: 'id'
        });

        return me.shopCombo;
    },

    createAddButton: function() {
        var me = this;

        me.addButton = Ext.create('Ext.button.Button', {
            text: '{s name=create}Create theme{/s}',
            handler: function() {
                me.fireEvent('create-theme', me);
            }
        });

        return me.addButton;
    },

    createRefreshButton: function() {
        var me = this;

        me.refreshButton = Ext.create('Ext.button.Button', {
            text: '{s name=refresh}Refresh list{/s}',
            handler: function() {
                me.fireEvent('refresh-list', me);
            }
        });

        return me.refreshButton;
    },

    createSearchField: function() {
        var me = this;

        me.searchField = Ext.create('Ext.form.field.Text', {
            cls: 'searchfield',
            width: 170,
            emptyText: '{s name=search}Search ...{/s}',
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