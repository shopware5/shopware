/* global Ext */
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

// {namespace name="backend/customer_stream/translation"}

Ext.define('Shopware.apps.Customer.view.customer_stream.conditions.HasOrderCountCondition', {

    getLabel: function() {
        return '{s name="order_count_condition"}{/s}';
    },

    supports: function(conditionClass) {
        return (conditionClass == 'Shopware\\Bundle\\CustomerSearchBundle\\Condition\\HasOrderCountCondition');
    },

    create: function(callback, loadPreviewFn) {
        callback(this._create(loadPreviewFn));
    },

    load: function(conditionClass, items, callback, loadPreviewFn) {
        callback(this._create(loadPreviewFn));
    },

    _create: function(loadPreviewFn) {
        return {
            title: '{s name="order_count_condition_input"}{/s}',
            conditionClass: 'Shopware\\Bundle\\CustomerSearchBundle\\Condition\\HasOrderCountCondition',
            items: [{
                xtype: 'numberfield',
                minValue: 1,
                value: 1,
                allowBlank: false,
                name: 'minimumOrderCount'
            }]
        };
    }
});
