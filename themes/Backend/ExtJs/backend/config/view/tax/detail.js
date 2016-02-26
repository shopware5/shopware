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

//{block name="backend/config/view/tax/detail"}
Ext.define('Shopware.apps.Config.view.tax.Detail', {
    extend: 'Shopware.apps.Config.view.base.Detail',
    alias: 'widget.config-tax-detail',

    width: 450,
    store: 'detail.Tax',

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: me.getItems()
        });

        me.callParent(arguments);
    },

    getItems: function() {
        var me = this;
        return [{
            name: 'name',
            fieldLabel: '{s name=tax/detail/name_label}Name{/s}'
        },{
            xtype: 'config-element-number',
            name: 'tax',
            decimalPrecision: 2,
            fieldLabel: '{s name=tax/detail/tax_label}Default tax{/s}'
        }, {
            xtype: 'config-tax-rule'
        }];
    }
});
//{/block}
