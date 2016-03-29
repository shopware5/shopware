
//{namespace name="backend/attributes/main"}

Ext.define('Shopware.apps.Attributes.store.Column', {
    extend: 'Shopware.store.AttributeConfig',

    groupers: [{
        property: 'configured',
        direction: 'DESC'
    }]
});
