Ext.define('Shopware.apps.Config.view.variantFilter.ExpandGroupsGrid', {
    extend: 'Shopware.form.field.Grid',
    alias: 'widget.variant-filter-expand-group-grid',
    mixins: ['Shopware.model.Helper'],

    initComponent: function() {
        var me = this;
        me.callParent(arguments);

        me.grid.on('cellclick',function(grid, td, cellIndex, record){
            console.log(record);
            if (record.getName() === 'expandGroup') {
                record.set('expandGroup', !record.get('expandGroup'));
                record.commit();
            }
        });
    },

    createColumns: function() {
        var me = this;

        columns = me.callParent(arguments);

        columns = Ext.Array.insert(columns, columns.length -1, [me.applyBooleanColumnConfig({
            name: 'expandGroupIds',
            dataIndex: 'expandGroup',
            width: 90,
            header: '{s name="expandGroup"}{/s}'
        })]);

        return columns;
    }
});
