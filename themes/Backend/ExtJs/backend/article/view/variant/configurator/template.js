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
 * @package    Article
 * @subpackage Detail
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Article variants - Detail.
 * The detail component is an extension of the Enlight.app.Window. It displayed when the
 * user clicks the pencil action column in the variant listing to edit the selected
 * variant over the detail page.
 *
 * @link http://www.shopware.de/
 * @license http://www.shopware.de/license
 * @package Article
 * @subpackage Variants
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/view/variant/configurator/template"}
Ext.define('Shopware.apps.Article.view.variant.configurator.Template', {
    /**
     * Define that the order main window is an extension of the enlight application window
     * @string
     */
    extend:'Enlight.app.Window',
    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'article-configurator-template-window',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.article-configurator-template-window',
    /**
     * Set no border for the window
     * @boolean
     */
    border:false,
    /**
     * True to automatically show the component upon creation.
     * @boolean
     */
    autoShow:true,
    /**
     * Set border layout for the window
     * @string
     */
    layout:'fit',
    /**
     * Define window width
     * @integer
     */
    width:900,
    /**
     * Define window height
     * @integer
     */
    height:600,
    /**
     * True to display the 'maximize' tool button and allow the user to maximize the window, false to hide the button and disallow maximizing the window.
     * @boolean
     */
    maximizable:true,

    /**
     * True to display the 'minimize' tool button and allow the user to minimize the window, false to hide the button and disallow minimizing the window.
     * @boolean
     */
    minimizable:true,

    /**
     * A flag which causes the object to attempt to restore the state of internal properties from a saved state on startup.
     */
    stateful:true,

    /**
     * The unique id for this object to use for state management purposes.
     */
    stateId:'shopware-article-variant-detail-window',

    /**
     * Contains all snippets for the component
     * @object
     */
    snippets: {
        baseFieldSet: {
            title:'{s name=detail/base/title_new}Head data{/s}',
            number: '{s name=detail/base/number}Article number{/s}',
            active: '{s name=detail/base/active}Active{/s}',
            activeBox: '{s name=detail/base/active_box}Article will be displayed in store front{/s}',
            numberValidation: '{s name=detail/base/number_validation}The inserted article number already exists!{/s}',
            additionalText: '{s name=detail/base/additional_text}Additional text{/s}',
            purchasePrice: '{s name=detail/base/purchase_price}Purchase price{/s}'
        },
        basePrice: {
            title:'{s name=detail/base_price/title}Base price calculation{/s}',
            content:'{s name=detail/base_price/content}Content{/s}',
            unit:'{s name=detail/base_price/unit}Unit{/s}',
            basicUnit:'{s name=detail/base_price/basic_unit}Basic unit{/s}',
            packingUnit:'{s name=detail/base_price/packing_unit}Packing unit{/s}',
            empty:'{s name=empty}Please select...{/s}'
        },
        settings: {
            title:'{s name=detail/settings/title}Settings{/s}',
            supplierNumber:'{s name=detail/settings/supplier_number}Supplier number{/s}',
            weight: '{s name=detail/settings/weight_bw}Weight (bw){/s}',
            deliveryTime: '{s name=detail/settings/delivery_time}Delivery time (days){/s}',
            stock: '{s name=detail/settings/stock}Stock{/s}',
            minStock: '{s name=detail/settings/min_stock}Minimum storage inventory{/s}',
            inStock: '{s name=detail/settings/on_sale_field}Disable when no stock{/s}',
            inStockBox: '{s name=detail/settings/on_sale_box}Do not display if stock <= 0{/s}',
            releaseDate: '{s name=detail/settings/release_date}Release date{/s}',
            ean: '{s name=detail/settings/ean}EAN{/s}',
            width: '{s name=detail/settings/width}Width{/s}',
            height: '{s name=detail/settings/height}Height{/s}',
            len: '{s name=detail/settings/length}Length{/s}',
            shippingFree: {
                field: '{s name=detail/settings/shipping_free_field}Free shipping{/s}',
                box: '{s name=detail/settings/shipping_free_box}Select article as free shipping{/s}'
            },
            graduation: '{s name=detail/settings/graduation}Graduation{/s}',
            maximumOrder: '{s name=detail/settings/maximum_order}Maximum order{/s}',
            minimumOrder: '{s name=detail/settings/minimum_order}Minimum order{/s}'
        },
        additional: {
            title:'{s name=detail/additional_fields/title}Additional fields{/s}',
            comment:'{s name=detail/additional_fields/comment}Comment{/s}',
        },
        data:'{s name=variant/list/toolbar/data}Apply standard data{/s}',
        save:'{s name=detail/save_button}Save article{/s}',
        cancel:'{s name=detail/cancel_button}Cancel{/s}',
        title:'{s name=detail/configurator_template}Configurator Template{/s}'
    },

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
    initComponent:function () {
        var me = this, mainWindow;
        me.items = me.createItems();
        me.dockedItems = [ me.createToolbar() ];
        me.title = me.snippets.title;
        me.registerEvents();
        me.callParent(arguments);

        me.mainWindow = me.subApplication.articleWindow;
        if(me.mainWindow.hasOwnProperty('unitStore')) {
            me.unitComboBox.bindStore(me.mainWindow.unitStore);
        }

        if (me.record) {
            me.formPanel.loadRecord(me.record);
            me.attributeForm.loadAttribute(me.record.get('id'));
            Ext.Function.defer(function() {
                me.formPanel.translationPlugin.initTranslationFields(me.formPanel);
            }, 300);
        }
    },

    /**
     * Creates the window toolbar which docked bottom and contains the cancel and save button.
     * @return Ext.toolbar.Toolbar
     */
    createToolbar: function() {
        var me = this;

        //creates the toolbar with a spaces, the cancel and save button.
        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            items: me.createToolbarItems()
        });

    },

    /**
     * Creates all toolbar elements of the window toolbar.
     */
    createToolbarItems: function() {
        var me = this, items = [];

        items.push(me.createToolbarFill());
        items.push(me.createSaveButton());
        items.push(me.createCancelButton());

        return items;
    },

    /**
     * Create the save button which fire the save event, the save event is handled in the detail controller.
     * @return Ext.button.Button
     */
    createSaveButton: function() {
        var me = this;

        me.saveButton = Ext.create('Ext.button.Button', {
            cls:'primary',
            text: me.snippets.save,
            handler: function() {
                me.fireEvent('saveTemplate', me, me.formPanel, me.record);
            }
        });

        return me.saveButton;
    },


    /**
     * Creates the cancel button which fire the cancel event, the cancel event is handled in the detail controller.
     * @return Ext.button.Button
     */
    createCancelButton: function() {
        var me = this;
        me.cancelButton = Ext.create('Ext.button.Button', {
            text: me.snippets.cancel,
            cls: 'secondary',
            handler: function() {
                me.fireEvent('cancelEdit', me, me.article);
            }
        });

        return me.cancelButton;
    },

    /**
     * Helper function which creates the toolbar fill element.
     */
    createToolbarFill: function() {
        return { xtype: 'tbfill' };
    },

    /**
     * Registers additional component events.
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * Event will be fired when the user clicks the save button.
             *
             * @event
             * @param [object] The variant detail window
             * @param [Ext.data.Model] The article variant record.
             */
            'saveTemplate',
            /**
             * Event will be fired when the user clicks the cancel button.
             *
             * @event
             * @param [object] The variant detail window
             */
            'cancelEdit'
        );
    },

    /**
     * Internal helper function which creates the form panel and the elements for the panel.
     * @return array
     */
    createItems: function() {
        var me = this;

        me.formPanel = Ext.create('Ext.form.Panel', {
            items: me.createFormItems(),
            autoScroll: true,
            bodyPadding: 10,
            defaults: {
                labelWidth: 155
            },
            plugins: [{
                ptype: 'translation',
                pluginId: 'translation',
                translationType: 'configuratorTemplate',
                translationMerge: false,
                translationKey: null
            }]
        });

        me.attributeForm = Ext.create('Shopware.attribute.Form', {
            table: 's_article_configurator_templates_attributes',
            allowTranslation: false,
            translationForm: me.formPanel
        });
        me.formPanel.add(me.attributeForm);

        return [me.formPanel];
    },

    /**
     * Creates the element for the form panel
     * @return array
     */
    createFormItems: function() {
        var me = this;

        var baseFieldSet = me.createBaseFieldSet();
        var priceFieldSet = me.createPriceFieldSet();
        var basePriceFieldSet = me.createBasePriceFieldSet();
        var settingFieldSet = me.createSettingsFieldSet();

        return [ baseFieldSet, priceFieldSet, basePriceFieldSet, settingFieldSet ];
    },

    /**
     * Creates the form field set for the article number and the active combo box.
     * Displayed at first position on the detail page.
     * @return Ext.form.FieldSet
     */
    createBaseFieldSet: function() {
        var me = this;

        return Ext.create('Ext.form.FieldSet', {
            title: me.snippets.baseFieldSet.title,
            layout: 'anchor',
            margin: '15 0 10',
            defaults: {
                anchor: '100%',
                labelWidth: 155
            },
            items: [{
                xtype: 'textfield',
                name: 'number',
                fieldLabel: me.snippets.baseFieldSet.number,
                allowBlank: false
            }, {
                xtype: 'checkbox',
                name: 'active',
                fieldLabel: me.snippets.baseFieldSet.active,
                boxLabel: me.snippets.baseFieldSet.activeBox,
                inputValue: true,
                uncheckedValue:false
            }, {
                xtype: 'numberfield',
                name: 'purchasePrice',
                fieldLabel: me.snippets.baseFieldSet.purchasePrice,
                minValue: 0,
                step: 0.01
            }]
        });
    },

    /**
     * Creates the field set for the price tabs and grids.
     */
    createPriceFieldSet: function() {
        var me = this;

        me.priceGrid =  Ext.create('Shopware.apps.Article.view.detail.Prices', {
            customerGroupStore: me.customerGroupStore,
            attributeTable: 's_article_configurator_template_prices_attributes',
            article: me.record
        });

        me.priceGrid.onStoresLoaded(me.record, { customerGroups: me.customerGroupStore });
        return me.priceGrid;
    },

    /**
     * Creates the field set for the article base price calculation.
     * @return Ext.form.FieldSet
     */
    createBasePriceFieldSet: function() {
        var me = this;

        me.unitComboBox = Ext.create('Ext.form.field.ComboBox', {
            labelWidth: 155,
            anchor: '100%',
            name: 'unitId',
            queryMode: 'local',
            fieldLabel: me.snippets.basePrice.unit,
            emptyText: me.snippets.basePrice.empty,
            store: me.unitStore,
            displayField: 'name',
            valueField: 'id'
        });

        return Ext.create('Ext.form.FieldSet', {
            layout: 'anchor',
            cls: Ext.baseCSSPrefix + 'article-base-price-field-set',
            defaults: {
                labelWidth: 155,
                anchor: '100%',
                xtype: 'textfield'
            },
            title: me.snippets.basePrice.title,
            items: [
                me.unitComboBox,
                {
                    xtype: 'numberfield',
                    name: 'purchaseUnit',
                    submitLocaleSeparator: false,
                    decimalPrecision: 4,
                    fieldLabel: me.snippets.basePrice.content
                }, {
                    name: 'referenceUnit',
                    submitLocaleSeparator: false,
                    fieldLabel: me.snippets.basePrice.basicUnit
                }, {
                    name: 'packUnit',
                    translatable: true,
                    fieldLabel: me.snippets.basePrice.packingUnit
                }
            ]
        });
    },

    /**
     * Creates the form field set for the variant settings and additional fields.
     * @return Ext.form.FieldSet
     */
    createSettingsFieldSet: function() {
        var me = this;

        return Ext.create('Ext.form.FieldSet', {
            layout: 'column',
            defaults: {
                labelWidth: 155,
                xtype: 'textfield'
            },
            title: me.snippets.settings.title,
            items: [
                me.createLeftSettingsContainer(),
                me.createRightSettingsContainer()
            ]
        });
    },

    /**
     * Creates the left container for the settings panel. We need an additional container
     * to configure the column layout with two columns.
     */
    createLeftSettingsContainer: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            columnWidth:0.5,
            defaults: {
                labelWidth: 155,
                anchor: '100%'
            },
            padding: '0 20 0 0',
            layout: 'anchor',
            border:false,
            items: [{
                xtype: 'textfield',
                name: 'supplierNumber',
                fieldLabel: me.snippets.settings.supplierNumber
            } , {
                xtype: 'numberfield',
                name: 'weight',
                submitLocaleSeparator: false,
                fieldLabel: me.snippets.settings.weight
            }, {
                xtype: 'numberfield',
                name: 'inStock',
                fieldLabel: me.snippets.settings.stock
            }, {
                xtype: 'numberfield',
                name: 'stockMin',
                fieldLabel: me.snippets.settings.minStock
            }, {
                xtype: 'numberfield',
                name: 'minPurchase',
                minValue: 1,
                value: 1,
                fieldLabel: me.snippets.settings.minimumOrder
            }, {
                xtype: 'numberfield',
                name: 'purchaseSteps',
                fieldLabel: me.snippets.settings.graduation
            }, {
                xtype: 'numberfield',
                name: 'maxPurchase',
                fieldLabel: me.snippets.settings.maximumOrder
            }, {
                xtype: 'datefield',
                name: 'releaseDate',
                submitFormat: 'd.m.Y',
                fieldLabel: me.snippets.settings.releaseDate
            }]
        });
    },

    /**
     * Creates the right container for the settings panel. We need an additional container
     * to configure the column layout with two columns.
     */
    createRightSettingsContainer: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            columnWidth:0.5,
            defaults: {
                labelWidth: 155,
                anchor: '100%',
                xtype: 'textfield'
            },
            padding: '0 20 0 0',
            layout: 'anchor',
            border:false,
            items: [{
                xtype: 'textfield',
                name: 'shippingTime',
                translatable: true,
                fieldLabel: me.snippets.settings.deliveryTime
            }, {
                xtype: 'checkboxfield',
                name: 'shippingFree',
                fieldLabel: me.snippets.settings.shippingFree.field,
                boxLabel: me.snippets.settings.shippingFree.box,
                inputValue: true,
                uncheckedValue:false
            }, {
                xtype: 'checkboxfield',
                name: 'lastStock',
                fieldLabel: me.snippets.settings.inStock,
                boxLabel: me.snippets.settings.inStockBox,
                inputValue: true,
                uncheckedValue:false
            }, {
                xtype: 'textfield',
                name: 'ean',
                fieldLabel: me.snippets.settings.ean
            }, {
                xtype: 'numberfield',
                name: 'width',
                submitLocaleSeparator: false,
                fieldLabel: me.snippets.settings.width
            }, {
                xtype: 'numberfield',
                name: 'height',
                submitLocaleSeparator: false,
                fieldLabel: me.snippets.settings.height
            }, {
                xtype: 'numberfield',
                name: 'len',
                submitLocaleSeparator: false,
                fieldLabel: me.snippets.settings.len
            }]
        });
    }
});
//{/block}
