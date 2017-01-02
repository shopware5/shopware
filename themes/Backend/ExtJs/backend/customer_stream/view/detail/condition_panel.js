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

//{namespace name="backend/customer_stream/translation"}
Ext.define('Shopware.apps.CustomerStream.view.detail.ConditionPanel', {
    extend: 'Ext.form.Panel',
    alias: 'widget.customer-stream-condition-panel',
    name: 'conditions',
    autoScroll: true,

    mixins: {
        formField: 'Ext.form.field.Base',
        factory: 'Shopware.attribute.SelectionFactory'
    },

    layout: {
        type: 'vbox',
        align: 'stretch'
    },
    bodyPadding: 10,

    initComponent: function() {
        var me = this;
        var handlers = me.registerHandlers();

        me.handlers = handlers.sort(function compare(a, b) {
            return a.getLabel().localeCompare(b.getLabel());
        });

        me.items = [];
        me.dockedItems = me.createDockedItems();

        me.callParent(arguments);
    },

    registerHandlers: function() {
        return [
            Ext.create('Shopware.apps.CustomerStream.view.conditions.HasAddressWithCountryCondition'),
            Ext.create('Shopware.apps.CustomerStream.view.conditions.HasCanceledOrdersCondition'),
            Ext.create('Shopware.apps.CustomerStream.view.conditions.IsCustomerSinceCondition'),
            Ext.create('Shopware.apps.CustomerStream.view.conditions.IsInCustomerGroupCondition'),
            Ext.create('Shopware.apps.CustomerStream.view.conditions.HasOrderCountCondition'),
            Ext.create('Shopware.apps.CustomerStream.view.conditions.OrderedAtWeekdayCondition'),
            Ext.create('Shopware.apps.CustomerStream.view.conditions.OrderedInLastDaysCondition'),
            Ext.create('Shopware.apps.CustomerStream.view.conditions.OrderedInShopCondition'),
            Ext.create('Shopware.apps.CustomerStream.view.conditions.RegisteredInShopCondition'),
            Ext.create('Shopware.apps.CustomerStream.view.conditions.OrderedOnDeviceCondition'),
            Ext.create('Shopware.apps.CustomerStream.view.conditions.OrderedProductCondition'),
            Ext.create('Shopware.apps.CustomerStream.view.conditions.OrderedProductOfCategoryCondition'),
            Ext.create('Shopware.apps.CustomerStream.view.conditions.OrderedProductOfManufacturerCondition'),
            Ext.create('Shopware.apps.CustomerStream.view.conditions.OrderedWithDeliveryCondition'),
            Ext.create('Shopware.apps.CustomerStream.view.conditions.OrderedWithPaymentCondition'),
            Ext.create('Shopware.apps.CustomerStream.view.conditions.HasTotalOrderAmountCondition'),
            Ext.create('Shopware.apps.CustomerStream.view.conditions.CustomerAttributeCondition')
        ];
    },

    createDockedItems: function() {
        var me = this;

        return [{
            xtype: 'toolbar',
            dock: 'top',
            style: 'border: 1px solid #9aacb8;',
            ui: 'shopware-ui',
            items: [{
                xtype: 'button',
                text: '{s name=add}{/s}',
                iconCls: 'sprite-plus-circle-frame',
                menu: me.createMenu()
            }, '->', {
                text: '{s name=preview}{/s}',
                iconCls: 'sprite-arrow-circle-225-left',
                handler: Ext.bind(me.loadPreview, me)
            }]
        }];
    },

    loadPreview: function() {
        this.fireEvent('load-preview', this.getSubmitData());
    },

    createMenu: function() {
        var me = this, items = [];

        Ext.each(me.handlers, function(handler) {
            items.push({
                text: handler.getLabel(),
                conditionHandler: handler,
                handler: Ext.bind(me.createCondition, me)
            });
        });
        return new Ext.menu.Menu({ items: items });
    },

    createCondition: function(button) {
        var me = this,
            handler = button.conditionHandler;

        handler.create(function(configuration) {
            me.addCondition(configuration);
        });
    },

    addCondition: function(configuration) {
        var me = this;

        var panel = Ext.create('Shopware.apps.CustomerStream.view.detail.ConditionField', {
            flex: 1,
            name: configuration.conditionClass,
            items: configuration.items
        });

        var container = Ext.create('Ext.panel.Panel', {
            title: configuration.title,
            layout: { type: 'hbox', align: 'stretch' },
            minHeight: 90,
            conditionClass: configuration.conditionClass,
            maxHeight: 180,
            bodyPadding: 10,
            collapsible: true,
            closable: true,
            margin: '10 0 0',
            conditionField: panel,
            items: [panel]
        });

        var exists = false;
        Ext.each(me.items.items, function(item) {
            if (item.conditionClass === configuration.conditionClass) {
                exists = true;
            }
        });

        if (!exists) {
            me.add(container);
        }
    },

    getValue: function() {
    },

    setValue: function(value) {
        var me = this;

        me.removeAll();
        if (!value) {
            return;
        }

        value = Ext.JSON.decode(value);

        for (var conditionClass in value) {
            var items = value[conditionClass];
            var handler = me.getHandler(conditionClass);

            handler.load(conditionClass, items, function(configuration) {
                me.addCondition(configuration);
            });
        }
        me.getForm().setValues(value);
    },

    getSubmitData: function() {
        var me = this,
            value = { },
            itemValues = { };

        Ext.each(me.items.items, function(panel) {
            itemValues = Ext.apply(itemValues, panel.conditionField.getSubmitData());
        });

        value['conditions'] = Ext.JSON.encode(itemValues);

        return value;
    },

    getHandler: function(conditionClass) {
        var me = this,
            handler = null;

        Ext.each(me.handlers, function(item) {
            if (item.supports(conditionClass)) {
                handler = item;
                return false;
            }
        });

        return handler;
    }
});