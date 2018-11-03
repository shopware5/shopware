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
 * Shopware UI - Order batch window
 *
 * todo@all: Documentation
 */
//{block name="backend/order/view/batch/form"}
Ext.define('Shopware.apps.Order.view.batch.Form', {

    /**
     * Define that the additional information is an Ext.panel.Panel extension
     * @string
     */
    extend: 'Ext.form.Panel',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.batch-settings-panel',

    /**
     * An optional extra CSS class that will be added to this component's Element.
     */
    cls: Ext.baseCSSPrefix + 'batch-settings-panel',

    autoScroll: true,

    layout: {
        align: 'stretch',
        type: 'vbox'
    },

    bodyPadding: 10,

    /**
     * Contains all snippets for the view component
     */
    snippets: {
        info: '{s name=settings/info}Caution: If you have selected orders with an already existing receipt, these will be regenerated.{/s}',
        mode: {
            label: '{s name=settings/mode}Mode{/s}',
            override: '{s name=settings/override}Recreate all documents{/s}',
            notExist: '{s name=settings/not_exist}Create only not existing documents{/s}'
        },
        documentType: '{s name=settings/document_type}Document type{/s}',
        paymentStatus: '{s name=settings/payment_status}Payment status{/s}',
        orderStatus: '{s name=settings/order_status}Order status{/s}',
        mail: '{s name=settings/auto_send}Send emails automatically{/s}',
        generate: '{s name=settings/process}Process changes{/s}',
        gridTitle: '{s name=settings/grid_title}Selected orders{/s}',
        settingsFieldSetLabel: '{s name=settings/field_set_label}Settings to generate{/s}',
        oneDocument: '{s name=settings/one_document}Create single document{/s}',
        selectOption: '{s name="settings/select_option"}Please select{/s}',
        invalidOption: '{s name="settings/invalid_option"}Invalid Option{/s}',
        errorTitle: '{s name=batch/error/title}Error{/s}',
        mustSelectDocument: '{s name=settings/must_select_document_type}Please select a document type first{/s}'
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
    initComponent: function () {
        var me = this;

        me.registerEvents();

        me.modeData = [
            [0, me.snippets.mode.override],
            [1, me.snippets.mode.notExist]
        ];
        me.items = [
            me.createInfoContainer(),
            me.createSettingsContainer(),
            me.createOrderGrid()
        ];

        me.addCls('layout-expert');
        me.callParent(arguments);
    },

    registerEvents: function () {
        this.addEvents(
            /**
             * Event will be fired when the user clicks the "generate documents" button which is
             * displayed within the form field set.
             *
             * @Event
             * @param { Ext.form.Panel } - This component
             */
            'processChanges'
        );
    },

    /**
     * Creates the info container which is displayed on top of the form.
     *
     * @return Ext.container.Container
     */
    createInfoContainer: function () {
        var me = this;

        return Ext.create('Ext.container.Container', {
            html: me.snippets.info,
            style: 'color: #999; font-style: italic; margin: 0 0 15px 0;'
        });
    },

    /**
     * Creates and returns the form fields
     *
     * @returns Array An array containing the form fields.
     */
    createFormFields: function () {
        var me = this;

        return [
            me.createDocumentTypeField(),
            me.createModeField(),
            me.createOrderStatusField(),
            me.createPaymentStatusField(),
            me.createAutoSendEmailField(),
            me.createAddAttachmentsField(),
            me.createSingleDocumentField(),
            me.createProcessButtonField()
        ];
    },

    /**
     * Creates the settings container
     *
     * @returns Ext.form.FieldSet
     */
    createSettingsContainer: function () {
        var me = this;

        return Ext.create('Ext.form.FieldSet', {
            title: me.snippets.settingsFieldSetLabel,
            layout: 'anchor',
            defaults: {
                labelWidth: 155,
                xtype: 'combobox',
                anchor: '100%'
            },
            items: me.createFormFields()
        });
    },

    /**
     * Creates the "Document type" field
     *
     * @returns Ext.form.field.ComboBox
     */
    createDocumentTypeField: function () {
        var me = this,
            store = Ext.create('Shopware.apps.Order.store.DocType');

        return Ext.create('Ext.form.field.ComboBox', {
            name: 'documentType',
            fieldLabel: me.snippets.documentType,
            store: store,
            editable: true,
            typeAhead: true,
            minChars: 2,
            displayField: 'name',
            valueField: 'id',
            emptyText: me.snippets.selectOption,
            validateOnBlur: true,
            allowBlank: true,
            validator: function (selectedValue) {
                var addAttachmentsField = me.getForm().findField('addAttachments'),
                    singleDocField = me.getForm().findField('createSingleDocument'),
                    autoSendMailField = me.getForm().findField('autoSendMail'),
                    selectedValLowered = typeof selectedValue === 'string' ? selectedValue.toLowerCase() : '',
                    recordFound = true;

                // If no value is selected, disable document-related fields and validate the form nonetheless
                if (!selectedValue || !selectedValue.length) {
                    addAttachmentsField.disable();
                    singleDocField.disable();
                    return recordFound;
                }
                // Validate the typed/selected option. Verify that it is indeed a store option
                recordFound = store.data.findBy(function (item) {
                    return item.data.name.toLowerCase() === selectedValLowered;
                });

                if (recordFound){
                    // Only enable the addAttachments field when E-Mails can be sent
                    if (autoSendMailField.getValue()) {
                        addAttachmentsField.enable();
                    }
                    singleDocField.enable();
                    return true;
                }

                addAttachmentsField.disable();
                singleDocField.disable();

                return me.snippets.invalidOption;
            },
            listeners: {
                scope: me,
                afterrender: this.disableAutocompleteAndSpellcheck,
                change: me.enableMailField
            }
        });
    },

    /**
     * Creates the "Mode" field
     *
     * @returns Ext.form.field.ComboBox
     */
    createModeField: function () {
        var me = this,
            store = new Ext.data.SimpleStore({
                fields: [
                    'value',
                    'description'
                ],
                data: me.modeData
            });

        return Ext.create('Ext.form.field.ComboBox', {
            name: 'mode',
            triggerAction: 'all',
            fieldLabel: me.snippets.mode.label,
            editable: true,
            typeAhead: true,
            minChars: 2,
            emptyText: me.snippets.selectOption,
            store: store,
            snippets: me.snippets,
            displayField: 'description',
            valueField: 'value',
            validateOnBlur: true,
            validator: me.validateComboboxSelection,
            listeners: {
                scope: me,
                afterrender: this.disableAutocompleteAndSpellcheck
            }
        });
    },

    /**
     * Creates the "Order status" field
     *
     * @returns Ext.form.field.ComboBox
     */
    createOrderStatusField: function () {
        var me = this,
            store = me.orderStatusStore;

        return Ext.create('Ext.form.field.ComboBox', {
            name: 'orderStatus',
            triggerAction: 'all',
            queryMode: 'local',
            editable: true,
            fieldLabel: me.snippets.orderStatus,
            store: store,
            displayField: 'description',
            valueField: 'id',
            emptyText: me.snippets.selectOption,
            mode: 'local',
            validateOnBlur: true,
            snippets: me.snippets,
            validator: me.validateComboboxSelection,
            listeners: {
                scope: me,
                afterrender: this.disableAutocompleteAndSpellcheck,
                change: me.enableMailField
            }
        });
    },

    /**
     * Enables the "Send emails automatically" field under certain circumstances
     *
     * @param el
     */
    enableMailField: function (el) {
        var me = this,
            autoSendMailField = me.getForm().findField('autoSendMail'),
            orderStatusField = me.getForm().findField('orderStatus'),
            documentTypeField = me.getForm().findField('documentType'),
            paymentStatusField = me.getForm().findField('paymentStatus'),
            orderStatusValid = orderStatusField.getValue() !== null && orderStatusField.isValid(),
            paymentStatusValid = paymentStatusField.getValue() !== null && paymentStatusField.isValid(),
            documentTypeValid = documentTypeField.getValue() !== null && documentTypeField.isValid(),
            statusChanged = orderStatusValid || paymentStatusValid;

        if (!statusChanged && !documentTypeValid) {
            autoSendMailField.setValue(false).disable();
            return;
        }

        autoSendMailField.enable();
    },

    /**
     * Creates the "Payment status field"
     *
     * @returns Shopware.form.field.PagingComboBox
     */
    createPaymentStatusField: function () {
        var me = this,
            store = me.paymentStatusStore;

        return Ext.create('Ext.form.field.ComboBox', {
            name: 'paymentStatus',
            triggerAction: 'all',
            queryMode: 'local',
            editable: true,
            fieldLabel: me.snippets.paymentStatus,
            store: store,
            displayField: 'description',
            valueField: 'id',
            emptyText: me.snippets.selectOption,
            mode: 'local',
            validateOnBlur: true,
            snippets: me.snippets,
            validator: me.validateComboboxSelection,
            listeners: {
                scope: me,
                afterrender: this.disableAutocompleteAndSpellcheck,
                change: me.enableMailField
            }
        });
    },

    /**
     * Validates combo box selection
     *
     * @param selectedValue
     */
    validateComboboxSelection: function (selectedValue) {
        var me = this,
            selectedValLowered = typeof selectedValue === 'string' ? selectedValue.toLowerCase() : '',
            recordFound = true,
            store = me.store;

        // If no option is selected, this is also considered as valid
        if (!selectedValue || !selectedValue.length || selectedValue === me.snippets.selectOption) {
            return recordFound;
        }
        // Validate the typed/selected option. Verify that is indeed a store option
        recordFound = store.data.findBy(function (item) {
            return item.data.description.toLowerCase() === selectedValLowered;
        });

        if (recordFound) {
            return true;
        }

        return me.snippets.invalidOption;
    },

    /**
     * Disables autocomplete and spellcheck on an element
     *
     * @return undefined
     */
    disableAutocompleteAndSpellcheck: function (el) {
        el.inputEl.set({
            autocomplete: 'off',
            spellcheck: 'false'
        })
    },

    /**
     * Create "Send emails automatically" checkbox.
     *
     * @returns Ext.form.field.Checkbox
     */
    createAutoSendEmailField: function () {
        var me = this;

        return Ext.create('Ext.form.field.Checkbox', {
            name: 'autoSendMail',
            fieldLabel: me.snippets.mail,
            inputValue: true,
            checked: true,
            uncheckedValue: false,
            disabled: true,
            listeners: {
                scope: me,
                change: function (el) {
                    var me = this,
                        addAttachmentsField = me.getForm().findField('addAttachments'),
                        documentTypeField = me.getForm().findField('documentType');

                    if (el.value === true && documentTypeField.getValue() !== null && documentTypeField.isValid()) {
                        addAttachmentsField.enable();
                        return;
                    }

                    addAttachmentsField.disable();
                },
                disable: function (el) {
                    var me = this,
                        attachmentsField = me.getForm().findField('addAttachments');

                    attachmentsField.setValue(false).disable();
                }
            }
        });
    },

    /**
     * Create "Add documents/attachments to email" checkbox.
     *
     * When no document type has been selected, the field will show a tooltip indicating that
     * in order to activate this checkbox, a document type must be selected.
     *
     * @returns Ext.form.field.Checkbox
     */
    createAddAttachmentsField: function () {
        var me = this;

        return Ext.create('Ext.form.field.Checkbox', {
            name: 'addAttachments',
            fieldLabel: '{s name=attachment/batch/add}{/s}',
            disabled: true,
            inputValue: true,
            uncheckedValue: false,
            listeners: {
                scope: me,
                disable: function (el) {
                    this.createTooltip(el, this.snippets.mustSelectDocument);
                },
                enable: function (el) {
                    this.removeTooltip(el);
                }
            }
        });
    },

    /**
     * Create "Single Document" checkbox.
     *
     * When no document type has been selected, the field will show a tooltip indicating that
     * in order to activate this checkbox, a document type must be selected.
     *
     * @returns Ext.form.field.Checkbox
     */
    createSingleDocumentField: function () {
        var me = this;

        return Ext.create('Ext.form.field.Checkbox', {
            name: 'createSingleDocument',
            fieldLabel: me.snippets.oneDocument,
            disabled: true,
            inputValue: true,
            uncheckedValue: false,
            listeners: {
                scope: me,
                disable: function (el) {
                    this.createTooltip(el, this.snippets.mustSelectDocument);
                },
                enable: function (el) {
                    this.removeTooltip(el);
                }
            }
        });
    },

    /**
     * Creates tooltip for a form field
     *
     * @param el [Ext.form.Field]
     * @param text [string] Text to show in the tooltip
     * @returns Ext.tip.QuickTip
     */
    createTooltip: function (el, text) {
        return Ext.tip.QuickTipManager.register({
            target: el.el,
            name: el.name + '-tooltip',
            title: '',
            text: text,
            trackMouse: false
        });
    },

    /**
     * Removes an Ext.Quicktip from an element
     *
     * @param el [Ext.form.Field]
     */
    removeTooltip: function (el) {
        return Ext.QuickTips.unregister(el.id);
    },

    /**
     * Creates the form submission button
     *
     * @returns Ext.button.Button
     */
    createProcessButtonField: function () {
        var me = this;

        return Ext.create('Ext.button.Button', {
            margin: '15 0',
            cls: 'primary',
            text: me.snippets.generate,
            handler: function () {
                me.fireEvent('processChanges', me)
            }
        });
    },

    /**
     * Creates the grid which contains all selected orders and display if the mail is already sent and the current order
     * and payment status.
     *
     * @returns Shopware.apps.Order.view.batch.List
     */
    createOrderGrid: function () {
        var me = this;

        var store = Ext.create('Ext.data.Store', {
            model: 'Shopware.apps.Order.model.Order',
            data: me.records
        });

        return Ext.create('Shopware.apps.Order.view.batch.List', {
            flex: 1,
            title: me.snippets.gridTitle,
            store: store
        });
    }
});
//{/block}
