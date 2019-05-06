// {namespace name="backend/mail_log/view/list"}
// {block name="backend/mail_log/view/list/window"}
Ext.define('Shopware.apps.MailLog.view.list.Window', {

    extend: 'Shopware.window.Listing',
    alias: 'widget.mail_log-list-window',
    height: 600,
    width: 1000,
    layout: 'fit',
    title: '{s name="window_title"}{/s}',

    configure: function () {
        return {
            listingGrid: 'Shopware.apps.MailLog.view.list.MailLog',
            listingStore: 'Shopware.apps.MailLog.store.MailLog',
            extensions: [
                {
                    xtype: 'mail_log-listing-filter-panel'
                }
            ]
        };
    },

    createItems: function () {
        var me = this,
            items = me.callParent(arguments);

        var tabs = [
            {
                title: '{s name="tabpanel_title_listing"}{/s}',
                layout: 'border',
                items: items,
            },
        ];
        /*{if {acl_is_allowed privilege=manage}}*/
        var config = Ext.create('Shopware.apps.MailLog.view.config.Container');

        tabs.push({
            title: '{s name="tabpanel_title_config"}{/s}',
            layout: 'fit',
            items: config,
        });
        /*{/if}*/
        var panel = Ext.create('Ext.tab.Panel', {
            items: tabs,
            activeTab: 0,
        });

        return [panel];
    }

});
// {/block}