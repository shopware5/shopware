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
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/order/main}

/**
 * Shopware Controller - Order backend module
 */
//{block name="backend/order/controller/mail"}
Ext.define('Shopware.apps.Order.controller.Mail', {

    /**
     * Extend from the standard ExtJS 4 controller
     *
     * @type { String }
     */
    extend: 'Ext.app.Controller',

    /**
     * all references to get the elements by the applicable selector
     *
     * @type { Array }
     */
    refs: [
        { ref: 'mailWindow', selector: 'order-mail-window' },
        { ref: 'attachmentGrid', selector: 'order-mail-attachment' },
        { ref: 'mailForm', selector: 'order-mail-form' }
    ],

    /**
     * Contains all snippets for the this component
     *
     * @type { Object }
     */
    snippets: {
        growlMessage: '{s name=growlMessage}Order{/s}',

        successTitle: '{s name=sent_success_title}Email has been sent to customer [0]{/s}',
        successMessage: '{s name=sent_success_message}Email sent to customer [0]{/s}',

        errorTitle: '{s name=sent_error_title}Email could not be sent.{/s}',
        errorMessage: '{s name=sent_error_message}An error has occurred while sending the status mail:{/s}',

        confirmation: {
            title: '{s name=mail_template_change/confirmation/title}Change mail template?{/s}',
            message: '{s name=mail_template_change/confirmation/message}You have changed the content of the e-mail, if you change the mail template these changes will be lost. Do you still want to change the template?{/s}'
        }
    },

    /**
     * A template method that is called when your application boots.
     * It is called before the Application's launch function is executed
     * so gives a hook point to run any code before your Viewport is created.
     */
    init: function() {
        var me = this;

        me.control({
            'order-mail-window order-mail-form': {
                sendMail: me.onSendMail,
                changeMailTemplateComboBox: this.onChangeMailTemplateComboBox
            },
            'order-mail-window order-mail-attachment': {
                'selectionModel-selection-change': this.onAttachmentSelectionChange
            }
        });

        me.callParent(arguments);
    },

    /**
     * Event listener method which is fired when the user clicks the "send email button" to send the displayed
     * email to the customer.
     *
     * @param { Ext.form.Panel } form
     */
    onSendMail: function(form) {
        var me = this,
            win = me.getMailWindow(),
            snippets = me.snippets,
            mail = form.getRecord(),
            rawData,
            message;

        mail.getReceipt().add(
            me.getAttachmentGrid().getStore().getActiveDocuments()
        );

        win.setLoading(true);

        form.getForm().updateRecord(mail);
        mail.setDirty();

        mail.save({
            callback: function(record, operation) {
                win.setLoading(false);

                rawData = record.getProxy().getReader().rawData;

                if (!operation.success) {
                    Shopware.Notification.createGrowlMessage(snippets.errorTitle, snippets.errorMessage + '<br>' + rawData.message, snippets.growlMessage);
                    return;
                }

                mail.set('set', true);
                message = Ext.String.format(snippets.successMessage, mail.get('to'));
                Shopware.Notification.createGrowlMessage(snippets.successTitle, message, snippets.growlMessage);

                win.destroy();
            }
        });
    },

    /**
     * Method is called when the user selects another mail template with the combo box
     *
     * @param { Shopware.apps.Order.view.mail.Form } mailFormPanel
     * @param { Ext.form.field.ComboBox } comboBox
     * @param { string } newValue
     * @param { string } oldValue
     */
    onChangeMailTemplateComboBox: function (mailFormPanel, comboBox, newValue, oldValue) {
        var callback = function (clickedButton) {
            if (clickedButton !== 'yes') {
                // Temporarily suspending events to not toggle the "modified" attribute
                mailFormPanel.mailTemplateComboBox.suspendEvents();
                mailFormPanel.mailTemplateComboBox.setValue(oldValue);
                mailFormPanel.mailTemplateComboBox.resumeEvents();

                return;
            }

            mailFormPanel.setLoading(true);

            Ext.Ajax.request({
                url: '{url controller=order action=createMail}',
                method: 'POST',
                params: {
                    orderId: mailFormPanel.order.get('id'),
                    mailTemplateName: newValue
                },

                success: function (response) {
                    var decodedResponse = Ext.JSON.decode(response.responseText);
                    decodedResponse.mail.templateName = newValue;
                    var mail = Ext.create('Shopware.apps.Order.model.Mail', decodedResponse.mail);
                    mailFormPanel.loadRecord(mail);
                },

                failure: function (response) {
                    Shopware.Notification.createGrowlMessage(
                        '{s name=document/attachemnt/error}Error{/s}',
                        response.status + '<br />' + response.statusText
                    );
                },

                callback: function (options, success, response) {
                    mailFormPanel.setLoading(false);
                },
                scope: this
            });
        };

        if (mailFormPanel.modified) {
            Ext.Msg.confirm(this.snippets.confirmation.title, this.snippets.confirmation.message, callback, this);
        } else {
            Ext.callback(callback, this, ['yes']);
        }
    },

    /**
     * Select the correct mail template depending on which document the user has selected in the attachment list
     *
     * @param { Shopware.apps.Order.store.DocumentRegistry } attachmentsStore
     * @param { Ext.selection.CheckboxModel } selectionModel
     * @param { Shopware.apps.Order.model.Receipt } selected
     */
    onAttachmentSelectionChange: function (attachmentsStore, selectionModel, selected) {
        if (this.getMailForm().modified || !Ext.isDefined(this.getMailForm().mailTemplateComboBox)) {
            // Don't change the template if the user has already typed stuff in the form
            return;
        }
        if (selected.length === 0) {
            // Don't change the template if no document is selected currently
            return;
        }
        if (selected.length === 1) {
            // Select the mail template fitting to the document type
            var documentType = this.getMailForm().documentTypeStore.getById(selected[0].get('typeId'));
            this.getMailForm().mailTemplateComboBox.selectTemplateByDocumentType(documentType);

            return;
        }

        // For multiple selected documents switch to the default mail template
        this.getMailForm().mailTemplateComboBox.selectTemplateByDocumentType(null);
    }
});
//{/block}
