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
 *
 * @category   Shopware
 * @package    Tax
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/tax/view/main}

/**
 * Shopware Backend - View for state-grid
 *
 * todo@all: Documentation
 */
//{block name="backend/tax/view/main/states"}
Ext.define('Shopware.apps.Tax.view.main.Rules', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.tax-rules',
    autoScroll:true,
    disabled: true,
    autoHeight: true,
    selType: 'rowmodel',
    viewConfig: {
        getRowClass: function(record, rowIndex, rp, ds){ // rp = rowParams

            if(record.data.areaId == 0){
                return Ext.baseCSSPrefix + 'grid-row-selected';
            }
        }
    },

    createDockedToolBar: function(){
          return [{
                dock: 'bottom',
                xtype: 'pagingtoolbar',
                displayInfo: true,
                store: this.ruleStore
          }];
    },

    /**
     * Initialize the view components
     *
     * @return void
     */
    initComponent: function() {
        var me = this;
        me.store = me.ruleStore;
        me.store.grid = me;
        me.dockedItems = this.createDockedToolBar();
        me.plugins = Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToEdit: 1
        });
        me.rowEditing = me.plugins;

        me.on('edit', me.onEditRow, me);

        // Initiate area store
        this.areaStore =  Ext.create('Shopware.apps.Tax.store.Areas', {

        }).load();

        this.areaCombo = Ext.create('Ext.form.field.ComboBox',
        {
                allowBlank: true,
                store: this.areaStore ,
                displayField: 'name',
                valueField: 'id',
                emptyText: 'No area restriction',
                listeners: {
                    'change': function (field,newValue,oldValue,options){
                        var countryStore = this.countryStore;
                        countryStore.clearFilter(true);
                        this.stateStore.clearFilter(true);

                        countryStore.filter(
                            Ext.create('Ext.util.Filter', { filterFn: function(item) {
                                return item["data"]["areaId"] == newValue || item["data"]["areaId"] == 0;
                            }, root: 'data'})
                        );

                        this.countryCombo.setRawValue(0);
                        this.stateCombo.setRawValue(0);
                        this.countryCombo.setValue('', true);
                        this.stateCombo.setValue('', true);


                    },
                    scope: this
                }

        }
        );

        this.countryStore =  Ext.create('Shopware.apps.Tax.store.Countries').load();
        this.countryCombo = Ext.create('Ext.form.field.ComboBox',
        {
                allowBlank: true,
                store: this.countryStore ,
                displayField: 'name',
                valueField: 'id',
                emptyText: 'All',
                valueNotFoundText: 'All',
                editable:false,
                listeners: {
                   'change': function (field,newValue,oldValue,options){
                       var stateStore = this.stateStore;
                       stateStore.clearFilter(true);

                       stateStore.filter(
                           Ext.create('Ext.util.Filter', { filterFn: function(item) {
                               return item["data"]["countryId"] == newValue || item["data"]["countryId"] == 0;
                           }, root: 'data'})
                       );

                       // The field has changed
                       if(!field.getValue()) {
                           field.setRawValue(0);
                           field.setValue('', true);
                       }

                       this.stateCombo.setRawValue(0);
                       this.stateCombo.setValue('', true);
                   },
                   scope: this
               }

        }
        );

        this.stateStore =  Ext.create('Shopware.apps.Tax.store.States').load();
        this.stateCombo = Ext.create('Ext.form.field.ComboBox',
        {
                allowBlank: true,
                store: this.stateStore ,
                displayField: 'name',
                valueField: 'id',
                emptyText: 'All',
                listeners: {
                     'change': function (field,newValue,oldValue,options){
                         // The field has changed

                         if(!field.getValue()) {
                             field.setRawValue(0);
                             field.setValue('', true);
                         }
                     },
                     scope: this
                 }

        }
        );

        // Define the columns and renderers
        this.columns = [

        {
            header: '{s name=ruleslist/colname}Name{/s}',
            dataIndex: 'name',
            width: 200,
            field: 'textfield'
        },
        {
            xtype: 'booleancolumn',
            header: '{s name=ruleslist/colactive}Enabled{/s}',
            dataIndex: 'active',
            flex: 1,
            editor: {
                xtype: 'checkbox',
                inputValue: 'true',
                uncheckedValue: 'false'
            }
        },
        {
            header: '{s name=ruleslist/colarea}Area{/s}',
            dataIndex: 'areaId',
            flex: 1,
            editor: this.areaCombo,
            renderer: this.areaRenderer
        },
        {
            header: '{s name=ruleslist/colcountry}Country{/s}',
            dataIndex: 'countryId',
            flex: 1,
            editor: this.countryCombo,
            renderer: this.countryRenderer
        },
        {
           header: '{s name=ruleslist/colstate}State{/s}',
           dataIndex: 'stateId',
           flex: 1,
           editor: this.stateCombo,
           renderer: this.stateRenderer
        },
        {
           header: '{s name=ruleslist/coltax}Tax{/s}',
           dataIndex: 'tax',
           flex: 1,
           xtype: 'numbercolumn', format:'00.00',
           editor: {
                xtype: 'numberfield',
                allowBlank: false,
                decimalPrecision: 2
           }
        },
        {
            xtype: 'actioncolumn',
            width: 50,
            items: [{
                iconCls: 'sprite-minus-circle',
                cls: 'delete',
                tooltip: '{s name=ruleslist/colactiondelete}Delete this rule{/s}',
                handler:function (view, rowIndex, colIndex, item) {
                    me.fireEvent('deleteRule', view, rowIndex, colIndex, item);
                }
            }]
        }];

        var notice = Shopware.Notification.createBlockMessage('You have not defined a fallback tax rule.', 'warning');
        notice.hide();
        // Toolbar
        this.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            dock: 'top',
            ui: 'shopware-ui',
            items: [{
                iconCls: 'sprite-plus-circle',
                text: '{s name=ruleslist/addrule}Add tax rule{/s}',
                action: 'addRule'
            }, notice
            ]
        });


        this.dockedItems = Ext.clone(this.dockedItems);
        this.dockedItems.push(this.toolbar);

        this.callParent();
    },
    areaRenderer: function (value){

        this.areaStore.clearFilter(true);
        var index = this.areaCombo.store.find(this.areaCombo.valueField,value);
        if (index == -1) return 0;
        var record = this.areaCombo.store.getAt(index);
        var comboValue = record.get(this.areaCombo.displayField);
        return comboValue.substr(0, 1).toUpperCase() + comboValue.substr(1);
    },
    countryRenderer: function (value){
       this.countryStore.clearFilter(true);
       var index = this.countryCombo.store.find(this.countryCombo.valueField,value);
       if (index == -1) return 0;
       var record = this.countryCombo.store.getAt(index);
       var comboValue = record.get(this.countryCombo.displayField);
       return comboValue.substr(0, 1).toUpperCase() + comboValue.substr(1);
    },
    stateRenderer: function (value){
      this.stateStore.clearFilter(true);
      var index = this.stateCombo.store.find(this.stateCombo.valueField,value);
      if (index == -1) return 0;
      var record = this.stateCombo.store.getAt(index);
      var comboValue = record.get(this.stateCombo.displayField);
      return comboValue.substr(0, 1).toUpperCase() + comboValue.substr(1);
    },
    /**
     * Event listener method which will be fired when the user
     * edits a row in the role grid with the built-in row
     * editor.
     *
     * Saves the edited record to the store.
     *
     * @event edit
     * @param [object] editor
     * @return void
     */
    onEditRow: function(editor, event) {
        var store = event.store;

        editor.grid.setLoading(true);
        store.sync({
            callback: function() {
                editor.grid.setLoading(false);
            }
        });
    }
});
//{/block}
