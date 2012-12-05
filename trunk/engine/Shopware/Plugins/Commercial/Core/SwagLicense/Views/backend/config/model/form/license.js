/**
 * todo@all: Documentation
 */
//{block name="backend/config/model/form/license"}
Ext.define('Shopware.apps.Config.model.form.License', {
    extend:'Ext.data.Model',

    fields: [
        //{block name="backend/config/model/form/license/fields"}{/block}
        { name: 'id', type:'int' },
        { name: 'label', type:'string' },
        { name: 'module', type:'string' },
        { name: 'host', type:'string' },
        { name: 'license', type:'string' },
        { name: 'version', type:'string' },
        { name: 'notation', type:'string' },
        { name: 'type', type:'int' },
        { name: 'added', type:'date' },
        { name: 'creation', type:'date' },
        { name: 'expiration', type:'date' },
        { name: 'active', type: 'boolean' }
    ]
});
//{/block}
