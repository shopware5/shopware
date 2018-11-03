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

//{namespace name=backend/config/view/search}
//{block name="backend/config/view/form/search"}
Ext.define('Shopware.apps.Config.view.form.Search', {
    extend: 'Ext.tab.Panel',
    alias: 'widget.config-form-search',

    layout: 'fit',
    activeTab: 0,
    deferredRender: false,

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: me.getItems()
        });

        me.callParent(arguments);
    },

    getItems: function() {
        var me = this;
        return [
            me.getConfigForm(),
            me.getFieldForm()
        ];
    },

    getConfigForm: function() {
        var me = this;
        return {
            xtype: 'config-main-form',
            title: '{s name=search/config/title}Settings{/s}',
            shopStore: me.shopStore,
            formRecord: me.formRecord
        };
    },

    getFieldForm: function() {
        var me = this;
        return {
            xtype: 'config-base-form',
            title: '{s name=search/form/title}Relevance / Fields{/s}',
            items: [{
                xtype: 'config-base-table',
                region: 'center',
                border: false,
                sortableColumns: false,
                store: 'form.SearchField',
                searchField: 'name',
                columns: me.getColumns()
            }, {
                xtype: 'config-base-detail',
                items: me.getFormItems()
            }]
        };
    },

    getColumns: function() {
        return [{
            dataIndex: 'name',
            text: '{s name=search/table/name_text}Name{/s}',
            flex: 1
        }, {
            dataIndex: 'relevance',
            text: '{s name=search/table/relevance_text}Relevance{/s}',
            flex: 1
        }, {
            dataIndex: 'field',
            text: '{s name=search/table/field_text}Field{/s}',
            flex: 1
        }, {
            dataIndex: 'table',
            text: '{s name=search/table/table_text}Table{/s}',
            flex: 1
        }];
    },

    getFormItems: function() {
        var doNotSplitCheckBox = Ext.create('Ext.form.field.Checkbox', {
            name: 'do_not_split',
            fieldLabel: '{s name=search/detail/do_no_split_text}Do not split{/s}',
            helpText: '{s name=search/detail/do_no_split_help_text}<b>Note:</b> Needs a rebuild of the search index if changed!<br><br>Activate this option to store the values of this table field in the search index as given. Otherwise all characters which are not a letter, number or underscore, will be replaced by a blank character.<br><br>Example: search keywords for order number 1234-5678-90:<br>active: \"1234-5678-90\"<br>inactive: \"1234\", \"5678\", \"90\"{/s}',
            labelWidth: 120
        });

        return [{
            name: 'name',
            fieldLabel: '{s name=search/detail/name_text}Name{/s}',
            allowBlank: false
        }, {
            xtype: 'config-element-number',
            name: 'relevance',
            fieldLabel: '{s name=search/detail/relevance_text}Relevance{/s}'
        }, {
            name: 'field',
            fieldLabel: '{s name=search/detail/field_text}Table field{/s}',
            allowBlank: false
        }, {
            xtype: 'config-element-select',
            name: 'tableId',
            store: 'base.SearchTable',
            fieldLabel: '{s name=search/detail/table_text}Table{/s}',
            allowBlank: false
        }, doNotSplitCheckBox];
    }
});
//{/block}
