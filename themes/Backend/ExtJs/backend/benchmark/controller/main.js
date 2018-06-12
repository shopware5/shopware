
//{block name="backend/benchmark/controller/main"}
Ext.define('Shopware.apps.Benchmark.controller.Main', {
    extend: 'Ext.app.Controller',

    refs: [
        { ref: 'settingsPanel', selector: 'form[name=benchmark-settings-panel]' }
    ],

    init: function () {
        var me = this,
            windowName = 'overview.Window',
            params = {};

        me.control({
            'benchmark-overview-window': {
                'beforeclose': me.onBeforeCloseOverviewWindow
            }
        });

        if (me.subApplication.params && me.subApplication.params.isTeaser) {
            params = {
                isTeaser: true,
                height: 700
            };
        }

        if (this.subApplication.action === 'Settings') {
            windowName = 'settings.Window';
            params = {};

            Ext.Ajax.request({
                url: '{url controller=Benchmark action=loadSettings}',
                success: function (response) {
                    var settingsData = Ext.decode(response.responseText);

                    me.getSettingsPanel().loadSettingsRecord(settingsData);
                    me.getSettingsPanel().setLoading(false);
                }
            });
        }

        me.mainWindow = me.getView(windowName).create(params).show();

        me.callParent(arguments);
    },

    /**
     * @param { Ext.window.Window } win
     */
    onBeforeCloseOverviewWindow: function (win) {
        /*{if {acl_is_allowed privilege=manage}}*/
        var el =  win.down('#disableBenchmarkTeaser');

        if (el && el.getValue()) {
            Ext.Ajax.request({
                url: '{url controller=Benchmark action=disableBenchmarkTeaser}'
            });
        }
        /*{/if}*/
    }
});
//{/block}
