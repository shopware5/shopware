/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 *
 * @category   Shopware
 * @package    Customer
 * @subpackage CustomerStream
 * @version    $Id$
 * @author shopware AG
 */

// {namespace name="backend/customer/view/main"}
// {block name="backend/customer/view/customer_stream/conditions/ordered_at_weekday_condition"}
Ext.define('Shopware.apps.Customer.view.customer_stream.conditions.OrderedAtWeekdayCondition', {

    getLabel: function() {
        return '{s name="ordered_at_weekday_condition"}{/s}';
    },

    supports: function(conditionClass) {
        return (conditionClass == 'Shopware\\Bundle\\CustomerSearchBundle\\Condition\\OrderedAtWeekdayCondition');
    },

    create: function(callback) {
        callback(this._create());
    },

    load: function(conditionClass, items, callback) {
        callback(this._create(items));
    },

    _create: function(filter) {
        var store = Ext.create('Ext.data.Store', {
            fields: ['id', 'label'],
            data: [
                { id: 'monday', label: '{s name="monday"}{/s}' },
                { id: 'tuesday', label: '{s name="tuesday"}{/s}' },
                { id: 'wednesday', label: '{s name="wednesday"}{/s}' },
                { id: 'thursday', label: '{s name="thursday"}{/s}' },
                { id: 'friday', label: '{s name="friday"}{/s}' },
                { id: 'saturday', label: '{s name="saturday"}{/s}' },
                { id: 'sunday', label: '{s name="sunday"}{/s}' }
            ]
        });
        var items = [];
        if (filter) {
            Ext.each(filter.weekdays, function(item) {
                items.push(store.getById(item));
            });
        }
        var second = Ext.create('Ext.data.Store', {
            fields: ['id', 'label'],
            data: items
        });

        return {
            title: '{s name="ordered_at_weekday_condition_selection"}{/s}',
            conditionClass: 'Shopware\\Bundle\\CustomerSearchBundle\\Condition\\OrderedAtWeekdayCondition',
            items: [{
                xtype: 'shopware-form-field-grid',
                name: 'weekdays',
                flex: 1,
                allowSorting: false,
                useSeparator: false,
                allowBlank: false,
                store: second,
                searchStore: store
            }]
        };
    }
});
// {/block}
