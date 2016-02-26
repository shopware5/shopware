/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

//{namespace name=backend/index/view/widgets}

/**
 * Shopware UI - shopware News Widget
 *
 * This file holds off the shopware News widget.
 */
//{block name="backend/index/view/widgets/news"}
Ext.define('Shopware.apps.Index.view.widgets.News', {
    extend: 'Shopware.apps.Index.view.widgets.Base',
    alias: 'widget.swag-shopware-news-widget',
    title: '{s name=news/title}shopware News{/s}',
    layout: 'fit',
    bodyPadding: '8 8',

    /**
     * Snippets for this widget.
     * @object
     */
    snippets: {
        offlineMsg: "{s name=news/offline_msg}We can't establish a connection to the Shopware server. Please check your connection and try again later.{/s}",
        headers: {
            pubDate: '{s name=news/headers/date}Date{/s}',
            title: '{s name=news/headers/title}Title{/s}'
        }
    },

    newsStore: null,

    /**
     * Initializes the widget.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.newsStore = Ext.create('Shopware.apps.Index.store.NewsLocal');

        me.grid = Ext.create('Ext.grid.Panel', {
            border: 0,
            store: me.newsStore,
            xtype: 'grid',
            columns: me.createColumns(),
            listeners: {
                itemclick: function(view, record) {
                    var win = window.open(record.data.link, '_blank');
                    win.focus();
                    view.getSelectionModel().deselectAll();
                    me.setVisited(record.data.linkHash);
                    record.set('visited', true);
                    view.store.sync();
                }
            }
        });

        me.items = [ me.grid ];

        me.tools = [{
            type: 'refresh',
            scope: me,
            handler: function() {
                me.refreshView(true);
            }
        }];

        me.createTaskRunner();

        me.callParent(arguments);
    },

    /**
     * Registers a new task runner to refresh
     * the store after a given time interval.
     *
     * @public
     * @param [object] store - Ext.data.Store
     * @return void
     */
    createTaskRunner: function() {
        var me = this;

        me.storeRefreshTask = Ext.TaskManager.start({
            scope: me,
            run: me.refreshView,
            interval: 300000
        });
    },

    /**
     * Helper method which will be called by the
     * task runner and when the user clicks the
     * refresh icon in the panel header.
     *
     * @public
     * @return void
     */
    refreshView: function(forceRefresh) {
        var me = this,
            timestampKey = me.newsStore.getProxy().id + '-timestamp',
            lastRefresh = new Date().getTime() - window.localStorage.getItem(timestampKey);

        if(me.newsStore.count() === 0 || lastRefresh >= 86400000 || forceRefresh === true) {
            me.syncRemoteToLocal();
            window.localStorage.setItem(timestampKey, new Date().getTime());
        }
    },

    /**
     * Fetches the RSS feed from the backend, which
     * fetches it from the shopware servers
     */
    syncRemoteToLocal: function() {
        var me = this,
            remoteStore = Ext.create('Shopware.apps.Index.store.News');

        remoteStore.load(function(records, operation, success) {
            if (!success) {
                me.showOfflineMessage();
                return;
            }

            me.newsStore.remove(me.newsStore.getRange());
            me.newsStore.sync();

            Ext.each(records, function(record) {
                var model = Ext.create('Shopware.apps.Index.model.News', record.data);
                me.newsStore.add(model);
            });

            me.newsStore.sync();
        });
    },

    /**
     * Shows in offline message instead of the `Ext.grid.Panel` to inform the shop owner that we can't establish a
     * connection to the shopware server.
     */
    showOfflineMessage: function() {
        var me = this;

        me.removeAll();

        me.add({
            xtype: 'container',
            html: '<div style="display: table-cell; vertical-align: middle;">' + me.snippets.offlineMsg + '</div>',
            style: {
                fontWeight: 'bold',
                textAlign: 'center',
                color: '#fff',
                textShadow: '1px 1px 1px rgba(0, 0, 0, 0.2)',
                fontSize: '15px',
                lineHeight: '23px',
                display: 'table'
            }
        });
    },

    /**
     * Saves already visited links in localStorage
     *
     * @param linkHash
     */
    setVisited: function(linkHash) {
        var visited = window.localStorage.getItem('widget-settings-shopware-news-cache-visited'),
            visitedLinks = Ext.decode(visited) || {};

        if (visitedLinks[linkHash]) {
            return;
        }

        visitedLinks[linkHash] = 1;

        window.localStorage.setItem('widget-settings-shopware-news-cache-visited', Ext.encode(visitedLinks));
    },

    /**
     * Helper method which creates the columns
     * for the grid.
     *
     * @public
     * @return [array] generated columns
     */
    createColumns: function() {
        var me = this;

        return [
            {
                header: me.snippets.headers.title,
                dataIndex: 'title',
                flex: 2,
                renderer: me.titleColumn
            },
            {
                header: me.snippets.headers.pubDate,
                dataIndex: 'pubDate',
                flex: 1,
                renderer: me.dateColumn
            }
        ];
    },

    /**
     * Formats the date column
     *
     * @param [string] - The order time value
     * @return [string] - The passed value, formatted with Ext.util.Format.date()
     */
    dateColumn: function(value) {
        if ( value === Ext.undefined ) {
            return value;
        }

        return Ext.util.Format.date(value);
    },

    /**
     * Formats the title column
     *
     * @param [string] value
     * @param [object] dom
     * @param [Ext.data.Model] record
     * @returns [string]
     */
    titleColumn: function(value, dom, record) {
        dom.tdCls = (record.get('visited') === false ? 'is-unread' : '');
        return value;
    }
});
//{/block}
