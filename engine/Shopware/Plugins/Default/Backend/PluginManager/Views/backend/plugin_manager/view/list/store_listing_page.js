
//{namespace name=backend/plugin_manager/translation}
Ext.define('Shopware.apps.PluginManager.view.list.StoreListingPage', {
    extend: 'Ext.container.Container',
    cls: 'plugin-manager-listing-page',
    alias: 'widget.plugin-manager-store-listing-page',
    autoScroll: true,

    initComponent: function() {
        var me = this;

        me.items = [ me.createStoreListing() ];

        me.callParent(arguments);
    },

    displayContent: function() {
        var me = this;
        me.content.show();
    },

    hideContent: function() {
        var me = this;
        me.content.hide();
    },

    createStoreListing: function() {
        var me = this;

        me.communityStore = Ext.create('Shopware.apps.PluginManager.store.StorePlugin');
        me.storeListing = Ext.create('PluginManager.components.Listing', {
            store: me.communityStore,
            name: 'community-store-listing',
            scrollContainer: me,
            padding: 30,
            width: 1007
        });

        me.filterPanel = Ext.create('Ext.container.Container', {
            cls: 'filter-panel',
            layout: 'hbox',
            padding: '15 0 15',
            margin: '0 30',
            items: [
                me.createPriceFilter(),
                me.createCertifiedFilter(),
                me.createSorting()
            ]
        });

        me.content = Ext.create('Ext.container.Container', {
            items: [
                me.filterPanel,
                me.storeListing
            ]
        });

        return me.content;
    },

    createSorting: function() {
        var me = this;

        me.sortStore = Ext.create('Ext.data.Store', {
            fields: [ 'key', 'name' ],
            data: [
                { key: 'release', name: '{s name="sort_release_date"}{/s}' },
                { key: 'popularity', name: '{s name="sort_popularity"}{/s}' },
                { key: 'description', name: '{s name="sort_description"}{/s}' }
            ]
        });

        me.sortField = Ext.create('Ext.form.field.ComboBox', {
            cls: 'sort-field',
            store: me.sortStore,
            queryMode: 'local',
            displayField: 'name',
            valueField: 'key',
            value: 'release',
            forceSelection: true,
            editable: false,
            fieldLabel: '{s name="sorting"}{/s}',
            margin: '0 0 0 10',
            listeners: {
                expand: function() {
                    if (this.picker) {
                        this.picker.addCls('plugin-manager-filter-picker');
                    }
                },
                select: function(combo, record) {
                    me.fireEvent('filter-store-listing', me);
                }
            }
        });

        return me.sortField;
    },

    createCertifiedFilter: function() {
        var me = this;

        me.certifiedField = Ext.create('Ext.form.field.Checkbox', {
            fieldLabel: '{s name="certified_filter"}{/s}',
            inputValue: true,
            labelWidth: 130,
            uncheckedValue: false,
            name: 'certified',
            cls: 'certified-field',
            margin: '0 25',
            value: false,
            listeners: {
                'change': function(field, value) {
                    me.fireEvent('filter-store-listing', me);
                }
            }
        });

        return me.certifiedField;
    },

    createPriceFilter: function() {
        var me = this;

        me.priceStore = Ext.create('Ext.data.Store', {
            fields: ['key', 'name'],
            data: [
                { key: 'all', name: '{s name="filter_price_all"}{/s}' },
                { key: 'buy', name: '{s name="filter_price_buy"}{/s}' },
                { key: 'rent', name: '{s name="filter_price_rent"}{/s}' },
                { key: 'test', name: '{s name="filter_price_test"}{/s}' },
                { key: 'free', name: '{s name="filter_price_free"}{/s}' }
            ]
        });

        me.priceFilter = Ext.create('Ext.form.field.ComboBox', {
            cls: 'price-filter',
            store: me.priceStore,
            queryMode: 'local',
            displayField: 'name',
            valueField: 'key',
            value: 'all',
            fieldLabel: '{s name="filter_price"}{/s}',
            margin: '0 15 0 0',
            listeners: {
                expand: function() {
                    if (this.picker) {
                        this.picker.addCls('plugin-manager-filter-picker');
                    }
                },
                select: function(combo, record) {
                    me.fireEvent('filter-store-listing', me);
                }
            }
        });

        return me.priceFilter;
    }
});