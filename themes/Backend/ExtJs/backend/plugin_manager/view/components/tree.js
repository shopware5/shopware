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

//{block name="backend/plugin_manager/view/components/tree"}
Ext.define('Shopware.apps.PluginManager.view.components.Tree', {
    extend: 'Ext.container.Container',
    alternateClassName: 'PluginManager.category.Tree',
    name: 'category-tree',
    cls: 'category-tree navigation-level',
    alias: 'widget.plugin-manager-category-tree',

    initComponent: function() {
        var me = this;

        me.store.on('load', function(store, records) {
            me.addNavigationItems(records, me);
        }, { single: true });

        me.callParent(arguments);
    },

    selectActiveTreeNode: function(category) {
        var me = this;
        var actives   = me.getActiveTreeRoute(category, me.items.items);
        var activeIds = me.getActiveIds(actives);

        me.disableTreeNodes(activeIds, me.items.items);
    },

    removeSelection: function() {
        this.selectActiveTreeNode(null);
    },

    /**
     * @param records
     * @param container
     */
    addNavigationItems: function(records, container) {
        var me = this;

        Ext.each(records, function(record) {
            var itemSubContainer = null;
            var content = '<div class="content">' + record.get('name') + '</div>';

            if (record.get('parentId')) {
                content = '<div class="node-lines">&nbsp;</div>' + content;
            }

            var itemContainer =  Ext.create('PluginManager.container.Container', {
                html: content,
                record: record,
                parentContainer: container,
                cls: 'category navigation-item',
                disabled: !Shopware.app.Application.sbpAvailable,
                listeners: {
                    click: function() {
                        me.selectActiveTreeNode(record);
                        me.fireEvent('select-category', record);
                    }
                }
            });

            container.add(itemContainer);

            if (record.getChildren() && record.getChildren().getCount() > 0) {

                itemSubContainer = Ext.create('Ext.container.Container', {
                    items: [],
                    cls: 'navigation-level sub-level',
                    hidden: true,
                    parentContainer: itemContainer
                });

                me.addNavigationItems(record.getChildren().data.items, itemSubContainer);

                container.add(itemSubContainer);
            }

            itemContainer.subContainer = itemSubContainer;
        });
    },

    getActiveTreeRoute: function(category, items) {
        var me = this, actives = [];

        Ext.each(items, function(item) {
            item.removeCls('active');

            if (category && item.hasCls('category') && item.record.get('id') == category.get('id')) {

                actives.push(item.subContainer);

                item.addCls('active');
            } else if (item.hasCls('navigation-level')) {

                var activeChildren = me.getActiveTreeRoute(category, item.items.items);

                if (activeChildren.length > 0) {
                    actives.push(item);

                    Ext.each(activeChildren, function(activeChild) {
                        actives.push(activeChild);
                    });
                }
            }
        });

        return actives;
    },

    getActiveIds: function(items) {
        var me = this, ids = [];

        Ext.each(items, function(item) {
            if (!item) {
                return true;
            }
            ids.push(item.id);
        });
        return ids;
    },

    disableTreeNodes: function(actives, items) {
        var me = this;

        Ext.each(items, function(item) {
            if (!item.hasCls('navigation-level')) {
                return true;
            }

            if (actives.indexOf(item.id) >= 0) {
                item.show();
            } else {
                item.hide();
            }

            me.disableTreeNodes(actives, item.items.items);
        });
    }
});
//{/block}