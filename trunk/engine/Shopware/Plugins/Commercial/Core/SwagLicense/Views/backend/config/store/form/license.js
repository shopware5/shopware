/**
 * todo@all: Documentation
 */
//{block name="backend/config/store/form/license"}
Ext.define('Shopware.apps.Config.store.form.License', {
    extend: 'Ext.data.Store',
    model:'Shopware.apps.Config.model.form.License',
    remoteSort: true,
    remoteFilter: true,
    pageSize: 20,
    proxy: {
        type: 'ajax',
        url: '{url controller=license action=getList}',
        api: {
            create: '{url controller=license action=save}',
            update: '{url controller=license action=save}',
            destroy: '{url controller=license action=delete}'
        },
        reader: {
            type: 'json',
            root: 'data'
        }
    }
});
//{/block}
