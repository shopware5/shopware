
//{namespace name=backend/update_wizard/main}

//{block name="backend/update_wizard/app"}
Ext.define('Shopware.apps.UpdateWizard', {
    extend: 'Enlight.app.SubApplication',

    name:'Shopware.apps.UpdateWizard',

    loadPath: '{url controller=UpdateWizard action=load}',
    bulkLoad: true,

    controllers: [ 'Main' ],

    views: [
        'Window',
        'Start',
        'Login',
        'Plugins'
    ],

    models: [],
    stores: [],

    launch: function() {
        return this.getController('Main').mainWindow;
    }
});
//{/block}
