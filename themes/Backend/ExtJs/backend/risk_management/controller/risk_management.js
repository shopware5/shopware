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
 * @package    RiskManagement
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/risk_management/main}

/**
 * Shopware Controller - RiskManagement backend module
 *
 * RiskManagement controller of the RiskManagement module.
 * It handles all actions made in the module.
 * Listeners:
 *  - Save button  => Creates and edits the ruleSets.
 *  - Combobox click => Changes the active payment with its ruleSets.
 *  - Delete button => Deletes the selected ruleSet.
 */
Ext.define('Shopware.apps.RiskManagement.controller.RiskManagement', {

    /**
    * Extend from the standard ExtJS 4
    * @string
    */
    extend: 'Ext.app.Controller',

    /**
    * Creates the necessary event listener for this
    * specific controller and opens a new Ext.window.Window
    * @return void
    */
    init: function() {
        var me = this;
        me.control({
            'risk_management-main-panel':{
                onChangePayment: me.onChangePayment
            },
            'risk_management-main-container button[action=delete]':{
                'click': me.onDelete
            },
            'risk_management-main-container button[action=saveRules]':{
                'click': me.onSaveRules
            },
            'risk_management-main-container':{
                onChangeRisk: me.onChangeRisk
            }
        });

        me.callParent(arguments);
    },

    /**
     * This is fired when the user changes the risk of a ruleSet
     * @param comboBox - The comboBox with the risks
     * @param newValue - The new selected value
     * @param indexOfNextItem - The index of the item next to the comboBox
     */
    onChangeRisk: function(comboBox, newValue, indexOfNextItem){
        var me = this,
            //The next item
            nextItem = comboBox.up('container').items.items[indexOfNextItem];

        //If the user selected the risk ZONEIS or ZONEISNOT, add a comboBox instead of a textField
        if(['ZONEIS', 'ZONEISNOT', 'BILLINGZONEIS', 'BILLINGZONEISNOT'].indexOf(newValue) >= 0) {
            var newComboBox = Ext.create('Ext.form.field.ComboBox', {
                store: me.subApplication.areasStore,
                displayField: 'name',
                valueField: 'name',
                editable: false,
                columnWidth: 0.1,
                style: {
                    marginLeft: '10px'
                }
            });
            comboBox.up('container').remove(nextItem);
            comboBox.up('container').insert(indexOfNextItem, newComboBox);
        }else if(newValue == 'SUBSHOP' || newValue=='SUBSHOPNOT'){
            var newComboBox = Ext.create('Ext.form.field.ComboBox', {
                store: me.subApplication.subShopStore,
                displayField: 'name',
                valueField: 'id',
                editable: false,
                columnWidth: 0.1,
                style: {
                    marginLeft: '10px'
                }
            });
            comboBox.up('container').remove(nextItem);
            comboBox.up('container').insert(indexOfNextItem, newComboBox);
        }else{
            if(nextItem.xtype != 'textfield'){
                var newComboBox = Ext.create('Ext.form.field.Text', {
                    columnWidth: 0.1,
                    style: {
                        marginLeft: '10px'
                    }
                });
                comboBox.up('container').remove(nextItem);
                comboBox.up('container').insert(indexOfNextItem, newComboBox);
            }
        }

        if(newValue == 'INKASSO'){
            nextItem.hide();
            nextItem.setValue('1');
        }else{
            nextItem.show();
        }
    },

    /**
     * This function is called, when the user saves the rules
     * It checks for new and changed models and adds them to the store,
     * to save them in just one request
     */
    onSaveRules: function(){
        var me = this,
            comboBox = me.panel.paymentFieldSet.items.items[0],
            newSelection = me.subApplication.paymentStore.data.findBy(function(item){
                if(item.internalId == comboBox.getValue()) {
                    return true;
                }
            }),
            ruleStore = newSelection.getRuleSets(),
            changedModels = [];

        //foreach container/rule-row
        Ext.each(me.panel.riskFieldSet.items.items, function(item){
            // Fix: Not all containers in this fieldset are of interest
            if(item.xtype != 'risk_management-main-container') {
                return;
            }

            var rule1 = item.items.items[0],
                value1 = item.items.items[1],
                rule2 = item.items.items[3],
                value2 = item.items.items[4],
                id = item.items.items[5];

            var model = Ext.create('Shopware.apps.RiskManagement.model.Rule');

            if(rule1 !== Ext.undefined && value1 !== Ext.undefined) {
                model.set('rule1', rule1.getValue());
                model.set('value1', value1.getValue());
            }

            if(rule2 !== Ext.undefined && value2 !== Ext.undefined) {
                model.set('rule2', rule2.getValue());
                model.set('value2', value2.getValue());
            }
            model.set('paymentId', comboBox.getValue());
            model.setId(id.getValue());
            //Checks, if the model is in the store and if it changed
            if(me.isChanged(ruleStore, model)){
                changedModels.push(model);
            }
        });

        //Foreach changed model, change the corresponding model in the store
        Ext.each(changedModels, function(model){
            var match = ruleStore.findBy(function(item){
                if(item.getId() == model.getId()){
                    return true;
                }
            });
            ruleStore.data.items[match] = model;

            if(model.getId() === 0 && model.get('rule1') != ""){
                ruleStore.data.items.push(model);
            }
        });

        ruleStore.sync({
            callback: function(){
                me.subApplication.paymentStore.load({
                    callback: function(data, operation){
                        var records = operation.getRecords(),
                            record = records[0],
                            rawData = record.getProxy().getReader().rawData;
                        if(operation.success){
                            me.onChangePayment(me.panel, me.panel.paymentFieldSet.items.items[0].getValue());
                            Shopware.Notification.createGrowlMessage('{s name=growlMessage_title/saveRuleSuccess}Rule successfully saved{/s}', '{s name=growlMessage_message/saveRuleSuccess}The rules were successfully saved.{/s}', '{s name=window_title}{/s}');
                        }else{
                            Shopware.Notification.createGrowlMessage('{s name=growlMessage_title/saveRuleError}An error occurred{/s}', rawData.errorMsg, '{s name=window_title}{/s}');
                        }
                    }
                });
            }
        });
    },

    /**
     * Checks, if the given model is in the store already and if it changed
     *
     * @param store Contains the ruleStore with all ruleSets
     * @param model Contains the model to be compared with the corresponding model in the store
     * @return Boolean
     */
    isChanged: function(store, model){
        var data = [];
        Ext.each(store.data.items, function(item){
            //if the itemID and the modelID are the same => it's the matching item in the store
            if(item.getId() == model.getId()){
                //Check each value of the two models, to get changes
                Ext.Object.each(item.data, function(key, value){
                    if(value != model.get(key) && key!='shopware.apps.riskmanagement.model.payment_id'){
                        data.push(model);
                    }
                });
            }
        });

        //If the ID is 0, so this is a new model, which has data
        if(model.getId() === 0 && model.get('rule1') != "" ){
            data.push(model);
        }

        if(data[0]){
            return true;
        }

        return false;
    },

    /**
     * Is called when the user wants to delete a rule
     *
     * @param btn Contains the delete-button
     */
    onDelete: function(btn){
        var me = this,
            rowIndex = btn.rowIndex,
            model = Ext.create('Shopware.apps.RiskManagement.model.Rule');
        model.setId(rowIndex);
        model.destroy({
            callback: function(){
                me.subApplication.paymentStore.load({
                    callback: function(data, operation){
                        var records = operation.getRecords(),
                            record = records[0],
                            rawData = record.getProxy().getReader().rawData;
                        if(operation.success){
                            me.onChangePayment(me.panel, me.panel.paymentFieldSet.items.items[0].getValue());
                            Shopware.Notification.createGrowlMessage('{s name=growlMessage_title/deleteRuleSuccess}Rules successfully deleted{/s}', '{s name=growlMessage_message/deleteRuleSuccess}The rule was successfully deleted.{/s}', '{s name=window_title}{/s}');
                        } else{
                            Shopware.Notification.createGrowlMessage('{s name=growlMessage_title/deleteRuleError}An error occurred{/s}', rawData.errorMsg, '{s name=window_title}{/s}');
                        }
                    }
                });
            }
        });
    },

    /**
     * This function is called, when the user changes the payment with its ruleSets
     * @param panel
     * @param newValue
     */
    onChangePayment: function(panel, newValue){
        var me = this,
            newSelection;
        me.panel = panel;
        newSelection = me.subApplication.paymentStore.data.findBy(function(item){
            if(item.internalId == newValue) {
                return true;
            }
        });
        //By hiding the fieldset while manipulating its items, we prevent it from being rendered multiple times
        panel.riskFieldSet.hide();
        //Remove all container, before adding the new ones
        panel.riskFieldSet.removeAll();
        Ext.each(newSelection.data.getRuleSets, function(item){
            panel.riskFieldSet.add(Ext.create('Shopware.apps.RiskManagement.view.risk_management.Container', {
                values: item,
                areasStore: me.subApplication.areasStore,
                subShopStore: me.subApplication.subShopStore
            }));
            //"OR"-Container
            panel.riskFieldSet.add(Ext.create('Ext.container.Container',{
                html: '<b>{s name=container_or}OR{/s}</b>',
                width: 165,
                style: {
                    height: '20px',
                    width: '165px',
                    textAlign: 'center',
                    paddingTop: '2px',
                    paddingBottom: '2px',
                    marginTop: '10px',
                    marginBottom: '10px'
                }
            }));
        });
        /*{if {acl_is_allowed privilege=save}}*/
        panel.riskFieldSet.add(Ext.create('Shopware.apps.RiskManagement.view.risk_management.Container'));
        /*{/if}*/
        // Show the fieldsets after everything has been added
        panel.riskFieldSet.show();
        panel.exampleFieldSet.show();
    }
});
