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

/**
 * todo@all: Documentation
 */

//{namespace name=backend/config/view/customer_group}

//{block name="backend/config/view/customer_group/discount"}
Ext.define('Shopware.apps.Config.view.customerGroup.Discount', {
    extend: 'Shopware.apps.Config.view.base.Property',
    alias: 'widget.config-customergroup-discount',

    title: '{s name=discount/title}Basket discount{/s}',
    name: 'discounts',

    sortableColumns: false,

    snippets:{
        discount:{
            basketValue: '{s name=discount/basket_value}Basket value{/s}',
            basketDiscount: '{s name=discount/basket_discount}Basket discount{/s}'
        }
    },

    getColumns: function() {
        var me = this;

        return [{
            header: me.snippets.discount.basketValue,
            dataIndex: 'value',
            align: 'right',
            flex: 1,
            xtype: 'numbercolumn',
            editor: {
                xtype: 'config-element-number',
                minValue: 0,
                decimalPrecision: 2
            }
        }, {
            xtype: 'numbercolumn',
            header: me.snippets.discount.basketDiscount,
            dataIndex: 'discount',
            align: 'right',
            flex: 1,
            format: '0,000.00 %',
            editor: {
                xtype: 'config-element-number',
                minValue: 0,
                maxValue: 100,
                decimalPrecision: 2
            }
        }, me.getActionColumn()];
    }
});
//{/block}
