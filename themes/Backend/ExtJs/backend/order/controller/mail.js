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
    extend:'Ext.app.Controller',

   /**
    * all references to get the elements by the applicable selector
    *
    * @type { Array }
    */
    refs:[
        { ref:'mailWindow', selector:'order-mail-window' }
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
        errorMessage: '{s name=sent_error_message}An error has occurred while sending the status mail:{/s}'
    },

    /**
     * A template method that is called when your application boots.
     * It is called before the Application's launch function is executed
     * so gives a hook point to run any code before your Viewport is created.
     */
    init:function () {
        var me = this;

        me.control({
            'order-mail-window order-mail-form': {
                sendMail: me.onSendMail
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
    }
});
//{/block}
