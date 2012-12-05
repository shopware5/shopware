Ext.define('Shopware.apps.Jira.view.edit.Comments', {
    extend: 'Ext.view.View',
    alias: 'widget.jira-view-edit-comments',
    emptyText: 'Es liegen keine Kommentare vor.',
    deferEmptyText: false,
    itemSelector: 'div.comment-item',

    initComponent: function() {
        var me = this;
        me.store = me.createStore();
        me.tpl = me.createTemplate();
        me.callParent( arguments );

        me.on('render', me.onRenderView, me);
    },

    createTemplate: function() {
        /*{literal}*/
        return new Ext.XTemplate(
            '<tpl for=".">',
                '<div class="comment-item">',
                    '<p class="author">{author}</p>',
                    '<p class="createdAt">schrieb am {createdAt}</p>',
                    '<p class="description">{description}</p>',
                '</div>',
            '</tpl>'
        );
       /* {/literal}*/
    },

    createStore: function() {
        return Ext.create('Shopware.apps.Jira.store.edit.Comments');
    },

    onRenderView: function() {
        var me = this;
        me.store.getProxy().extraParams = { issueId: me._record.get('id'), issueKey: me._record.get('key') };
        me.store.load();
    }
});