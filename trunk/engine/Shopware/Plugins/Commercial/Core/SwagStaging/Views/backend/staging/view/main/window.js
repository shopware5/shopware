//{namespace name=backend/plugins/staging/main}
Ext.define('Shopware.apps.Staging.view.main.Window', {
    extend: 'Enlight.app.Window',
    title: '{s name=window/title}Staging{/s}',
    alias: 'widget.staging-main-window',
    border: false,
    autoShow: true,
    layout: 'border',
    isMaster: false,
    modal: true,
    height: 650,
    width: 925,

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;
        me.dockedItems = me.createNoticeContainer();
        
        me.tabPanel = Ext.create('Ext.tab.Panel', {
	        region:'center',
            items:me.getTabs()
        });
        
        me.items = [ me.tabPanel ];

        me.callParent(arguments);
    },
    createNoticeContainer: function() {
        var me = this;

        if (me.isMaster){
            var notification = Shopware.Notification.createBlockMessage('{s name=window/scope_master}Current-Scope: Master{/s}', 'success');
        }else {
            var notification = Shopware.Notification.createBlockMessage('{s name=window/scope_staging}Current-Scope: Staging{/s}', 'notice');
        }
        notification.margin = '10 5';
        return notification;
    },
    getTabs:function () {
        return [
            {
                xtype:'staging-main-configuration',
                initialTitle: 'config',
                isMaster: this.isMaster,
                testStatus: this.testStatus,
                store: this.testStore
            },
            {
                xtype:'staging-main-profiles',
                initialTitle: 'profiles',
                profileStore: this.profileStore,
                disabled: this.testStatus == true ? false : true,
                isMaster: this.isMaster,
                tableStore: this.tableStore,
                disabled: !this.testStatus
            },
            {
                xtype:'staging-main-status',
                initialTitle: 'jobs',
                store: this.jobStore,
                queueStore: this.queueStore,
                disabled: this.testStatus == true ? false : true,
                isMaster: this.isMaster,
                disabled: !this.testStatus
            }
        ];
    }
});
