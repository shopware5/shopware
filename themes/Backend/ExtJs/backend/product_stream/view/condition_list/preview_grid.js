
Ext.define('Shopware.apps.ProductStream.view.condition_list.PreviewGrid', {
    extend: 'Ext.grid.Panel',
    title: 'Preview',
    alias: 'widget.product-stream-preview-grid',

    initComponent: function() {
        var me = this;

        me.store = Ext.create('Shopware.apps.ProductStream.store.Preview');

        me.pagingbar = Ext.create('Ext.toolbar.Paging', {
            store: me.store,
            dock: 'bottom',
            displayInfo: true
        });
        me.toolbar = me.createToolbar();

        me.columns = me.createColumns();
        me.dockedItems = [me.toolbar, me.pagingbar];
        me.callParent(arguments);
    },

    createToolbar: function() {
        var me = this;

        me.customerGroupStore = Ext.create('Shopware.store.Search', {
            configure: function() {
                return { entity: 'Shopware\\Models\\Customer\\Group' }
            },
            fields: ['key', 'name']
        }).load();

        me.currencyStore = Ext.create('Shopware.store.Search', {
            configure: function() {
                return { entity: 'Shopware\\Models\\Shop\\Currency' }
            },
            fields: ['id', 'currency']
        }).load();

        me.currencyCombo = Ext.create('Ext.form.field.ComboBox', {
            displayField: 'currency',
            valueField: 'id',
            store: me.currencyStore,
            value: 1,
            fieldLabel: 'Currency',
            forceSelection: true,
            name: 'currency',
            labelWidth: 100
        });
        me.customerCombo = Ext.create('Ext.form.field.ComboBox', {
            displayField: 'name',
            valueField: 'key',
            store: me.customerGroupStore,
            value: 'EK',
            name: 'customerGroup',
            fieldLabel: 'Customer group',
            forceSelection: true,
            labelWidth: 100
        });
        me.shopCombo = Ext.create('Ext.form.field.ComboBox', {
            store: Ext.create('Shopware.apps.Base.store.ShopLanguage').load(),
            displayField: 'name',
            valueField: 'id',
            name: 'shop',
            forceSelection: true,
            value: 1,
            fieldLabel: 'Shop',
            labelWidth: 100
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'top',
            items: [me.shopCombo, me.currencyCombo, me.customerCombo]
        });
    },

    createColumns: function() {
        return [{
            header: 'Number',
            width: 110,
            dataIndex: 'number'
        },{
            header: 'Name',
            flex: 1,
            dataIndex: 'name'
        }, {
            header: 'Stock',
            width: 60,
            dataIndex: 'stock'
        }, {
            header: 'Price',
            dataIndex: 'cheapestPrice',
            renderer: this.priceRenderer
        }];
    },

    priceRenderer: function(value) {
        var me = this;

        if (!Ext.isObject(value)) {
            return '';
        }

        if (!value.hasOwnProperty('calculatedPrice')) {
            return '';
        }

        return value.calculatedPrice;
    }
});