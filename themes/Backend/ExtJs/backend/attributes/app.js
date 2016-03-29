
//{namespace name="backend/attributes/main"}

Ext.define('Shopware.apps.Attributes', {
    name:'Shopware.apps.Attributes',
    extend:'Enlight.app.SubApplication',
    bulkLoad: true,
    loadPath: '{url action=load}',

    views: ['Window', 'Listing', 'Detail'],
    models: ['Table', 'Types'],
    stores: ['Table', 'Column', 'Types', 'Entities', 'DependingTable'],

    controllers: [ 'Main' ],

    launch: function() {
        var me = this,
            mainController = me.getController('Main');

        return mainController.mainWindow;
    }
});