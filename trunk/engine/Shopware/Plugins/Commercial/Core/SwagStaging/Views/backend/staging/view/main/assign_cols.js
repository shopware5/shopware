//{namespace name=backend/plugins/staging/main}
Ext.define('Shopware.apps.Staging.view.main.AssignCols', {
    extend: 'Enlight.app.Window',
    title: '{s name=assign_cols/title}Column assignment{/s}',
    alias: 'widget.staging-main-profiles-assigncols',
    border: false,
    modal: true,
    autoShow: true,
    layout: 'border',
    height: 600,
    width: 600,

	snippets:{
		please_select_columns: '{s name=assign_cols/please_select_columns}Please select columns that should be  migrated in real time from master database during replication{/s}',
		cancel: '{s name=assign_cols/cancel_btn}Cancel{/s}',
		save_btn: '{s name=assign_cols/save_btn}Save{/s}'
	},

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;
        me.items = [me.getGrid(me.store)];

        me.addEvents('updateColumns');
        me.dockedItems = [
            {
              xtype:'panel',
              dock:'top',
              html: me.snippets.please_select_columns,
              bodyPadding: 10,
              height: 30
            },
            {
            xtype: 'toolbar',
            dock: 'bottom',
            ui: 'shopware-ui',
            cls: 'shopware-toolbar',
            items: ['->', {
                text: me.snippets.cancel,
                cls: 'secondary',
                scope: me,
                handler: me.close
            },{
                text: me.snippets.save_btn,
                cls: 'primary',
                handler: function(btn) {
                    var me = this;
                    me.fireEvent('updateColumns', me.getGrid(), me);
                },
                scope:this
            }]
        }];

        me.callParent(arguments);

    },
    getGrid: function(store){
        if (this.grid) return this.grid;
        this.grid = Ext.create('Ext.grid.Panel',{
            region: 'center',
            store: store,
            selModel: Ext.create('Ext.selection.CheckboxModel'),
            columns: [
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'name',
                    text: 'Column',
                    width: 200
                }
            ],
            listeners: {
                'viewready': function(){
                    var grid = this.getGrid();
                    var selModel = grid.getSelectionModel();
                    var checkedCols = new Array();
                    Ext.each(this.records, function(record) {
                       if (record.data.checked){
                           checkedCols.push(record);
                       }
                       selModel.select(checkedCols);
                    });
                },
                scope:this
            }
        });
        return this.grid;
    }

});
