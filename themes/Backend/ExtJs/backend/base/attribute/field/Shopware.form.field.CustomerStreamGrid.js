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
 * @category    Shopware
 * @package     Base
 * @subpackage  Attribute
 * @version     $Id$
 * @author      shopware AG
 */

// {namespace name=backend/customer/view/main}

Ext.define('Shopware.form.field.CustomerStreamGrid', {
    extend: 'Shopware.form.field.Grid',
    alias: 'widget.shopware-form-field-customer-stream-grid',
    mixins: ['Shopware.model.Helper'],

    createColumns: function() {
        var me = this;

        return [
            me.createSortingColumn(),
            { dataIndex: 'name', flex: 1, renderer: me.nameRenderer },
            me.createActionColumn()
        ];
    },

    nameRenderer: function (value, meta, record) {
        return '<span class="stream-name-column"><b>' + value + '</b> - '+ record.get('customer_count') +' {s name="customer_count_suffix"}{/s}</span>';
    },

    createSearchField: function() {
        return Ext.create('Shopware.form.field.CustomerStreamSingleSelection', this.getComboConfig());
    },

    createActionColumnItems: function() {
        var me = this,
            items = me.callParent(arguments);

        items.push(me.createModuleIcon());
        return items;
    },

    createModuleIcon: function() {
        return {
            action: 'open-customer',
            iconCls: 'customers--customer-list',
            handler: function () {
                Shopware.app.Application.addSubApplication({
                    name: 'Shopware.apps.Customer'
                });
            }
        };
    }
});