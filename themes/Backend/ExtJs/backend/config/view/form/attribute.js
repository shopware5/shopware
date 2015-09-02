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

//{block name="backend/config/view/form/attribute"}
Ext.define('Shopware.apps.Config.view.form.Attribute', {
    extend: 'Shopware.apps.Config.view.base.Form',
    alias: 'widget.config-form-attribute',

    getItems: function() {
        var me = this;
        return [{
            xtype: 'config-base-table',
            store: 'form.Attribute',
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
            text: '{s name=attribute/table/name_text}Name{/s}',
            flex: 1
        },{
            xtype: 'gridcolumn',
            dataIndex: 'label',
            text: '{s name=attribute/table/label_text}Label{/s}',
            flex: 1
        },{
            xtype: 'gridcolumn',
            dataIndex: 'type',
            text: '{s name=attribute/table/type_text}Type{/s}',
            flex: 1
        }, me.getActionColumn()];
    },

    getFormItems: function() {
        var me = this;
        return [{
            name: 'name',
            fieldLabel: '{s name=attribute/detail/name_label}Name{/s}',
            allowBlank: false
        },{
            xtype: 'config-element-select',
            name: 'type',
            allowBlank: false,
            supportText:'{s name=attribute/detail/type_support_text}An adaptation of the respective column type of the database table s_articles_attributes may be necessary depending on the selection.{/s}',
            fieldLabel: '{s name=attribute/detail/type_label}Field type{/s}',
            store: [
                ['text',     '{s name=attribute/detail/type_text}Text field{/s}'],
                ['boolean',  '{s name=attribute/detail/type_boolean}Checkbox{/s}'],
                ['select',   '{s name=attribute/detail/type_select}Select field{/s}'],
                ['date',     '{s name=attribute/detail/type_date}Date field{/s}'],
                ['number',   '{s name=attribute/detail/type_number}Number field{/s}'],
                ['textarea', '{s name=attribute/detail/type_textarea}Text area{/s}'],
                ['time',     '{s name=attribute/detail/type_time}Time field{/s}'],
                ['html',     '{s name=attribute/detail/type_html}HTML field{/s}'],
                ['article',  '{s name=attribute/detail/type_article}Article search{/s}']
            ],
            listeners:{
                scope: me,
                change: function(combo, value) {
                    var form = combo.up('form'),
                        storeFormField = form.down('[name=store]'),
                        translatableFormField = form.down('[name=translatable]'),
                        translatableFields = ['textarea', 'text', 'html'];
                    if(value == 'select') {
                        storeFormField.show();
                    } else {
                        storeFormField.setValue(null);
                        storeFormField.hide();
                    }

                    //if the fields are not translatable don't show the checkbox
                    if (translatableFields.indexOf(value) == -1) {
                        translatableFormField.hide();
                    } else {
                        translatableFormField.show();
                    }
                }
            }
        },{
            name: 'store',
            fieldLabel: '{s name=attribute/detail/store_label}Store{/s}',
            supportText: '{s name=attribute/detail/store_help}Possible values​​: base.Payment, base.Country etc.{/s}'
        },{
            xtype: 'textarea',
            name: 'default',
            fieldLabel: '{s name=attribute/detail/default_label}Default value{/s}'
        },{
            name: 'label',
            fieldLabel: '{s name=attribute/detail/label_label}Field label{/s}'
        },{
            name: 'help',
            fieldLabel: '{s name=attribute/detail/help_label}Help text{/s}'
        },{
            xtype: 'config-element-number',
            name: 'position',
            fieldLabel: '{s name=attribute/detail/position_label}Position{/s}'
        },{
            xtype: 'config-element-boolean',
            name: 'required',
            fieldLabel: '{s name=attribute/detail/required_label}Required{/s}'
        },{
            xtype: 'config-element-boolean',
            name: 'variantable',
            fieldLabel: '{s name=attribute/detail/variantable_label}Variants capability{/s}'
        },{
            xtype: 'config-element-boolean',
            name: 'translatable',
            fieldLabel: '{s name=attribute/detail/translatable_label}Translatable{/s}'
        }];
    }
});
//{/block}
