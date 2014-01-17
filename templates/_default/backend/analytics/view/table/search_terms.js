//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/table/search-terms"}
Ext.define('Shopware.apps.Analytics.view.table.SearchTerms', {
    extend: 'Shopware.apps.Analytics.view.main.Table',
    alias: 'widget.analytics-table-search-terms',

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
            dataIndex: 'searchterm',
            text: 'Suchbegriff'
        }];
    }
});
//{/block}