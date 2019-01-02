
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

        if (me.subApplication.params) {
            if (me.subApplication.params.isTeaser) {
                params = {
                    isTeaser: true,
                    height: 700
                };
            }

            if (me.subApplication.params.shopId) {
                params = {
                    shopId: me.subApplication.params.shopId
                };
            }
        }

        if (this.subApplication.action === 'Settings') {
            windowName = 'settings.Window';
            params = {};
        }

        me.mainWindow = me.getView(windowName).create(params).show();

        window.addEventListener('message', function (msg) {
            if (msg.data === 'closeWindow') {
                me.mainWindow.destroy();
            }
        }, false);

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
