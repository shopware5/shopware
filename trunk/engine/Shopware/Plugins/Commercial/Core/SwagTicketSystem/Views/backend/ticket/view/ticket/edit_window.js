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
 * @package    Ticket
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stephan Pohl
 * @author     $Author$
 */

//{namespace name=backend/ticket/main}
//{block name="backend/ticket/view/ticket/edit_window"}
Ext.define('Shopware.apps.Ticket.view.ticket.EditWindow', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend:'Enlight.app.Window',

    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'ticket-ticket-edit-window',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.ticket-ticket-edit-window',

    /**
     * Set no border for the window
     * @boolean
     */
    border:false,

    /**
     * Layout definition for the component.
     * @object
     */
    layout: {
        type: 'vbox',
        align : 'stretch',
        pack  : 'start'
    },

    /**
     * True to automatically show the component upon creation.
     * @boolean
     */
    autoShow:true,

    /**
     * Let's the view add a scrollbar dynamically based on the height of the window.
     * @boolean
     */
    autoScroll: true,

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
    maximizable:false,

    /**
     * True to display the 'minimize' tool button and allow the user to minimize the window, false to hide the button and disallow minimizing the window.
     * @boolean
     */
    minimizable:false,
    /**
     * A flag which causes the object to attempt to restore the state of internal properties from a saved state on startup.
     */
    stateful:false,

    /**
     * The unique id for this object to use for state management purposes.
     */
    stateId:'shopware-ticket-ticket-edit-window',

    /**
     * Title of the window.
     * @string
     */
    title: '{s name=edit_ticket_window_title}Ticket system - Edit ticket{/s}',

    /**
     * Padding for the "body"-element of the window.
     * @integer
     */
    bodyPadding: 10,

    /**
     * Default configuration for the fieldset.
     * @object
     */
    formDefaults: { labelWidth: 200, anchor: '100%' },

    /**
     * Initializes the component and the
     * main tab panel.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.addEvents('answerTicket');

        // Remove the extra parameter from the submission store
        me.on('destory', function() {
            me.submissionStore.getProxy().extraParams = {};
            me.submissionStore.load();
        }, me);

        me.submissionDetailStore.getProxy().extraParams = { onlyCustomSubmissions: true };

        me.formPanel = Ext.create('Ext.form.Panel', {
            unstyled: false,
            border: false,
            bodyBorder: 0,
            bodyStyle: 'background: #F9FAFA !important',
            style: 'background: #F9FAFA !important',
            items: me.createAnswerContainer()
        });



        me.items = [
            me.createMessageContainer(),
            me.formPanel,
            me.createSupportHistoryGridPanel()
        ];

        me.callParent(arguments);
    },

    /**
     * Creates an fieldset which contains the subject and message
     * of the ticket.
     *
     * @public
     * @return [object] Ext.form.FieldSet
     */
    createMessageContainer: function() {
        var me = this;

        var msgSubject = Ext.create('Ext.form.field.Text', {
            value: me.record.get('subject'),
            fieldLabel: '{s name=edit_window/label/subject}Subject{/s}',
            readOnly: true,
            labelWidth: 180
        });

        var msgText = Ext.create('Ext.form.field.TextArea', {
            value: me.record.get('message'),
            fieldLabel: '{s name=edit_window/label/message}Message{/s}',
            readOnly: true,
            labelWidth: 180
        });

        return Ext.create('Ext.form.FieldSet', {
            title: me.record.get('ticketTypeName') + ' #' + me.record.get('id'),
            defaults: me.formDefaults,
            layout: 'anchor',
            items: [ msgSubject, msgText ]
        });
    },

    /**
     * Creates an fieldset to answer the ticket system.
     *
     * @public
     * @return [object] Ext.form.FieldSet
     */
    createAnswerContainer: function() {
        var me = this;

        var hiddenId = Ext.create('Ext.form.field.Hidden', {
            value: me.record.get('id'),
            name: 'id'
        });

        var hiddenIsHTML = Ext.create('Ext.form.field.Hidden', {
            value: me.record.get('isHTML'),
            name: 'isHTML'
        });

        var localeField = Ext.create('Ext.form.field.ComboBox', {
            fieldLabel: '{s name=edit_window/label/locale}Locale{/s}',
            allowBlank: false,
            displayField: 'name',
            valueField: 'id',
            name: 'shop',
            store: me.localeStore,
            queryMode: 'remote',
            emptyText: '{s name=toolbar/combo_empty}Please select...{/s}',
            labelWidth: 180,
            listeners: {
                scope: me,
                change: function(field, shopId) {
                    shopId = ~~(1 * shopId);
                    me.submissionDetailStore.getProxy().extraParams.locale = shopId;
                    me.submissionDetailStore.load();
                }
            }
        });

        var supportWay = Ext.create('Ext.form.RadioGroup', {
            fieldLabel: '{s name=edit_window/label/support_way}Type of support{/s}',
            labelWidth: 180,
            allowBlank: false,
            items: [{
                boxLabel: '{s name=edit_window/label/support_way/administration}Via the support management{/s}',
                name: 'onlyEmailAnswer',
                checked: true,
                inputValue: '0'
            }, {
                boxLabel: '{s name=edit_window/label/support_way/email}Directly as an eMail to the customer{/s}',
                name: 'onlyEmailAnswer',
                inputValue: '1'
            }]
        });

        var customerMail = Ext.create('Ext.form.field.Text', {
            fieldLabel: '{s name=edit_window/label/customer_mail}Customer eMail address{/s}',
            name: 'email',
            allowBlank: false,
            value: me.record.get('email'),
            vtype: 'email',
            labelWidth: 180
        });

        var ccMail = Ext.create('Ext.form.field.Text', {
            fieldLabel: '{s name=edit_window/label/cc_mail}Cc{/s}',
            name: 'cc',
            allowBlank: true,
            vtype: 'email',
            labelWidth: 180,
            emptyText: '{s name=edit_window/empty/sender_address}youraddress@example.com{/s}'
        });

        var loadSubmission = Ext.create('Ext.form.field.ComboBox', {
            fieldLabel: '{s name=edit_window/label/load_submission}Load submission{/s}',
            allowBlank: true,
            valueField: 'id',
            displayField: 'description',
            store: me.submissionDetailStore,
            name: 'submission',
            emptyText: '{s name=toolbar/combo_empty}Please select...{/s}',
            labelWidth: 180,
            listeners: {
                scope: me,
                change: function(field, newValue) {
                    var record = me.submissionDetailStore.getById(~~(1 * newValue));

                    if(!record) {
                        Shopware.Notification.createGrowlMessage('{s name=window_title}Ticket system{/s}', '{s name=edit_window/error/submission_failed}The selected submission could not be loaded successfully.{/s}');
                        return false;
                    }
                    subject.setValue(record.get('subject'));
                    senderAddress.setValue(record.get('fromMail'));
                    senderName.setValue(record.get('fromName'));
                    hiddenIsHTML.setValue(record.get('isHTML'));

                    if(record.get('isHTML')) {
                        message.setValue(record.get('contentHTML'));
                    }
                    else {
                        var content = record.get('content');
                        content = content.split("\r\n").join("<br />");
                        content = content.split("\n").join("<br />");
                        message.setValue(content);
                    }
                }
            }
        });

        var statusCombo = Ext.create('Ext.form.field.ComboBox', {
            fieldLabel: '{s name=edit_window/label/status_after_answering}Status after answering{/s}',
            name: 'status',
            allowBlank: false,
            valueField: 'id',
            displayField: 'description',
            store: me.statusStore,
            emptyText: '{s name=toolbar/combo_empty}Please select...{/s}',
            labelWidth: 180
        });

        var senderAddress = Ext.create('Ext.form.field.Text', {
            fieldLabel: '{s name=edit_window/label/sender_address}Your sender address{/s}',
            allowBlank: false,
            name: 'senderAddress',
            emptyText: '{s name=edit_window/empty/sender_address}youraddress@example.com{/s}',
            margin: '0 10 0 0',
            labelWidth: 180
        });

        var senderName = Ext.create('Ext.form.field.Text', {
            fieldLabel: '{s name=edit_window/label/sender_name}Your sender name{/s}',
            allowBlank: false,
            name: 'senderName',
            emptyText: '{s name=edit_window/empty/sender_name}John Doe{/s}',
            margin: '0 0 0 10',
            labelWidth: 130
        });

        var senderContainer = Ext.create('Ext.container.Container', {
            layout: 'column',
            margin: '0 0 8 0',
            defaults: { labelWidth: 180, anchor: '100%', columnWidth: .5 },
            items: [ senderAddress, senderName ]
        });

        var subject = Ext.create('Ext.form.field.Text', {
            fieldLabel: '{s name=edit_window/label/subject}Subject{/s}',
            allowBlank: false,
            name: 'subject',
            value: '{s name=edit_window/value/subject}Reply to your ticket{/s}' + ' #' + me.record.get('id'),
            labelWidth: 180
        });

        var message = Ext.create('Shopware.form.field.TinyMCE', {
            fieldLabel: '{s name=edit_window/label/message}Your message{/s}',
            anchor: '100%',
            allowBlank: false,
            name: 'message',
            height: 250,
            width: '100%',
            labelWidth: 180
        });

        var sendContainer = Ext.create('Ext.container.Container', {
            style: 'position: relative',
            height: 30,
            items: [{
                xtype: 'button',
                text: '{s name=edit_window/button/submit}Submit{/s}',
                cls: 'primary',
                style: 'position: absolute; right: 0; top: 0;',
                handler: function(btn) {
                    me.fireEvent('answerTicket', btn, me);
                }
            }]
        });


        //load default values
        subject.setValue(me.defaultSubmission.get('subject'));
        senderAddress.setValue(me.defaultSubmission.get('fromMail'));
        senderName.setValue(me.defaultSubmission.get('fromName'));
        hiddenIsHTML.setValue(me.defaultSubmission.get('isHTML'));

        if(me.defaultSubmission.get('isHTML')) {
            message.setValue(me.defaultSubmission.get('contentHTML'));
        }
        else {
            message.setValue(me.defaultSubmission.get('content'));
        }


        return Ext.create('Ext.form.FieldSet', {
            title: '{s name=edit_window/answer_ticket}Answer ticket{/s}',
            layout: 'anchor',
            defaults: { labelWidth: 180, anchor: '100%' },
            items: [
                localeField, supportWay, customerMail, ccMail, loadSubmission, statusCombo, senderContainer, subject,
                message, sendContainer, hiddenId, hiddenIsHTML
            ]
        });
    },

    /**
     * Creates the support history grid.
     *
     * @public
     * @return [object] Ext.grid.Panel
     */
    createSupportHistoryGridPanel: function() {
        var me = this;

        return Ext.create('Ext.grid.Panel', {
            store: me.historyStore,
            height: 200,
            margin: '10 0 0',
            title: '{s name=edit_window/title/ticket_history}Ticket history{/s}',
            columns: me.createSupportHistoryColumns(),
            plugins: [{
                ptype: 'rowexpander',
                rowBodyTpl: '{literal}{message}{/literal}'
            }]
        });
    },

    /**
     * Creates the column model for the support history grid.
     *
     * @public
     * @return [array] generated columns
     */
    createSupportHistoryColumns: function() {
        var me = this;

        return [{
            dataIndex: 'receipt',
            sortable: false,
            header: '{s name=edit_window/columns/receipt}Date{/s}',
            flex: 1,
            renderer: function(value) {
                return Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, 'H:i:s');
            }
        }, {
            dataIndex: 'swUser',
            sortable: false,
            header: '{s name=edit_window/columns/sender_name}Sender{/s}',
            renderer: me.emailRenderer,
            flex: 1
        }, {
            dataIndex: 'subject',
            sortable: false,
            header: '{s name=edit_window/columns/subject}Subject{/s}',
            flex: 2,
            renderer: function(value) {
                return '<strong>' + value + '</strong>';
            }
        }]
    },

    /**
     * renders the name or the eMail adress
     *
     * @param value
     * @param meta
     * @param record
     * @return string
     */
    emailRenderer: function(value, meta, record) {
        var email = record.get('email');
        if(!email.length) {
            return value;
        }
        if(!value) {
            return '<a href="mailto:' + email + '">' + email + '<a/>';
        }
        return value;
    }

});
//{/block}