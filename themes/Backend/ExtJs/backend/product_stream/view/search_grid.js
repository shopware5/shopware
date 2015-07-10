
Ext.define('Shopware.apps.ProductStream.view.SearchGrid', {
    extend: 'Ext.form.FieldContainer',
    layout: { type: 'vbox', align: 'stretch' },
    border: false,
    cls: 'product-stream-search-grid',

    /**
     * @required
     */
    searchStore: null,

    /**
     * @required
     */
    store: null,

    initComponent: function() {
        this.items = this.createItems();
        this.callParent(arguments);
    },

    createItems: function() {
        return [
            this.createSearchField(),
            this.createGrid()
        ];
    },

    createSearchField: function() {
        var me = this;

        me.searchField = Ext.create('Shopware.form.field.Search', {
            store: me.searchStore,
            displayField: 'name',
            valueField: 'id',
            multiSelect: true,
            fieldLabel: 'Search',
            pageSize: me.searchStore.pageSize,
            listeners: {
                select: function (combo, records) {
                    me.onSelectItem(combo, records);
                }
            }
        });
        return me.searchField;
    },

    createGrid: function() {
        this.grid = Ext.create('Ext.grid.Panel', {
            flex: 1,
            store: this.store,
            dockedItems: [this.createPagingBar()],
            columns: this.createColumns()
        });
        return this.grid;
    },

    createColumns: function() {
        var columns = this.createDisplayColumns();
        columns.push(this.createActionColumn());
        return columns;
    },

    createDisplayColumns: function() {
        return [{
            header: 'Name',
            sortable: false,
            dataIndex: 'name',
            flex: 1
        }];
    },

    createActionColumn: function() {
        return {
            xtype: 'actioncolumn',
            width: 25,
            sortable: false,
            items: this.createActionItems()
        };
    },

    createActionItems: function() {
        var me = this;
        return [{
            iconCls: 'sprite-minus-circle-frame',
            handler: function (view, rowIndex, colIndex, item, opts, record) {
                me.removeRecord(record);
            }
        }];
    },

    createPagingBar: function() {
        var me = this;
        me.pagingbar = Ext.create('Ext.toolbar.Paging', {
            store: me.store,
            dock: 'bottom'
        });
        return me.pagingbar;
    },

    onSelectItem: function (combo, records) {
        var me = this, inStore;

        Ext.each(records, function (record) {
            inStore = me.store.getById(record.get('id'));
            if (inStore === null) {
                me.addRecord(record);
            }
        });
        combo.setValue('');
    },

    addRecord: function(record) {
        this.store.add(record);
    },

    removeRecord: function(record) {
        this.store.remove(record);
    }
});