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
//{block name="backend/product_stream/view/condition_list/field/range"}
Ext.define('Shopware.apps.ProductStream.view.condition_list.field.Range', {

    extend: 'Ext.form.FieldContainer',
    layout: { type: 'hbox', align: 'stretch' },
    mixins: [ 'Ext.form.field.Base' ],
    height: 30,
    value: undefined,

    minField: 'minPrice',
    maxField: 'maxPrice',
    decimalPrecision: 2,

    initComponent: function() {
        var me = this;
        me.items = me.createItems();
        me.callParent(arguments);
    },

    createItems: function() {
        var me = this;
        return [
            me.createFromField(),
            me.createToField()
        ];
    },

    createFromField: function() {
        var me = this;

        me.fromField = Ext.create('Ext.form.field.Number', {
            fieldLabel: '{s name=from}from{/s}',
            minValue: 0,
            labelWidth: 30,
            decimalPrecision: me.decimalPrecision,
            flex: 1,
            listeners: {
                change: function() {
                    me.toField.setMinValue(me.fromField.getValue());
                }
            }
        });
        return me.fromField;
    },

    createToField: function() {
        var me = this;

        me.toField = Ext.create('Ext.form.field.Number', {
            labelWidth: 30,
            fieldLabel: '{s name=to}to{/s}',
            minValue: 0,
            padding: '0 0 0 10',
            decimalPrecision: me.decimalPrecision,
            flex: 1,
            listeners: {
                change: function() {
                    me.fromField.setMaxValue(me.toField.getValue());
                }
            }
        });
        return me.toField;
    },

    getValue: function() {
        return this.value;
    },

    setValue: function(value) {
        var me = this;

        me.value = value;

        if (!Ext.isObject(value)) {
            me.fromField.setValue(null);
            me.toField.setValue(null);
            return;
        }


        if (value.hasOwnProperty(me.minField)) {
            me.fromField.setValue(value[me.minField]);
        }
        if (value.hasOwnProperty(me.maxField)) {
            me.toField.setValue(value[me.maxField]);
        }
    },

    getSubmitData: function() {
        var value = {};

        value[this.name] = { };
        value[this.name][this.minField] = this.fromField.getValue();
        value[this.name][this.maxField] = this.toField.getValue();

        return value;
    },

    validate: function() {
        var valid = (this.fromField.getValue() !== null || this.toField.getValue());

        if (!valid) {
            Shopware.Notification.createGrowlMessage(
                '{s name=validation_title}Validation{/s}',
                this.getErrorMessage()
            );
        }

        return valid;
    },

    getErrorMessage: function() {
        return 'Range requires at least one value';
    }
});
//{/block}
