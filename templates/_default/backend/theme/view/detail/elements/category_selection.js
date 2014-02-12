
Ext.define('Shopware.apps.Theme.view.detail.elements.CategorySelection', {
    extend: 'Shopware.form.field.Search',
    alias: 'widget.theme-category-selection',

    initComponent: function() {
        var me = this;

        me.store = Ext.create('Shopware.apps.Theme.store.Category');
        me.store.getProxy().extraParams.parents = true;
        me.store.load();

        me.callParent(arguments);
    }
});
