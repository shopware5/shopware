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
//{block name="backend/product_stream/view/condition_list/field/release_date"}
Ext.define('Shopware.apps.ProductStream.view.condition_list.field.ReleaseDate', {

    extend: 'Ext.form.FieldContainer',
    layout: { type: 'vbox', align: 'stretch' },
    mixins: [ 'Ext.form.field.Base' ],
    height: 70,
    value: undefined,

    initComponent: function() {
        var me = this;
        me.items = me.createItems();
        me.callParent(arguments);
    },

    createItems: function() {
        var me = this;
        return [
            me.createDirectionField(),
            me.createDayField()
        ];
    },

    createDirectionField: function() {
        var me = this;

        me.directionStore = Ext.create('Ext.data.Store', {
            fields: ['value', 'label'],
            data: [
                { value: 'past', label: '{s name=release_date/past}Past{/s}' },
                { value: 'future', label: '{s name=release_date/future}Future{/s}' }
            ]
        });

        me.directionField = Ext.create('Ext.form.field.ComboBox', {
            allowBlank: false,
            fieldLabel: '{s name=release_date/input_text}Release date in the{/s}',
            value: 'past',
            displayField: 'label',
            valueField: 'value',
            store: me.directionStore,
            labelWidth: 160
        });

        return me.directionField;
    },

    createDayField: function() {
        var me = this;

        me.dayField = Ext.create('Ext.form.field.Number', {
            labelWidth: 30,
            fieldLabel: '{s name=days}days{/s}',
            allowBlank: false,
            minValue: 1,
            value: 1,
        });
        return me.dayField;
    },

    getValue: function() {
        return this.value;
    },

    setValue: function(value) {
        var me = this;

        me.value = value;

        if (!Ext.isObject(value)) {
            me.directionField.setValue('past');
            me.dayField.setValue(1);
            return;
        }

        if (value.hasOwnProperty('direction')) {
            me.directionField.setValue(value.direction);
        }
        if (value.hasOwnProperty('days')) {
            me.dayField.setValue(value.days);
        }
    },

    getSubmitData: function() {
        var value = {};

        value[this.name] = {
            direction: this.directionField.getValue(),
            days: this.dayField.getValue()
        };
        return value;
    },

    validate: function() {

        return true;
    }
});
//{/block}
