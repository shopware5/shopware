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
// {block name="backend/customer/view/main/customer_list"}

Ext.define('Shopware.apps.Customer.view.main.CustomerList', {
    extend: 'Shopware.grid.Panel',
    alias: 'widget.customer-list',

    configure: function() {
        return {
            displayProgressOnSingleDelete: false,

            /* {if {acl_is_allowed privilege=delete}} */
                deleteButton: true,
                deleteColumn: true,
            /* {else} */
                deleteButton: false,
                deleteColumn: false,
            /* {/if} */

            /* {if {acl_is_allowed privilege=detail}} */
                editColumn: true,
            /* {else} */
                editColumn: false,
            /* {/if} */

            /* {if {acl_is_allowed privilege=update}} */
                addButton: true,
            /* {else} */
                addButton: false,
            /* {/if} */

            columns: {
                active: { header: '{s name="active"}{/s}', width: 50 },
                id: { header: '{s name="id"}{/s}' },
                customerGroup: { header: '{s name="column/customer_group"}{/s}' },
                shop: { header: '{s name="shop"}{/s}' },
                number: { header: '{s name="column/number"}{/s}' },
                email: { header: '{s name="email"}{/s}', renderer: this.mailRenderer, flex: 2 },
                salutation: { header: '{s name="salutation"}{/s}', renderer: this.salutationRenderer },
                title: { header: '{s name="title"}{/s}', width: 70 },
                company: { header: '{s name="company"}{/s}' },
                firstname: { header: '{s name="column/first_name"}{/s}' },
                lastname: { header: '{s name="column/last_name"}{/s}' },
                zipcode: { header: '{s name="zip_code"}{/s}' },
                city: { header: '{s name="city"}{/s}' },
                countryName: { header: '{s name="column/country"}{/s}' },
                firstLogin: { header: '{s name="first_login"}{/s}', renderer: this.dateRenderer },
                lastLogin: { header: '{s name="lastLogin"}{/s}', renderer: this.dateRenderer },
                accountMode: { header: '{s name="column/accountMode"}{/s}', renderer: this.accountModeRenderer },
                lockedUntil: { header: '{s name="lockedUntil"}{/s}', renderer: this.dateRenderer },
                birthday: { header: '{s name="birthday"}{/s}' }
            }
        };
    },

    initComponent: function() {
        var me = this;

        me.salutationStore = Ext.create('Shopware.apps.Base.store.Salutation').load({
            callback: function () {
                me.getView().refresh();
                me.setLoading(false);
            }
        });

        me.callParent(arguments);
    },

    salutationRenderer: function(value) {
        var me = this;
        if (me.salutationStore.count() === 0) {
            me.setLoading(true);
            return '';
        }
        return this.salutationStore.getByKey(value);
    },

    accountModeRenderer: function (value) {
        if (value) {
            return '{s name="accountModeGuest"}{/s}';
        }

        return '{s name="accountModeNormal"}{/s}';
    },

    mailRenderer: function(value) {
        return Ext.String.format('<a href="mailto:[0]" data-qtip="[0]">[0]</a>', value);
    },

    _onEdit: function(view, rowIndex, colIndex, item, opts, record) {
        this.fireEvent('edit', record);
    },

    onAddItem: function(view, rowIndex, colIndex, item, opts, record) {
        this.fireEvent('create', record);
    },

    dateRenderer: function (value) {
        if (value && value instanceof Date) {
            if (value.getTime() > 0) {
                return Ext.Date.format(value, Ext.Date.defaultFormat)
            }
        }

        return '';
    }
});
// {/block}
