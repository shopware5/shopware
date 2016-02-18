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

/*{namespace name=backend/shipping/controller/costs_matrix}*/

/**
 * todo@all: Documentation
 */
//{block name="backend/shipping/controller/costs_matrix"}
Ext.define('Shopware.apps.Shipping.controller.CostsMatrix', {
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
        { ref : 'rightForm', selector : 'shipping-top-right-form' }
    ],
    /**
     * Keeps the current config for the grid.
     * The config is based on the dispatch calculation
     * @object
     */
    currentConfig : null,

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the sub-application
     * @return void
     */
    init : function () {
        var me = this;
        me.control({
            'shipping-view-edit-costs-matrix' : {

                /**
                 * Checks the new entry
                 */
                'beforeedit' : me.onEditStart,
                /*
                 * Handles the edit event
                 */
                'edit' :  me.onCostMatrixEdit,

                /**
                 * will fire the delete event
                 * Parameter given
                 * - rowIndex
                 * - record
                 */
                'deleteCostsMatrixEntry' : me.onDeleteCostsMatrixEntry
            },
            'shipping-view-edit-costs-matrix actioncolumn' : {
                render : function (view) {
                    view.scope = this;
                    view.handler = this.handleActionColumn;
                }
            }
        });

        me.callParent(arguments);
    },
    /**
     * Helper method which handles all clicks of the action column
     *
     * @param [object] view - The view
     * @param [integer] rowIndex - On which row position has been clicked
     * @param [integer] colIndex - On which column position has been clicked
     * @param [object] item - The item that has been clicked
     * @return void
     */
    handleActionColumn : function (view, rowIndex, colIndex, item) {
        var me = this.scope;

        switch (item.iconCls) {
            case 'sprite-minus-circle-frame':
                 var store = view.getStore(),
                    record = store.getAt(rowIndex);
                me.onDeleteCostsMatrixEntry(rowIndex, record);
                break;
            default:
                break;
        }
    },
    /**
     * Reacts on the edit event and formats the entry
     *
     * @param obj
     * @param options
     * @return void
     */
    onEditStart : function(obj, options) {
        var me = this,
            column = options.column,
            editor = column.getEditor(),
            mainController = me.getController('Main'),
            calculationTypeField = me.getCalculationField(),
            config =  mainController.getConfig(calculationTypeField.value),
            columns = obj.grid.getColumns(),
            fromEditor = columns[0];

        if('to' == options.field)
        {
            editor.decimalPrecision = config.decimalPrecision;
            fromEditor.decimalPrecision = config.decimalPrecision;
        }
    },
    /**
     * Logic to edit the costs matrix
     *
     * @param [object] editor
     * @param [object] options
     * @return boolean
     */
    onCostMatrixEdit :   function(editor, options) {
         var rec = options.record,
             me = this,
             field = options.field,
             fromValue  = 1*rec.get('from'),
             datakeys = options.grid.store.data.keys,
             toField = options.column.field,
             mainController = me.getController('Main'),
             calculationTypeField = me.getCalculationField(),
             calculationType = calculationTypeField.value,
             config =  mainController.getConfig(calculationType),
             fieldOriginal = options.originalValue;

        if('to' == field) {
            // If the entered value is smaller than the value in the from field
            if((options.value <= fromValue) ) {
                var errorText = '{s name=dialog_text}Value must higher than ([0]){/s}';
                toField.setRawValue(fieldOriginal);
                rec.set('to', 0);
                toField.setValue(0);
                options.column.field.addCls(Ext.baseCSSPrefix + 'form-text ' + Ext.baseCSSPrefix + 'form-invalid-field');
                return false;
            }
            // check if there are more rows
            if(datakeys[options.rowIdx+1]) {
                // iterate through all rows
                while(datakeys[options.rowIdx+1]) {
                    var recordID = datakeys[options.rowIdx+1];
                    var nextRecord = editor.grid.store.getById(recordID);
                    // remove everthing higher than the new value
                    if ((null != nextRecord) && nextRecord.get("to") && nextRecord.get("to") <= options.value) {
                        options.grid.store.remove(nextRecord);
                    } else {
                        if(null != nextRecord) {
                            nextRecord.set("from", options.value);
                        }
                        break;
                    }
                }
            } else {
                if(options.originalValue === 0) {
                    editor.decimalPrecision = config.decimalPrecision;
                    var newValue  = Ext.util.Format.round(options.value + config.minChange, config.decimalPrecision);
                    editor.completeEdit();
                    me.addCostsMatrixEntry(newValue, options.grid.store);
                    editor.startEditByPosition({
                        row: options.rowIdx + 1,
                        column: 1
                    });
                    return true;
                }
            }
        }
    },

    /**
    * Removes the last entry from the grid
    *
    * @param [integer] rowIndex
    * @param [object] record
    * @return void
    */
    onDeleteCostsMatrixEntry: function(rowIndex, record) {
        /* {if {acl_is_allowed privilege=delete}} */
        var me = this,
        store = record.store;

        Ext.MessageBox.confirm('{s name=delete_dialog_title}Delete selected Costs Entry?{/s}',
            '{s name=delete_dialog_body}Do you really want delete this entry?{/s}',
            function (response) {
                if (response !== 'yes') {
                    return false;
                }
                // Row has been created, but not yet saved, so we just remove it
                if(record.phantom) {
                    record.store.remove(record);
                } else {
                    var costsMatrixModel = me.getModel('Costsmatrix').create();
                    costsMatrixModel.set(record.data);
                    costsMatrixModel.destroy({
                        success : function () {
                            store.load();
                            Shopware.Msg.createGrowlMessage('','{s name=dialog_success}Costs entry has been deleted successfully{/s}', '{s name=title}{/s}')
                        },
                        failure : function () {
                            Shopware.Msg.createGrowlMessage('', '{s name=dialog_error}An error occurred while deleting the costs entry{/s}', '{s name=title}{/s}');
                        }
                    });
                }
            }
        );
        /* {/if} */
    },
    /**
     * Method tho add a new row at the end of a grid
     *
     * @param [float] from
     * @param Ext.data.Store store
     * @return void
     */
    addCostsMatrixEntry : function(from, store) {
        var me = this,
        last = store.getCount(),
        lastEntry = store.getAt(last-1),
        from  = 1*from;
        // Create a model instance
        var newCosts =  Ext.create('Shopware.apps.Shipping.model.Costsmatrix', {
            from: from,
            value : 0,
            factor : 0,
            dispatchId: lastEntry.get('dispatchId')
        });
        // Add the new record at the end
        store.insert(last, newCosts);
    },
    getCalculationField : function() {
        return this.getRightForm().calculationField;
    }
});
//{/block}
