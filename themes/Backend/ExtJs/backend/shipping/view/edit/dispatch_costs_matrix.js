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
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

/*{namespace name=backend/shipping/view/edit/costs_matrix}*/

/**
 * Shopware UI - Shipping Costs
 *
 * todo@all: Documentation
 */
//{block name="backend/shipping/view/edit/dispatch_costs_matrix"}
Ext.define('Shopware.apps.Shipping.view.edit.DispatchCostsMatrix', {
    extend : 'Ext.grid.Panel',
    /**
     * Alias Name
     * @string
     */
    alias:'widget.shipping-view-edit-costs-matrix',
    /**
     * Name
     * @string
     */
    name : 'shipping-view-edit-costs-matrix',

    /**
     * Title of the tab
     * @string
     */
    title : '{s name=title}Shipping costs{/s}',
    /**
     * Border width
     * @integer
     */
    border : 0,
    /**
     * Height
     * @integer
     */
    height: 150,

    /**
     * Selection Model
     * @string
     */
    selType: 'rowmodel',
    /**
     * Set Autoscroll
     * @boolean
     */
    autoScroll: true,
    /**
     * Plugins to use
     * @array
     */
    plugins: [],
    /**
     * What Edit mode is enabeld (rowEdit or cellEdit)
     * @boolean
     */
    editMode : null,

    /**
     * Initialize the Shopware.apps.Supplier.view.main.List and defines the necessary
     * default configuration
     * @return void
     */
    initComponent : function() {
        var me = this;

        me.editMode = me.getEditingMode();
        me.plugins = [ me.editMode ];
        me.columns = me.getColumns();

        me.registerEvents();
        me.items = [ me.columns ];
        // check for emtpy stores (e.g. create scenario)
        if(me.store.getCount() == 0) {
            me.store.add(Ext.create('Shopware.apps.Shipping.model.Costsmatrix', {
                'dispatchId' :  me.dispatchId
            }));
        }
        me.callParent(arguments);
    },

    /**
     * Returns the Edit Mode
     * @return Ext.grid.plugin.CellEditing
     */
    getEditingMode: function() {
        return  Ext.create('Ext.grid.plugin.CellEditing', {
            clicksToEdit: 2,
            pluginId: 'costmatrixCellEditing'
        });
    },
    /**
     * Return the Column Model
     * @return array
     */
    getColumns : function() {
        var me = this;
        return [
            {
                header      : '{s name=from}From{/s}',
                dataIndex   : 'from',
                name        : 'from',
                flex: 1,
                sortable : false,
                editor: {
                    xtype: 'numberfield',
                    allowBlank: false,
                    decimalPrecision: 4
                }
            },
            {
                header      : '{s name=to}To{/s}',
                dataIndex   : 'to',
                flex: 2,
                sortable : false,
                renderer : me.onToRender,
                editor: {
                    xtype: 'numberfield',
                    allowBlank: false,
                    decimalPrecision: 4
                }
            },
            {
                header      : '{s name=deliver_costs}Shipping costs{/s}',
                dataIndex   : 'value',
                flex: 2,
                sortable : false,
                renderer : me.onRenderZeroValueEmptyReturn,
                editor: {
                   xtype: 'numberfield',
                   allowBlank: false
                }
            },
            {
                format      : '0.00',
                header      : '{s name=factor}Factor(%){/s}',
                dataIndex   : 'factor',
                flex: 2,
                sortable : false,
                renderer : me.onRenderZeroValueEmptyReturn,
                editor: {
                   xtype: 'numberfield',
                   allowBlank: false
                }
            },
            {
                header: '',
                xtype : 'actioncolumn',
                width : 60,
                /* {if {acl_is_allowed privilege=delete}} */
                items: [{
                    iconCls : 'sprite-minus-circle-frame',
                    action  : 'deleteCostsMatrixEntry',
                    //cls     : 'dispatchDelete',
                    tooltip : '{s name=grid_delete_tooltip}Delete these shipping costs.{/s}',
                    renderer: me.onActionRender,
                    getClass: function(value, metadata, record) {
                        if (record.data.to > 0)  {
                            return 'x-hidden';
                        }
                    }
                }] // end of items
                /* {/if} */
            }
        ]; // end of return
    },

    /**
     * This method takes care that just only in the last
     * row a delete button will be shown. And this method
     * builds an wrapper around the click event.
     *
     * @param val
     * @param meta
     * @param rec
     * @param row
     */
    onActionRender : function(val, meta, rec, row) {
        var me = this;
        /* {if {acl_is_allowed privilege=delete}} */
        if(rec.get('to') == '') {

//            return Ext.DomHelper.markup({
//                tag     : 'img',
//                'class'   : 'sprite-minus-circle-frame x-action-col-icon delete ',
//                cls     : 'delete',
//                style   : 'margin-left: 10px',
//                tooltip : '{s name=delete}Delete this entry.{/s}',
//                // actionColum
//                onclick : "Ext.getCmp('shipping-view-edit-costs-matrix').onDeleteClick(" + row + ");"
//            });
        }
        /* {/if} */
    },

    /**
     * When ever the value is zero we display unlimited
     * @param value
     */
    onToRender : function(value) {
        if (value == 0) {
            return '{s name=unlimited}unlimited{/s}';
        }
        return value;
    },
    /**
     * When ever the value is zero display nothing at all.
     * @param value
     */
    onRenderZeroValueEmptyReturn : function(value) {
        var me = this;
        if (value == 0) {
            return '';
        }
        return value;
    },

     /**
     * This method is called through the actionbar handler
     * @param editorMode
     */
    onAddClick: function(editorMode) {
        var me  = this;
        me.fireEvent('addCostsMatrixEntry', editorMode, me);
    },

    registerEvents : function() {
        var me = this;
        me.addEvents(
            /**
             * @event deleteCostsMatrixEntry
             *
             * We can just delete one row after an other to avoid data inconsistency,
             * if some on clicks the delete icon in this view, the event
             * deleteCostsMatrixEntry will be fired.
             *
             * This event can easily be captured in the controller
             * eg.
             * <code>
             * this.control({ 'deleteCostsMatrixEntry' : function(){
             *     console.log('the delete button has been pressed.');
             * }
             * </code>
             *
             * @param [object] row
             * @param [object] rec
             */
            'deleteCostsMatrixEntry',
            /**
             * @event addCostsMatrixEntry
             *
             * This event will be triggerd by pressing the add entry button
             *
             * This event can easily be captured in the controller
             * eg.
             * <code>
             * this.control({ 'addCostsMatrixEntry' : function(editorMode){
             *     console.log('the delete button has been pressed.');
             * }
             * </code>
             *
             * @param [object] editorMode
             * @param [object] grid
             */
            'addCostsMatrixEntry'
        );
    }

});
//{/block}
