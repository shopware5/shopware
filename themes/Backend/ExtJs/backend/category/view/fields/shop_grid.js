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
 * @package    Category
 * @subpackage Main
 * @version    $Id$
 * @author shopware AG
 */

/* {namespace name=backend/category/main} */

//{block name="backend/category/view/fields/shop_grid"}
Ext.define('Shopware.apps.Category.view.fields.ShopGrid', {
    /**
     * Parent Element Shopware.form.field.ShopGrid
     * @string
     */
    extend: 'Shopware.form.field.ShopGrid',

    /**
     * @return { array }
     */
    createColumns: function() {
        var me = this,
            activeColumn = { dataIndex: 'active', width: 30 };

        me.applyBooleanColumnConfig(activeColumn);

        return [
            me.createSortingColumn(),
            activeColumn,
            { dataIndex: 'name', flex: 1 },
            { dataIndex: 'mainShopName', flex: 1, renderer: me.shopRenderer },
            { dataIndex: 'baseUrl', width: 90 },
            me.createActionColumn()
        ];
    },

    /**
     * @param { string } value
     * @return { string }
     */
    shopRenderer: function(value) {
        if (value && value.length) {
            return '{s name="shop/grid/shop_label"}Shop{/s}: ' + value;
        }

        return '';
    }
});
//{/block}
