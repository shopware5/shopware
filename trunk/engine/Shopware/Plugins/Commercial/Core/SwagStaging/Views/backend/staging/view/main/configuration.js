//{namespace name=backend/plugins/staging/main}
Ext.define('Shopware.apps.Staging.view.main.Configuration', {
    extend:'Ext.panel.Panel',
    alias:'widget.staging-main-configuration',
    border: 0,
    bodyPadding: 10,
    collapsed: false,
    title: '{s name=config/title}Configuration{/s}',
    layout: {
        type: 'border'
    },

	snippets:{
		panel_title: '{s name=config/panel_title}Help & Manual{/s}',
		panel_text: '{s name=config/panel_text}\<strong\>It is very important that you understand the concept behind the staging system before you start using the module.\<br /\>\<br /\>\<span color=\'#F00\'\>Warning: Incorrectly use can lead to data loss\</span\>\</strong\>\<br /\>\<a href=\'http://wiki.shopware.de/_detail_873.html\' target=\'_blank\'\>Open manual in new browser tab\</a\>{/s}',
		test: '{s name=config/test_text}Test{/s}',
		status: '{s name=config/status_text}Status{/s}',
		system_check: '{s name=config/system_check}Repeat system check{/s}',
		center_panel_title: '{s name=config/center_panel_title}System configuration / Integrity Check{/s}',
		test_again: '{s name=config/test_again}Test again{/s}',
		config_text: '{s name=config/config_text}\<pre\>Required .htaccess modifications check [OKAY]\nStaging Bootstrap found [OKAY]\nDatabase compatibility checks [OKAY] \n    - Seems that your database user has no \'CREATE-VIEW\' privilege [FAILURE] \nFound staging configuration in /config_staging.php [OKAY]\n    - Staging-System URL: http://staging.test.shopware.in/\n    - Staging-System Database: sw4_staging [OKAY]\n    - Staging-System Cache-Directory: /staging/cache [NOT WRITEABLE]\n    - Staging-System Synchronized?: Job1234 [OKAY]\n    - Staging-System Database: sw4_staging [OKAY]\n</pre>{/s}'
	},

    initComponent:function () {
        var me = this;
        me.items = [me.getTopPanel(),me.getGrid(me.store)];
        me.callParent(arguments);
    },
    getTopPanel: function(){
        this.topPanel = Ext.create('Ext.panel.Panel',{
            title: this.snippets.panel_title,
            html: this.snippets.panel_text,
            region: 'north',
            bodyPadding: 20,
            height: '200',
            margin: '0 0 15'
        });
        return this.topPanel;
    },
    getGrid: function(store){
        var me = this;
        if (me.grid) return me.grid;
        me.grid = Ext.create('Ext.grid.Panel',{
            region: 'center',
            store: store,
            dockedItems: me.getGridToolbar(),
            columns: [
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'test',
                    text: me.snippets.test,
                    flex: 1
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'status',
                    text: me.snippets.status,
                    width: 50,
                    renderer: function(v){
                       if (v==1){
                           // Job successful
                           return '<div class="sprite-tick-small"  style="width: 25px; height: 25px">&nbsp;</div>';
                       }else{
                           // Job error
                           return '<div class="sprite-exclamation-red"  style="width: 25px; height: 25px">&nbsp;</div>';
                       }
                   }
                }
            ]
        });
        return this.grid;
    },
    getGridToolbar: function(){
    	var me = this;
        return {
              xtype: 'toolbar',
              dock: 'top',
              items: [
                  {
                      xtype: 'button',
                      text: me.snippets.system_check,
                      iconCls: 'sprite-open-share-balloon',
                      scope: this,
                      handler: function() {
                          this.getGrid().store.load({
                              callback:function (records,operation,status) {
                                 if(!operation.wasSuccessful()) {
                                    var rawData = this.getGrid().store.getProxy().getReader().rawData;
                                    Shopware.Notification.createGrowlMessage('Error', rawData.message, 'Error');
                                    return;
                                 }
                                 var win = me.up('window'),
                                     testStatus = true;

								 Ext.each(records, function(record) {
								     if(record.get('status') === 0) {
								        testStatus = false;
								     }
								 });

								 win.tabPanel.items.each(function(tab) {
								 	 if(tab.initialTitle !== 'config') {
									 	 tab.setDisabled(!testStatus);
								 	 }
								 });
                              }
                          });
                      }
                  }
              ]
          };
    },
    getCenterPanel: function(){
        this.centerPanel = Ext.create('Ext.panel.Panel',{
           title: this.snippets.center_panel_title,
           dockedItems: [
               {
                  xtype: 'toolbar',
                  dock: 'top',
                  items: [
                     {
                         xtype: 'button',
                         text: this.snippets.test_again,
                         action: 'test',
                         iconCls: 'sprite-open-share-balloon'
                     }
                  ]
               }
           ],
           bodyPadding: 20,
           html: this.snippets.config_text,
           region: 'center'
        });
        return this.centerPanel;
    }
});