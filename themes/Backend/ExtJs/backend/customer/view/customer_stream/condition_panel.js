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
 * @package    Customer
 * @subpackage CustomerStream
 * @version    $Id$
 * @author shopware AG
 */

// {namespace name=backend/customer/view/main}
// {block name="backend/customer/view/customer_stream/condition_panel"}
Ext.define('Shopware.apps.Customer.view.customer_stream.ConditionPanel', {
    extend: 'Ext.form.Panel',
    alias: 'widget.customer-stream-condition-panel',
    name: 'conditions',
    autoScroll: true,
    cls: 'shopware-form customer-stream-condition-panel',
    bodyCls: 'stream-condition-panel-body',

    mixins: {
        formField: 'Ext.form.field.Base',
        factory: 'Shopware.attribute.SelectionFactory'
    },

    layout: {
        type: 'vbox',
        align: 'stretch'
    },

    hasConditions: false,

    bodyPadding: '10 0 0',

    initComponent: function() {
        var me = this,
            handlers = me.registerHandlers();

        me.handlers = handlers.sort(function compare(a, b) {
            return a.getLabel().localeCompare(b.getLabel());
        });

        me.items = [];

        me.on('remove', Ext.bind(me.onRemoveChildComponent, me));

        me.callParent(arguments);
    },

    registerHandlers: function() {
        return [
            Ext.create('Shopware.apps.Customer.view.customer_stream.conditions.AgeCondition'),
            Ext.create('Shopware.apps.Customer.view.customer_stream.conditions.HasAddressWithCountryCondition'),
            Ext.create('Shopware.apps.Customer.view.customer_stream.conditions.HasCanceledOrdersCondition'),
            Ext.create('Shopware.apps.Customer.view.customer_stream.conditions.HasNewsletterRegistrationCondition'),
            Ext.create('Shopware.apps.Customer.view.customer_stream.conditions.IsCustomerSinceCondition'),
            Ext.create('Shopware.apps.Customer.view.customer_stream.conditions.IsInCustomerGroupCondition'),
            Ext.create('Shopware.apps.Customer.view.customer_stream.conditions.HasOrderCountCondition'),
            Ext.create('Shopware.apps.Customer.view.customer_stream.conditions.OrderedAtWeekdayCondition'),
            Ext.create('Shopware.apps.Customer.view.customer_stream.conditions.OrderedInLastDaysCondition'),
            Ext.create('Shopware.apps.Customer.view.customer_stream.conditions.OrderedInShopCondition'),
            Ext.create('Shopware.apps.Customer.view.customer_stream.conditions.RegisteredInShopCondition'),
            Ext.create('Shopware.apps.Customer.view.customer_stream.conditions.OrderedOnDeviceCondition'),
            Ext.create('Shopware.apps.Customer.view.customer_stream.conditions.OrderedProductCondition'),
            Ext.create('Shopware.apps.Customer.view.customer_stream.conditions.OrderedProductOfCategoryCondition'),
            Ext.create('Shopware.apps.Customer.view.customer_stream.conditions.OrderedProductOfManufacturerCondition'),
            Ext.create('Shopware.apps.Customer.view.customer_stream.conditions.OrderedWithDeliveryCondition'),
            Ext.create('Shopware.apps.Customer.view.customer_stream.conditions.OrderedWithPaymentCondition'),
            Ext.create('Shopware.apps.Customer.view.customer_stream.conditions.HasTotalOrderAmountCondition'),
            Ext.create('Shopware.apps.Customer.view.customer_stream.conditions.CustomerAttributeCondition'),
            Ext.create('Shopware.apps.Customer.view.customer_stream.conditions.SalutationCondition'),
            Ext.create('Shopware.apps.Customer.view.customer_stream.conditions.SearchTermCondition'),
            Ext.create('Shopware.apps.Customer.view.customer_stream.conditions.AccountModeCondition')
        ];
    },

    createCondition: function(handler) {
        var me = this;

        handler.create(function(configuration) {
            me.addCondition(configuration);
        }, Ext.bind(me.loadPreview, me));
    },

    createConditionContainer: function(configuration) {
        var panel = Ext.create('Shopware.apps.Customer.view.customer_stream.ConditionField', {
            flex: 1,
            name: configuration.conditionClass,
            items: configuration.items
        });

        return Ext.create('Ext.panel.Panel', {
            layout: { type: 'hbox', align: 'stretch' },
            minHeight: 90,
            cls: 'condition-container',
            maxHeight: 190,
            bodyPadding: 10,
            header: {
                cls: 'condition-container-header'
            },
            closable: true,
            bodyCls: 'condition-container-body',
            margin: '0 10 10',
            title: configuration.title,
            conditionClass: configuration.conditionClass,
            conditionField: panel,
            items: [panel]
        });
    },

    addCondition: function(configuration) {
        var me = this,
            container = me.createConditionContainer(configuration);

        if (me.emptyMessageContainer) {
            me.remove(me.emptyMessageContainer);
        }

        if (!me.conditionExists(me.items.items, configuration)) {
            me.add(container);
            me.hasConditions = true;
            me.fireEvent('condition-panel-change');
        }
    },

    conditionExists: function(items, configuration) {
        var exists = false;

        Ext.each(items, function(item) {
            if (item.conditionClass === configuration.conditionClass) {
                exists = true;
            }
        });

        return exists;
    },

    getValue: function() {
    },

    setValue: function(value) {
        var me = this;

        if (!value) {
            return;
        }

        me.removeAll();
        value = Ext.JSON.decode(value);

        var containers = [];

        for (var conditionClass in value) {
            var items = value[conditionClass],
                handler = me.getHandler(conditionClass);

            handler.load(conditionClass, items, function(configuration) {
                if (!me.conditionExists(containers, configuration)) {
                    me.hasConditions = true;
                    var container = me.createConditionContainer(configuration);
                    containers.push(container);
                }
            });
        }

        me.add(containers);
        me.getForm().setValues(value);
        me.fireEvent('condition-panel-change');
    },

    getSubmitData: function() {
        var me = this,
            value = { },
            itemValues = { };

        Ext.each(me.items.items, function(panel) {
            if (panel.hasCls('customer-stream-empty-message')) {
                return;
            }

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
    },

    createEmptyMessage: function() {
        var me = this;
        me.emptyMessageContainer = Ext.create('Ext.panel.Panel', {
            padding: 15,
            cls: 'customer-stream-empty-message',
            bodyStyle: 'border: 1px solid #a4b5c0; padding: 15px; background: #fff; border-radius: 3px',
            items: [{
                xtype: 'container',
                style: 'color: #475c6a; line-height: 1.6',
                html: '{s name="empty_message"}{/s}'
            }]
        });

        return me.emptyMessageContainer;
    },

    onRemoveChildComponent: function(panel, comp) {
        var me = this;

        if (me.items.length > 0) {
            return;
        }

        if (comp.cls === 'customer-stream-empty-message') {
            return;
        }

        me.hasConditions = false;
        me.fireEvent('condition-panel-change');
    }
});
// {/block}
