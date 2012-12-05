Ext.define('Shopware.apps.Jira.view.overview.Version', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.jira-view-overview-version',
    title: 'Ticket&uuml;bersicht',
    autoScroll:true,
    region:'center',
    viewConfig: {
            //Return CSS class to apply to rows depending upon data values
            getRowClass: function(record, index) {

                var c = record.get('status');
                if (c != 'Open') {
                    return 'finished';
                } else if (c > 0) {
                    return '';
                }
            }
        },
    initComponent: function() {
        var me = this;
        me.columns = me.createColumns();
        me.dockedItems = me.createDockedItems();
        me.addEvents('editIssue', 'searchChanged');

        me.callParent( arguments );

//        me.store.on('load', function(){
//            me.fireEvent('editIssue', null, 0, 0, null);
//        });
    },

    createColumns: function() {
        var me = this;
        return [{
            header: 'Issue-ID',
            dataIndex: 'key',
            width: 70
        },{
            header: 'Issue',
            dataIndex: 'name',
            flex: 10
        },{
            header: 'Autor',
            dataIndex: 'reporter',
            width: 120
        },{
            header: 'Status',
            dataIndex: 'status',
            width: 100
        },{
            xtype: 'datecolumn',
            header: 'Erstelldatum',
            dataIndex: 'createdAt',
            width: 100
        },{
            xtype: 'datecolumn',
            header: 'Letzte Anpassung',
            dataIndex: 'modifiedAt',
            width: 100
        },{
            xtype: 'actioncolumn',
            width: 25,
            iconCls: 'sprite-pencil',
            action: 'edit',
            tooltip: '{s name=column/edit}Issue einsehen{/s}',
            handler: function (view, rowIndex, colIndex, item) {
                me.fireEvent('editIssue', view, rowIndex, colIndex, item, 'version');
            }
        }];
    },

    createDockedItems: function() {
        var me = this;
        /**
        Ext.create('Ext.form.field.ComboBox', {
                       store: Ext.create('Ext.data.ArrayStore',{
                           fields: [
                            'id','name'
                           ],
                           data: Ext.JSON.decode(fieldModel.get('default'))
                       }),
                       valueField: 'id',
                       forceSelection: true,
                       displayField: 'name'
                   });
        **/
        return [
        {
            xtype: 'pagingtoolbar',
            displayInfo: true,
            store: me.store,
            dock: 'bottom',
            items: [ '->' ,{
                xtype:'textfield',
                name:'searchfield',
                action:'search',
                width:170,
                cls: 'searchfield',
                enableKeyEvents:true,
                checkChangeBuffer: 500,
                emptyText:'Suche...',
                listeners: {
                    change: function(textfield, value) {
                        me.fireEvent('searchChanged', textfield, value, 'version');
                    }
                }
            }]
        }];
    }
});