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

//{block name="backend/config/view/form/country_area"}
Ext.define('Shopware.apps.Config.view.form.CountryArea', {
    extend: 'Shopware.apps.Config.view.base.Form',
    alias: 'widget.config-form-countryarea',

    getItems: function() {
        var me = this;
        return [{
            xtype: 'config-base-table',
            store: 'form.CountryArea',
            columns: me.getColumns()
        },{
            xtype: 'config-base-detail',
//            plugins: [{
//                ptype: 'translation',
//                pluginId: 'translation',
//                translationType: 'config_areas',
//                translationMerge: true
//            }],
            items: me.getFormItems()
        }];
    },

    getColumns: function() {
        var me = this;
        return [{
            dataIndex: 'name',
            text: '{s name=country_area/table/name_text}Name{/s}',
            renderer: function(v) { return v && (v.charAt(0).toUpperCase() + v.substr(1)); },
            flex: 1
        }, {
            xtype: 'booleancolumn',
            dataIndex: 'active',
            text: '{s name=country_area/table/active_text}Active{/s}',
            flex: 1
        }, me.getActionColumn()];
    },

    getFormItems: function() {
        var me = this;
        return [{
            name: 'name',
            fieldLabel: '{s name=country_area/detail/name_label}Name{/s}',
            translatable: true,
            allowBlank: false
        },{
            xtype: 'config-element-boolean',
            name: 'active',
            fieldLabel: '{s name=country_area/detail/active_label}Active{/s}'
        }];
    }
});
//{/block}
