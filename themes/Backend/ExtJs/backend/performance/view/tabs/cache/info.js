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

//{namespace name=backend/performance/main}

//{block name="backend/performance/view/tabs/cache/info"}
Ext.define('Shopware.apps.Performance.view.tabs.cache.Info', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.performance-tabs-cache-info',

    title: '{s name=info/title}Cache-Directory information{/s}',

    layout: 'fit',
    autoScroll: true,

    initComponent: function() {
        var me = this;

        me.buttons = [
            Ext.create('Ext.Button', {
                text: '{s name=fieldset/buttons/refresh}Refresh{/s}',
                cls: 'secondary',
                scope: me,
                handler:function () {
                    me.refreshCacheData();
                }}
            )
        ];

        Ext.applyIf(me, {
            columns: me.getColumns()
        });

        me.callParent(arguments);
    },

    /**
     * Reloads cache data from server
     */
    refreshCacheData: function() {
        var me = this;

        me.store.load();
    },

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns: function() {
        var me = this;

        return [{
            header: '{s name=info/columns/name}Name{/s}',
            dataIndex: 'name',
            flex: 5
        }, {
            header: '{s name=info/columns/backend}Backend{/s}',
            dataIndex: 'backend',
            flex: 3
        }, {
            header: '{s name=info/columns/directory}Directory{/s}',
            dataIndex: 'dir',
            flex: 4
        }, {
            header: '{s name=info/columns/files}Files{/s}',
            dataIndex: 'files',
            align: 'right',
            flex: 2
        }, {
            header: '{s name=info/columns/size}Size{/s}',
            dataIndex: 'size',
            align: 'right',
            flex: 2
        }, {
            header: '{s name=info/columns/freeSpace}Free memory{/s}',
            dataIndex: 'freeSpace',
            align: 'right',
            flex: 2
        }, {
            header: '{s name=info/columns/message}Message{/s}',
            dataIndex: 'message',
            width: 60,
            renderer: me.messageRenderer
        }];
    },

    messageRenderer: function(value, metaData, record, colIndex, store, view) {
        var tpl = new Ext.XTemplate(
            '<div class="sprite-balloon"></div>'
        );

        if (value.length == 0) {
            return '<center><div style="width: 16px; height: 16px;" class="sprite-tick-circle"></div></center>';
        } else {
            metaData.tdAttr = 'data-qtip="' + value + '"';
            return '<center><div style="width: 16px; height: 16px;" class="sprite-exclamation"></div></center>';
        }
    }
});
//{/block}
