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

//{block name="backend/config/view/form/locale"}
Ext.define('Shopware.apps.Config.view.form.Locale', {
    extend: 'Shopware.apps.Config.view.base.Form',
    alias: 'widget.config-form-locale',

    getItems: function() {
        var me = this;
        return [{
            xtype: 'config-base-table',
            region: 'center',
            store: 'form.Locale',
            searchField: 'language',
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
            dataIndex: 'language',
            text: '{s name=locale/table/name_text}Name{/s}',
            flex: 1,
            renderer: function (v,p,record){
                return record.data.language + ' (' + record.data.territory + ')';
            }
        }, {
            xtype: 'gridcolumn',
            dataIndex: 'locale',
            text: '{s name=locale/table/iso_text}ISO{/s}',
            flex: 1
        }, me.getActionColumn()];
    },

    getFormItems: function() {
        var me = this;
        return [{
            name: 'language',
            fieldLabel: '{s name=locale/detail/language_text}Language{/s}',
            allowBlank: false
        },{
            name: 'territory',
            fieldLabel: '{s name=locale/detail/territory_text}Territory{/s}',
            allowBlank: false
        },{
            name: 'locale',
            fieldLabel: '{s name=locale/detail/iso_text}ISO{/s}',
            minLength: 4,
            maxLength: 6
        }];
    }
});
//{/block}
