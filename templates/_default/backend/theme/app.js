
Ext.define('Shopware.apps.Theme', {
    extend: 'Enlight.app.SubApplication',

    name:'Shopware.apps.Theme',

    loadPath: '{url action=load}',
    bulkLoad: true,

    controllers: [ 'Main' ],

    views: [
        'list.Window',
        'list.Theme',
        'list.extensions.Info',

        'detail.Theme',
        'detail.Window'
    ],

    models: [ 'Theme' ],
    stores: [ 'Theme' ],

    launch: function() {
        return this.getController('Main').mainWindow;
    }
});