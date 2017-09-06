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
 * @subpackage CustumerStream
 * @version    $Id$
 * @author shopware AG
 */

// {namespace name=backend/customer/view/main}

// {block name="backend/customer/view/customer_stream/conditions/field/attribute_value"}

Ext.define('Shopware.apps.Customer.view.customer_stream.conditions.field.AttributeValue', {
    extend: 'Ext.form.FieldContainer',
    layout: { type: 'vbox', align: 'stretch' },
    mixins: {
        formField: 'Ext.form.field.Base'
    },

    initComponent: function() {
        var me = this;

        me.items = me.createItems();

        me.operator = me.operatorField.getValue();

        me.operatorField.on('change', function (field, value) {
            me.operator = value;

            if (value === 'BETWEEN') {
                me.betweenContainer.show();
                me.valueField.hide();
                me.fromField.setDisabled(false);
                me.toField.setDisabled(false);
                me.valueField.setDisabled(true);
            } else {
                me.betweenContainer.hide();
                me.valueField.show();
                me.fromField.setDisabled(true);
                me.toField.setDisabled(true);
                me.valueField.setDisabled(false);
            }
        });

        me.callParent(arguments);
    },

    createItems: function () {
        var me = this;

        return [
            me.createValueField(),
            me.createBetweenContainer()
        ];
    },

    getValue: function() {
        var value = this.valueField.getValue();

        if (this.operator === 'BETWEEN') {
            return {
                min: this.fromField.getValue(),
                max: this.toField.getValue()
            };
        } else if (this.operator === 'IN') {
            return value.split(',');
        }

        return value;
    },

    setValue: function(value) {
        var me = this;

        if (Ext.isObject(value)) {
            me.fromField.setValue(value.min);
            me.toField.setValue(value.max);

            return;
        }
        me.valueField.setValue(value);
    },

    getSubmitData: function() {
        var result = {};
        result[this.name] = this.getValue();

        return result;
    },

    createFromField: function() {
        var me = this;

        me.fromField = Ext.create('Ext.form.field.Number', {
            fieldLabel: '{s name=attribute/from_text}{/s}',
            allowBlank: false,
            width: '100%',
            listeners: {
                change: function() {
                    me.toField.setMinValue(this.getValue() + 1);
                }
            }
        });
        return me.fromField;
    },

    createToField: function() {
        var me = this;

        me.toField = Ext.create('Ext.form.field.Number', {
            fieldLabel: '{s name=attribute/to_text}{/s}',
            allowBlank: false,
            width: '100%',
            listeners: {
                change: function() {
                    me.fromField.setMaxValue(this.getValue() - 1);
                }
            }
        });
        return me.toField;
    },

    createValueField: function () {
        var me = this;

        me.valueField = Ext.create('Ext.form.field.Text', {
            fieldLabel: '{s name=attribute/value}{/s}',
            allowBlank: false,
            name: 'value'
        });
        return me.valueField;
    },

    createBetweenContainer: function () {
        var me = this;

        me.betweenContainer = Ext.create('Ext.container.Container', {
            layout: { type: 'vbox' },
            hidden: true,
            items: [me.createFromField(), me.createToField()]
        });
        return me.betweenContainer;
    }
});
// {/block}
