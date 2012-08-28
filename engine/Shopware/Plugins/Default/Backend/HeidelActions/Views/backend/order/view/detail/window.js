//{extends file="[default]backend/order/view/detail/window.js"}
//{block name="backend/order/view/detail/window" append}
//{namespace name=backend/order/main}
Ext.define('Shopware.apps.Order.view.detail.Window-SwagHeidelpay', {
    /**
     * Defines an override applied to a class.
     * @string
     */
    override: 'Shopware.apps.Order.view.detail.Window',

    requires: [ 'Shopware.apps.Order.view.detail.Heidelpay' ],


    createTabPanel: function() {
        var me = this,
            tabPanel = me.callOverridden(arguments);

        var panel = Ext.create('Shopware.apps.Order.view.detail.Heidelpay', {
            title: 'Heidelpay',
            layout: 'fit',
            border: 0,
            bodyBorder: 0,
            record: me.record
        });
        tabPanel.add(panel);

        return tabPanel;

    }


});
//{/block}