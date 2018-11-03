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
 * @package    Order
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/order/main}

/**
 * Shopware UI - Order detail page
 *
 * todo@all: Documentation
 */
//{block name="backend/order/view/detail/configuration"}
Ext.define('Shopware.apps.Order.view.detail.Configuration', {

    /**
     * The configuration is an extension of the form panel
     */
    extend: 'Ext.form.Panel',

    /**
     * The configuration panel uses the column layout
     */
    layout: 'column',

    cls: 'shopware-form',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.order-configuration-panel',

    /**
     * Default configuration for the left and right container
     * @object
     */
    formDefaults: {
        labelWidth: 155,
        style: 'margin-bottom: 10px !important;',
        labelStyle: 'font-weight: 700;',
        anchor: '100%'
    },

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        vatId: {
            label: '{s name=configuration/vat_id_label}Customer VAT ID{/s}',
            empty: '{s name=configuration/vat_id_empty}Not filed{/s}'
        },
        documentType:  '{s name=configuration/document_type}Document type{/s}',
        comment: {
            label:  '{s name=configuration/comment_label}Document comment{/s}',
            support:  '{s name=configuration/comment_support}Changes are not saved permanently{/s}'
        },
        voucher:  '{s name=configuration/voucher}Voucher{/s}',
        taxFree:  '{s name=configuration/tax_free}Tax free{/s}',
        invoiceNumber:  '{s name=configuration/invoice_number}Invoice number{/s}',
        deliveryDate:  '{s name=configuration/delivery_date}Delivery date{/s}',
        displayDate:  '{s name=configuration/display_date}Displayed date{/s}',
        buttons: {
            preview:  '{s name=configuration/preview}Preview{/s}',
            reset: '{s name=configuration/reset}Reset settings{/s}',
            create: '{s name=configuration/create}Create document{/s}'
        },
        form: '{s name=configuration/form_title}Configuration{/s}'
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
    initComponent: function() {
        var me = this;

        me.title = me.snippets.form;
        me.items = [ me.createLeftItems(), me.createRightItems() ];
        me.buttons = me.createButtons();
        me.callParent(arguments);
        me.loadDefaultConfiguration();
    },

    /**
     * Registers the custom component events.
     * @return void
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * Event will be fired when the user clicks the "create document" button
             * which is placed in the document tab on bottom.
             *
             * @event
             * @param [Ext.data.Model]          The record of the detail page (Shopware.apps.Order.model.Order)
             * @param [Ext.data.Model]          The configuration record of the document form (Shopware.apps.Order.model.Configuration)
             * @param [Ext.container.Container] me
             */
            'createDocument',

            /**
             * Event will be fired when the user clicks the "reset" button
             * which is placed in the document tab on bottom.
             *
             * @event
             * @param [Ext.container.Container] me
             */
            'resetConfiguration',

            /**
             * Event will be fired when the user clicks the "preview" button
             * which is placed in the document tab on bottom.
             *
             * @event
             * @param [Ext.data.Model]          The record of the detail page (Shopware.apps.Order.model.Order)
             * @param [Ext.data.Model]          The configuration record of the document form (Shopware.apps.Order.model.Configuration)
             * @param [Ext.container.Container] me
             */
            'documentPreview'
        );
    },

    /**
     * Creates the left form fields for the document creation.
     * @return Ext.container.Container
     */
    createLeftItems: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            columnWidth: 0.5,
            layout: 'anchor',
            padding: 10,
            defaults: me.formDefaults,
            items: [
                {
                    xtype: 'combobox',
                    queryMode: 'local',
                    triggerAction: 'all',
                    fieldLabel: me.snippets.documentType,
                    store: me.documentTypesStore,
                    displayField: 'name',
                    valueField: 'id',
                    name: 'documentType'
                },
                {
                    xtype: 'textfield',
                    fieldLabel: me.snippets.invoiceNumber,
                    name: 'invoiceNumber'
                },
                {
                    xtype: 'datefield',
                    fieldLabel: me.snippets.displayDate,
                    submitFormat: 'd.m.Y',
                    value: new Date(),
                    name: 'displayDate'
                },
                {
                    xtype: 'datefield',
                    fieldLabel: me.snippets.deliveryDate,
                    submitFormat: 'd.m.Y',
                    name: 'deliveryDate'
                },
                {
                    xtype: 'displayfield',
                    name: 'vatId',
                    fieldLabel: me.snippets.vatId.label,
                    emptyText: me.snippets.vatId.empty
                },
                {
                    xtype: 'checkbox',
                    fieldLabel: me.snippets.taxFree,
                    name: 'taxFree',
                    uncheckedValue:0,
                    inputValue:1
                }
            ]
        });

    },

    /**
     * Loads the default configuration.
     * @return void
     */
    loadDefaultConfiguration: function() {
        var me = this, vatId,
            billing = me.record.getBilling();

        if(billing == null || billing.first() == null) {
            vatId = me.snippets.vatId.empty;
        }else{
            billing = billing.first();
            if (Ext.isEmpty(billing.get('vatId'))) {
                vatId = me.snippets.vatId.empty;
            } else {
                vatId = billing.get('vatId');
            }
        }

        me.defaultConfig = Ext.create('Shopware.apps.Order.model.Configuration', {
            displayDate: new Date(),
            documentType: 1,
            voucher: null,
            vatId: vatId,
            orderId: me.record.get('id'),
            taxFree: me.record.get('taxFree')
        });

        me.loadRecord(me.defaultConfig);
    },

    /**
     * Creates the left form fields for the document creation.
     * @return Ext.container.Container
     */
    createRightItems: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            columnWidth: 0.5,
            layout: 'anchor',
            padding: 10,
            defaults: me.formDefaults,
            items: [{
                xtype: 'textarea',
                fieldLabel: me.snippets.comment.label,
                supportText: me.snippets.comment.support,
                name: 'docComment'
            }, {
                xtype: 'combobox',
                fieldLabel: me.snippets.voucher,
                name: 'voucher',
                store: Ext.create('Shopware.apps.Order.store.Voucher'),
                displayField: 'display',
                valueField: 'id'
            }]
        });
    },

    /**
     * Creates the form buttons create, reset and preview.
     * @return array
     */
    createButtons: function() {
        var me = this;

        me.createButton = Ext.create('Ext.button.Button', {
            text: me.snippets.buttons.create,
            action: 'create-document',
            cls:'primary',
            handler: function() {
                var config = Ext.create('Shopware.apps.Order.model.Configuration');

                me.getForm().updateRecord(config);
                me.fireEvent('createDocument', me.record, config, me);
            }
        });

        me.resetButton = Ext.create('Ext.button.Button', {
            text: me.snippets.buttons.reset,
            action: 'reset-config',
            cls: 'secondary',
            handler: function() {
                me.fireEvent('resetConfiguration', me, me.defaultConfig);
            }
        });

        me.previewButton = Ext.create('Ext.button.Button', {
            text: me.snippets.buttons.preview,
            action: 'document-preview',
            cls: 'secondary',
            handler: function() {
                var values = me.getValues(),
                    config = Ext.create('Shopware.apps.Order.model.Configuration', values);

                me.fireEvent('documentPreview', me.record, config, me);
            }
        });

        return [
            me.resetButton,
            /*{if {acl_is_allowed privilege=update}}*/
                me.previewButton,
                me.createButton
            /*{/if}*/
        ];
    }

});
//{/block}
