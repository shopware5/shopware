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

//{block name="backend/config/view/form/price_group"}
Ext.define('Shopware.apps.Config.view.form.PriceGroup', {
    extend: 'Shopware.apps.Config.view.base.Form',
    alias: 'widget.config-form-pricegroup',

    getItems: function() {
        var me = this;
        return [{
            xtype: 'config-base-table',
            store: 'form.PriceGroup',
            columns: me.getColumns()
        },{
            xtype: 'config-base-detail',
            store: 'detail.PriceGroup',
            items: me.getFormItems()
        }];
    },

    getColumns: function() {
        var me = this;
        return [{
            xtype: 'gridcolumn',
            dataIndex: 'name',
            text: '{s name=price_group/table/name_text}Name{/s}',
            flex: 1
        }, me.getActionColumn()];
    },

    getFormItems: function() {
        var me = this;
        return [{
            name: 'name',
            fieldLabel: '{s name=price_group/detail/name_label}Name{/s}',
            allowBlank: false
        }, {
            xtype: 'config-pricegroup-discount'
        }];
    }
});
//{/block}
//taxinput
