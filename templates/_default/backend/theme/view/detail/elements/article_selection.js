
Ext.define('Shopware.apps.Theme.view.detail.elements.ArticleSelection', {
    extend: 'Shopware.form.field.Search',

    alias: 'widget.theme-article-selection',

    hideTrigger: true,

    initComponent: function() {
        var me = this, params = { };

        if (me.value) {
            params.id = me.value
        }

        me.store = Ext.create('Shopware.apps.Theme.store.Article').load(params);

        me.callParent(arguments);
    }
});
