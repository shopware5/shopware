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
//{namespace name="backend/product_stream/main"}
//{block name="backend/product_stream/view/condition_list/condition/attribute_date"}
Ext.define('Shopware.apps.ProductStream.view.condition_list.field.AttributeDate', {

    extend: 'Shopware.apps.ProductStream.view.condition_list.field.Attribute',

    createFromField: function () {
        var me = this;

        me.fromField = Ext.create('Ext.form.field.Date', {
            fieldLabel: '{s name="attribute/from_text"}From{/s}',
            flex: 1
        });

        return me.fromField;
    },

    setValue: function (value) {
        var me = this;

        me.value = value;
        if (Ext.isObject(value)) {
            me.attributeField = value.field;
            me.operatorSelection.setValue(value.operator);

            if (value.operator === 'BETWEEN') {
                me.fromField.setValue(new Date(value.value.min));
                me.toField.setValue(new Date(value.value.max));
            } else {
                me.valueField.setValue(value.value);
            }
        }
    },

    createToField: function () {
        var me = this;

        me.toField = Ext.create('Ext.form.field.Date', {
            labelWidth: 50,
            fieldLabel: '{s name="attribute/to_text"}to{/s}',
            padding: '0 0 0 10',
            flex: 1
        });

        return me.toField;
    },
});
