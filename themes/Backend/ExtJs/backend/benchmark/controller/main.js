
//{block name="backend/benchmark/controller/main"}
Ext.define('Shopware.apps.Benchmark.controller.Main', {
    extend: 'Ext.app.Controller',

    refs: [
        { ref: 'settingsPanel', selector: 'form[name=benchmark-settings-panel]' }
    ],

    init: function () {
        var me = this,
            windowName = 'overview.Window';

        if (this.subApplication.action === 'Settings') {
            windowName = 'settings.Window';

            Ext.Ajax.request({
                url: '{url controller=Benchmark action=loadSettings}',
                success: function (response) {
                    var settingsData = Ext.decode(response.responseText);

                    me.getSettingsPanel().loadSettingsRecord(settingsData);
                    me.getSettingsPanel().setLoading(false);
                }
            });
        }

        me.mainWindow = me.getView(windowName).create().show();

        me.callParent(arguments);
    }
});
//{/block}
