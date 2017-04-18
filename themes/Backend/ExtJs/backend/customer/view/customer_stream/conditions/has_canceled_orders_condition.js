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
// {block name="backend/customer/view/customer_stream/conditions/has_canceled_orders_condition"}
Ext.define('Shopware.apps.Customer.view.customer_stream.conditions.HasCanceledOrdersCondition', {

    getLabel: function() {
        return '{s name="has_canceled_orders_condition"}{/s}';
    },

    supports: function(conditionClass) {
        return (conditionClass == 'Shopware\\Bundle\\CustomerSearchBundle\\Condition\\HasCanceledOrdersCondition');
    },

    create: function(callback) {
        callback(this._create());
    },

    load: function(conditionClass, items, callback) {
        callback(this._create());
    },

    _create: function() {
        return {
            title: this.getLabel(),
            conditionClass: 'Shopware\\Bundle\\CustomerSearchBundle\\Condition\\HasCanceledOrdersCondition',
            items: [{
                xtype: 'component',
                padding: 10,
                html: '{s name="has_canceled_orders_condition_text"}{/s}'
            }]
        };
    }
});
// {/block}
