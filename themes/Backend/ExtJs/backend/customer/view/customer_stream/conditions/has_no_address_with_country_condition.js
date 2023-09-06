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
// {block name="backend/customer/view/customer_stream/conditions/has_no_address_with_country_condition"}
Ext.define('Shopware.apps.Customer.view.customer_stream.conditions.HasNoAddressWithCountryCondition', {

    mixins: {
        factory: 'Shopware.attribute.SelectionFactory'
    },

    getLabel: function() {
        return '{s name="has_no_address_with_country_condition"}{/s}';
    },

    supports: function(conditionClass) {
        return (conditionClass == 'Shopware\\Bundle\\CustomerSearchBundle\\Condition\\HasNoAddressWithCountryCondition');
    },

    create: function(callback) {
        callback(this._create());
    },

    load: function(conditionClass, items, callback) {
        callback(this._create());
    },

    _create: function() {
        var factory = Ext.create('Shopware.attribute.SelectionFactory');
        var store = factory.createEntitySearchStore('Shopware\\Models\\Country\\Country');
        store.remoteSort = true;
        store.sort([
            {
                property: 'active',
                direction: 'DESC'
            },
            {
                property: 'name',
                direction: 'ASC'
            }
        ]);

        return {
            title: '{s name="has_no_address_with_country_condition_selection"}{/s}',
            conditionClass: 'Shopware\\Bundle\\CustomerSearchBundle\\Condition\\HasNoAddressWithCountryCondition',
            items: [{
                xtype: 'shopware-form-field-grid',
                name: 'countryIds',
                flex: 1,
                allowSorting: false,
                useSeparator: false,
                allowBlank: false,
                store: factory.createEntitySearchStore('Shopware\\Models\\Country\\Country'),
                searchStore: store,
            }]
        };
    }
});
// {/block}
