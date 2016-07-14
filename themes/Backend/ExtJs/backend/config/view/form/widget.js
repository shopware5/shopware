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

//{block name="backend/config/view/form/widget"}
Ext.define('Shopware.apps.Config.view.form.Widget', {
    extend: 'Shopware.apps.Config.view.base.Form',
    alias: 'widget.config-form-widget',

    getItems: function() {
        var me = this;
        return [{
            xtype: 'config-base-table',
            store: 'form.WidgetView',
            searchField: 'label',
            columns: me.getColumns()
        },{
            xtype: 'config-base-detail',
            items: me.getFormItems()
        }];
    },

    getColumns: function() {
        var me = this;
        return [{
            dataIndex: 'label',
            text: 'Label',
            flex: 1,
            sortable:false
        }, {
            dataIndex: 'column',
            text: 'Column',
            flex: 1,
            sortable: false
        }, {
            dataIndex: 'position',
            text: 'Position',
            flex: 1,
            sortable: false
        }, me.getActionColumn()];
    },

    getFormItems: function() {
        var me = this;
        return [{
            xtype: 'config-element-select',
            name: 'widgetId',
            fieldLabel: 'Widget',
            store: 'form.Widget',
            displayField: 'label'
        },{
            name: 'label',
            fieldLabel: 'Label'
        },{
            xtype: 'config-element-number',
            name: 'column',
            fieldLabel: 'Column'
        },{
            xtype: 'config-element-number',
            name: 'position',
            fieldLabel: 'Position'
        }];
    }
});
//{/block}
