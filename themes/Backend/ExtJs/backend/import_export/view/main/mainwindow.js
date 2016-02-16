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
 * @package    ImportExport
 * @subpackage Main
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/import_export/view/mainwindow}
//{block name="backend/import_export/view/main/mainwindow"}
Ext.define('Shopware.apps.ImportExport.view.main.Mainwindow', {
    extend: 'Enlight.app.Window',
    alias : 'widget.importexport-main-mainwindow',
    width: 600,

    stateful: true,
    stateId: 'shopware-importexport-mainwindow',

    height: '90%',
    autoScroll: true,

    layout: {
        type: 'vbox',
        pack: 'start',
        align: 'stretch'
    },

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        title : '{s name=title}Import / Export{/s}',
        titleExportArticlesCategories: '{s name=title_articles_categories}Export Articles and Categories{/s}',
        titleExportOrders:  '{s name=title_export_orders}Export Orders{/s}',
        titleExportMisc:  '{s name=title_export_misc}Export Misc{/s}',
        titleImport:  '{s name=title_import}Import{/s}',
        orderNumberFrom:  '{s name=order_number_From}Ordernumber from{/s}',
        dateFrom:  '{s name=date_from}Date from{/s}',
        dateTo:  '{s name=date_to}Date to{/s}',
        orderState:  '{s name=order_state}Order state{/s}',
        paymentState:  '{s name=payment_state}Payment state{/s}',
        choose:  '{s name=choose}Please choose{/s}',
        chooseButton:  '{s name=choose_button}Choose{/s}',
        format: '{s name=format}Format{/s}',
        updateOrderState:  '{s name=update_order_state}Update Orderstate{/s}',
        offset: '{s name=offset}Offset{/s}',
        limit:  '{s name=limit}Limit{/s}',
		start: '{s name=start}Start{/s}',
		data: '{s name=data}Data{/s}',
		uploading: '{s name=uploading}uploading...{/s}',
        exportType:  '{s name=export_type}Export Type{/s}',
        exportVariants: '{s name=export_variants}Export Variants{/s}',
        exportTranslations: '{s name=export_translations}Export Translations{/s}',
        exportCustomergroupPrices: '{s name=export_customergroup_prices}Export Customergroup Prices{/s}',
		categories: '{s name=categories}Categories{/s}',
		articles: '{s name=articles}Articles{/s}',
		customers: '{s name=customers}Customers{/s}',
		in_stock: '{s name=in_stock}In stock{/s}',
		not_in_stock: '{s name=not_in_stock}Articles not in stock{/s}',
		prices: '{s name=prices}Article prices{/s}',
		article_images: '{s name=article_images}Article images{/s}',
		newsletter: '{s name=newsletter}Newsletter receiver{/s}',
		article: '{s name=article}Article{/s}',
		neither: '{s name=neither}none{/s}',
		all_before_import: '{s name=all_before_import}all before import{/s}',
    	not_imported: '{s name=not_imported}not imported{/s}',
		empty: '{s name=empty}empty{/s}',
		file: '{s name=file}File{/s}',
        noticeMessage: '{s name=notice_message}The import / export options do possibly not support all of your maintained fields. Please read our \<a href=\'http://wiki.shopware.de/Datenaustausch_detail_308.html\' target=\'_blank\' \>wiki\</a\> documentation before using the module.{/s}',
        deprecationMessage: '{s name=deprecated_message}The import / export is now marked as deprecated and will be removed soon. Please refer to our new import / export module.{/s}',
        deprecationButton: '{s name=deprecated_button}get new import / export{/s}',
        deprecationTitle: '{s name=deprecated_title}Heads up!{/s}'
    },

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.title = me.snippets.title;

        /* {if {acl_is_allowed privilege=read}} */
        me.items = [
			me.getCreateHeader(),
            me.getInformationText(),
            me.getExportArticlesForm(),
            me.getExportOrdersForm(),
            me.getExportMiscForm(),
            me.getImportForm()
        ];
        /* {/if} */

        me.callParent(arguments);
    },

    /**
     * @return [Ext.container.Container]
     */
    getInformationText: function() {
        var me = this;

        me.InformationText = Ext.create('Ext.container.Container', {
            html: me.snippets.noticeMessage,
            plain: true,
            padding: '21 7 14 7'
        });

        return me.InformationText;
    },

    /**
     * @return [Ext.container.Container]
     */
    getCreateHeader: function() {
        var me = this;

        me.headerDeprecatedTitle = Ext.create('Ext.container.Container', {
            html: me.snippets.deprecationTitle,
            plain: true,
            style: {
                fontWeight: 700
            }
        });

        me.headerDeprecatedPanel = Ext.create('Ext.container.Container', {
            html: me.snippets.deprecationMessage,
            plain: true,
            margin: '0 0 10 0'
        });

        me.headerAction = Ext.create('Ext.button.Button', {
            html: me.snippets.deprecationButton,
            cls: 'primary',
            handler: function() {
                openNewModule('Shopware.apps.PluginManager', {
                    params: {
                        hidden: true,
                        displayPlugin: 'SwagImportExport'
                    }
                });
            }
        });

        me.headerContainer = Ext.create('Ext.container.Container', {
            margin: 1,
            padding: 7,
            plain: true,
            style: {
                textAlign: 'center'
            },
            items: [
                me.headerDeprecatedTitle,
                me.headerDeprecatedPanel,
                me.headerAction
            ]
        });

        return me.headerContainer;
    },

    /**
     * @return [Ext.form.Panel]
     */
    getExportOrdersForm: function() {
        var me = this;

        var toolbar = Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            cls: 'shopware-toolbar',
        /* {if {acl_is_allowed privilege=export}} */
            items: [ '->',
                {
                    text: me.snippets.start,
                    cls: 'primary',
                    formBind: true,
                    handler: function () {
                        var form = this.up('form').getForm();
                        if (!form.isValid()) {
                            return;
                        }

                        form.submit({
                            method: 'GET',
                            url: '{url module=backend controller=ImportExport action=exportOrders}'
                        });
                    }
                }
            ]
        /* {/if} */
        });

        var orderStatusStore = Ext.create('Shopware.store.OrderStatus');

        return Ext.create('Ext.form.Panel', {
            title: me.snippets.titleExportOrders,
            bodyPadding: 5,
            standardSubmit: true,
            target: 'iframe',
            layout: 'anchor',
            dockedItems: toolbar,
            defaults: {
                anchor: '100%',
                labelWidth: 300
            },
            defaultType: 'textfield',
            items: [
                {
                    fieldLabel: me.snippets.orderNumberFrom,
                    name: 'ordernumberFrom'
                },
                {
                    xtype: 'datefield',
                    fieldLabel: me.snippets.dateFrom,
                    name: 'dateFrom',
                    maxValue: new Date(),
                    submitFormat: 'd.m.Y'
                },
                {
                    xtype: 'datefield',
                    fieldLabel: me.snippets.dateTo,
                    name: 'dateTo',
                    maxValue: new Date(),
                    submitFormat: 'd.m.Y'
                },
                {
                    xtype: 'combobox',
                    name: 'orderstate',
                    fieldLabel: me.snippets.orderState,
                    emptyText: me.snippets.choose,
                    store: orderStatusStore,
                    editable: false,
                    displayField: 'description',
                    valueField: 'id'
                },
                {
                    xtype: 'combobox',
                    name: 'paymentstate',
                    fieldLabel: me.snippets.paymentState,
                    emptyText: me.snippets.choose,
                    store: Ext.create('Shopware.store.PaymentStatus'),
                    editable: false,
                    displayField: 'description',
                    valueField: 'id'
                },
                {
                    xtype: 'combobox',
                    name: 'updateOrderstate',
                    fieldLabel:  me.snippets.updateOrderState,
                    emptyText: me.snippets.choose,
                    store: orderStatusStore,
                    editable: false,
                    displayField: 'description',
                    valueField: 'id'
                },
                {
                    xtype: 'combobox',
                    fieldLabel: me.snippets.format,
                    name: 'format',
                    listeners: {
                        'afterrender': function () {
                            this.setValue(this.store.getAt('0').get('id'));
                        }
                    },
                    store: me.getFormatComboStore(),

                    forceSelection: true,
                    allowBlank: false,
                    editable: false,
                    mode: 'local',
                    triggerAction: 'all',
                    displayField: 'label',
                    valueField: 'id'
                }
            ]
        });
    },

    /**
     * @return [Ext.form.Panel]
     */
    getExportArticlesForm: function() {
        var me = this;

        var exportVariantsCheckbox = Ext.create('Ext.form.Checkbox', {
            name: 'exportVariants',
            fieldLabel: me.snippets.exportVariants,
            inputValue: 1,
            uncheckedValue: 0,
            hidden: true,
            anchor: '100%',
            labelWidth: 300
        });

        var exportTranslationsCheckbox = Ext.create('Ext.form.Checkbox', {
            name: 'exportArticleTranslations',
            fieldLabel: me.snippets.exportTranslations,
            inputValue: 1,
            uncheckedValue: 0,
            hidden: true,
            anchor: '100%',
            labelWidth: 300
        });

        var exportCustomergroupPricesCheckbox = Ext.create('Ext.form.Checkbox', {
            name: 'exportCustomergroupPrices',
            fieldLabel: me.snippets.exportCustomergroupPrices,
            inputValue: 1,
            uncheckedValue: 0,
            hidden: true,
            anchor: '100%',
            labelWidth: 300
        });

        var limitField = Ext.create('Ext.form.Number', {
            fieldLabel: me.snippets.limit,
            name: 'limit',
            hidden: true,
            anchor: '100%',
            labelWidth: 300
        });

        var offsetField = Ext.create('Ext.form.Number', {
            fieldLabel: me.snippets.offset,
            name: 'offset',
            hidden: true,
            anchor: '100%',
            labelWidth: 300
        });

        var toolbar = Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            cls: 'shopware-toolbar',
        /* {if {acl_is_allowed privilege=export}} */
            items: [ '->',
                {
                    text: me.snippets.start,
                    cls: 'primary',
                    formBind: true,
                    handler: function () {
                        var form = this.up('form').getForm();
                        if (!form.isValid()) {
                            return;
                        }

                        var values = form.getValues();
                        var url = '';

                        if (values.type === 'categories') {
                            url = '{url module=backend controller=ImportExport action=exportCategories}';
                        } else if (values.type === 'articles') {
                            url = '{url module=backend controller=ImportExport action=exportArticles}';
                        }

                        form.submit({
                            method: 'GET',
                            url: url
                        });
                    }
                }
            ]
        /* {/if} */
        });

        return Ext.create('Ext.form.Panel', {
            title: me.snippets.titleExportArticlesCategories,
            bodyPadding: 5,
            standardSubmit: true,
            target: 'iframe',
            layout: 'anchor',
            dockedItems: toolbar,
            defaults: {
                anchor: '100%',
                labelWidth: 300
            },
            defaultType: 'textfield',
            items: [
                {
                    xtype: 'combobox',
                    fieldLabel: me.snippets.data,
                    name: 'type',
                    listeners: {
                        'afterrender': function () {
                            this.setValue(this.store.getAt('0').get('id'));
                        },
                        'change': function(view, newValue) {
                            if (newValue === 'articles') {
                                exportVariantsCheckbox.show();
                                exportCustomergroupPricesCheckbox.show();
                                exportTranslationsCheckbox.show();
                                limitField.show();
                                offsetField.show();
                            } else {
                                exportVariantsCheckbox.hide();
                                exportCustomergroupPricesCheckbox.hide();
                                exportTranslationsCheckbox.hide();
                                limitField.hide();
                                offsetField.hide();
                            }
                        }
                    },
                    store: me.getDataComboStore(),

                    forceSelection: true,
                    allowBlank: false,
                    editable: false,
                    mode: 'local',
                    triggerAction: 'all',
                    displayField: 'label',
                    valueField: 'id'
                },
                {
                    xtype: 'combobox',
                    fieldLabel: me.snippets.format,
                    name: 'format',
                    listeners: {
                        'afterrender': function () {
                            this.setValue(this.store.getAt('0').get('id'));
                        }
                    },
                    store: me.getFormatComboStore(),

                    forceSelection: true,
                    allowBlank: false,
                    editable: false,
                    mode: 'local',
                    triggerAction: 'all',
                    displayField: 'label',
                    valueField: 'id'
                },
                exportVariantsCheckbox,
                exportCustomergroupPricesCheckbox,
                exportTranslationsCheckbox,
                limitField,
                offsetField
            ]
        });
    },

    /**
     * @return [Ext.form.Panel]
     */
    getExportMiscForm: function() {
        var me = this;

        var exportVariantsCheckbox = Ext.create('Ext.form.Checkbox', {
            name: 'exportVariants',
            fieldLabel: me.snippets.exportVariants,
            inputValue: 1,
            uncheckedValue: 0,
            hidden: true,
            anchor: '100%',
            labelWidth: 300
        });

        var limitField = Ext.create('Ext.form.Number', {
            fieldLabel: me.snippets.limit,
            name: 'limit',
            hidden: true,
            anchor: '100%',
            labelWidth: 300
        });

        var offsetField = Ext.create('Ext.form.Number', {
            fieldLabel: me.snippets.offset,
            name: 'offset',
            hidden: true,
            anchor: '100%',
            labelWidth: 300
        });

        /* {if {acl_is_allowed privilege=export}} */
        var toolbar = Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            cls: 'shopware-toolbar',
            items: [ '->',
                {
                    text: me.snippets.start,
                    cls: 'primary',
                    formBind: true,
                    handler: function () {
                        var form = this.up('form').getForm();
                        if (!form.isValid()) {
                            return;
                        }

                        var values = form.getValues();
                        var url = '';

                        if (values.type === 'customers') {
                            url = '{url module=backend controller=ImportExport action=exportCustomers}';
                        } else if (values.type === 'instock') {
                            url = '{url module=backend controller=ImportExport action=exportInStock}';
                        } else if (values.type === 'notinstock') {
                            url = '{url module=backend controller=ImportExport action=exportNotInStock}';
                        } else if (values.type === 'newsletter') {
                            url = '{url module=backend controller=ImportExport action=exportNewsletter}';
                        } else if (values.type === 'prices') {
                            url = '{url module=backend controller=ImportExport action=exportPrices}';
                        } else if (values.type === 'images') {
                            url = '{url module=backend controller=ImportExport action=exportArticleImages}';
                        } else {
                            return;
                        }

                        form.submit({
                            method: 'GET',
                            url: url
                        });
                    }
                }
            ]
        });
        /* {/if} */

        return Ext.create('Ext.form.Panel', {
            title: me.snippets.titleExportMisc,
            bodyPadding: 5,
            standardSubmit: true,
            target: 'iframe',
            layout: 'anchor',
        /* {if {acl_is_allowed privilege=export}} */
            dockedItems: toolbar,
        /* {/if} */
            defaults: {
                anchor: '100%',
                labelWidth: 300
            },
            defaultType: 'textfield',
            items: [
                {
                    xtype: 'combobox',
                    fieldLabel: me.snippets.exportType,
                    name: 'type',
                    listeners: {
                        'afterrender': function () {
                            this.setValue(this.store.getAt('0').get('id'));
                        },
                        'change': function(view, newValue) {
                            if (newValue === 'customers' || newValue === 'newsletter' || newValue === 'images') {
                                exportVariantsCheckbox.hide();
                                limitField.hide();
                                offsetField.hide();
                            } else {
                                exportVariantsCheckbox.show();
                                limitField.show();
                                offsetField.show();
                            }
                        }
                    },
                    store: me.getMiscComboStore(),
                    forceSelection: true,
                    allowBlank: false,
                    editable: false,
                    mode: 'local',
                    triggerAction: 'all',
                    displayField: 'label',
                    valueField: 'id'
                },
                exportVariantsCheckbox,
                limitField,
                offsetField
            ]
        });
    },

    /**
     * @return [Ext.form.Panel]
     */
    getImportForm: function() {
        var me = this;

        var toolbar = Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            cls: 'shopware-toolbar',
        /* {if {acl_is_allowed privilege=import}} */
            items: [ '->',
                {
                    text: me.snippets.start,
                    cls: 'primary',
                    formBind: true,
                    handler: function () {
                        var form = this.up('form').getForm();
                        if (!form.isValid()) {
                            return;
                        }

                        form.submit({
                            url: ' {url module=backend controller=ImportExport action=import}',
                            waitMsg: me.snippets.uploading,
                            success: function (fp, o) {
                                Ext.Msg.alert('Result', o.result.message);
                            },
                            failure: function (fp, o) {
                                Ext.Msg.alert('Fehler', o.result.message);
                            }
                        });
                    }
                }
            ]
        /* {/if} */
        });

        var deleteCategoriesComboBox = Ext.create('Ext.form.ComboBox', {
            fieldLabel: 'Delete categories',
            name: 'deleteCategories',
            listeners: {
                'afterrender': function () {
                    this.setValue(this.store.getAt('0').get('id'));
                }
            },
            store: me.getDeleteCategoriesComboStore(),
            forceSelection: true,
            allowBlank: false,
            editable: false,
            mode: 'local',
            triggerAction: 'all',
            displayField: 'label',
            valueField: 'id',
            enabled: false,
            anchor: '100%',
            labelWidth: 300
        });

        var deleteArticlesComboBox = Ext.create('Ext.form.ComboBox', {
            fieldLabel: 'Delete Articles',
            name: 'deleteArticles',
            listeners: {
                'afterrender': function () {
                    this.setValue(this.store.getAt('0').get('id'));
                }
            },
            store: me.getDeleteArticlesComboStore(),
            forceSelection: true,
            allowBlank: false,
            editable: false,
            mode: 'local',
            triggerAction: 'all',
            displayField: 'label',
            valueField: 'id',
            enabled: false,
            anchor: '100%',
            labelWidth: 300
        });

        return Ext.create('Ext.form.Panel', {
            xtype: 'form',
            title: me.snippets.titleImport,
            bodyPadding: 5,
            layout: 'anchor',
            dockedItems: toolbar,
            defaults: {
                anchor: '100%',
                labelWidth: 300
            },
            items: [
                {
                    xtype: 'combobox',
                    fieldLabel: me.snippets.data,
                    name: 'type',
                    store: me.getImportComboStore(),
                    emptyText: me.snippets.choose,
                    forceSelection: true,
                    allowBlank: false,
                    editable: false,
                    mode: 'local',
                    triggerAction: 'all',
                    displayField: 'label',
                    valueField: 'id',
                    listeners: {
                        'change': function (field, newValue) {
                        }
                    }
                },
                {
                    xtype: 'filefield',
                    emptyText: me.snippets.choose,
                    buttonText:  me.snippets.chooseButton,
                    name: 'file',
                    fieldLabel: me.snippets.file,
                    allowBlank: false
                }
            ]
        });
    },

    /**
     * Creates store object used for the typ column
     *
     * @return [Ext.data.SimpleStore]
     */
    getFormatComboStore: function() {
        return new Ext.data.SimpleStore({
            fields: ['id', 'label'],
            data: [
                ['csv', 'CSV'],
                ['excel', 'Excel'],
                ['xml', 'XML']
            ]
        });
    },

    /**
     * Creates store object used for the typ column
     *
     * @return [Ext.data.SimpleStore]
     */
    getDataComboStore: function() {
		var me = this;
        return new Ext.data.SimpleStore({
            fields: ['id', 'label'],
            data: [
                ['categories', me.snippets.categories],
                ['articles', me.snippets.articles]
            ]
        });
    },

    /**
     * Creates store object used for the typ column
     *
     * @return [Ext.data.SimpleStore]
     */
    getMiscComboStore: function() {
		var me = this;
        return new Ext.data.SimpleStore({
            fields: ['id', 'label'],
            data: [
                ['customers', me.snippets.customers],
                ['instock', me.snippets.in_stock],
                ['notinstock', me.snippets.not_in_stock],
                ['prices', me.snippets.prices],
                ['images', me.snippets.article_images],
                ['newsletter', me.snippets.newsletter]
            ]
        });
    },

    /**
     * Creates store object used for the typ column
     *
     * @return [Ext.data.SimpleStore]
     */
    getImportComboStore: function() {
		var me = this;
        return new Ext.data.SimpleStore({
            fields: ['id', 'label'],
            data: [
                ['customers', me.snippets.customers],
                ['instock', me.snippets.in_stock],
                ['newsletter', me.snippets.newsletter],
                ['prices', me.snippets.prices],
                ['articles', me.snippets.article],
                ['images', me.snippets.article_images],
                ['categories', me.snippets.categories]
            ]
        });
    },

    /**
     * Creates store object used for the typ column
     *
     * @return [Ext.data.SimpleStore]
     */
    getDeleteCategoriesComboStore: function() {
		var me = this;
        return new Ext.data.SimpleStore({
            fields: ['id', 'label'],
            data: [
                [0, me.snippets.neither],
                [1, me.snippets.all_before_import],
                [2, me.snippets.not_imported],
                [3, me.snippets.empty]
            ]
        });
    },

    /**
     * Creates store object used for the typ column
     *
     * @return [Ext.data.SimpleStore]
     */
    getDeleteArticlesComboStore: function() {
		var me = this;
        return new Ext.data.SimpleStore({
            fields: ['id', 'label'],
            data: [
                [0, me.snippets.neither],
                [1, me.snippets.all_before_import],
                [2, me.snippets.not_imported]
            ]
        });
    }
});
//{/block}
