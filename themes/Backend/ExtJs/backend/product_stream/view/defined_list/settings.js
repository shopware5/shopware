
Ext.define('Shopware.apps.ProductStream.view.defined_list.Settings', {
    extend: 'Ext.form.Panel',
    alias: 'widget.product-stream-settings',
    title: 'Settings',
    height: 170,
    margin: '0 0 10',
    bodyPadding: 10,
    collapsible: true,

    initComponent: function() {
        var me = this;

        me.items = me.createItems();
        me.callParent(arguments);
    },

    loadRecord: function(record) {
        var me = this;

        me.callParent(arguments);

        var sorting = record.get('sorting');
        sorting = Object.keys(sorting)[0];

        me.sortingCombo.setValue(sorting);
    },

    createItems: function() {
        return [
            this.createNameField(),
            this.createDescriptionField(),
            this.createSortingCombo()
        ];
    },

    createSortingCombo: function() {
        var me = this;

        me.sortingStore = Ext.create('Ext.data.Store', {
            fields: ['key', 'value', 'direction'],
            data: me.getSortings()
        });

        me.sortingCombo = Ext.create('Ext.form.field.ComboBox', {
            name: 'sorting',
            store: me.sortingStore,
            fieldLabel: 'Sorting',
            valueField: 'key',
            displayField: 'value',
            queryMode: 'local',
            anchor: '100%',
            allowBlank: false,
            forceSelection: true
        });

        return me.sortingCombo;
    },

    getSortings: function() {
        return [
            { key: 'Shopware\\Bundle\\SearchBundle\\Sorting\\ReleaseDate', value: 'Release date', direction: 'desc' },
            { key: 'Shopware\\Bundle\\SearchBundle\\Sorting\\PopularitySorting', value: 'Popularity', direction: 'desc' },
            { key: 'Shopware\\Bundle\\SearchBundle\\Sorting\\PriceSorting', value: 'Cheapest price', direction: 'asc' },
            { key: 'Shopware\\Bundle\\SearchBundle\\Sorting\\PriceSorting', value: 'Highest price', direction: 'desc' },
            { key: 'Shopware\\Bundle\\SearchBundle\\Sorting\\ProductNameSorting', value: 'Article description', direction: 'asc' }
        ];
    },

    createNameField: function() {
        this.nameField = Ext.create('Ext.form.field.Text', {
            name: 'name',
            anchor: '100%',
            allowBlank: false,
            fieldLabel: 'Name'
        });

        return this.nameField;
    },

    createDescriptionField: function() {
        this.descriptionField = Ext.create('Ext.form.field.TextArea', {
            name: 'description',
            anchor: '100%',
            rows: 3,
            fieldLabel: 'Description'
        });

        return this.descriptionField;
    }
});
