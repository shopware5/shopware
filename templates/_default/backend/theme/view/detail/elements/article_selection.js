
Ext.define('Shopware.apps.Theme.view.detail.elements.ArticleSelection', {
    extend: 'Shopware.form.field.Search',

    alias: 'widget.theme-article-selection',

    initComponent: function() {
        var me = this;

        me.store = Ext.create('Shopware.apps.Base.store.Article').load();
        me.callParent(arguments);
    }
});
