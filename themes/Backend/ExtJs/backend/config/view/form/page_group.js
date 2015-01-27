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

//{block name="backend/config/view/form/page_group"}
Ext.define('Shopware.apps.Config.view.form.PageGroup', {
    extend: 'Shopware.apps.Config.view.base.Form',
    alias: [
        'widget.config-form-sitegroup',
        'widget.config-form-pagegroup'
    ],

    getItems: function() {
        var me = this;
        return [{
            xtype: 'config-base-table',
            store: 'form.PageGroup',
            columns: me.getColumns()
        },{
            xtype: 'config-base-detail',
            store: 'detail.PageGroup',
            items: me.getFormItems()
        }];
    },

    getColumns: function() {
        var me = this;
        return [{
            xtype: 'gridcolumn',
            dataIndex: 'name',
            text: '{s name=page_group/table/name_text}Name{/s}',
            flex: 1
        }, {
            xtype: 'gridcolumn',
            dataIndex: 'mapping',
            text: '{s name=page_group/table/mapping_text}Mapping{/s}',
            flex: 1
        }, {
            xtype: 'booleancolumn',
            name: 'active',
            fieldLabel: '{s name=page_group/table/active_text}Active{/s}'
        }, me.getActionColumn()];
    },

    getFormItems: function() {
        var me = this;
        return [{
            name: 'name',
            fieldLabel: '{s name=page_group/detail/name_label}Name{/s}',
            allowBlank: false
        },{
            name: 'key',
            fieldLabel: '{s name=page_group/detail/key_label}Template key{/s}',
            allowBlank: false
        },{
            xtype: 'config-element-select',
            name: 'mappingId',
            fieldLabel: '{s name=page_group/detail/mapping_label}Mapping{/s}',
            store: 'base.PageGroup'
        },{
            xtype: 'config-element-boolean',
            name: 'active',
            fieldLabel: '{s name=page_group/detail/active_label}Active{/s}'
        }];
    }
});
//{/block}
