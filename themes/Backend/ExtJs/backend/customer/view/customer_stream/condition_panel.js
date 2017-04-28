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

    bodyPadding: '10 0 0',

    initComponent: function() {
        var me = this;
        var handlers = me.registerHandlers();

        me.handlers = handlers.sort(function compare(a, b) {
            return a.getLabel().localeCompare(b.getLabel());
        });

        me.items = [];

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
            Ext.create('Shopware.apps.Customer.view.customer_stream.conditions.SearchTermCondition')
        ];
    },

    createCondition: function(handler) {
        var me = this;

        handler.create(function(configuration) {
            me.addCondition(configuration);
        }, Ext.bind(me.loadPreview, me));
    },

    createConditionContainer: function(configuration) {
        var me = this;

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
            items: [panel],
            listeners: {
                close: function() {
                    Ext.defer(function() {
                        me.fireEvent('condition-removed', this)
                    }, 100);
                }
            }
        });
    },

    addCondition: function(configuration) {
        var me = this,
            container = me.createConditionContainer(configuration);

        if (!me.conditionExists(me.items.items, configuration)) {
            me.add(container);
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

        me.removeAll();
        if (!value) {
            return;
        }

        value = Ext.JSON.decode(value);

        var containers = [];

        for (var conditionClass in value) {
            var items = value[conditionClass];
            var handler = me.getHandler(conditionClass);

            handler.load(conditionClass, items, function(configuration) {
                if (!me.conditionExists(containers, configuration)) {
                    var container = me.createConditionContainer(configuration);
                    containers.push(container);
                }
            });
        }
        me.add(containers);
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
// {/block}
