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
//{block name="backend/article/view/variant/detail"}
Ext.define('Shopware.apps.Article.view.variant.Detail', {
    /**
     * Define that the order main window is an extension of the enlight application window
     * @string
     */
    extend:'Enlight.app.Window',
    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'article-variant-detail-window',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.article-variant-detail-window',
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
    width:920,
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

    footerButton: false,

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
            activeBox: '{s name=detail/base/active_box}Product can be purchased{/s}',
            inStock: '{s name=detail/settings/on_sale_field}Disable when no stock{/s}',
            inStockBox: '{s name=detail/settings/on_sale_box}Do not display if stock <= 0{/s}',
            numberValidation: '{s name=detail/base/number_validation}The inserted article number already exists!{/s}',
            additionalText: '{s name=detail/base/additional_text}Additional text{/s}',
            additionalTextSupport: '{s name=detail/base/additional_text_support}If left empty, an automatic text will be generated using the configurator options. This behaviour can be configured.{/s}',
            purchasePrice: '{s name=detail/base/purchase_price}Purchase price{/s}',
            configuratorOptions: '{s name=detail/base/configurator_options}Configurator options{/s}'
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
        title:'{s name=detail/title}Article details: [0]{/s}'
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
        me.registerEvents();
        me.callParent(arguments);

        mainWindow = me.subApplication.articleWindow;

        if(mainWindow.hasOwnProperty('unitStore')) {
            me.unitComboBox.bindStore(mainWindow.unitStore);
        }

        if (me.record) {
            me.formPanel.loadRecord(me.record);
            me.attributeForm.loadAttribute(me.record.get('id'));
            me.setTitle(Ext.String.format(me.snippets.title, me.record.get('additionalText')));
        } else {
            me.setTitle(Ext.String.format(me.snippets.title, '-'));
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
                me.fireEvent('saveVariant', me, me.formPanel, me.record);
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
            'saveVariant',
            /**
             * Event will be fired when the user clicks the cancel button.
             *
             * @event
             * @param [object] The variant detail window
             */
            'cancelEdit',
            /**
             * Event will be fired when the user clicks the apply data button.
             *
             * @event
             */
            'applyData'
        );
    },

    /**
     * Internal helper function which creates the form panel and the elements for the panel.
     * @return array
     */
    createItems: function() {
        var me = this, translationType = 'variant', translationKey = null;

        if (me.record) {
            if (me.record.get('kind') === 1) {
                translationType = 'variantMain';
                translationKey = me.record.get('articleId');
            } else {
                translationType = 'variant';
                translationKey = me.record.get('id');
            }
        }

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
                translationType: translationType,
                translationMerge: false,
                translationKey: translationKey
            }]
        });

        me.attributeForm = Ext.create('Shopware.attribute.Form', {
            table: 's_articles_attributes',
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

        //creates the price button to apply the standard prices of the main article on all variants.
        me.applyDataButton = Ext.create('Ext.button.Button', {
            style: 'position: absolute !important; top: 5px !important; right: 10px !important;',
            iconCls:'sprite-money--arrow',
            text: me.snippets.data,
            hidden: (me.record.get('kind') == 1),
            action: 'applyData',
            handler: function() {
                me.fireEvent('applyData', me, me.record);
            }
        });
        var buttonContainer = Ext.create('Ext.container.Container', {
            items: [me.applyDataButton]
        });

        var baseFieldSet = me.createBaseFieldSet();
        var priceFieldSet = me.createPriceFieldSet();
        var basePriceFieldSet = me.createBasePriceFieldSet();
        var settingFieldSet = me.createSettingsFieldSet();

        return [ buttonContainer, baseFieldSet, priceFieldSet, basePriceFieldSet, settingFieldSet];
    },

    /**
     * Creates the form field set for the article number and the active combo box.
     * Displayed at first position on the detail page.
     * @return Ext.form.FieldSet
     */
    createBaseFieldSet: function() {
        var me = this, articleId;

        articleId = null;
        if (me.record) {
            articleId = me.record.get('id');
        }

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
                allowBlank: false,
                enableKeyEvents:true,
                checkChangeBuffer:700,
                vtype:'remote',
                validationUrl: '{url action="validateNumber"}',
                validationRequestParam: articleId,
                validationErrorMsg: me.snippets.baseFieldSet.numberValidation
            }, {
                xtype: 'fieldcontainer',
                fieldLabel: me.snippets.baseFieldSet.configuratorOptions,
                items: [{
                    xtype: 'box',
                    autoEl: {
                        tag: 'div',
                        html: (new Ext.XTemplate(
                            // {literal}
                            '<tpl for=".">',
                                '<span style="display: inline-block; border-radius: 4px; background-color: white; border: 1px solid #CED4D8; padding: 4px 8px; font-size: 0.85em; margin-right: 5px; box-shadow: 1px 1px 1px rgba(0,0,0,0.1); font-weight: bold">{.}</span>',
                            '</tpl>'
                            // {/literal}
                        )).apply(me.getConfiguratorOptionNames()),
                    },
                }],
            } , {
                xtype: 'textfield',
                allowBlank: true,
                name: 'additionalText',
                translatable: true,
                fieldLabel: me.snippets.baseFieldSet.additionalText,
                supportText: me.snippets.baseFieldSet.additionalTextSupport
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
        var me = this, priceFieldset = Ext.create('Shopware.apps.Article.view.detail.Prices'),
            stores = [];

        stores['customerGroups'] = me.customerGroupStore;
        priceFieldset.onStoresLoaded(me.record, stores);

        return priceFieldset;
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
                me.unitComboBox, {
                    xtype: 'numberfield',
                    name: 'purchaseUnit',
                    submitLocaleSeparator: false,
                    decimalPrecision: 4,
                    fieldLabel: me.snippets.basePrice.content
                }, {
                    name: 'referenceUnit',
                    submitLocaleSeparator: false,
                    decimalPrecision: 3,
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
                decimalPrecision: 3,
                submitLocaleSeparator: false,
                fieldLabel: me.snippets.settings.weight
            }, {
                xtype: 'numberfield',
                name: 'inStock',
                decimalPrecision: 0,
                fieldLabel: me.snippets.settings.stock
            }, {
                xtype: 'numberfield',
                name: 'stockMin',
                decimalPrecision: 0,
                fieldLabel: me.snippets.settings.minStock
            }, {
                xtype: 'numberfield',
                name: 'minPurchase',
                decimalPrecision: 0,
                minValue: 1,
                value: 1,
                fieldLabel: me.snippets.settings.minimumOrder
            }, {
                xtype: 'numberfield',
                name: 'purchaseSteps',
                decimalPrecision: 0,
                fieldLabel: me.snippets.settings.graduation
            }, {
                xtype: 'numberfield',
                name: 'maxPurchase',
                decimalPrecision: 0,
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
            } , {
                xtype: 'checkbox',
                name: 'lastStock',
                fieldLabel: me.snippets.baseFieldSet.inStock,
                boxLabel: me.snippets.baseFieldSet.inStockBox,
                inputValue: true,
                uncheckedValue:false
            }, {
                xtype: 'textfield',
                name: 'ean',
                fieldLabel: me.snippets.settings.ean
            }, {
                xtype: 'numberfield',
                name: 'width',
                decimalPrecision: 3,
                submitLocaleSeparator: false,
                fieldLabel: me.snippets.settings.width
            }, {
                xtype: 'numberfield',
                name: 'height',
                decimalPrecision: 3,
                submitLocaleSeparator: false,
                fieldLabel: me.snippets.settings.height
            }, {
                xtype: 'numberfield',
                name: 'len',
                decimalPrecision: 3,
                submitLocaleSeparator: false,
                fieldLabel: me.snippets.settings.len
            }]
        });
    },

    /**
     * Return array of all configurator option names of the article variant
     *
     * @return { string[] }
     */
    getConfiguratorOptionNames: function () {
        if (!this.record) {
            return [];
        }

        var configuratorOptionNames = [];
        this.record.getConfiguratorOptions().each(function (configuratorOption) {
            configuratorOptionNames.push(configuratorOption.get('name'));
        });

        return configuratorOptionNames;
    }
});
//{/block}
