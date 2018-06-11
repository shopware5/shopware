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

/*{namespace name=backend/shipping/view/edit/default}*/

/**
 * Shopware UI - Shipping Costs
 *
 * todo@all: Documentation
 */
//{block name="backend/shipping/view/edit/default/form_right"}
Ext.define('Shopware.apps.Shipping.view.edit.default.FormRight', {
    extend      :'Ext.container.Container',
    /**
     * Title of the right form
     * @string
     */
    title       : '{s name=right_title}Config{/s}',
    /**
     * Alias
     * @string
     */
    alias       : 'widget.shipping-top-right-form',

    /**
     * Disable the collapsible possibility
     * @boolean
     */
    collapsible : false,

    /**
     * Disable the split possibility
     * @boolean
     */
    split       : false,
    /**
     * Translation table containing
     *  - 0 = Always
     *  - 1 = Exclude dispatch fee free products
     *  - 2 = Never,
     *  - 3 = Display as basket item
     * @Array of Objects
     */
    dispatchSurchargeCalculationData : null,

    /**
     * Translation table containing
     *  - 0 = Default dispatch type
     *  - 1 = Alternate dispatch type
     *  - 2 = Surcharge type
     *  - 3 = Reduction type
     * @Array of Objects
     */
    dispatchTypeData : null,

    /**
     * Translation table containing
     *  - 0 = Weight
     *  - 1 = Price
     *  - 2 = Product count
     *  - 3 = Own calculation
     * @Array of Objects
     */
    dispatchCalculationData : null,

    /**
     * Default column width
     * @float
     */
    columnWidth : 0.49,

    /**
     * Layout Anchor
     * @string
     */
    layout      : 'anchor',

    /**
     * Height of the component
     * @string
     */
    height      : '275px',

    /**
     * Some default values
     * todo@stp Move this to CSS please :)
     * @object
     */
    defaults    : {
        labelStyle  : 'font-weight: 700; text-align: left;',
        xtype       : 'combo',
        anchor: '100%',
        labelWidth:130,
        minWidth:250
    },
    /**
     * Padding
     * @integer
     */
    bodyPadding     : 5,
    /**
     * Set border to zero
     * @integer
     */
    border      : 0,

    /**
     * Array of form elements
     */
    items       : [],

    margin: '0 0 0 10',

    /**
     * Stores the customer group store
     *
     * @Shopware.apps.Base.store.CustomerGroup
     */
    customerGroupStore : null,

    /**
     * Contains the calculation field.
     * @Ext.form.ComboBox
     */
    calculationField : null,

    /**
     * Contains the type combo box
     * @Ext.Form.ComboBox
     */
    typeField : null,

    /**
     * Keeps all known Shops
     *
     * @Shopware.apps.Base.store.Shop
     */
    shopStore : Ext.create('Shopware.apps.Base.store.Shop'),

    /**
     * Keeps all known tax codes
     *
     * @Shopware.apps.Base.store.Tax
     */
    taxStore : Ext.create('Shopware.apps.Base.store.Tax'),

    /**
     * Initialize the Shopware.apps.Supplier.view.main.List and defines the necessary
     * default configuration
     */
    initComponent : function() {
        var me = this;

        me.shopStore.load();
        me.taxStore.load();

        me.addEvents(
            /**
             * @event calculationFieldChange
             *
             * To react when ever the calculation type is changed the calculationFieldChange event will be fired
             *
             * This event can easily be captured in the controller
             * eg.
             * <code>
             * this.control({ 'calculationFieldChange' : function(){
             *     console.log('Calculation type change detected');
             * }
             * </code>
             *
             * @param [object] row
             * @param [object] rec
             */
            'calculationFieldChange',

            /**
             * @event typeFieldChange
             *
             * To react when ever the type is changed the typeFieldChange event will be fired
             *
             * This event can easily be captured in the controller
             * eg.
             * <code>
             * this.control({ 'typeFieldChange' : function(){
             *     console.log('Type change detected');
             * }
             * </code>
             *
             * @param [object] row
             * @param [object] rec
             */
            'typeFieldChange'
        );

        me.customerGroupStore = Ext.create('Shopware.apps.Base.store.CustomerGroup');
        // add a default element to this store.
        me.customerGroupStore.load();
        me.items = me.getFormElements();
        me.callParent(arguments);
    },
    /**
     * Returns a hugh array of Form Elements
     * @array
     */
    getFormElements : function() {
        var me = this;
        return [
            {
                name        : 'multiShopId',
                emptyText   : '{s name=right_empty_text_shop}All Shops{/s}',
                fieldLabel  : '{s name=right_shop}Shop{/s}',
                store       :  me.shopStore,
                valueField  : 'id',
                displayField: 'name',
                editable    : true,
                allowBlank  : true,
                queryMode : 'local',
                emptyText:'{s name=right_empty_text_shop}All Shops{/s}',
            }, {
                name        : 'customerGroupId',
                emptyText   : '{s name=right_empty_text_customer_group}All groups{/s}',
                fieldLabel  : '{s name=right_customer_group}Customer Group{/s}',
                store       :  me.customerGroupStore,
                valueField  : 'id',
                displayField: 'name',
                editable    : true,
                allowBlank  : true,
                queryMode : 'local',
                emptyText: '{s name=right_empty_text_customer_group}All groups{/s}',
            },
            me.getCalculationField(),
            {
                xtype: 'numberfield',
                fieldLabel: '{s name=right_shipping_free}Shipping free from{/s}',
                name: 'shippingFree',
                decimalPrecision : 2,
                allowNegative: false,
                minValue: 0,
                emptyText: '{s name=right_shipping_free_emptyText}Never{/s}',
                allowDecimals: true,
                listeners: {
                    blur: function(field, obv) {
                        var value = field.getValue();
                        field.setValue((!value || value === 0) ? null : value);
                    }
                }
            },
            me.getTypeField(),
            me.getSurchargeField(),
            {
                name        : 'taxCalculation',
                emptyText   : '{s name=right_empty_tax}Highest tax{/s}',
                fieldLabel  : '{s name=right_tax}Tax{/s}',
                store       : me.taxStore,
                valueField  : 'id',
                displayField: 'name',
                editable    : true,
                allowBlank  : true,
                style       : 'width: 100%',
                selectOnFocus   : false,
                triggerAction   : 'all',
                helpText: '{s name=right_empty_tax_help}{/s}',
                listeners: {
                    change: function(field, value) {
                        field.setValue((!value || value === 0) ? '{s name=right_empty_tax}Highest tax{/s}' : value);
                    }
                }
            }
        ];

    },

    getTypeField : function() {
        var me = this;

        me.typeField = {
            name        : 'type',
            hiddenName  : 'type',
            emptyText   : '{s name=right_choose}Choose...{/s}',
            value       : 0,
            fieldLabel  : '{s name=right_dispatch_type}Dispatch type{/s}',
            valueField  : 'type',
            editable    : false,
            store       : new Ext.data.SimpleStore({
               fields  : ['type', 'name'],
               data    :this.dispatchTypeData
            }),
            displayField:'name',
            mode: 'local',
            selectOnFocus:true,
            allowBlank: false,
            typeAhead: false,
            triggerAction: 'all',
            forceSelection : true,
            listeners: {
                scope: me,
                change: function(el, value, oldValue) {
                    me.fireEvent('typeFieldChange', el, value, oldValue);
                }
            }
        };
        return me.typeField;
    },

    /**
     * Returns the field which contains the calculation combo box
     * @return Ext.form.ComboBox
     */
    getCalculationField : function() {
        var me = this,
            defaultConfig;

        defaultConfig = {
            name        : 'calculation',
            hiddenName   : 'calculation',
            emptyText   : '{s name=right_choose}Choose...{/s}',
            fieldLabel  : '{s name=right_dispatch_calculation}Calculate dispatch costs based on{/s}',
            valueField  : 'calculation',
            editable    : false,
            store       : new Ext.data.SimpleStore({
                fields  : ['calculation', 'name'],
                data    : me.dispatchCalculationData
            }),
            displayField:'name',
            mode: 'local',
            selectOnFocus:true,
            allowBlank: false,
            typeAhead: false,
            triggerAction: 'all',
            forceSelection : true,

            listeners: {
                scope: me,
                change: function(el, value, oldValue) {
                    me.fireEvent('calculationFieldChange', el, value, oldValue);
                }
            }
        };
        defaultConfig = Ext.apply(defaultConfig, me.defaults);

        me.calculationField = Ext.create('Ext.form.field.ComboBox', defaultConfig);
        return me.calculationField;
    }  ,
    /**
     * Returns the field which contains the calculation combo box
     * @return Ext.form.ComboBox
     */
    getSurchargeField : function() {
        var me = this,
            defaultConfig;
        defaultConfig = {
                name        : 'surchargeCalculation',
                hiddenName  : 'surchargeCalculation',
                emptyText   : '{s name=right_choose}Choose...{/s}',
                fieldLabel  : '{s name=right_surcharge}Surcharge type{/s}',
                valueField  : 'surchargeCalculation',
                editable    : false,
                store       : new Ext.data.SimpleStore({
                   fields  : ['surchargeCalculation', 'name'],
                   data    :this.dispatchSurchargeCalculationData
                }),
                displayField:'name',
                mode: 'local',
                selectOnFocus:true,
                allowBlank: false,
                typeAhead: false,
                triggerAction: 'all',
                forceSelection : true
            };
        defaultConfig = Ext.apply(defaultConfig, me.defaults);

        me.surchargeField = Ext.create('Ext.form.field.ComboBox', defaultConfig);
        return me.surchargeField;
    }
});
//{/block}
