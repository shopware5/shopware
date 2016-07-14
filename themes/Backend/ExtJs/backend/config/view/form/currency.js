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

//{block name="backend/config/view/main/table"}
Ext.define('Shopware.apps.Config.view.form.Currency', {
    extend: 'Shopware.apps.Config.view.base.Form',
    alias: 'widget.config-form-currency',

    getItems: function() {
        var me = this;
        return [{
            xtype: 'config-base-table',
            store: 'form.Currency',
            columns: me.getColumns()
        },{
            xtype: 'config-base-detail',
            items: me.getFormItems()
        }];
    },

    getColumns: function() {
        var me = this;
        return [{
            xtype: 'gridcolumn',
            dataIndex: 'name',
            text: '{s name=currency/table/name_text}Name{/s}',
            flex: 1
        }, {
            xtype: 'gridcolumn',
            dataIndex: 'currency',
            text: '{s name=currency/table/iso_text}ISO{/s}',
            flex: 1
        }, me.getActionColumn()];
    },

    getFormItems: function() {
        var me = this;
        return [{
            name: 'name',
            fieldLabel: '{s name=currency/detail/name_label}Name{/s}',
            allowBlank: false
        },{
            name: 'currency',
            allowBlank: false,
            fieldLabel: '{s name=currency/detail/iso_label}ISO{/s}',
            minLength: 3,
            maxLength: 3
        },{
            name: 'symbol',
            fieldLabel: '{s name=currency/detail/symbol_label}Symbol{/s}',
            allowBlank: false
        },{
            xtype: 'combobox',
            name: 'symbolPosition',
            fieldLabel: '{s name=currency/detail/symbol_position_label}Symbol position{/s}',
            store: [
                [0,  '{s name=currency/detail/symbol_position_default}Default{/s}'],
                [16, '{s name=currency/detail/symbol_position_right}Right{/s}'],
                [32, '{s name=currency/detail/symbol_position_left}Left{/s}']
            ]
        },{
            xtype: 'config-element-boolean',
            name: 'default',
            readOnly: true,
            fieldLabel: '{s name=currency/detail/default_label}Default{/s}'
        },{
            xtype: 'config-element-number',
            name: 'factor',
            decimalPrecision: 10,
            fieldLabel: '{s name=currency/detail/factor_label}Factor{/s}',
            allowBlank: false
        },{
            xtype: 'config-element-number',
            name: 'position',
            decimalPrecision: 0,
            fieldLabel: '{s name=currency/detail/position_label}Position{/s}'
        }];
    }
});
//{/block}
