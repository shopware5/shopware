//{namespace name=backend/plugins/staging/main}
Ext.define('Shopware.apps.Staging.view.main.Status', {
    extend:'Ext.panel.Panel',
    alias:'widget.staging-main-status',
    border: 0,
    bodyPadding: 10,
    collapsed: false,
    isMaster: false,
    title: '{s name=status/title}Status{/s}',
    layout: {
        type: 'border'
    },
    initComponent:function () {
        var me = this;
        me.items = [
            me.getTree(me.store),
            me.getPanel(me.queueStore)
        ];
        me.addEvents('deleteJob','startJob', 'stopJob', 'resetJob');
        me.callParent(arguments);
    },
    getPanel: function(queueStore){
        var me = this;
        return Ext.create('Ext.panel.Panel',{
            layout: {
                type: 'border'
            },
            bodyBorder: false,
            disabled: true,
            ref: 'jobProperties',
            title: '{s name=status/job_master_slave_replication}Job Master > Slave Replication{/s}',
            region: 'center',
            items: [
                 me.getStatusPanel(),
                 me.getQueuePanel(queueStore)
            ]
        });
    },
    getStatusPanel: function(){
    	var me = this;
    	
    	me.startJobButton = Ext.create('Ext.button.Button', {
	    	text: '{s name=status/button_start_job}Start Job{/s}',
			ref: 'startJob',
			iconCls: 'sprite-control-power',
			handler: function(r,rowIdx,v) {
				me.fireEvent('startJob', me);
			},
			scope:this
    	});
    	
    	me.stopJobButton = Ext.create('Ext.button.Button', {
	    	text: '{s name=status/button_stop_job}Stop Job{/s}',
			disabled: true,
			action: 'stopJob',
			iconCls: 'sprite-control-stop-square',
			handler: function() {
				me.fireEvent('stopJob', me);
			}
    	});
    	
    	me.resetJobButton = Ext.create('Ext.button.Button', {
	    	text: '{s name=status/button_reset_job}Reset Job{/s}',
			ref: 'resetJob',
			disabled: true,
			handler: function() {
			
			  this.fireEvent('resetJob');
			},
			scope:this,
			iconCls: 'sprite-arrow-circle'
    	});
    
        return Ext.create('Ext.panel.Panel',{
            height: 215,
            autoScroll: false,
            region: 'north',
            dockedItems: [
               {
                   xtype: 'toolbar',
                   dock: 'top',
                   items: [ me.startJobButton, me.stopJobButton, me.resetJobButton,
                      {
                         xtype: 'button',
                         text: '{s name=status/button_delete_backup}Delete Backup{/s}',
                         disabled: true,
                         action: 'deleteBackup',
                         iconCls: 'sprite-disk--minus'
                      },
                      {
                        xtype: 'button',
                        text: '{s name=status/button_restore_backup}Restore Backup{/s}',
                        disabled: true,
                        action: 'restoreBackup',
                        iconCls: 'sprite-disk--plus'
                      }
                   ]
               }
            ],
            items: [
                {
                    xtype: 'fieldset',
                    margin: 10,
                    title: 'Statistics',
                    items: [
                        {
                            xtype: 'displayfield',
                            value: '',
                            ref: 'backupExists',
                            fieldLabel: '{s name=status/backup_status}Backup Status:{/s}',
                            anchor: '100%'
                        },
                        {
                            xtype: 'progressbar',
                            text: '{s name=status/job_progress}Job progress{/s}',
                            value: 0.0,
                            animate: false
                        },
                        {
                            xtype: 'displayfield',
                            value: '',
                            ref: 'startedOn',
                            fieldLabel: '{s name=status/started_on}Started on{/s}',
                            anchor: '100%'
                        },
                        {
                            xtype: 'displayfield',
                            value: '',
                            ref: 'startedBy',
                            fieldLabel: '{s name=status/started_by}Started by{/s}'
                        },
                        {
                            xtype: 'displayfield',
                            value: '',
                            ref: 'finishedOn',
                            fieldLabel: '{s name=status/finished_on}Finished on{/s}',
                            anchor: '100%'
                        }
                    ]
                }
            ]
        });
    },
    getQueuePanel: function(queueStore){
        return Ext.create('Ext.grid.Panel',{
           title: '{s name=status/title_queue}Queue{/s}',
           region: 'center',
           ref: 'jobQueue',
           store: queueStore,
           plugins: [
           {
                ptype: "rowexpander",
                //{literal}
                rowBodyTpl: [ '<p><b>Job-Description: </b>{text}<br/>{errorMsg}</p>' ],
                //{/literal}
                expandOnDblClick: false
           }],
           columns: [
               {
                   xtype: 'gridcolumn',
                   dataIndex: 'position',
                   text: '{s name=status/column_position}Position{/s}',
                   width: 50
               },
               {
                   xtype: 'gridcolumn',
                   dataIndex: 'text',
                   text: '{s name=status/column_job_description}Job-Description{/s}',
                   width:350
               },
               {
                   xtype: 'gridcolumn',
                   dataIndex: 'done',
                   text: '{s name=status/column_status}Status{/s}',
                   width: 100,
                   renderer: function(v){

                       if (v==1){
                           // Job successful
                           return '<div class="sprite-tick-small"  style="width: 25px; height: 25px">&nbsp;</div>';
                       }else if(v==2){
                           // Job error
                           return '<div class="sprite-exclamation-red"  style="width: 25px; height: 25px">&nbsp;</div>';
                       }else {
                           // Job not progressed yet
                           return '<div class="sprite-quill"  style="width: 25px; height: 25px">&nbsp;</div>';
                       }
                   }
               },
               {
                   xtype: 'gridcolumn',
                   dataIndex: 'duration',
                   text: '{s name=status/column_duration}Duration{/s}',
                   renderer: function(v){
                       return Ext.Date.format(v,'H:i:s');
                   }
               }
           ]
        });
    },
    getTree: function(store){
         if (this.treePanel) return this.treePanel;
         this.treePanel = Ext.create('Ext.tree.Panel',{
           width: 250,
           title: '{s name=status/title_jobs}Jobs{/s}',
           region: 'west',
           store: store,
           alias : 'widget.staging-status-job-tree',
           rootVisible:false,
           dockedItems: [
               {
                   xtype: 'toolbar',
                   dock: 'top',
                   items: [
                       {
                           xtype: 'button',
                           text: '{s name=status/button_add_job}Add job{/s}',
                           action: 'addNewJob'
                       },
                       {
                           xtype: 'button',
                           text: '{s name=status/button_delete_job}Delete job{/s}',
                           handler: function(){
                               this.fireEvent('deleteJob');
                           },
                           scope:this
                       },
                       {
                          xtype: 'button',
                          text: '{s name=status/button_reload_tree}Reload tree{/s}',
                          handler: function(){
                              this.getTree().store.load();
                          },
                          scope:this
                      }
                   ]
               }
           ]
        });

        return this.treePanel;
    }
});