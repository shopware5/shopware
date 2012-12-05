Ext.define('Shopware.apps.Jira.view.main.Panel', {
    extend: 'Ext.tab.Panel',
    layout: 'border',
    activeTab: 0,
    autoHeight: true,
    autoScroll:true,
    title: 'Shopware Bug-Tracker',
    initComponent: function() {
        var me = this;
        me.dockedItems = me.createNoticeContainer();
        me.callParent(arguments);
    },
    createNoticeContainer: function() {
        return [
        {
            xtype: 'toolbar',
            dock: 'top',
            items: [{
                xtype:'button',
                text:'Zur Hilfe',
                handler: function(){
                    window.open('http://wiki.shopware.de/Anleitung-Bug-Tracker_detail_939_719.html');
                }
            },
            {
                xtype:'button',
                text:'Tracker im neuen Fenster öffnen',
                handler: function(){
                    window.open('http://jira.shopware.de/');
                }
            },
            ]
        }];

        var me = this;


        var notification = Shopware.Notification.createBlockMessage('<a href="http://wiki.shopware.de" target="_blank">Zur Hilfe / Anleitung</a>', 'success');
        notification.margin = '0 0 0 0';
        return notification;
    }
});