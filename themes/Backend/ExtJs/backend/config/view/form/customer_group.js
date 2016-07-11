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

//{block name="backend/config/view/form/customer_group"}
Ext.define('Shopware.apps.Config.view.form.CustomerGroup', {
    extend: 'Shopware.apps.Config.view.base.Form',
    alias: 'widget.config-form-customergroup',

    getItems: function() {
        var me = this;
        return [{
            xtype: 'config-base-table',
            store: 'form.CustomerGroup',
            columns: me.getColumns(),
            plugins: [{
                ptype: 'grid-attributes',
                table: 's_core_customergroups_attributes'
            }]
        },{
            xtype: 'config-customergroup-detail'
        }];
    },

    getColumns: function() {
        var me = this;
        return [{
            xtype: 'gridcolumn',
            dataIndex: 'name',
            text: '{s name=customer_group/table/name_text}Name{/s}',
            flex: 1
        }, {
            xtype: 'gridcolumn',
            dataIndex: 'key',
            text: '{s name=customer_group/table/key_text}Key{/s}',
            flex: 1
        }, me.getActionColumn()];
    }
});
//{/block}
//taxinput
