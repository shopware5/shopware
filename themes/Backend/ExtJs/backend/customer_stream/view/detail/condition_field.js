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
Ext.define('Shopware.apps.CustomerStream.view.detail.ConditionField', {
    extend: 'Ext.form.FieldContainer',
    layout: { type: 'vbox', align: 'stretch' },
    mixins: {
        formField: 'Ext.form.field.Base'
    },

    getValue: function() {
        var me = this,
            values = { };

        Ext.each(me.items.items, function(item) {
            if (Ext.isFunction(item.getValue)) {
                values[item.name] = item.getValue();
            }
        });
        return values;
    },

    setValue: function(values) {
        var me = this;

        Ext.each(me.items.items, function(field) {
            if (values.hasOwnProperty(field.name)) {
                field.setValue(values[field.name]);
            }
        });
    },

    getSubmitData: function() {
        var value = { };
        value[this.name] = this.getValue();
        return value;
    }
});