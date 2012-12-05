
//{namespace name=backend/config/view/main}
Ext.define('Shopware.apps.ConfigIframe.view.main.Window', {
    extend: 'Enlight.app.Window',
    title: '{s name=window/title}Basic settings{/s}',
    bodyBorder: false,
    layout: 'fit',

    initComponent: function() {
        var me = this;

        me.height = 600;
        me.width = 1000;
        me.callParent(arguments);
    },

    afterRender: function() {
        this.callParent(arguments);
        var me = this,
            body = me.body;

        me.setLoading(true);
        var iframe = document.createElement('iframe');
        iframe.setAttribute('src', '{url controller=Config}');
        iframe.setAttribute('width', '100%');
        iframe.setAttribute('height', '100%');
        iframe.addEventListener('load', function() {
            me.setLoading(false);
        }, false);

        body.appendChild(iframe);
    }
});