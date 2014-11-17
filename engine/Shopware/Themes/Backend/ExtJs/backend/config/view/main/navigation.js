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
 * @package    Shopware_Config
 * @subpackage Config
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/config/view/main}

/**
 * todo@all: Documentation
 */
//{block name="backend/config/view/main/navigation"}
Ext.define('Shopware.apps.Config.view.main.Navigation', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.config-navigation',

    width: 200,

    autoScroll: true,
    layout: 'fit',

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            //dockedItems: me.getToolbar(),
            items: me.getTree()
        });

        me.callParent(arguments);
    },

    getTree: function() {
        var me = this;
        return {
            xtype: 'treepanel',
            rootVisible: false,
            //flex: 1,
            border: false,
            store: 'main.Navigation',
            dockedItems: me.getSearchToolbar()
        };
    },

    getToolbar: function() {
        var me = this;
        return [{
            xtype: 'toolbar',
            ui: 'shopware-ui',
            dock: 'top',
            border: false,
            items: [ {
                xtype: 'config-element-select',
                name: 'shop',
                flex: 1,
                forceSelection: true,
                store: 'base.Shop'
            }]
        }];
    },

    getSearchToolbar: function() {
        var me = this;
        return [{
            xtype: 'toolbar',
            ui: 'shopware-ui',
            dock: 'top',
            border: false,
            items: me.getTopBar()
        }];
    },

    getTopBar:function () {
        return [ { xtype: 'tbspacer', width: 22 }, {
            xtype:'config-base-search',
            width: 165
        }];
    }
});
//{/block}
