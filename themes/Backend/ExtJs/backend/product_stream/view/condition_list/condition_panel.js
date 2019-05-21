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
 * @package    ProductStream
 * @subpackage Window
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/product_stream/main}
//{block name="backend/product_stream/view/condition_list/condition_panel"}
Ext.define('Shopware.apps.ProductStream.view.condition_list.ConditionPanel', {
    extend: 'Ext.form.Panel',
    cls: 'shopware-form',
    alias: 'widget.product-stream-condition-panel',
    autoScroll: true,
    layout: { type: 'vbox', align: 'stretch'},
    bodyPadding: '10 20',
    conditions: [],

    title: '{s name=conditions}Conditions{/s}',

    initComponent: function() {
        var me = this;

        me.conditions = [];
        me.items = [];
        me.conditionHandlers = me.sort(
            me.createConditionHandlers()
        );
        me.dockedItems = [me.createToolbar()];

        me.callParent(arguments);
    },

    sort: function(handlers) {
        return handlers.sort(function(a, b) {
            return a.getLabel().localeCompare(b.getLabel());
        });
    },

    loadPreview: function(conditions) {
        this.fireEvent('load-preview', conditions);
    },

    validateConditions: function() {
        return this.getForm().isValid();
    },

    getConditions: function() {
        var me = this;

        var values = me.getValues();
        var conditions = { };

        for (var key in values) {
            if (key.indexOf('condition.') == 0) {
                var newKey = key.replace('condition.', '');
                conditions[newKey] = values[key];
            }
        }
        return conditions;
    },

    putItemInContainer: function(item, container) {
        var me = this;

        container.name = item.getName();
        container.add(item);
        me.conditions.push(item.getName());
        me.add(container);
    },

    loadConditions: function(conditions) {
        var me = this;

        for (var key in conditions) {
            var condition = conditions[key];

            Ext.each(me.conditionHandlers, function(handler) {
                var container = me.createConditionContainer(handler);
                var item = handler.load(key, condition, container, conditions);
                if (item) {
                    container.collapsed = true;
                    me.putItemInContainer(item, container);
                }
            });
        }
    },

    createConditionHandlers: function() {
        return [
            Ext.create('Shopware.apps.ProductStream.view.condition_list.condition.Price'),
            Ext.create('Shopware.apps.ProductStream.view.condition_list.condition.Manufacturer'),
            Ext.create('Shopware.apps.ProductStream.view.condition_list.condition.Property'),
            Ext.create('Shopware.apps.ProductStream.view.condition_list.condition.Attribute'),
            Ext.create('Shopware.apps.ProductStream.view.condition_list.condition.Category'),
            Ext.create('Shopware.apps.ProductStream.view.condition_list.condition.ImmediateDelivery'),
            Ext.create('Shopware.apps.ProductStream.view.condition_list.condition.Closeout'),
            Ext.create('Shopware.apps.ProductStream.view.condition_list.condition.HasPseudoPrice'),
            Ext.create('Shopware.apps.ProductStream.view.condition_list.condition.IsNew'),
            Ext.create('Shopware.apps.ProductStream.view.condition_list.condition.CreateDate'),
            Ext.create('Shopware.apps.ProductStream.view.condition_list.condition.ReleaseDate'),
            Ext.create('Shopware.apps.ProductStream.view.condition_list.condition.VoteAverage'),
            Ext.create('Shopware.apps.ProductStream.view.condition_list.condition.Sales'),
            Ext.create('Shopware.apps.ProductStream.view.condition_list.condition.SearchTerm'),
            Ext.create('Shopware.apps.ProductStream.view.condition_list.condition.Height'),
            Ext.create('Shopware.apps.ProductStream.view.condition_list.condition.Width'),
            Ext.create('Shopware.apps.ProductStream.view.condition_list.condition.Length'),
            Ext.create('Shopware.apps.ProductStream.view.condition_list.condition.Weight')
        ];
    },

    addCondition: function(conditionHandler) {
        var me = this;

        var container = me.createConditionContainer(conditionHandler);
        conditionHandler.create(function(item) {
            var singleton = conditionHandler.isSingleton();
            var name = item.getName();


            if (singleton && me.conditions.indexOf(name) > -1) {
                Shopware.Notification.createGrowlMessage(
                    '{s name=singleton_filter_title}Unique filter{/s}',
                    '{s name=singleton_filter_description}Each filter type can only be added once{/s}'
                );
                return;
            }

            me.putItemInContainer(item, container);

        }, container, me.conditions);
    },

    createConditionContainer: function(conditionHandler) {
        var me = this;

        return Ext.create('Ext.panel.Panel', {
            title: conditionHandler.getLabel(),
            items: [],
            collapsible: true,
            closable: true,
            bodyPadding: 5,
            margin: '0 0 5',
            fixToggleTool: function() {
                this.addTool(Ext.widget({
                    xtype: 'tool',
                    type: (this.collapsed && !this.isPlaceHolderCollapse()) ? ('expand-' + this.getOppositeDirection(this.collapseDirection)) : ('collapse-' + this.collapseDirection),
                    handler: this.toggleCollapse,
                    scope: this
                }));
            },
            listeners: {
                close: function() {
                    var index = me.conditions.indexOf(this.name);
                    delete me.conditions[index];
                }
            },
            layout: { type: 'vbox', align: 'stretch' }
        });
    },

    createToolbar: function() {
        var me = this;

        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            items: me.createToolbarItems(),
            style: 'border: 1px solid #9aacb8;',
            ui: 'shopware-ui'
        });
        return me.toolbar;
    },

    createToolbarItems: function() {
        var me = this,
            items = [];

        items.push(me.createAddButton());
        items.push('->');
        items.push(me.createPreviewButton());
        return items;
    },

    createAddButton: function() {
        var me = this;

        me.addButton =Ext.create('Ext.button.Split', {
            text: '{s name=add_condition}Add condition{/s}',
            iconCls: 'sprite-plus-circle-frame',
            menu: me.createMenu()
        });
        return me.addButton;
    },

    createPreviewButton: function() {
        var me = this;

        me.previewButton = Ext.create('Ext.button.Button', {
            text: '{s name=refresh_preview}Refresh preview{/s}',
            iconCls: 'sprite-arrow-circle-225-left',
            handler: function() {
                me.loadPreview();
            }
        });
        return me.previewButton;
    },

    createMenu: function() {
        var me = this, items = [];

        Ext.each(me.conditionHandlers, function(handler) {
            items.push({
                text: handler.getLabel(),
                conditionHandler: handler,
                handler: function() {
                    me.addCondition(this.conditionHandler);
                }
            });
        });

        return new Ext.menu.Menu({ items: items });
    }
});
//{/block}
