{block name="backend/config/view/form/search" append}
{if !1}<script type="text/javascript">{/if}

Ext.define('Shopware.apps.Config.model.form.SearchTable', {
    extend: 'Ext.data.Model',
    fields: [
        { name: 'id', type: 'int' },
        { name: 'name', type: 'string' },
        { name: 'referenz_table', type: 'string', useNull: true },
        { name: 'foreign_key', type: 'string', useNull: true },
        { name: 'where', type: 'string', useNull: true }
    ]
});
Ext.define('Shopware.apps.Config.store.form.SearchTable', {
    model: 'Shopware.apps.Config.model.form.SearchTable',
    remoteSort: true,
    remoteFilter: true,
    pageSize: 20,
    proxy: {
        type: 'ajax',
        url: '{url action=getTableList name=searchTable}',
        api: {
            create: '{url action=saveTableValues name=searchTable}',
            update: '{url action=saveTableValues name=searchTable}',
            destroy: '{url action=deleteTableValues name=searchTable}'
        },
        reader: {
            type: 'json',
            root: 'data'
        }
    }
});
Ext.override(Shopware.apps.Config.view.form.Search, {
    getItems: function () {
        var me = this,
                store = me.getStore(),
                tabs = me.callOverridden(arguments);
        tabs.push({
            xtype: 'config-base-form',
            title: 'Table configuration',
            items: [{
                xtype: 'config-base-table',
                region: 'center',
                border: false,
                sortableColumns: false,
                store: store,
                columns: me.getColumnsTable()
            },{
                xtype: 'config-base-detail',
                items: me.getFormItemsTable()
            }]
        });
        return tabs;
    },
    getStore: function(){
        return Ext.create('Shopware.apps.Config.store.form.SearchTable');
    },
    getColumnsTable: function() {
        var me = this;
        return [{
            dataIndex: 'name',
            text: 'Table',
            allowBlank: false,
            flex: 1
        }, {
            dataIndex: 'referenz_table',
            text: 'Reference-Table',
            flex: 1
        }, {
            dataIndex: 'foreign_key',
            text: 'Foreign-Key',
            flex: 1
        }, {
            dataIndex: 'where',
            text: 'Additional conditions',
            flex: 1
        }/*, me.getActionColumn()*/];
    },
    getFormItemsTable: function() {
        var me = this;
        return [{
            name: 'name',
            fieldLabel: 'Table',
            allowBlank: false
        },{
            name: 'referenz_table',
            fieldLabel: 'Reference table'
        },{
            name: 'foreign_key',
            fieldLabel: 'Foreign key'
        },{
            name: 'where',
            fieldLabel: 'Additional statement'
        }];
    }
});
{/block}