
Ext.define('Shopware.apps.Theme.view.detail.containers.TabPanel', {
    extend: 'Ext.tab.Panel',
    alias: 'widget.theme-tab-panel',
    bodyPadding: 15,
    layout: {
        type: 'hbox',
        align: 'stretch'
    },
    defaults: {
        flex: 1
    }
});