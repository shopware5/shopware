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
 */

//{namespace name=backend/index/view/widgets}

/**
 * todo@all: Documentation
 */
//{block name="backend/index/view/merchant/window"}
Ext.define('Shopware.apps.Index.view.merchant.Window', {
    extend: 'Enlight.app.Window',
    alias : 'widget.merchant-window',
    width: 600,
    height: 500,
    stateful: true,
    stateId: 'shopware-merchant-window',
    layout: 'fit',

    /**
     * Snippets for the window.
     *
     * @object
     */
    snippets: {
        labels: {
            recipient_mail: '{s name=merchant/window/label/recipient_mail}Recipient mail address{/s}',
            sender_name: '{s name=merchant/window/label/sender_name}Sender {/s}',
            sender_mail: '{s name=merchant/window/label/sender_mail}Sender mail address{/s}',
            subject: '{s name=merchant/window/label/subject}Subject{/s}',
            message: '{s name=merchant/window/label/message}Message{/s}'
        },
        buttons: {
            allow_merchant: '{s name=merchant/window/buttons/allow_merchant}Unlock and send confirmation{/s}',
            decline_merchant: '{s name=merchant/window/buttons/decline_merchant}Deny inquiry{/s}'
        },
        messages: {
            success: '{s name=merchant/window/messages/success}The mail was sent successful.{/s}',
            error: '{s name=merchant/window/messages/error}The mail could not be sent.{/s}'
        }
    },

    /**
     * Sets up the ui component
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.items = [ me.createFormPanel() ];
        me.dockedItems = [ me.createActionToolbar() ];

        me.addEvents(
            'allow',
            'decline'
        );

        me.callParent(arguments);

        if(me.record) {
            me.formPanel.loadRecord(me.record);
        }
    },

    createFormPanel: function() {
        var me = this, labels = me.snippets.labels;

        return me.formPanel = Ext.create('Ext.form.Panel', {
            layout: 'anchor',
            url: '{url controller=widgets action=sendMailToMerchant}',
            bodyPadding: 10,
            defaults: {
                labelWidth: 155,
                xtype: 'textfield',
                anchor: '100%'
            },
            items: [{
                xtype: 'hidden',
                name: 'userId'
            },{
                xtype: 'hidden',
                name: 'status'
            }, {
                fieldLabel: labels.recipient_mail,
                name: 'toMail'
            }, {
                fieldLabel: labels.sender_name,
                name: 'fromName'
            }, {
                fieldLabel: labels.sender_mail,
                name: 'fromMail'
            }, {
                fieldLabel: labels.subject,
                name: 'subject'
            }, {
                xtype: 'htmleditor',
                name: 'content',
                fieldLabel: labels.message,
                height: 250
            }]
        });
    },

    createActionToolbar: function() {
        var me = this, buttons = me.snippets.buttons;

        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            cls: 'shopware-toolbar',
            items: ['->', {
                text: (me.mode === 'allow') ? buttons.allow_merchant : buttons.decline_merchant,
                cls: 'primary',
                handler: function() {
                    me.submitFormPanel();
                }
            }]
        })
    },

    /**
     * Submits the form panel to the serverside using
     * an AJAX request
     * @return [false|null]
     */
    submitFormPanel: function() {
        var me = this,
            form = me.formPanel.getForm(),
            messages = me.snippets.messages;

        if(!form.isValid()) {
            return false;
        }

        me.setLoading(true);
        form.submit({
            success: function(form, operation) {
                var response = operation.result;
                me.setLoading(false);
                if(!response.success) {
                    Shopware.Notification.createGrowlMessage(me.title, messages.error);
                    return false;
                }

                Shopware.Notification.createGrowlMessage(me.title, messages.success);
                me.destroy();
            },
            failure: function(form, operation) {
                var response = operation.result;
                me.setLoading(false);

                Shopware.Notification.createGrowlMessage(me.title, messages.error);
            }
        })
    }
});
//{/block}
