Ext.define('Shopware.apps.Jira.view.edit.Commits', {
    extend: 'Ext.view.View',
    alias: 'widget.jira-view-edit-commits',
    emptyText: 'Es liegen keine Commits vor.',
    deferEmptyText: false,
    itemSelector: 'div.commit-item',

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
                '<div class="commit-item">',
                    '<p class="author">{date} - {author} - {message}</p>',
                    '<p class="description"><a href="{url}" target="_blank">{url}</a></p>',
                '</div>',
            '</tpl>'
        );
       /* {/literal}*/
    },

    createStore: function() {
        return Ext.create('Shopware.apps.Jira.store.edit.Commits');
    },

    onRenderView: function() {
        var me = this;
        me.store.getProxy().extraParams = { issueId: me._record.get('id'), issueKey: me._record.get('key') };
        me.store.load();
    }
});