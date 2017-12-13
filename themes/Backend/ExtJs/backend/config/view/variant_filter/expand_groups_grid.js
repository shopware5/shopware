Ext.define('Shopware.apps.Config.view.variantFilter.ExpandGroupsGrid', {
    extend: 'Shopware.form.field.Grid',
    alias: 'widget.variant-filter-expand-group-grid',

    initComponent: function() {
        console.warn(this);
        this.callParent(arguments);
    },

    createColumns: function() {
        var columns = this.callParent(arguments);

        columns = Ext.Array.insert(columns, columns.length -1, [{
            header: 'Beschreibung',
            dataIndex: 'description',
            flex: 1,
            renderer: function(val) {
                if (!val) {
                    return 'yay';
                }
                return '<i>' + val + '</i>';
            }
        }]);

        return columns;
    }
});
