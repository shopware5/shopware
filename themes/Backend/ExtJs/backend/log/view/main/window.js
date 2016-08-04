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
 *
 * @category   Shopware
 * @package    Log
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/log/main}

/**
 * todo@all: Documentation
 */
//{block name="backend/log/view/main/window"}
Ext.define('Shopware.apps.Log.view.main.Window', {
	extend: 'Enlight.app.Window',
    title: '{s name=window_title}Log{/s}',
    cls: Ext.baseCSSPrefix + 'log-window',
    alias: 'widget.log-main-window',
    border: false,
    autoShow: true,
    layout:'fit',
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
        var me = this;

        me.tabPanel = Ext.create('Ext.tab.Panel', {
            name: 'log-main-tab-panel',
            items: me.createTabPanelItems()
        });

        me.items = [
            me.tabPanel
        ];

        me.callParent(arguments);
    },

    /**
     * @return Ext.Component[]
     */
    createTabPanelItems: function() {
        var me = this;

        me.backendLogList = Ext.create('Shopware.apps.Log.view.log.backend.List', {
            logStore: me.backendLogStore
        });
        me.coreLogList = Ext.create('Shopware.apps.Log.view.log.core.List', {
            store: me.coreLogStore
        });
        me.pluginLogList = Ext.create('Shopware.apps.Log.view.log.plugin.List', {
            store: me.pluginLogStore
        });

        return [
            me.backendLogList,
            me.coreLogList,
            me.pluginLogList
        ];
    }
});
//{/block}
