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
 * Shopware UI - Editor
 * View for the editor which allows the user to create new newsletters
 */
//{block name="backend/newsletter_manager/view/newsletter/editor"}
Ext.define('Shopware.apps.NewsletterManager.view.newsletter.Editor', {
    extend: 'Ext.form.Panel',
    alias: 'widget.newsletter-manager-newsletter-editor',
    title: '{s name=title/Editor}Newsletter Editor{/s}',
    layout: 'fit',
    autoScroll:true,
    defaults: {
        bodyBorder: 0
    },

    /**
     * Initializes the component, sets up toolbar and pagingbar and and registers some events
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        // Create the items of the container
        me.items = me.createPanel();
        me.dockedItems = me.getToolbar();

        me.addEvents(
            /**
             * Fired when the user clicks the "send test mail" button
             * * @param this.form
             */
            'sendTestMail',

            /**
             * Fired when the user clicks the "preview" button
             * @param this.tinyMCE
             */
            'openPreview');

        me.callParent(arguments);
    },

    /**
     * Creates the toolbar for this view which allows the user to set his mail, sent a testmail and preview the mail
     *
     * @return [Ext.toolbar.Toolbar] toolbar
     */
    getToolbar: function() {
        var me = this;

        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            items: [
                {
                    xtype: 'textfield',
                    vtype: 'remote',
                    validationUrl: '{url controller="base" action="validateEmail"}',
                    validationErrorMsg: '{s name=invalid_email namespace=backend/base/vtype}The email address entered is not valid{/s}',
                    name: 'mailAddress',
                    padding: '0 0 0 8',
                    ui: 'shopware-ui',
                    checkChangeBuffer: 1000,
                    listeners: {
                        validitychange: function(field, isValid) {
                            var button = Ext.getCmp('sendMail');
                            button.setDisabled(!(isValid && field.getValue() !== ''));
                        }
                    },
                    fieldLabel: '{s name=testMailAddress}Mail address{/s}' // re-use the column-snippet
                },
                {
                    xtype: 'button',
                    id: 'sendMail',
                    name: 'sendMail',
                    iconCls:'sprite-mail-send',
                    text: '{s name=sendTestMail}Send testmail{/s}',
                    handler: function() {
                        me.fireEvent('sendTestMail', me.form);
                    },
                    disabled: true
                },
                '-',
                {
                    xtype: 'button',
                    iconCls: 'sprite-globe--arrow',
                    name: 'preview',
                    text: '{s name=preview}Preview{/s}',
                    handler: function() {
                        me.fireEvent('openPreview', me.tinyMce);
                    }
                }
            ]
        });

        return me.toolbar;
    },

    /**
     * Creates the actual newsletter component
     * @return
     */
    createPanel: function() {
        var me = this;

        me.tinyMce = Ext.create('Shopware.form.field.TinyMCE', {
            name : 'content',
            // Workaround for the tinyMCE height bug
            margin: '0 0 27 0 ',
            height: 457,
            editor: {
                relative_urls: false
            }
        });

        return me.tinyMce;
    }

});
//{/block}
