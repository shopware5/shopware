//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/table/search-terms"}
Ext.define('Shopware.apps.Analytics.view.table.SearchUrls', {
    extend: 'Shopware.apps.Analytics.view.main.Table',
    alias: 'widget.analytics-table-search-urls',

    initComponent: function () {
        var me = this;

        me.columns = {
            items: me.getColumns(),
            defaults: {
                flex:1
            }
        };

        me.callParent(arguments);
    },

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns: function () {
        var me = this;

        return [{
            dataIndex: 'count',
            text: 'Anzahl'
        }, {
            dataIndex: 'referrer',
            text: 'Suchlink'
        }, {
            xtype: 'actioncolumn',
            text: 'Optionen',
            items: [{
                action: 'viewSearchUrl',
                iconCls: 'sprite-application',
                tooltip:  'View search url',
                handler: function(grid, rowIndex, colIndex) {
                    var store = grid.store,
                        record = store.getAt(rowIndex);

                    window.open(record.get('referrer'), '_blank');
                }
            }]
        }];
    }
});
//{/block}