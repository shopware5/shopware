//{namespace name=backend/plugins/staging/main}
Ext.define('Shopware.apps.Staging.view.main.Table', {
    extend:'Ext.grid.Panel',
    alias:'widget.staging-main-table',
    border: 0,
    collapsed: false,
    title: '{s name=table_settings/title}Table-Settings{/s}',
    region: 'center',
    disabled: true,
    columnLines: true,
    profileType: 'master',
    plugins: [
        Ext.create('Ext.grid.plugin.RowEditing', {
            ptype: 'rowediting'
        })
    ],
    createTopBar: function() {
	    var me = this;
		me.updateTablesButton = Ext.create('Ext.button.Button', {
			iconCls: 'sprite-upload-cloud',
			text: '{s name=table_settings/button_update_table}Update table strategy in batch{/s}',
			disabled: true,
			listeners: {
				click: function(){
					this.fireEvent('updateTablesInBatch',this);
				},
				scope:this
			}
		});
    
        return {
           xtype: 'toolbar',
           dock: 'top',
           items: [
               {
                   xtype: 'button',
                   text: '{s name=table_settings/button_load_tables}Load tables from master{/s}',
                   action: 'syncTables',
                   iconCls: 'sprite-open-share-balloon'
               },
               this.getStrategySelectCombo(), me.updateTablesButton ]
       };
    },
    getStrategySelectCombo: function(){
    	var me = this;
        me.strategySelectCombo = Ext.create('Ext.form.field.ComboBox',{
            xtype: 'combobox',
            ref: 'strategySelect',
            allowBlank: true,
            valueField: 'id',
            displayField: 'description',
            store : me.strategyStoreMaster,
            editable: false,
            listeners: {
	            scope: me,
	            change: function(field, value) {
		        	me.updateTablesButton.setDisabled(!value.length);
	            }
            }
        });
        return me.strategySelectCombo;
    },
    setProfileType: function(profileType){
        this.profileType = profileType;
        if (profileType == "master"){
            this.strategySelectCombo.bindStore(this.strategyStoreMaster);
            this.gridEditor.bindStore(this.strategyStoreMaster);
        }else {
            this.strategySelectCombo.bindStore(this.strategyStoreSlave);
            this.gridEditor.bindStore(this.strategyStoreSlave);
        }
    },
    setStrategyStore: function(){
        /**
         * Master > Slave Profile:
         * replicate
         * view
         * ignore
         *
         * Slave > Master Profile:
         * replicate (Default complete, or col-based)
         * ignore
         * columns - Synchronize only some column values back
         * @type { Ext.data.SimpleStore}
         */
        var me = this;

        me.strategyStoreMaster = new Ext.data.SimpleStore({
            autoSync:true,
            fields: ['id', 'description'],
            data: [['replicate', '{s name=table_settings/combobox/copy_table}Copy table{/s}'], ['view', '{s name=table_settings/combobox/view}Use as view{/s}'], ['ignore', '{s name=table_settings/combobox/ignore}Ignore{/s}']]
        });

        me.strategyStoreSlave = new Ext.data.SimpleStore({
           autoSync:true,
           fields: ['id', 'description'],
           data: [['replicate', '{s name=table_settings/combobox/copy_table_back}Copy table back{/s}'], ['ignore', '{s name=table_settings/combobox/ignore}Ignore{/s}'], ['col_based_replication','{s name=table_settings/combobox/column_copy}Column based copy{/s}']]
       });
    },
    createDockedToolBar: function(){
         return  {
               dock: 'bottom',
               xtype: 'pagingtoolbar',
               displayInfo: true,
               store: this.store,
               items: [
                '{s name=table_settings/search}Search{/s}',
                 {
                    xtype: 'textfield',
                    action: 'updateTableStrategy',
                    iconCls: 'sprite-upload-cloud',
                    emptyText: '{s name=table_settings/search_tables}Search tables{/s}',
                    enableKeyEvents:true,
                    checkChangeBuffer:500,
                    listeners: {
                        change: function(field, value) {
                            this.fireEvent('searchTable', this.store, value);
                        },
                        scope:this
                    }
                 }
               ]
         };
     },
    initComponent:function () {
        var me = this;
        me.selModel = Ext.create('Ext.selection.CheckboxModel');
        me.setStrategyStore('master');
        me.columns = me.getColumns();
        me.on('edit', me.onEditRow, me);
        me.addEvents('searchTable','updateTablesInBatch','defineCols');
        me.dockedItems = [
            this.createTopBar(),
            this.createDockedToolBar()
        ];
        me.callParent(arguments);
    },
    getEditor: function(){

        var store = this.profileType == "master" ? this.strategyStoreMaster : this.strategyStoreSlave;

        this.gridEditor =  Ext.create('Ext.form.field.ComboBox',{
           xtype: 'combobox',
           allowBlank: true,
           valueField: 'id',
           displayField: 'description',
           store : store,
           editable: false
        });
        return this.gridEditor;
    },
    getColumns: function(){
        return [
            {
                xtype: 'gridcolumn',
                dataIndex: 'tableName',
                text: '{s name=table_settings/column_table}Table{/s}',
                flex: 2
            },
            {
                xtype: 'gridcolumn',
                dataIndex: 'strategy',
                text: '{s name=table_settings/column_deployment}Deployment strategy{/s}',
                flex: 2,
                renderer: function(origValue){
                    if (!origValue) return;

                    var store = (this.profileType == "master" ? this.strategyStoreMaster : this.strategyStoreSlave);
                    if (!store) return;
                    
                    //console.log(store);
                    
                    var value = store.find('id',origValue);
                    if (!value && !value === 0) return origValue;
                    value = store.getAt(value);
                    if (!value){
                        return origValue;
                    }
                    return value.get('description');
                },
                editor: this.getEditor()
            },
            {
                xtype: 'actioncolumn',
                text: '{s name=table_settings/column_assignment}Column assignment{/s}',
                width: 120,
                items: [
                    {
                        iconCls: 'sprite-table-split-row',
                        handler: function(r,rowIdx,v){
                            this.fireEvent('defineCols', this.store, rowIdx);
                        },
                        scope:this,
                        getClass: this.actionItemRenderer
                    }
                ]
            }
        ];
    },
    actionItemRenderer: function(value,meta,record,rowIx,ColIx, store) {
        return (record.get("strategy") == "replicate" || record.get("strategy") == "col_based_replication") && this.profileType == "slave" ?//test some condition
                        'x-grid-center-icon': //Show the action icon
                        'x-hide-display';  //Hide the action icon
    },
    /**
     * Event listener method which will be fired when the user
     * edits a row in the role grid with the built-in row
     * editor.
     *
     * Saves the edited record to the store.
     *
     * @event edit
     * @param [object] editor
     * @return void
     */
    onEditRow: function(editor, event) {
        var store = event.store;

        //editor.grid.setLoading(true);
        store.sync({
            callback: function() {
               // editor.grid.setLoading(false);
            }
        });
        Shopware.Notification.createGrowlMessage('{s name=table/Success}Successful{/s}', '{s name=table/updatedSuccessfully}Table has been updated{/s}', '{s name="table/title"}Staging{/s}');
    }
});