

Ext.define('Shopware.apps.Template.view.list.Template', {
    extend: 'Ext.view.View',
    alias:  'widget.template-listing-grid',
    region: 'center',

    initComponent: function() {
        var me = this;

        me.itemSelector = '.thumb-wrap';
        me.tpl = me.createTemplate();

        me.callParent(arguments);
    },

    createTemplate: function() {

        return new Ext.XTemplate(
            '{literal}<tpl for=".">',
                '<div>{name}</div>',
            '</tpl>',
            '<div class="x-clear"></div>{/literal}'
        );
    }
});
