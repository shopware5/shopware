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
 * @package    Shipping
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

/*{namespace name=backend/shipping/controller/default_form}*/

/**
 * todo@all: Documentation
 */
//{block name="backend/shipping/controller/default_form"}
Ext.define('Shopware.apps.Shipping.controller.DefaultForm', {
    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend : 'Ext.app.Controller',

    /**
     * Some references to get a better grip of the single elements
     */
    refs   : [
        { ref : 'costsGrid', selector : 'shipping-view-edit-costs-matrix' },
        { ref : 'selectedPaymentGrid', selector : 'shipping-view-edit-payment-means-right-grid' },
        { ref : 'selectedCountryGrid', selector : 'shipping-view-edit-country-right-grid' },
        { ref : 'categoryDataBox', selector : 'shipping-view-edit-categories-boxselect' },
        { ref : 'categoryTree', selector : 'shipping-view-edit-categories-tree' },
        { ref : 'advancedForm', selector : 'shipping-view-edit-advanced' },
        { ref : 'attributeForm', selector: 'shopware-shipping-edit-panel shopware-attribute-form' }
    ],
    /**
     * Translations
     */
    messages: {
        warning : '{s name=dialog_reset_cost_matrix}Be aware that the current costs matrix will be erased, if the dispatch type is changed.<br>Do you want stil want change the dispatch type?{/s}'
    },

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the sub-application
     */
    init : function () {
        var me = this;
        me.control({
            'shipping-top-right-form': {
                'calculationFieldChange': me.onCalculationChange,
                'typeFieldChange' :  me.onDispatchTypeChange
            },

            'button[action=saveDispatch]' : {
                'click' : me.onDispatchSave
            }
        });

        me.callParent(arguments);
    },

    /**
     * Acts when the main save button has been pressed
     *
     * @param button
     * @param event
     */
    onDispatchSave : function(button, event) {
        var me = this,
            win = button.up('window'),
            form = me.getMainFormData(button),
            holidayComponent = me.getAdvancedForm().down('boxselect'),
            categoryComponent = me.getCategoryData();

        var mainStore = me.getStore('Dispatch'),
            advancedForm = me.getAdvancedFormData(),
            attributeForm = me.getAttributeForm(),
            record = form.getRecord();

        // resetting some ids, so that the updateRecord process will set them new.
        record.set('multiShopId', null);
        record.set('customerGroupId', null);

        //updating the data
        advancedForm.updateRecord(record);
        form.updateRecord(record);

        // Update from external stores.
        record['getHolidaysStore'] = holidayComponent.valueStore;

        // just update the category selection if the tab has been rendered
        if (me.getCategoryTree().rendered) {
            record['getCategoriesStore']  = Ext.create('Ext.data.Store', {
                model : 'Shopware.apps.Base.model.Category',
                data : categoryComponent.getView().getChecked()
            });
        }

        // check form
        if (!form.isValid()) {
            return;
        }

        if(record.get('clone')){
            record.set('id', '');
        }

        // save the rest
        record.save({
             callback: function(answer, answerConfig) {
                // save costs matrix
                if (answerConfig.success ) {
                    var records = answerConfig.getRecords(),
                        record = records[0],
                        rawData = record.getProxy().getReader().rawData;
                    // Prevent cloned records from creating new records on each save-action
                    if(record.get('clone')){
                        record.set('id', rawData.data.id);
                        record.set('clone', false);
                    }
                    attributeForm.saveAttribute(rawData.data.id);
                    me.onCostsMatrixSave(button, rawData.data.id);
                }
                me.getStore('Dispatch').load();
                Shopware.Notification.createGrowlMessage('','{s name=growl_save_success}The settings have been saved successfully.{/s}', '{s name=title}{/s}');
             }
        });
    },

    /**
     * Collects the data from the main form
     * @return array
     */
    getMainFormData : function(button, event) {

        var win = button.up('window'),
            form = win.down('form');

        return form.getForm();
    },

    /**
     * Collects the data from the category form
     * @return Array
     */
    getCategoryData : function() {
        return this.getCategoryTree().treeSelect;
    },

    /**
     * Collects the data from the advanced
     * @return
     */
    getAdvancedFormData : function() {
        var me = this;
        return me.getAdvancedForm().getForm();
    },

    /**
     * When the Calculation has changed the costs matrix must be reconfigured
     * @param [object] el
     * @param [object] value
     * @param [object] oldValue
     */
    onCalculationChange : function(el, value, oldValue) {
        var me = this,
            mainController = me.getController('Main');

        if (oldValue == undefined) {
            // get the current config for the first time
            me.currentConfig = mainController.getConfig(el.getValue());
            return true;
        }
        var form1 = el.up('form'),
            form = form1.getForm(),
            costsMatrixGrid = me.getCostsGrid(),
            costsMatrixStore = costsMatrixGrid.getStore();

        Ext.MessageBox.confirm('{s name=dialog_reset_cost_matrix_title}Warning{/s}',
            me.messages.warning,
            function(response)
            {
                if (response !== 'yes') {
                    var record = el.getStore().getAt(oldValue);
                    // using setRawValue to avoid to trigger an other change event.
                    el.setRawValue(record.get('name'));
                    return false;
                }
                me.currentConfig = mainController.getConfig(el.getValue());
                mainController.currentConfig = me.currentConfig;
                costsMatrixStore.removeAll();
                costsMatrixStore.add(Ext.create('Shopware.apps.Shipping.model.Costsmatrix', {
                    'dispatchId' :  me.dispatchId
                }));
                var gridColumns = costsMatrixGrid.getColumns();
                Ext.each(gridColumns, function(column){
                    if(column.editor) {
                        if('from' == column.dataIndex || 'to' == column.dataIndex)
                        {
                            column.editor.decimalPrecision = me.currentConfig.decimalPrecision;
                        }
                    }
                });
            });
    },
    /**
     * Reacts if the 'dispatch type' is changed. The rules are
     * code 1 -> disable the shop field and customer group field
     *
     * @param [object] obj
     * @param [integer] value
     * @param [object] oldValue
     * @return void
     */
    onDispatchTypeChange: function(obj, value, oldValue) {
        var form1 = obj.up('form'),
            me    = this,
            form  = form1.getForm();
        me.resetDefaultForm(form);
        switch(value) {
            case 1: // alternate delivery mode
                form.findField('multiShopId').disable();
                form.findField('customerGroupId').disable();
               break;
            case 2: // surcharge
            case 3: // discount
            case 4: // surcharge
                form.findField('statusLink').hide();
                form.findField('comment').hide();
                form.findField('surchargeCalculation').hide();
                break;
            default:
                me.resetDefaultForm(form);
        }
    },

    /**
     * Rebuild the default form - enables all fields and displays them
     * @param [object] form
     * @return void
     */
    resetDefaultForm: function(form) {
        form.findField('multiShopId').enable();
        form.findField('customerGroupId').enable();
        form.findField('statusLink').show();
        form.findField('comment').show();
        form.findField('surchargeCalculation').show();
    },
    /**
     * Handels the saving of all form data
     *
     * @param [object] button
     * @param [integer] dispatchId
     * @return void
     */
    onCostsMatrixSave: function(button, dispatchId) {
        var me = this,
            win = button.up('window'),
            form = win.down('form'),
            costsGrid = me.getCostsGrid(),
            costsMatrixStore = costsGrid.getStore();

        costsMatrixStore.getProxy().extraParams = {
            dispatchId : dispatchId,
            minChange: me.currentConfig.minChange
        };
        // clean the whole grid - to avoid headaches during updates, we build everything from scratch
        costsMatrixStore.each(function(element) {
            // remove id -> force to create a new entry
            element.setId(null);
            Ext.data.Model.id(element);
            element.set('dispatchId', dispatchId);
            // mark as new
            element.phantom = true;
        });

        // trash and refill the store.
        costsMatrixStore.sync({
            callback: function() {
                // Syncing is done, so reload the store
                costsMatrixStore.load();
            }
        });
    }
});
//{/block}
