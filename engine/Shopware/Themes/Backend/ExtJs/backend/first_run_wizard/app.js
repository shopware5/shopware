
Ext.define('Shopware.apps.FirstRunWizard', {
    name:'Shopware.apps.FirstRunWizard',
    extend:'Enlight.app.SubApplication',
    bulkLoad:true,
    loadPath:'{url action=load}',
    views:[
        'main.Window',
        'main.Home',
        'main.Localization',
        'main.Config',
        'main.DemoData',
        'main.Recommendation',
        'main.ShopwareId',
        'main.Payment',
    ],

    stores:[
        'Plugin'
    ],

    models: [
        'Plugin'
    ],

    controllers: [
        'Main',
        'Config',
        'ShopwareId',
        'Localization',
        'Home'
    ],

    launch: function() {
        return this.getController('Main').mainWindow;
    }
});


