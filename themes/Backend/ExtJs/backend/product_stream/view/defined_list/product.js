
Ext.define('Shopware.apps.ProductStream.view.defined_list.Product', {
    extend: 'Shopware.apps.ProductStream.view.SearchGrid',

    enable: function() {
        this.grid.enable();
        this.callParent(arguments);
    },

    initComponent: function() {
        var me = this;
        me.store = Ext.create('Shopware.apps.ProductStream.store.DefinedProducts');
        me.searchStore = me.createSearchStore();
        me.callParent(arguments);
    },

    createGrid: function() {
        var grid = this.callParent(arguments);
        grid.disabled = true;
        return grid;
    },

    createSearchStore: function() {
        return Ext.create('Shopware.store.Search', {
            model: 'Shopware.apps.Base.model.Article',
            pageSize: 20,
            configure: function() {
                return { entity: "Shopware\\Models\\Article\\Article" }
            }
        });
    },

    addRecord: function(record) {
        this.callParent(arguments);
        this.sendAjaxRequest(
            '{url controller=ProductStream action=addDefinedProduct}',
            { streamId: this.streamId, articleId: record.get('id') }
        );
    },

    removeRecord: function(record) {
        this.callParent(arguments);
        this.sendAjaxRequest(
            '{url controller=ProductStream action=removeDefinedProduct}',
            { streamId: this.streamId, articleId: record.get('id') }
        );
    },

    sendAjaxRequest: function(url, params, callback) {
        Ext.Ajax.request({
            url: url,
            params: params,
            method: 'POST',
            success: function(operation, opts) {
                var response = Ext.decode(operation.responseText);

                if (Ext.isFunction(callback)) {
                    callback(response);
                }
            }
        });
    }
});