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
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

// {namespace name=backend/customer/view/main}
// {block name="backend/customer/view/main/customer_list_filter"}

Ext.define('Shopware.apps.Customer.view.main.CustomerListFilter', {
    extend: 'Shopware.listing.FilterPanel',
    alias:  'widget.customer-listing-filter-panel',
    cls: 'customer-listing-filter-panel detail-view',
    width: 270,
    filterFieldStyle: '',
    collapsible: true,
    collapsed: true,

    configure: function() {
        var factory = Ext.create('Shopware.attribute.SelectionFactory');
        var customerGroupStore = factory.createEntitySearchStore("Shopware\\Models\\Customer\\Group");
        var shopStore = factory.createEntitySearchStore("Shopware\\Models\\Shop\\Shop");
        var salutationStore = Ext.create('Shopware.apps.Base.store.Salutation');
        var countryStore = factory.createEntitySearchStore("Shopware\\Models\\Country\\Country");
        countryStore.remoteSort = true;

        countryStore.sort([{
            property: 'active',
            direction: 'DESC'
        }, {
            property: 'name',
            direction: 'ASC'
        }]);

        var modeStore = Ext.create('Ext.data.Store', {
            fields: ['key', 'label'],
            data: [
                { key: 1, label: '{s name="accountModeGuest"}{/s}' },
                { key: 0, label: '{s name="accountModeNormal"}{/s}' }
            ]
        });

        return {
            controller: 'CustomerQuickView',
            model: 'Shopware.apps.Customer.model.QuickView',
            fields: {
                number: {
                    fieldLabel: '{s name="column/number"}{/s}',
                    expression: 'LIKE'
                },
                email: {
                    fieldLabel: '{s name="email"}{/s}',
                    expression: 'LIKE'
                },
                salutation: {
                    fieldLabel: '{s name="salutation"}{/s}',
                    xtype: 'combobox',
                    displayField: 'label',
                    valueField: 'id',
                    store: salutationStore
                },
                company: {
                    fieldLabel: '{s name="company"}{/s}',
                    expression: 'LIKE'
                },
                title: {
                    fieldLabel: '{s name="title"}{/s}',
                    expression: 'LIKE'
                },
                firstname: {
                    fieldLabel: '{s name="column/first_name"}{/s}',
                    expression: 'LIKE'
                },
                lastname: {
                    fieldLabel: '{s name="column/last_name"}{/s}',
                    expression: 'LIKE'
                },
                zipcode: {
                    fieldLabel: '{s name="zip_code"}{/s}',
                    expression: 'LIKE'
                },
                city: {
                    fieldLabel: '{s name="city"}{/s}',
                    expression: 'LIKE'
                },
                countryId: {
                    xtype: 'pagingcombobox',
                    displayField: 'name',
                    valueField: 'id',
                    store: countryStore,
                    fieldLabel: '{s name="column/country"}{/s}'
                },
                active: {
                    fieldLabel: '{s name="active"}{/s}'
                },
                newsletter: {
                    fieldLabel: '{s name="newsletter"}{/s}'
                },
                accountMode: {
                    xtype: 'combobox',
                    displayField: 'label',
                    valueField: 'key',
                    store: modeStore,
                    fieldLabel: '{s name="column/accountMode"}{/s}',
                },
                customerGroup: {
                    xtype: 'combobox',
                    displayField: 'name',
                    valueField: 'id',
                    store: customerGroupStore,
                    fieldLabel: '{s name="column/customer_group"}{/s}'
                },
                shop: {
                    fieldLabel: '{s name="shop"}{/s}',
                    xtype: 'combobox',
                    displayField: 'name',
                    valueField: 'id',
                    store: shopStore
                },
                firstLogin: {
                    fieldLabel: '{s name="first_login"}{/s}',
                    expression: '>='
                },
                lastLogin: {
                    fieldLabel: '{s name="lastLogin"}{/s}'
                },
                lockedUntil: {
                    fieldLabel: '{s name="lockedUntil"}{/s}',
                    expression: '<='
                },
                birthday: {
                    fieldLabel: '{s name="birthday"}{/s}'
                }
            }
        };
    }
});
// {/block}
