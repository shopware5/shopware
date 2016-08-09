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

//{namespace name=backend/mail/view/form}

/**
 * todo@all: Documentation
 */
//{block name="backend/mail/view/main/form"}
Ext.define('Shopware.apps.Mail.view.main.Form', {
    extend: 'Ext.form.Panel',
    alias: 'widget.mail-main-form',
    autoScroll: true,
    bodyPadding: 10,

    layout: {
        type: 'vbox',
        align: 'stretch'
    },

    /*{if {acl_is_allowed privilege=create} || {acl_is_allowed privilege=update}}*/
    plugins: [{
         // Includes the default translation plugin
        pluginId: 'my-translation',
        ptype: 'translation',
        translationType: 'config_mails'
    }],
    /*{/if}*/

    newRecord: function(record) {
        var me   = this,
            form = me.getForm();

        form.findField('name').validationRequestParam = 0;

        if (record) {
            form.loadRecord(record);
        }

        // Reset the form so it does not seem dirty.
        form.getFields().each(function(field) {
            field.resetOriginalValue();
        });

        form.reset();

        // disable attachments tab
        me.getComponent('tabpanel').getComponent('attachmentsTab').setDisabled(true);

        me.setTitle('{s name=title_new}Create new template{/s}');

        me.getComponent('tabpanel').setActiveTab(0);

        /*{if !{acl_is_allowed privilege=create} && !{acl_is_allowed privilege=update}}*/
        form.findField('name').setReadOnly(true);
        return;
        /*{/if}*/

        form.findField('name').setReadOnly(false);
    },

    loadRecord: function(record) {
        var me   = this,
            form = me.getForm(),
            contentTab = me.getComponent('tabpanel').getComponent('contentTab');

        form.findField('name').validationRequestParam = record.get('id');
        form.loadRecord(record);

        me.attributeForm.loadAttribute(record.get('id'));

        // Enable attachments tab
        me.getComponent('tabpanel').getComponent('attachmentsTab').setDisabled(false);

        // Enable html-content-tab if HTML-Mail is checked
        if (form.findField('isHtml').getValue()) {
            me.getComponent('tabpanel').getComponent('htmlContentTab').setDisabled(false);
        }

        me.setTitle(Ext.String.format('{s name=title_edit}Edit template - [0]{/s}', record.data.name));

        me.getComponent('tabpanel').setActiveTab(0);

        // trigger resize event on the tab to fit the editor
        contentTab.fireEvent('resize', contentTab, contentTab.getWidth(), contentTab.getHeight());

        /*{if !{acl_is_allowed privilege=create} && !{acl_is_allowed privilege=update}}*/
        form.findField('name').setReadOnly(true);
        return;
        /*{/if}*/

        if(record.get('type') !== 'userMail') {
            form.findField('name').setReadOnly(true);
        } else {
            form.findField('name').setReadOnly(false);
        }
    },


    /**
     * Initializes the component and builds up the main interface
     *
     * @public
     * @return void
     */
    initComponent: function () {
        var me = this;

        me.initialConfig = Ext.apply({
            trackResetOnLoad: true
        }, me.initialConfig);

        me.items = me.getItems();

        me.callParent(arguments);

        me.newRecord();
    },

    /**
     * Creates items shown in form panel
     *
     * @return array
     */
    getItems: function () {
        var me = this;

        me.attributeForm = Ext.create('Shopware.attribute.Form', {
            table: 's_core_config_mails_attributes',
            title: '{s namespace="backend/attributes/main" name="attribute_form_title"}{/s}',
            bodyPadding: 20,
            autoScroll: true,
            translationForm: me,
            allowTranslation: false
        });

        me.tabPanel = Ext.create('Ext.tab.Panel', {
            itemId: 'tabpanel',
            flex: 1,
            listeners: {
                scope: me,
                // SW-3564 - Refresh codemirror fields on tab change
                tabchange: function(tabPanel, tab) {
                    var editorField = tab.editorField;
                    if (editorField && editorField.editor) {
                        editorField.editor.refresh();
                    }
                    me.translationPlugin.initTranslationFields(me);
                }
            },
            items: [
                {
                    xtype: 'mail-main-contentEditor',
                    itemId: 'contentTab',
                    name: 'contentTab',
                    title: '{s name=tab_plaintext}Plaintext{/s}'
                },
                {
                    xtype: 'mail-main-contentEditor',
                    isHtml: true,
                    title: '{s name=tab_html}HTML{/s}',
                    itemId: 'htmlContentTab',
                    id: 'htmlContentTab',
                    name: 'htmlContentTab',
                    disabled: true
                },
                {
                    xtype: 'mail-main-attachments',
                    itemId: 'attachmentsTab',
                    name: 'attachmentsTab',
                    title: '{s name=tab_attachments}Attachments{/s}',
                    store: me.attachmentStore,
                    disabled: true
                },
                me.attributeForm
            ]
        });

        return [
            {
                xtype: 'fieldset',
                title: '{s name=fieldset_settings}Template settings{/s}',
                defaultType: 'textfield',
                autoScroll:true,
                flex:1,
                defaults: {
                    /*{if !{acl_is_allowed privilege=create} && !{acl_is_allowed privilege=update}}*/
                    readOnly: true,
                    /*{/if}*/
                    labelStyle: 'font-weight: 700; text-align: right;',
                    layout: 'anchor',
                    labelWidth: 130,
                    anchor: '100%'
                },
                items: [
                    {
                        fieldLabel: '{s name=label_name}Name{/s}',
                        name: 'name',
                        allowBlank: false,
                        checkChangeBuffer: 300,
                        vtype: 'remote',
                        validationUrl: '{url controller="mail" action="validateName"}',
                        validationErrorMsg: '{s name=validation_error_name}The entered name is already in use{/s}'
                    },
                    {
                        fieldLabel: '{s name=label_frommail}FromMail{/s}',
                        name: 'fromMail',
                        translatable: true // Indicates that this field is translatable
                    },
                    {
                        fieldLabel: '{s name=label_fromname}FromName{/s}',
                        name: 'fromName',
                        translatable: true // Indicates that this field is translatable
                    },
                    {
                        fieldLabel: '{s name=label_subject}Subject{/s}',
                        name: 'subject',
                        translatable: true, // Indicates that this field is translatable
                        allowBlank: false
                    },
                    {
                        xtype: 'checkboxfield',
                        inputValue: true,
                        uncheckedValue: false,
                        name: 'isHtml',
                        fieldLabel: '{s name=label_htmlmail}HTML-Mail{/s}',
                        boxLabel: '{s name=boxlabel_htmlmail}Send template as HTML email{/s}',
                        listeners: {
                            /**
                             * Fires when a user-initiated change is detected in the value of the field.
                             *
                             * @event change
                             * @param [Ext.form.field.Field]
                             * @param [Object] checked
                             */
                            change: function(field, newValue) {
                                me.getComponent('tabpanel').getComponent('htmlContentTab').setDisabled(!newValue);
                            }
                        }
                    }
                ]
            },
            me.tabPanel
        ];
    }
});
//{/block}
