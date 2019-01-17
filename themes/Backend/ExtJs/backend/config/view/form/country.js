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

//{block name="backend/config/view/form/country"}
Ext.define('Shopware.apps.Config.view.form.Country', {
    extend: 'Shopware.apps.Config.view.base.Form',
    alias: 'widget.config-form-country',

    getItems: function() {
        var me = this;
        return [{
            xtype: 'config-base-table',
            store: 'form.Country',
            columns: me.getColumns(),
            plugins: [{
                ptype: 'grid-attributes',
                table: 's_core_countries_attributes'
            }]
        },{
            xtype: 'config-base-detail',
            store: 'detail.Country',
            plugins: [{
                ptype: 'translation',
                pluginId: 'translation',
                translationType: 'config_countries',
                translationMerge: true
            }],
            items: me.getFormItems()
        }];
    },

    getColumns: function() {
        var me = this;
        return [
        {
            dataIndex: 'name',
            text: '{s name=country/table/name_text}Name{/s}',
            flex: 1
        }, {
            dataIndex: 'area',
            text: '{s name=country/table/area_text}Area{/s}',
            renderer: function(v) { return v && (v.charAt(0).toUpperCase() + v.substr(1)); },
            flex: 1
        }, {
            dataIndex: 'iso',
            text: '{s name=country/table/iso_text}Short code{/s}',
            flex: 1
        }, {
            dataIndex: 'position',
            text: '{s name=country/table/position_text}Position{/s}',
            flex: 1
        },
        me.getActionColumn()];
    },

    getFormItems: function() {
        var me = this;
        return [{
            name: 'name',
            fieldLabel: '{s name=country/detail/name_label}Name{/s}',
            translatable: true,
            allowBlank: false
        },{
            name: 'isoName',
            fieldLabel: '{s name=country/detail/iso_name_label}International name{/s}'
        },{
            xtype: 'config-element-boolean',
            name: 'active',
            translatable: true,
            fieldLabel: '{s name=country/detail/active_label}Active{/s}'
        },{
            xtype: 'config-element-boolean',
            name: 'allowShipping',
            translatable: true,
            translationName: 'allow_shipping',
            fieldLabel: '{s name=country/detail/allow_shipping}Allow the usage as shipping country{/s}'
        },{
            xtype: 'config-element-select',
            name: 'areaId',
            fieldLabel: '{s name=country/detail/area_label}Area{/s}',
            store: 'base.CountryArea'
        },{
            name: 'iso',
            fieldLabel: '{s name=country/detail/iso_label}Short code{/s}'
        },{
            name: 'iso3',
            fieldLabel: '{s name=country/detail/iso_three_label}Short code (three-letter){/s}'
        },{
            xtype: 'config-element-number',
            name: 'position',
            fieldLabel: '{s name=country/detail/position_label}Position{/s}',
            helpText: '{s name=country/detail/position_help}Position in select fields.{/s}'
        },{
            xtype: 'config-element-textarea',
            name: 'description',
            fieldLabel: '{s name=country/detail/description_label}Description{/s}',
            translatable: true,
            helpText: '{s name=country/detail/description_help}Notices to display in frontend.{/s}'
        },{
            xtype: 'config-element-boolean',
            name: 'taxFree',
            fieldLabel: '{s name=country/detail/tax_free_label}Tax free{/s}',
            helpText: '{s name=country/detail/tax_free_help}If true disable tax rules for this country.{/s}'
        },{
            xtype: 'config-element-boolean',
            name: 'taxFreeUstId',
            fieldLabel: '{s name=country/detail/tax_free_company_label}Tax free for companies{/s}',
            helpText: '{s name=country/detail/tax_free_company_help}If user entered a vat id disable tax.{/s}'
        },{
            xtype: 'config-element-boolean',
            name: 'displayStateInRegistration',
            fieldLabel: '{s name=country/detail/display_state_label}Display state selection{/s}',
            helpText: '{s name=country/detail/display_state_help}Display state selection in registration process{/s}'
        },{
            xtype: 'config-element-boolean',
            name: 'forceStateInRegistration',
            fieldLabel: '{s name=country/detail/required_state_label}Force state selection{/s}',
            helpText: '{s name=country/detail/required_state_help}Force state selection in registration process{/s}'
        },
        me.getStateGrid()];
    },

    getStateGrid: function() {
        var me = this;
        return {
            xtype: 'config-base-property',
            title: '{s name=country/state_table/title}States{/s}',
            name: 'states',
            plugins: [{
                ptype: 'cellediting',
                pluginId: 'cellediting',
                clicksToEdit: 1
            }, {
                ptype: 'gridtranslation',
                pluginId: 'translation',
                translationType: 'config_country_states',
                translationMerge: true
            }, {
                ptype: 'grid-attributes',
                table: 's_core_countries_states_attributes'
            }],
            columns: me.getStateColumns()
        }
    },

    getStateColumns: function() {
        return [{
            dataIndex: 'name',
            text: '{s name=country/state_table/name_text}Name{/s}',
            flex: 2,
            editor: {
                xtype: 'config-element-text'
            },
            translationEditor: {
                xtype: 'config-element-text',
                name: 'name',
                fieldLabel: '{s name=country/state_table/name_text}Name{/s}'
            }
        },{
            dataIndex: 'shortCode',
            text: '{s name=country/state_table/short_code_text}Short code{/s}',
            flex: 1,
            editor: {
                xtype: 'config-element-text'
            }
        },{
            dataIndex: 'position',
            text: '{s name=country/state_table/position_text}Position{/s}',
            flex: 1,
            editor: {
                xtype: 'config-element-number'
            }
        },{
            xtype: 'booleancolumn',
            dataIndex: 'active',
            text: '{s name=country/state_table/active_text}Active{/s}',
            flex: 1,
            editor: {
                xtype: 'config-element-boolean'
            }
        }];
    }
});
//{/block}
