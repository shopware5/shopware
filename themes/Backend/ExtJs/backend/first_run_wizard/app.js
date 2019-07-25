// {block name="backend/first_run_wizard/app"}
Ext.define('Shopware.apps.FirstRunWizard', {
    name: 'Shopware.apps.FirstRunWizard',
    extend: 'Enlight.app.SubApplication',
    bulkLoad: true,
    loadPath: '{url action=load}',
    views: [
        'main.Window',
        'main.Localization',
        'main.LocalizationSwitcher',
        'main.LocalizationInstaller',
        'main.Config',
        'main.DemoData',
        'main.Recommendation',
        'main.PayPal'
    ],

    stores: [
        'RecommendedPlugin',
        'LicensePlugin',
        'IntegratedPlugin',
        'DemoPlugin',
        'AvailableLocalizationPlugin',
        'LocalizationPlugin',
        'Localization',
        'Locale',
        'IntegratedPluginsCountries'
    ],

    models: [
        'Localization',
        'Locale',
        'IntegratedPluginsCountries'
    ],

    controllers: [
        'PayPal', // Do not change order!
        'Main',
        'Config',
        'Localization',
        'Recommendation',
    ],

    launch: function() {
        return this.getController('Main').mainWindow;
    }
});
// {/block}
