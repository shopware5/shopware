Ext.define('Shopware.window.Progress', {
    extend: 'Enlight.app.SubWindow',
    title: 'Delete items',
    layout: 'fit',
    bodyPadding: 10,
    width: 300,
    modal: true,
    height: 90,


    progressTitle: 'Items: [0] of [1]',
    progressCount: 1,

    initComponent: function () {
        var me = this;

        me.items = [ me.createProgressbar() ];

        me.callParent(arguments);
    },

    createProgressbar: function () {
        var me = this;

        me.progressbar = Ext.create('Ext.ProgressBar', {
            animate: true,
            text: Ext.String.format(me.progressTitle, 0, me.progressCount),
            value: 0,
            margin: '0 0 15',
            style: 'border-width: 1px !important;',
            cls: 'left-align'
        });

        return me.progressbar;
    }
});