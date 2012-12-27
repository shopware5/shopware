/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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

/**
 * todo@all: Documentation
 */

//{namespace name=backend/cache/view/main}

//{block name="backend/cache/view/main/info"}
Ext.define('Shopware.apps.Cache.view.main.Info', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.cache-info',

    title: '{s name=info/title}Cache-Directory information{/s}',

    layout: 'fit',
    autoScroll: true,

    initComponent: function() {
        var me = this;

         Ext.applyIf(me, {
             store: 'main.Info',
             columns: me.getColumns()
         });

        me.callParent(arguments);
        me.store.load();
    },

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns: function() {
        return [{
            header: '{s name=info/columns/name}Name{/s}',
            dataIndex: 'name',
            flex: 2
        }, {
            header: '{s name=info/columns/backend}Backend{/s}',
            dataIndex: 'backend',
            flex: 3
        }, {
            header: '{s name=info/columns/directory}Directory{/s}',
            dataIndex: 'dir',
            flex: 5
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
            flex: 3
        }];
    }
});
//{/block}