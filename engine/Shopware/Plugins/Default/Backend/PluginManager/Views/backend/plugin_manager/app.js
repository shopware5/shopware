
Ext.define('Shopware.apps.PluginManager', {
    extend: 'Enlight.app.SubApplication',
    name: 'Shopware.apps.PluginManager',
    bulkLoad: true,
    loadPath: '{url controller=PluginManager action=load}',

    controllers: [
        'Main',
        'Navigation',
        'Plugin'
    ],

    views: [
        'PluginHelper',

        'components.Container',
        'components.ImageSlider',
        'components.Listing',
        'components.StorePlugin',
        'components.Tab',
        'components.Tree',

        'list.HomePage',
        'list.LocalPluginListingPage',
        'list.Navigation',
        'list.StoreListingPage',
        'list.UpdatePage',
        'list.LicencePage',
        'list.Window',


        'detail.Window',
        'detail.Container',
        'detail.Prices',
        'detail.Comments',
        'detail.Header',
        'detail.Meta',
        'detail.Actions',

        'loading.Mask',
        'account.Login',
        'account.Checkout',
        'account.Upload'

    ],

    stores: [
        'Basket',
        'Licence',
        'LocalPlugin',
        'StorePlugin',
        'Category',
        'UpdatePlugins'
    ],

    models: [
        'Licence',
        'Plugin',
        'Comment',
        'Picture',
        'Basket',
        'BasketPosition',
        'Domain',
        'Address',
        'Price',
        'Category',
        'Producer'
    ],

    launch: function () {
        return this.getController('Main').mainWindow;
    }
});
