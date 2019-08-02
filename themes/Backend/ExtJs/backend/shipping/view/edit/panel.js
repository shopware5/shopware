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

/**
 * Shopware UI - Shipping Costs
 *
 * todo@all: Documentation
 */
//{block name="backend/shipping/view/edit/panel"}
Ext.define('Shopware.apps.Shipping.view.edit.Panel', {
    /**
     * Extends the Enlight.app.Window
     * @string
     */
    extend : 'Enlight.app.Window',
    /**
     * Alias
     * @string
     */
    alias : 'widget.shopware-shipping-edit-panel',

    /**
     * Layout is border
     * @string
     */
    layout : {
        type: 'vbox',
        align: 'stretch'
    },

    /**
     * Title of the Edit Window
     * @string
     */
    title : '{s name=dispatch_costs_edit_title}Shipping costs{/s}',

    /**
     * Use stateful
     * @boolean
     */
    stateful : true,
    /**
     * Id used for the stateful
     * @string
     */
    stateId : 'shopware-shipping-edit',
    /**
     * Width of the window
     * @string
     */
    width       : 990,

    /**
     * Height of the window
     * @string
     */
    height      : '90%',
    /**
     * Scroll if necessary
     * @boolean
     */
    autoScroll : false,
    /**
     * Property which holds the defaults settings for the different form pieces.
     * todo@stp Please move this to CSS
     * @object
     */
    formDefaults: {
        labelStyle  : 'font-weight: 700; text-align: right;',
        anchor      : '100%',
        xtype       : 'textfield',
        labelWidth  : 80,
        minWidth    : 250
    },

    /**
     * Translation Object
     * @object
     */
    dispatchCalculationData : [
        [0, '{s name=dispatch_calculation_data_weight}Weight{/s}'],
        [1, '{s name=dispatch_calculation_data_price}Price{/s}'],
        [2, '{s name=dispatch_calculation_data_count}Number of articles{/s}'],
        [3, '{s name=dispatch_calculation_data_own}Own calculation{/s}']
    ],
     /**
     * Translation Object
     * @object
     */
    dispatchTypeData : [
        [0, '{s name=dispatch_type_data_default}Default shipping type{/s}'],
        [1, '{s name=dispatch_type_data_alternative}Alternate shipping type{/s}'],
        [2, '{s name=dispatch_type_data_charge}Surcharge type{/s}'],
        [4, '{s name=dispatch_type_data_charge_as_position}Surcharge type as position{/s}'],
        [3, '{s name=dispatch_calculation_data_discount}Reduction type{/s}'],
    ],

    /**
     * Translation Object
     * @object
     */
    dispatchSurchargeCalculationData : [
        [0, '{s name=dispatch_surcharge_data_always}Always charge{/s}'],
        [1, '{s name=dispatch_surcharge_data_weight}Exclude articles free of shipping costs{/s}'],
        [2, '{s name=dispatch_surcharge_data_never}Never{/s}'],
        [3, '{s name=dispatch_surcharge_data_basket_item}Display as own basket item{/s}']
    ],

    /**
     * Contains the costs matrix record.
     *
     * @Ext.data.Store
     */
    costMatrixStore: null,


    /**
     * Initialize the Shopware.apps.Supplier.view.main.List and defines the necessary
     * default configuration.
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.tabPanel  = me.createTabPanel();
        me.formPanel = me.createFormPanel();

        if (me.editRecord) {
            me.formPanel.loadRecord(me.editRecord);
            me.attributeForm.loadAttribute(me.editRecord.get('id'));
        }

        me.items = [ me.formPanel, me.tabPanel ];

        me.bbar = {
            xtype: 'toolbar',
            dock: 'bottom',
            ui: 'shopware-ui',
            items: me.createActionButtons()
        };

        var form = me.formPanel.getForm();
        if(!me.editRecord.get('surchargeCalculation')) {
            form.findField('surchargeCalculation').setValue(0);
        } else {
            form.findField('surchargeCalculation').setValue(me.editRecord.get('surchargeCalculation'));
        }
        //me.surchargeField.setValue(2);
        me.callParent(arguments);
    },

    /**
     * Returns the most top form element
     *
     * @return Ext.form.FieldSet
     */
    createFormPanel: function() {
        var me = this;

        var fieldSet = Ext.create('Ext.form.FieldSet', {
            title: '{s name=dispatch_main_form_title}Configuration{/s}',
            height: 300,
            layout: 'column',
            bodyPadding: 10,
            defaults : {
                labelStyle  : 'font-weight: 700; text-align: right;',
                anchor      : '100%',
                xtype       : 'textfield',
                labelWidth  : 80,
                minWidth    : 250
            },
            items: [{
                xtype: 'shipping-top-left-form'
            }, {
                xtype: 'shipping-top-right-form',
                dispatchCalculationData: me.dispatchCalculationData,
                dispatchTypeData: me.dispatchTypeData,
                dispatchSurchargeCalculationData: me.dispatchSurchargeCalculationData
            }]
        });

        return Ext.create('Ext.form.Panel', {
            bodyPadding: 8,
            autoScroll:true,
            region: 'center',
            /*{if {acl_is_allowed privilege=create} || {acl_is_allowed privilege=update}}*/
            plugins:[
                {
                    // Includes the default translation plugin
                    ptype:'translation',
                    translationType:'config_dispatch',
                    translationMerge: true
                }
            ],
            /*{/if}*/
            items:[fieldSet]
        });
    },
    /**
     * Returns the tab panel in the south of the main form
     *
     * @return Ext.tab.Panel
     */
    createTabPanel: function() {
        var me = this,
            advancedTab = me.createAdvancedTab();

        advancedTab.record = me.editRecord;
        advancedTab.loadRecord(me.editRecord);

        me.attributeForm = Ext.create('Shopware.attribute.Form', {
            title: '{s namespace="backend/attributes/main" name="attribute_form_title"}{/s}',
            table: 's_premium_dispatch_attributes',
            bodyPadding: 10,
            autoScroll: true
        });

        var panels = [
            me.createDispatchMatrixTab(),
            me.createPaymentTab(),
            me.createCountryTab(),
            me.createCategoriesTreeTab(),
            advancedTab,
            me.attributeForm
        ];

        return Ext.create('Ext.tab.Panel', {
            region: 'south',
            activeTab: 0,
            flex: 1,
            items: panels
        });
    },
    /**
     * Creates the advanced tab
     *
     * @return Shopware.apps.Shipping.view.edit.Advanced
     */
    createAdvancedTab : function() {
        this.availableHolidays.load();

        return Ext.create('Shopware.apps.Shipping.view.edit.Advanced',{
            store: this.mainStore,
            dispatchId: this.dispatchId,
            record: this.editRecord,
            availableHolidays: this.availableHolidays
        });
    },

    /**
     * Creates and returns the tab containing the costs matrix
     *
     * @return Shopware.apps.Shipping.view.edit.DispatchCostsMatrix
     */
    createDispatchMatrixTab: function() {
        return Ext.create('Shopware.apps.Shipping.view.edit.DispatchCostsMatrix', {
            store: this.costMatrixStore,
            dispatchId: this.dispatchId
        });
    },

    /**
     * Creates and returns the tab containing the means of payment
     *
     * @return Shopware.apps.Shipping.view.edit.PaymentMeans
     */
    createPaymentTab: function() {
        return Ext.create('Shopware.apps.Shipping.view.edit.PaymentMeans', {
            dispatchId: this.dispatchId,
            availablePayments: this.availablePayments,
            record: this.editRecord
        });
    },
    /**
     * Creates and returns the tab containing the country
     *
     * @return Shopware.apps.Shipping.view.edit.Country
     */
    createCountryTab: function() {
        return Ext.create('Shopware.apps.Shipping.view.edit.Country', {
            dispatchId: this.dispatchId,
            availableCountries: this.availableCountries,
            record: this.editRecord
        });
    },
    /**
     * Creates and returns the tab containing the means of payment
     *
     * @return Shopware.apps.Shipping.view.edit.PaymentMeans
     */
    createCategoriesTreeTab: function() {
        return Ext.create('Shopware.apps.Shipping.view.edit.CategoriesTree', {
            availableCategoriesTree: this.availableCategoriesTree,
            record: this.editRecord
        });
    },


    /**
     * Creates and returns the default action button - save and cancel
     * @array of buttons
     */
    createActionButtons:function () {
        var me = this;

        return ['->', {
            text:'{s name=default_cancel}Cancel{/s}',
            scope: me,
            cls: 'secondary',
            handler: me.destroy
        }, {
            text:'{s name=default_save}Save{/s}',
            action:'saveDispatch',
            cls:'primary'
        }];

    }
});
//{/block}
