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
 * @package    PluginManager
 * @subpackage Components
 * @version    $Id$
 * @author shopware AG
 */

//{block name="backend/plugin_manager/view/components/tab"}
Ext.define('Shopware.apps.PluginManager.view.components.Tab', {
    extend: 'Ext.container.Container',
    cls: 'shopware-plugin-manager-tab',
    alias: 'widget.shopware-plugin-manager-tab',
    alternateClassName: 'PluginManager.tab.Panel',

    initComponent: function() {
        var me = this;

        me.tabs = me.items;

        me.items = [
            me.createNavigation(),
            me.createContent()
        ];

        me.callParent(arguments);
    },

    disableTab: function(index) {
        var item = this.getNavigationItemByIndex(index);
        if (item) item.disable();
    },

    enableTab: function(index) {
        var item = this.getNavigationItemByIndex(index);

        if (item) item.enable();
    },

    hideTab: function(index) {
        var me = this,
            item = this.getNavigationItemByIndex(index);

        if (item) item.hide();
        this.checkDisplayedTabs();
    },

    checkDisplayedTabs: function() {
        var me = this;
        var somethingShown = false;

        Ext.each(me.navigation.items.items, function(item) {
            if (item.hidden == false) {
                somethingShown = true;
            }
        });

        if (somethingShown) {
            me.show();
        } else {
            me.hide();
        }
    },

    showTab: function(index) {
        var item = this.getNavigationItemByIndex(index);

        if (item) item.show();
        this.checkDisplayedTabs();
    },

    getNavigationItemByIndex: function(index) {
        var me = this;
        var element = null;

        Ext.each(me.navigation.items.items, function(item, i) {
            if (i == index) {
                element = item;
                return true;
            }
        });

        return element;
    },

    createNavigation: function() {
        var me = this, items = [], cls = '';

        Ext.each(me.tabs, function(tab, index) {
            cls = 'tab-navigation-item';
            if (index == 0) {
                cls += ' active';
            }

            var container = Ext.create('PluginManager.container.Container', {
                cls: cls,
                definition: tab,
                html: '<span>'+tab.title+'</span>',
                height: 38,
                index: index,
                listeners: {
                    click: function() {
                        me.navigationClick(index);
                    }
                }
            });

            items.push(container);
        });

        me.navigation = Ext.create('Ext.container.Container', {
            height: 38,
            items: items,
            cls: 'tab-navigation'
        });

        return me.navigation;
    },

    createContent: function() {
        var me = this, cls = '',
            items = [];

        Ext.each(me.tabs, function(content, index) {
            var tab = Ext.create('Ext.container.Container', {
                cls: 'tab-item-content',
                items: [ content ]
            });

            items.push(tab);
        });

        me.cardContainer = Ext.create('Ext.container.Container', {
            layout: 'card',
            cls: 'tab-content',
            items: items
        });

        return me.cardContainer;
    },

    navigationClick: function(index) {
        var me = this;

        if (!me.cardContainer || me.cardContainer.length <= 0) {
            return;
        }

        try {
            me.cardContainer.getLayout().setActiveItem(index);
        } catch (e) {
        }

        Ext.each(me.navigation.items.items, function(item, itemIndex) {
            if (itemIndex == index) {
                item.addCls('active');
            } else {
                item.removeCls('active');
            }
        });
    }
});
//{/block}