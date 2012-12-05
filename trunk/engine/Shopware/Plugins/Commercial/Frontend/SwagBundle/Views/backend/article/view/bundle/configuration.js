/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @package    Article
 * @subpackage Bundle
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     shopware AG
 */
//{namespace name="backend/bundle/article/view/main"}
Ext.define('Shopware.apps.Article.view.bundle.Configuration', {

    /**
     * The parent class that this class extends.
     */
    extend: 'Ext.form.Panel',
    cls: 'shopware-form',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets
     */
    alias: 'widget.bundle-configuration-panel',

    /**
     * Specifies the border size for this component. The border can be a single numeric value to apply to all
     * sides or it can be a CSS style specification for each style, for example: '10 5 3 10' (top, right, bottom, left).
     * For components that have no border by default, setting this won't make the border appear by itself.
     */
    border: false,

    /**
     * A shortcut for setting a padding style on the body element. The value can either be
     * a number to be applied to all sides, or a normal css string describing padding. Defaults to undefined.
     */
    bodyPadding: 10,

    /**
     * Important: In order for child items to be correctly sized and positioned, typically a layout manager must
     * be specified through the layout configuration option. The sizing and positioning of child items is
     * the responsibility of the Container's layout manager which creates and manages the type of layout you have in mind.
     * For example:
     * If the layout configuration is not explicitly specified for a general purpose container
     * (e.g. Container or Panel) the default layout manager will be used which does nothing but render
     * child components sequentially into the Container (no sizing or positioning will be performed in this situation).
     */
    layout: 'fit',

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
     *
     * @return void
     */
    initComponent: function() {
        var me = this;
        me.registerEvents();
        me.items = [ me.createFormFieldSet() ];
        me.callParent(arguments);
    },

    /**
     * Adds the specified events to the list of events which this Observable may fire
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * @Event
             * Custom component event.
             * Fired when the user change the value of the discount type combo box.
             */
            'discountTypeChanged'
        );
    },

    /**
     * Creates the field set for the configuration panel.
     *
     * @return object
     */
    createFormFieldSet: function() {
        var me = this;

        return Ext.create('Ext.form.FieldSet', {
            layout: 'column',
            items: me.createFormItems(),
            title: '{s name=configuration/title}Configuration{/s}',
            defaults: {
                labelWidth: 155,
                labelStyle: 'font-weight: bold'
            }
        });
    },

    /**
     * Creates the left and right container for the form panel.
     * @return Array
     */
    createFormItems: function() {
        var me = this, items = [];

        items.push(me.createLeftContainer());
        items.push(me.createRightContainer());

        return items;
    },

    /**
     * Creates the left container for the, column layout, form panel.
     * @return Ext.container.Container
     */
    createLeftContainer: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            layout: 'anchor',
            columnWidth: 0.5,
            padding: 10,
            defaults: {
                anchor: '100%',
                labelWidth: 155
            },
            items: me.createLeftContainerItems()
        });
    },

    /**
     * Creates the right container for the, column layout, form panel.
     * @return Ext.container.Container
     */
    createRightContainer: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            layout: 'anchor',
            padding: 10,
            columnWidth: 0.5,
            defaults: {
                anchor: '100%',
                labelWidth: 155
            },
            items: me.createRightContainerItems()
        });
    },

    /**
     * Creates the form elements for the left container of the form panel.
     * @return Array
     */
    createLeftContainerItems: function() {
        var me = this, items = [];

        items.push(me.createBundleNameItem());
        items.push(me.createBundleTypeItem());
        items.push(me.createDiscountTypeItem());
        items.push(me.createActiveItem());
        items.push(me.createNumberItem());

        return items;
    },

    /**
     * Creates the form elements for the right container of the form panel.
     * @return Array
     */
    createRightContainerItems: function() {
        var me = this, items = [];

        items.push(me.createLimitedItem());
        items.push(me.createQuantityItem());
        items.push(me.createValidFromItem());
        items.push(me.createValidToItem());

        return items;
    },

    /**
     * Creates the text field to edit the bundle name.
     * @return Ext.form.field.Text
     */
    createBundleNameItem: function() {
        var me = this;

        me.bundleNameField = Ext.create('Ext.form.field.Text', {
            fieldLabel: '{s name=configuration/name_field}Bundle name{/s}',
            allowBlank: false,
            name: 'name'
        });

        return me.bundleNameField;
    },

    /**
     * Creates the bundle type combo box.
     * Displayed in the left form panel container.
     * @return Ext.form.field.ComboBox
     */
    createBundleTypeItem: function() {
        var me = this;

        me.bundleTypeData = [
            [1, '{s name=configuration/normal_bundle_typ}Normal bundle{/s}'],
            [2, '{s name=configuration/selectedable_bundle_typ}Selectedable bundle{/s}']
        ];

        me.bundleTypeComboBox = Ext.create('Ext.form.field.ComboBox', {
            name: 'type',
            store:new Ext.data.SimpleStore({
                fields:['id', 'name'],
                data:me.bundleTypeData
            }),
            queryMode: 'local',
            valueField: 'id',
            displayField: 'name',
            allowBlank: false,
            fieldLabel: '{s name=configuration/bundle_typ_field}Bundle typ{/s}'
        });

        return me.bundleTypeComboBox;
    },

    /**
     * Creates the discount type combo box.
     * Displayed in the left form panel container.
     * @return Ext.form.field.ComboBox
     */
    createDiscountTypeItem: function() {
        var me = this;

        me.discountTypeData = [
            ['abs', '{s name=configuration/absolute_bundle_discount}Absolute discount{/s}'],
            ['pro', '{s name=configuration/percentage_bundle_discount}Percentage discount{/s}']
        ];

        me.discountTypeComboBox = Ext.create('Ext.form.field.ComboBox', {
            name: 'discountType',
            store:new Ext.data.SimpleStore({
                fields:['id', 'name'],
                data:me.discountTypeData
            }),
            queryMode: 'local',
            valueField: 'id',
            displayField: 'name',
            allowBlank: false,
            fieldLabel: '{s name=configuration/discount_typ_field}Discount typ{/s}',
            listeners: {
                change: function(combo, newValue, oldValue) {
                    me.fireEvent('discountTypeChanged', newValue, oldValue);
                }
            }
        });

        return me.discountTypeComboBox;
    },

    /**
     * Creates the active checkbox.
     * Displayed in the left form panel container.
     * @return Ext.form.field.Checkbox
     */
    createActiveItem: function() {
        var me = this;

        me.activeCheckbox = Ext.create('Ext.form.field.Checkbox', {
            name: 'active',
            inputValue: true,
            uncheckedValue: false,
            fieldLabel: '{s name=configuration/active_field}Active{/s}'
        });

        return me.activeCheckbox;
    },

    /**
     * Creates the order number text field.
     * Displayed in the left form panel container.
     * @return Ext.form.field.Text
     */
    createNumberItem: function() {
        var me = this;

        me.numberField = Ext.create('Ext.form.field.Text', {
            name: 'number',
            allowBlank: false,
            fieldLabel: '{s name=configuration/bundle_order_number}Bundle order number{/s}',
            //strips illegal characters
            regex: /^[a-zA-Z0-9-_.]+$/,
            regexText: '{s name=configuration/bundle_order_number_validation_regex}The inserted bundle number contains illegal characters!{/s}',
            //enables the key event with a buffering of 700ms
            enableKeyEvents: true,
            checkChangeBuffer: 700,
            //defines the validation function
            vtype:'remote',
            validationUrl: '{url controller="Bundle" action="validateNumber"}',
            validationErrorMsg: '{s name=configuration/bundle_order_number_validation_remove}Bundle order number already exist{/s}'
        });

        return me.numberField;
    },

    /**
     * Creates the limited checkbox.
     * Displayed in the right form panel container.
     * @return Ext.form.field.Checkbox
     */
    createLimitedItem: function() {
        var me = this;

        me.limitedCheckbox = Ext.create('Ext.form.field.Checkbox', {
            name: 'limited',
            inputValue: true,
            uncheckedValue: false,
            fieldLabel: '{s name=configuration/limited_field}Limited{/s}'
        });

        return me.limitedCheckbox;
    },

    /**
     * Creates the quantity number field.
     * Displayed in the right form panel container.
     * @return Ext.form.field.Number
     */
    createQuantityItem: function() {
        var me = this;

        me.quantityField = Ext.create('Ext.form.field.Number', {
            name: 'quantity',
            fieldLabel: '{s name=configuration/stock_column}Stock{/s}',
            minValue: 0,
            decimalPrecision: 0
        });

        return me.quantityField;
    },

    /**
     * Creates the valid from date field.
     * Displayed in the right form panel container.
     * @return Ext.form.field.Date
     */
    createValidFromItem: function() {
        var me = this;

        me.validFromField = Ext.create('Ext.form.field.Date', {
            name: 'validFrom',
            fieldLabel:'{s name=configuration/valid_from_field}Valid from{/s}',
            listeners: {
                change: function(field, newValue) {
                    me.validToField.setMinValue(newValue);
                }
            }
        });

        return me.validFromField;
    },

    /**
     * Creates the valid to date field.
     * Displayed in the right form panel container.
     * @return Ext.form.field.Date
     */
    createValidToItem: function() {
        var me = this;

        me.validToField = Ext.create('Ext.form.field.Date', {
            name: 'validTo',
            fieldLabel: '{s name=configuration/valid_to_field}Valid to{/s}',
            listeners: {
                change: function(field, newValue) {
                    me.validFromField.setMaxValue(newValue);
                }
            }
        });

        return me.validToField;
    }

});