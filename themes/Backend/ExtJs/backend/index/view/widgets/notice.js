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
 * Shopware UI - Upload Widget
 *
 * This file holds off the upload widget.
 *
 * @link http://www.shopware.de/
 * @license http://www.shopware.de/license
 * @package index
 * @subpackage views/widgets/Upload
 */
//{block name="backend/index/view/widgets/notice"}
Ext.define('Shopware.apps.Index.view.widgets.Notice', {
    extend: 'Shopware.apps.Index.view.widgets.Base',
    alias: 'widget.swag-notice-widget',

    resizable: {
        handles: 's'
    },

    /**
     * Snippets for the widget.
     * @object
     */
    snippets: {
        buttons: {
            reset: '{s name=notice/buttons/reset}Reset{/s}',
            submit: '{s name=notice/buttons/submit}Submit{/s}'
        },
        success_msg: {
            title: '{s name=notice/success_msg/title}Notice widget{/s}',
            text: '{s name=notice/success_msg/text}Your notice was successfully saved.{/s}'
        },
        failure_msg: {
            title: '{s name=notice/success_msg/title}Notice widget{/s}',
            text: "{s name=notice/failure_msg/text}Your notice couldn't be saved successfully.{/s}"
        }
    },

    /**
     * Initializes the widget.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.items = [ me.createFormPanel() ];
        me.dockedItems = [ me.createActionToolbar() ];

        me.callParent(arguments);
        me.getNotice();
    },

    /**
     * Creates the form panel for this widget.
     *
     * @public
     * @return [object] Ext.form.Panel
     */
    createFormPanel: function() {
        var me = this;

        me.textArea = Ext.create('Ext.form.field.TextArea', {
            name: 'notice',
            flex: 1
        });

        return me.formPanel = Ext.create('Ext.form.Panel', {
            unstyled: true,
            margin: '10 0 0',
            url: '{url controller=widgets action=saveNotice}',
            layout: {
                type: 'vbox',
                align : 'stretch',
                pack  : 'start'
            },
            items: [ me.textArea ]
        });
    },

    /**
     * Creates the action toolbar for the widget.
     * @return [object] Ext.toolbar.Toolbar
     */
    createActionToolbar: function() {
        var me = this;

        me.resetBtn = Ext.create('Ext.button.Button', {
            cls: 'small secondary',
            text: me.snippets.buttons.reset,
            handler: function() {
                me.textArea.reset();
            }
        });

        me.submitBtn = Ext.create('Ext.button.Button', {
            cls: 'small primary',
            text: me.snippets.buttons.submit,
            handler: function() {
                me.submitFormPanel();
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            cls: 'shopware-toolbar',
            items: [ '->', me.resetBtn, me.submitBtn ]
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
            field = me.textArea;

        if(!form.isValid()) {
            return false;
        }

        form.submit({
            success: function() {
                Shopware.Msg.createGrowlMessage(me.snippets.success_msg.title, me.snippets.success_msg.text);
            },
            failure: function() {
                Shopware.Msg.createGrowlMessage(me.snippets.failure_msg.title, me.snippets.failure_msg.text);
            }
        })
    },

    /**
     * Gets the last saved notice from the server side
     * using an AJAX request and sets it into
     * the textarea.
     *
     * @public
     * @return void
     */
    getNotice: function() {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller=widgets action=getNotice}',
            method: 'POST',
            success: function(response) {
                var response = Ext.decode(response.responseText);

                if(!response.success) {
                    return;
                }
                if(!response.notice || response.notice == 'false') {
                    return;
                }
                me.textArea.setValue(response.notice);
            }
        })
    }
});
//{/block}
