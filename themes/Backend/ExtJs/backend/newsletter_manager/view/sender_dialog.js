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
 * @package    NewsletterManager
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name="backend/newsletter_manager/main"}

/**
 * Shopware UI - sender dialog
 * A popup window which will ask the user to edit/create a sender
 * todo@dn: there should be a row-editor for this!
 */
//{block name="backend/newsletter_manager/view/sender_dialog"}
Ext.define('Shopware.apps.NewsletterManager.view.SenderDialog', {
    extend: 'Enlight.app.Window',
    alias : 'widget.newsletter-manager-sender_dialog',
    layout: 'fit',
    width: 300,
    height: 150,

    stateful: true,
    stateId: 'shopware-newsletter-manager-sender_dialog',

//    modal: true,
//    footerButton: false,

    autoShow: true,

    border: false,

    /**
     * Init the component, add noticeContainer and Tabs
     */
    initComponent: function() {
        var me = this;

        if(me.record == null) {
            me.title = '{s name=title/newSender}Create new sender{/s}'
        }else{
            me.title = '{s name=title/editSender}Edit sender{/s}'
        }

        me.items = me.createForm();

        me.addEvents(
            /**
             * Fired when the user submits the valid form
             * @param Ext.form.Panel panel
             */
            'saveSender'
        );
        me.callParent(arguments);
    },

    /**
     * Creates and returns a form field with a 'mail' and 'name' textfield
     */
    createForm: function() {
        var me = this;

        me.form = Ext.create('Ext.form.Panel', {
            layout: {
                  type: 'vbox',       // Arrange child items vertically
                  align: 'stretch',    // Each takes up full width
                  padding: 5
              },
            // The fields
            defaultType: 'textfield',
            items: [{
                fieldLabel: 'Mail address',
                name: 'email',
                allowBlank: false,
                vtype: 'remote',
                validationUrl: '{url controller="base" action="validateEmail"}',
                validationErrorMsg: '{s name=invalid_email namespace=backend/base/vtype}The email address entered is not valid{/s}',
            },{
                fieldLabel: 'Name',
                name: 'name',
                allowBlank: false
            }],

            // Reset and Submit buttons
            buttons: [{
                text: '{s name=cancel}Cancel{/s}',
                handler: function() {
                    this.up('form').getForm().reset();
                    me.destroy();
                }
            }, {
                text: '{s name=submit}Submit{/s}',
                formBind: true, //only enabled once the form is valid
                disabled: true,
                handler: function() {
                    var form = this.up('form').getForm();
                    if (form.isValid()) {
                        me.fireEvent('saveSender', form);
                        me.destroy();
                    }
                }
            }]
        });

        if(me.record) {
            me.form.getForm().loadRecord(me.record);
        }
        return me.form;

    }

});
//{/block}
