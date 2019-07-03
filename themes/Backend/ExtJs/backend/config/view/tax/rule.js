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
 * Shopware Backend - View for tax rules
 */

//{namespace name=backend/config/view/form}

//{block name="backend/config/view/tax/rule"}
Ext.define('Shopware.apps.Config.view.tax.Rule', {
    extend:'Shopware.apps.Config.view.base.Property',
    alias:'widget.config-tax-rule',

    title: '{s name=tax/rules/title}Tax rules{/s}',
    name: 'rules',

    initComponent: function() {
        var me = this;

        me.callParent(arguments);

        me.on('beforeedit', me.onComboEdit);
    },

    getColumns: function() {
        var me = this;

        return [{
            header: '{s name=tax/rules/name_header}Name{/s}',
            dataIndex: 'name',
            flex: 1.5,
            field: 'textfield'
        },{
            header: '{s name=tax/rules/area_header}Area{/s}',
            dataIndex: 'areaId',
            flex:1,
            editor: {
                xtype: 'config-element-select',
                queryMode:'local',
                queryCaching:true,
                store: Ext.data.StoreManager.lookup('base.CountryArea').load(),
                displayField: 'name',
                valueField: 'id',
                emptyText: '{s name=tax/rules/select_empty_text}All{/s}',
                matchFieldWidth: false,
                listConfig: {
                    width: 250
                }
            },
            renderer: me.onComboRenderer
        },{
            header:'{s name=tax/rules/country_header}Country{/s}',
            dataIndex:'countryId',
            flex:1,
            editor: {
                xtype: 'config-element-select',
                queryMode:'local',
                queryCaching:true,
                store: Ext.data.StoreManager.lookup('base.Country').load(),
                displayField:'name',
                valueField:'id',
                emptyText: '{s name=tax/rules/select_empty_text}All{/s}',
                matchFieldWidth: false,
                listConfig: {
                    width: 250
                }
            },
            renderer: me.onComboRenderer
        },{
            header:'{s name=tax/rules/state_header}State{/s}',
            dataIndex:'stateId',
            flex:1,
            editor: {
                xtype: 'config-element-select',
                queryMode:'local',
                queryCaching:true,
                store:Ext.data.StoreManager.lookup('base.CountryState').load(),
                displayField:'name',
                valueField:'id',
                emptyText: '{s name=tax/rules/select_empty_text}All{/s}',
                matchFieldWidth: false,
                listConfig: {
                    width: 250
                }
            },
            renderer: me.onComboRenderer
        },{
            header:'{s name=tax/rules/tax_header}Tax{/s}',
            dataIndex:'tax',
            flex:1,
            xtype:'numbercolumn', format:'00.00',
            editor:{
                xtype:'numberfield',
                allowBlank:false,
                decimalPrecision:2
            }
        }, me.getActionColumn()];
    },

    onComboEdit: function(plugin, event) {
        var me = this,
            column = me.columns[event.colIdx];
        if(!column || !column.editor && !column._editor) {
            return;
        }
        if(!column._editor) {
            column._editor = column.editor;
        }
        var editor = column._editor,
            store = editor.store,
            record = event.record,
            filter, value, filters;
        switch(event.field) {
            case 'areaId':
                record.set('countryId', null);
                record.set('stateId', null);
                break;
            case 'countryId':
                filter = 'areaId';
                record.set('stateId', null);
                break;
            case 'stateId':
                filter = 'countryId';
                break;
        }
        if(filter) {
            value = record.get(filter);
        }
        if(value) {
            filters = [{
                property: filter,
                value: value
            }]
        }
        store.load({
            filters: filters
        });

        me.checkFieldState(record);
    },

    checkFieldState: function(record) {
        var me = this,
            fieldsToDisable = [];

        if (!record.get('countryId')) {
            fieldsToDisable.push('stateId');
        }

        if (!record.get('areaId')) {
            fieldsToDisable.push('countryId');
        }

        Ext.each(me.columns, function(col) {
            if (col.dataIndex) {
                var newState = fieldsToDisable.indexOf(col.dataIndex) >= 0;
                col.getEditor().setDisabled(newState);
            }
        });
    },

    onComboRenderer: function(value, metadata, record, rowIndex, colIndex) {
        var me = this,
            column = me.columns[colIndex];
        if(!column.editor && !column._editor) {
            return value;
        }
        if(!column._editor) {
            column._editor = column.editor;
        }
        var editor = column._editor,
            store = column._editor.store,
            index, record;

        if(!value) {
            return editor.emptyText;
        }
        store.clearFilter(true);
        index = store.find(editor.valueField, value);
        record = store.getAt(index);
        if(!record) {
            return editor.emptyText;
        }
        value = record.get(editor.displayField);
        return value.substr(0, 1).toUpperCase() + value.substr(1);
    },

    getTopBar: function () {
        var me = this,
            topBar = me.callParent();
        me.addButton = Ext.apply(topBar[0],{ disabled:true });

        topBar.push({
            xtype: 'config-element-select',
            flex: 1,
            isPropertyFilter: true,
            name: 'customerGroupId',
            editable: false,
            store: 'base.CustomerGroup',
            listeners: {
                enable: function() {
                    // if available get the first entry
                    if(this.store.getAt('0')) {
                        this.setValue(this.store.getAt('0').get('id'));
                    }
                }
            }
        });
        return topBar;
    }
});
//{/block}
