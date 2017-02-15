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
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/mail/controller/main}

/**
 * todo@all: Documentation
 */
//{block name="backend/mail/controller/main"}
Ext.define('Shopware.apps.Mail.controller.Main', {

    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend: 'Ext.app.Controller',

    /**
     * Define references for the different parts of our application. The
     * references are parsed by ExtJS and Getter methods are automatically created.
     *
     * @array
     */
    refs: [
        { ref: 'mainWindow', selector: 'mail-main-window' },
        { ref: 'navigationTree', selector: 'mail-main-navigation' },
        { ref: 'formPanel', selector: 'mail-main-form' },
        { ref: 'attachmentTree', selector: 'mail-main-attachments' },
        { ref: 'infoPanel', selector: 'mail-main-info' },
        { ref: 'attributeForm', selector: 'mail-main-window shopware-attribute-form' },

        { ref: 'contentEditor', selector: 'mail-main-contentEditor' },
        { ref: 'tabPanel', selector: 'mail-main-form tabpanel' },

        { ref: 'saveBtn', selector: 'mail-main-window button[action=mail-window-save]' },
        { ref: 'resetBtn', selector: 'mail-main-window button[action=mail-window-reset]' },

        { ref: 'deleteBtn', selector: 'mail-main-window button[action=mail-window-delete]' },
        { ref: 'copyBtn', selector: 'mail-main-window button[action=mail-window-copy]' },

        { ref: 'previewBtn', selector: 'mail-main-window button[action=preview]' },
        { ref: 'testmailBtn', selector: 'mail-main-window button[action=testmail]' }
    ],

    /**
     * Contains all snippets for the controller
     * @object
     */
    snippets: {
        looseUnsavedTitle: '{s name=message_loose_unsaved_title}Unsaved changes{/s}',
        looseUnsavedMessage: '{s name=message_loose_unsaved_message}The form contains unsaved changes. Are you sure you want to exit the form?{/s}',

        resetTitle: '{s name=message_reset_title}Reset form{/s}',
        resetMessage: '{s name=message_reset_message}Are you sure you want to reset the form?{/s}',

        saveSuccessTitle: '{s name=message_save_success_title}Successful{/s}',
        saveSuccessMessage: '{s name=message_save_success_message}The email template has been saved.{/s}',

        saveErrorTitle: '{s name=message_save_error_title}Error{/s}',
        saveErrorMessage: '{s name=message_save_error_message}An error has occurred. The email template could not be saved.{/s}',

        duplicateTitle: '{s name=message_duplicate_title}Duplicate template{/s}',
        duplicateMessage: '{s name=message_duplicate_message}Are you sure you want to duplicate the selected template?{/s}',

        duplicateSuccessfullTitle :'{s name=message_duplicate_successfull_title}Successful{/s}',
        duplicateSuccessfullMessage: '{s name=message_duplicate_successfull_message}The email template has been duplicated.{/s}',

        multipleDeleteTitle: '{s name=message_delete_multiple_title}Delete selected templates{/s}',
        multipleDeleteMessage: '{s name=message_delete_multiple_message}Are you sure you want to delete all selected templates?{/s}',

        sendmailSuccessfullTitle :'{s name=message_sendmail_successfull_title}Successful{/s}',
        sendmailSuccessfullMessage: '{s name=message_sendmail_successfull_message}Email has been sent.{/s}',

        sendmailFailureTitle :'{s name=message_sendmail_failure_title}Failure{/s}',
        sendmailFailureMessage: '{s name=message_sendmail_failure_message}Email could not be sent. Errormessage:{/s}',

        growlMessage: '{s name=growlMessage}Mail{/s}'
    },

    /**
     * Class property which holds the main application if it is created
     *
     * @default null
     * @object
     */
    mainWindow: null,

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * @return void
     */
    init: function() {
        var me = this;

        me.control({
            'mail-main-navigation': {
                itemclick:       me.onItemClick,
                beforeitemclick: me.onBeforeItemClick,
                beforeselect:    me.onBeforeSelect,
                checkchange:     me.onCheckChange
            },

            'mail-main-window button[action=mail-window-add]' : {
                click: me.onAddMail
            },

            'mail-main-window button[action=mail-window-copy]' : {
                click: me.onCopyMail
            },

            'mail-main-window button[action=mail-window-delete]' : {
                click: me.onDelete
            },

            'mail-main-window button[action=mail-window-reset]' : {
                click: me.onReset
            },

            'mail-main-window button[action=mail-window-save]' : {
                click: me.onSave
            },

            'mail-main-window textfield[action=search]' : {
                change: me.onSearch
            },

            'mail-main-form': {
                dirtychange:    me.onDirtyChange
            },

            'mail-main-form tabpanel': {
                tabchange: me.onTabChange
            },

            'mail-main-contentEditor': {
                showPreview:  me.onShowPreview,
                sendTestMail: me.onSendTestMail
            }
        });

        me.mainWindow = me.getView('main.Window').create({
            treeStore:       me.getStore('Tree'),
            attachmentStore: me.getStore('Attachment')
        });

        me.mainWindow.show();

        me.callParent(arguments);
    },

    /**
     * @event beforeselect
     * @param [Ext.selection.RowModel] rowModel
     * @param [Ext.data.Model] record
     * @param [Number] index
     *
     * @return boolean
     */
    onBeforeSelect: function(rowModel, record, index) {
        var me   = this,
            form = me.getFormPanel().getForm();

        if (!record.isLeaf()) {
            return true;
        }

        if (!form.isDirty()) {
            return true;
        }

        return false;
    },

    /**
     * @event beforeitemclick
     * @param [Ext.view.View] view - the view that fired the event
     * @param [Ext.data.Model] record
     * @param [HTMLElement ] item
     * @param [Number] index
     *
     * @return boolean
     */
    onBeforeItemClick: function(view, record, item, index) {
        var me   = this,
            form = me.getFormPanel().getForm();

        if (!record.isLeaf()) {
            return true;
        }

        if (!form.isDirty()) {
            return true;
        }

        Ext.MessageBox.confirm(me.snippets.looseUnsavedTitle, me.snippets.looseUnsavedMessage, function (response) {
            if (response !== 'yes') {
                return false;
            }

            form.reset();
            me.getNavigationTree().getSelectionModel().select(record);
            me.loadRecord(record);
        });

        return false;
    },


    /**
     * @event click
     * @param [object] btn - the btn that fired the event
     * @return void
     */
    onAddMail: function(btn) {
        var me     = this,
            form   = me.getFormPanel(),
            record = Ext.create('Shopware.apps.Mail.model.Mail');

        me.getPreviewBtn().setDisabled(true);
        me.getTestmailBtn().setDisabled(true);

        form.newRecord(record);
    },

    /**
     * @event itemclick
     * @param [Ext.view.View] view - the view that fired the event
     * @param [Ext.data.Model] record
     * @param [HTMLElement ] item
     * @param [Number ] index
     *
     * @return void
     */
    onItemClick: function(view, record, item, index) {
        if (!record.isLeaf()) {
            return;
        }

        this.loadRecord(record);
    },

    /**
     * Helper method to load record into form
     *
     * @param [Ext.data.Model] record
     *
     * @return void
     */
    loadRecord: function(record) {
        var me        = this,
            formPanel = me.getFormPanel(),
            infoPanel = me.getInfoPanel(),
            store     = me.getStore('Mail');

        formPanel.setLoading(true);

        store.load({
            id: record.getId(),
            scope: this,
            callback: function(records, operation, success) {
                if (success) {
                    me.getFormPanel().newRecord();

                    formPanel.loadRecord(records[0]);

                    // Reset the form so it does not seem dirty.
                    formPanel.getForm().getFields().each(function(field) {
                        field.resetOriginalValue();
                    });

                    formPanel.getForm().reset();


                    infoPanel.updateContext(records[0].get('contextPath'));

                    me.getPreviewBtn().setDisabled(false);
                    me.getTestmailBtn().setDisabled(false);
                }
                formPanel.setLoading(false);

                var tabPanel = me.getTabPanel();
            }
        });
    },


    /**
     * Resets the form
     *
     * @event click
     * @param [object] btn - the btn that fired the event
     * @return void
     */
    onReset: function(btn) {
        var me        = this,
            form      = me.getFormPanel().getForm();

        Ext.MessageBox.confirm(me.snippets.resetTitle, me.snippets.resetMessage, function (response) {
            if (response !== 'yes') {
                return false;
            }

            form.reset();
        });
    },

    /**
     * Event will be fired when the user clicks the send testmail button
     *
     * @event showPreview
     * @param [string] content of the textarea
     * @param [boolean]
     */
    onSendTestMail: function(content, isHtml) {
        var me        = this,
            id        = me.getFormPanel().getRecord().get('id');

        Ext.Ajax.request({
            url: '{url controller="mail" action="sendTestmail"}',
            method: 'POST',
            params: {
                id: id,
                value: content
            },
            success: function(response) {
                var status = Ext.decode(response.responseText);
                if (status.success) {
                    Shopware.Notification.createGrowlMessage(me.snippets.sendmailSuccessfullTitle, me.snippets.sendmailSuccessfullMessage, me.snippets.growlMessage);
                } else {
                    Shopware.Notification.createGrowlMessage(me.snippets.sendmailFailureTitle, me.snippets.sendmailFailureMessage + ' ' + status.message, me.snippets.growlMessage);
                }
            }
        });
    },

    /**
     * Event will be fired when the user clicks the show preview button
     *
     * @event showPreview
     * @param [string] content of the textarea
     * @param [boolean]
     */
    onShowPreview: function(content, isHtml) {
        var me         = this,
            id         = me.getFormPanel().getRecord().get('id'),
            htmlString = '';

        Ext.Ajax.request({
            url: '{url controller="mail" action="verifySmarty"}',
            method: 'POST',
            params: {
                id: id,
                value: content
            },
            success: function(response) {
                var status = Ext.decode(response.responseText);
                if (status.success) {

                    if (isHtml) {
                        htmlString =  "<div style=\"margin: 15px\">" + status.message + "</div>"
                    } else {
                        htmlString =  "<div style=\"margin: 15px\"><pre>" + status.message + "</pre></div>"
                    }

                    Ext.create('Enlight.app.Window', {
                        title : '{s name=title}Email templates{/s}',
                        autoScroll: true,
                        subApplication: me.subApplication,
                        subApp: me.subApplication,
                        items: [{
                            xtype: 'container',
                            html: htmlString
                        }]
                    }).show();
                } else {
                    Shopware.Notification.createGrowlMessage('Invalid', status.message, me.snippets.growlMessage);
                }
            }
        });
    },

    /**
     * @event click
     * @param [object] btn - the btn that fired the event
     * @return void
     */
    onSave: function (btn) {
        var me              = this,
            formPanel       = me.getFormPanel(),
            form            = formPanel.getForm(),
            record          = form.getRecord(),
            treeNeedsReload = false;

        if (!form.isValid()) {
            return;
        }

        if (!record) {
            record = Ext.create('Shopware.apps.Mail.model.Mail');
        }

        var oldName = record.get('name');

        form.updateRecord(record);

        // if we insert a new record or name of record changed reload the tree
        if (record.phantom || record.get('name') != oldName) {
            treeNeedsReload = true;
        }

        formPanel.setLoading(true);
        record.save({
            success: function(record, operation) {
                me.getAttributeForm().saveAttribute(record.get('id'), function() {
                    formPanel.setLoading(false);
                    me.loadRecord(record);
                    if (treeNeedsReload) {
                        me.reloadTree();
                    }
                    Shopware.Notification.createGrowlMessage(me.snippets.saveSuccessTitle, me.snippets.saveSuccessMessage, me.snippets.growlMessage);
                });
            },
            failure: function() {
                formPanel.setLoading(false);
                Shopware.Notification.createGrowlMessage(me.snippets.saveErrorTitle, me.snippets.saveErrorMessage, me.snippets.growlMessage);
            }
        });
    },

    /**
     * Event listener method which will be fired when the user
     * insert a value in the search field on the right hand of the module,
     * to search forms by their name.
     *
     * @event change
     * @param [object] field - Ext.form.field.Text
     * @param [string] value - inserted search value
     * @return void
     */
    onSearch: function(field, value) {
        var me           = this,
            store        = this.getStore('Tree'),
            searchString = Ext.String.trim(value);

        store.myFilter(searchString);
    },

    /**
     * Event listener which copies a mail
     *
     * @event click
     * @return void
     */
    onCopyMail: function() {
        var me           = this,
            tree         = me.getNavigationTree(),
            checkedItems = tree.getChecked(),
            record       = checkedItems[0];

        Ext.MessageBox.confirm(me.snippets.duplicateTitle, me.snippets.duplicateMessage, function (response) {
            if (response !== 'yes') {
                return false;
            }

            record.copy(function(success) {
                if (success) {
                    Shopware.Notification.createGrowlMessage(me.snippets.duplicateSuccessfullTitle, me.snippets.duplicateSuccessfullMessage, me.snippets.growlMessage);
                }

                me.reloadTree();
            });
        });
    },

    /**
     * Event listener which deletes a mail
     *
     * @event click
     * @param [object] btn - the btn that fired the event
     * @return void
     */
    onDelete: function(btn) {
        var me           = this,
            checkedItems = me.getNavigationTree().getChecked(),
            form         = me.getFormPanel(),
            record       = Ext.create('Shopware.apps.Mail.model.Mail');

        Ext.MessageBox.confirm(me.snippets.multipleDeleteTitle, me.snippets.multipleDeleteMessage, function (response) {
            if (response !== 'yes') {
                return false;
            }

            Ext.each(checkedItems, function(record) {
                record.destroy();
            });

            me.getPreviewBtn().setDisabled(true);
            me.getTestmailBtn().setDisabled(true);
            form.newRecord(record);
            me.reloadTree();
        });
    },

    /**
     * Fires after a change event
     *
     * Enables/Disables the delete and copy button
     *
     * @event checkchange
     * @param [Ext.menu.CheckItem] node
     * @param [boolean] checked
     * @return void
     */
    onCheckChange: function(node, checked) {
        /*{if !{acl_is_allowed privilege=create} && !{acl_is_allowed privilege=update}}*/
            return;
        /*{/if}*/

        var me           = this,
            deleteBtn    = me.getDeleteBtn(),
            copyBtn      = me.getCopyBtn(),
            checkedItems = me.getNavigationTree().getChecked();

        // Delete button should be only enabled if one or more items are checked
        deleteBtn.setDisabled(checkedItems.length < 1);

        // Copy button should be only enabled if exact one item is checked
        copyBtn.setDisabled(checkedItems.length != 1);
    },

    /**
     * Fires when the dirty state of the entire form changes.
     *
     * @event dirtychange
     * @param [Ext.form.Basic] form - the form firing the event
     * @param [boolean] dirty - true if the form is now valid, false if it is now invalid.
     * @return void
     */
    onDirtyChange: function(form, dirty) {
        /*{if !{acl_is_allowed privilege=create} && !{acl_is_allowed privilege=update}}*/
            return;
        /*{/if}*/

        var me           = this,
            resetBtn     = me.getResetBtn();

        // Reset button should be only enabled if form is dirty
        resetBtn.setDisabled(!dirty);
    },

    /**
     * @return void
     */
    setupState: function() {
        /*{if !{acl_is_allowed privilege=create} && !{acl_is_allowed privilege=update}}*/
            return;
        /*{/if}*/

        var me        = this,
            deleteBtn = me.getDeleteBtn(),
            copyBtn   = me.getCopyBtn();

        deleteBtn.setDisabled(true);
        copyBtn.setDisabled(true);
    },

    /**
     * @return void
     */
    reloadTree: function() {
        var me       = this,
            tree     = me.getNavigationTree(),
            store    = me.getStore('Tree'),
            rootNode = tree.getRootNode(),
            selModel = tree.getSelectionModel(),
            record   = me.getFormPanel().getRecord();

        me.setupState();

        rootNode.removeAll(false);
        tree.setLoading(true);
        store.load({
            callback: function() {
                tree.setLoading(false);

                if (record) {
                    var lastSelected = store.getNodeById(record.data.id);

                    if (lastSelected) {
                        lastSelected.bubble(function(node) {
                            node.expand()
                        });

                        selModel.select(lastSelected);
                    }
                }

            }
        });
    },


    /**
     * Reloads attachments
     *
     * @event tabchange
     * @param [Ext.tab.Panel] tabPanel
     * @param [Ext.Component] newCard
     *
     * @return void
     */
    onTabChange: function(tabPanel, newCard) {
        var me = this;

        if (newCard.getXType() == 'mail-main-attachments') {
            me.reloadAttachmentTree();
        }
    },

    /**
     * Reloads attachments
     *
     * @return void
     */
    reloadAttachmentTree: function() {
        var me           = this,
            store        = me.getStore('Attachment'),
            rootNode     = me.getAttachmentTree().getRootNode(),
            mailRecord   = me.getFormPanel().getRecord();

        store.getProxy().extraParams.mailId = mailRecord.get('id');

        rootNode.removeAll(false);
        store.load();
    }
});
//{/block}
