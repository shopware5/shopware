
//{namespace name="backend/benchmark/main"}
//{block name="backend/benchmark/view/overview/window"}
Ext.define('Shopware.apps.Benchmark.view.overview.Window', {
    extend: 'Enlight.app.Window',

    title: '{s name="overview/title"}Benchmark Overview{/s}',

    initComponent: function () {
        this.items = this.getItems();

        this.callParent(arguments);
    },

    /**
     * @returns { Ext.container.Container[] }
     */
    getItems: function () {
        var url = '{url controller=Benchmark action=render}';

        return [
            Ext.create('Ext.container.Container', {
                html: '<iframe src="' + url + '" width="100%" height="100%"></iframe>'
            })
        ];
    }
});
//{/block}
