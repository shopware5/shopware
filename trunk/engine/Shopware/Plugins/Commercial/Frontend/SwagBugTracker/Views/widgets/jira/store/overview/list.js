Ext.define('Shopware.apps.Jira.store.overview.List', {
    extend: 'Ext.data.Store',
    model: 'Shopware.apps.Jira.model.overview.List',
    autoLoad: true,
    remoteSort: true
});