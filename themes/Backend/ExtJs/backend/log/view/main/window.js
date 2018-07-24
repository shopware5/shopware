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

//{namespace name=backend/log/main}

//{block name="backend/log/view/main/window"}
Ext.define('Shopware.apps.Log.view.main.Window', {
    extend: 'Enlight.app.Window',
    title: '{s name=window_title}Log{/s}',
    cls: Ext.baseCSSPrefix + 'log-window',
    alias: 'widget.log-main-window',
    border: false,
    autoShow: true,
    layout: 'fit',
    height: '90%',
    width: 925,

    stateful: true,
    stateId:'shopware-log-window',

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this, items = [];

        items.push({
            title: '{s name=tabs/backend}Backend log{/s}',
            xtype: 'log-main-list',
            store: me.logStore
        });

        /*{if {acl_is_allowed privilege=system}}*/
        items.push({
            title: '{s name=tabs/system}System log{/s}',
            xtype: 'log-system-list',
            store: me.systemLogsStore,
            logFilesStore: me.logFilesStore
        });
        /* {/if} */

        me.items = [Ext.create('Ext.tab.Panel', {
            layout: 'fit',
            items: items,
            activeTab: me.mode === 'systemlogs' ? 1 : 0
        })];

        me.callParent(arguments);
    }
});
//{/block}
