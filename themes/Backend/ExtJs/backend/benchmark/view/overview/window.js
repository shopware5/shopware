
//{namespace name="backend/benchmark/main"}
//{block name="backend/benchmark/view/overview/window"}
Ext.define('Shopware.apps.Benchmark.view.overview.Window', {
    extend: 'Enlight.app.Window',
    layout: 'fit',
    width: 1050,
    height: 670,

    alias: 'widget.benchmark-overview-window',

    title: '{s name="overview/title"}Benchmark Overview{/s}',

    initComponent: function () {
        var me = this;

        me.items = me.getItems();

        if (me.isTeaser) {
            me.checkbox = Ext.create('Ext.form.field.Checkbox', {
                padding: '0 0 0 5px',
                itemId: 'disableBenchmarkTeaser',
                width: 150,
                boxLabel: '{s name=window/do_not_show_again}{/s}'
            });

            me.dockedItems = [{
                xtype: 'toolbar',
                dock: 'bottom',
                items:[me.checkbox, '->', me.cancelButton]
            }];
        }

        me.callParent(arguments);
    },

    /**
     * @returns { Ext.container.Container[] }
     */
    getItems: function () {
        var url = '{url controller=BenchmarkOverview}';

        if (this.shopId) {
            url = '{url controller=BenchmarkOverview shopId=replaceShopId}';
            url = url.replace('replaceShopId', this.shopId);
        }

        return [
            Ext.create('Ext.container.Container', {
                html: '<iframe src="' + url + '" width="100%" height="100%"></iframe>'
            })
        ];
    }
});
//{/block}
