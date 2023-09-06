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
 */

/**
 * todo@all: Documentation
 */

//{namespace name="backend/config/view/form"}

//{block name="backend/config/view/form/tax"}
Ext.define('Shopware.apps.Config.view.form.Tax', {
    extend: 'Shopware.apps.Config.view.base.Form',
    alias: 'widget.config-form-tax',

    getItems: function() {
        var me = this;
        return [{
            xtype: 'config-base-table',
            store: 'form.Tax',
            columns: me.getColumns()
        }, {
            xtype: 'config-tax-detail'
        }];
    },

    getColumns: function() {
        var me = this;
        return [{
            xtype: 'gridcolumn',
            dataIndex: 'name',
            text: '{s name="tax/table/name_text"}Name{/s}',
            flex: 1
        }, {
            xtype: 'numbercolumn',
            dataIndex: 'tax',
            text: '{s name="tax/table/tax_text"}Default tax{/s}',
            align: 'right',
            flex: 1
        }, me.getActionColumn()];
    }
});
//{/block}
