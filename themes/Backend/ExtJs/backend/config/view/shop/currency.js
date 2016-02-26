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

//{namespace name=backend/config/view/form}

//{block name="backend/config/view/shop/currency"}
Ext.define('Shopware.apps.Config.view.shop.Currency', {
    extend: 'Shopware.apps.Config.view.base.Property',
    alias: 'widget.config-shop-currency',

    title: '{s name=shop/currency/title}Select currencies{/s}',
    name: 'currencies',

    getColumns: function() {
        var me = this;

        return [{
            header: '{s name=shop/currency/iso_header}Currency{/s}',
            dataIndex: 'currency',
            flex: 1
        }, {
            header: '{s name=shop/currency/name_header}Name{/s}',
            dataIndex: 'name',
            flex: 1
        }, me.getActionColumn()];
    },

    getTopBar: function () {
        var me = this;
        return [{
            xtype: 'config-element-select',
            flex: 1,
            name: 'property',
            store: 'base.Currency'
        }];
    }
});
//{/block}
