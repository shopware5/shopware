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
 * @category    Shopware
 * @package     Base
 * @subpackage  Attribute
 * @version     $Id$
 * @author      shopware AG
 */

//{namespace name="backend/attributes/fields"}

Ext.define('Shopware.form.field.OrderDetailGrid', {
    extend: 'Shopware.form.field.Grid',
    alias: 'widget.shopware-form-field-order-detail-grid',
    mixins: ['Shopware.model.Helper'],

    createColumns: function() {
        var me = this;

        return [
            me.createSortingColumn(),
            { dataIndex: 'quantity' },
            { dataIndex: 'articleNumber' },
            { dataIndex: 'price', renderer: me.priceRenderer },
            { dataIndex: 'articleName' },
            me.createActionColumn()
        ];
    },

    priceRenderer: function(value) {
        if ( value === Ext.undefined ) {
            return value;
        }
        return Ext.util.Format.currency(value);
    },

    createSearchField: function() {
        return Ext.create('Shopware.form.field.OrderDetailSingleSelection', this.getComboConfig());
    }
});
