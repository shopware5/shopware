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
 * @package    Mail
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/mail/view/contentEditor}

/**
 * todo@all: Documentation
 */
//{block name="backend/mail/view/main/contentEditor"}
Ext.define('Shopware.apps.Mail.view.main.ContentEditor', {
    extend: 'Ext.Panel',
    alias: 'widget.mail-main-contentEditor',
    bodyPadding: 10,

    layout: 'fit',

    isHtml: false,

    /**
     * Defines additional events which will be fired
     *
     * @return void
     */
    registerEvents:function () {
        this.addEvents(
            /**
             * Event will be fired when the user clicks the show preview button
             *
             * @event showPreview
             * @param [string] content of the textarea
             * @param [boolean]
             */
            'showPreview',

            /**
             * Event will be fired when the user clicks the send testmail button
             *
             * @event sendTestMail
             * @param [string] content of the textarea
             * @param [boolean]
             */
            'sendTestMail'
        );
    },

    /**
     * Initializes the component and builds up the main interface
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.items = me.getItems();
        me.dockedItems = [ me.getToolbar() ];

        me.callParent(arguments);
    },

    /**
     * Creates items shown in form panel
     *
     * @return array
     */
    getItems: function() {
        var me = this;

        me.editorField = null;

        if (this.isHtml) {
            me.editorField= Ext.create('Shopware.form.field.CodeMirror', {
                xtype: 'codemirrorfield',
                mode: 'smarty',
                name: 'contentHtml',
                translationLabel: '{s name=codemirrorHtml_translationLabel}Html-Content{/s}',
                translatable: true // Indicates that this field is translatable
            });
        } else {
            me.editorField = Ext.create('Shopware.form.field.CodeMirror', {
                xtype: 'codemirrorfield',
                mode: 'smarty',
                name: 'content',
                translationLabel: '{s name=codemirror_translationLabel}Content{/s}',
                translatable: true // Indicates that this field is translatable
            });
            me.editorField.name = 'content';
            me.editorField.translationLabel = 'content';
        }

        me.editorField.on('editorready', function(editorField, editor) {
            var scroller, size;

            if(!editor || !editor.hasOwnProperty('display')) {
                return false;
            }

            scroller = editor.display.scroller;
            size = editorField.getSize();
            editor.setSize('100%', size.height);
            Ext.get(scroller).setSize(size);
        });

        me.on('resize', function(cmp, width, height) {
            var editorField = me.editorField,
                editor = editorField.editor,
                scroller;

            if(!editor || !editor.hasOwnProperty('display')) {
                return false;
            }

            scroller = editor.display.scroller;

            width -= me.bodyPadding * 2;
            // We need to remove the bodyPadding, the padding on the field itself and the scrollbars
            height -= me.bodyPadding * 5;

            editor.setSize(width, height);
            Ext.get(scroller).setSize({ width: width, height: height });
        });

        return me.editorField;
    },

    /**
     * Creates the toolbar.
     *
     * @return [object] generated Ext.toolbar.Toolbar
     */
    getToolbar: function() {
        var me = this;

        return {
            xtype: 'toolbar',
            dock: 'top',
            items: [
                {
                    xtype: 'button',
                    text: '{s name=button_preview}Display preview{/s}',
                    action: 'preview',
                    disabled: !me.isHtml,
                    listeners: {
                        click: function() {
                            me.fireEvent('showPreview', me.editorField.getValue(), me.isHtml);
                        }
                    }
                },
                {
                    xtype: 'tbfill'
                },
                {
                    xtype: 'button',
                    text: '{s name=button_send_testmail}Send testmail to shop owner{/s}',
                    action: 'testmail',
                    disabled: !me.isHtml,
                    listeners: {
                        click: function() {
                            me.fireEvent('sendTestMail', me.editorField.getValue(), me.isHtml);
                        }
                    }
                }
            ]
        };
    }
});
//{/block}
