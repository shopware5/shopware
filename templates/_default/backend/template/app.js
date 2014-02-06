
Ext.define('Shopware.apps.Template', {
    extend: 'Enlight.app.SubApplication',

    name:'Shopware.apps.Template',

    loadPath: '{url action=load}',
    bulkLoad: true,

    controllers: [ 'Main' ],

    views: [
        'list.Window',
        'list.Template',

        'detail.Template',
        'detail.Window'
    ],

    models: [ 'Template' ],
    stores: [ 'Template' ],

    launch: function() {
        return this.getController('Main').mainWindow;
    }
});