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
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name="backend/newsletter_manager/main"}

/**
 * Shopware Controller - Admin controller
 * For events and actions fired in the admin tab
 */
//{block name="backend/newsletter_manager/controller/admin"}
Ext.define('Shopware.apps.NewsletterManager.controller.Admin', {

    extend: 'Ext.app.Controller',

    refs:[
        { ref:'recipientGrid', selector:'newsletter-manager-tabs-recipients' }
    ],

    snippets: {
        saveRecipient: {
            successTitle: '{s name=saveRecipient/successTitle}Successfully saved{/s}',
            successMessage: '{s name=saveRecipient/successMessage}Successfully saved the recipient{/s}',
            errorTitle: '{s name=saveRecipient/errorTitle}Error{/s}',
            errorMessage: '{s name=saveRecipient/errorMessage}An error occured while saving the recipient{/s}'
        },
        saveSender: {
            successTitle: '{s name=createSender/successTitle}Successfully saved{/s}',
            successMessage: '{s name=createSender/successMessage}Successfully saved the sender{/s}',
            errorTitle: '{s name=createSender/errorTitle}Error{/s}',
            errorMessage: '{s name=createSender/errorMessage}An error occured while saving the sender{/s}'
        },
        saveNewsletterGroup: {
            successTitle: '{s name=saveNewsletterGroup/successTitle}Successfully saved{/s}',
            successMessage: '{s name=saveNewsletterGroup/successMessage}Successfully saved the newsletter group{/s}',
            errorTitle: '{s name=saveNewsletterGroup/errorTitle}Error{/s}',
            errorMessage: '{s name=saveNewsletterGroup/errorMessage}An error occured while saving the newsletter group{/s}'
        },
        deleteSender: {
            successTitle: '{s name=deleteSender/successTitle}Successfully deleted{/s}',
            successMessage: '{s name=deleteSender/successMessage}Successfully deleted sender{/s}',
            errorTitle: '{s name=deleteSender/errorTitle}Error{/s}',
            errorMessage: '{s name=deleteSender/errorMessage}An error occured while deleting the sender{/s}'
        },
        deleteRecipient: {
            successTitle: '{s name=deleteRecipient/successTitle}Successfully deleted{/s}',
            successMessage: '{s name=deleteRecipient/successMessage}Successfully deleted recipient(s){/s}',
            errorTitle: '{s name=deleteRecipient/errorTitle}Error{/s}',
            errorMessage: '{s name=deleteRecipient/errorMessage}An error occured while deleting the recipient(s){/s}'
        },
    growl: '{s name=title}Newsletter Manager{/s}'
    },

    /**
     * A template method that is called when your application boots. It is called before the Application's
     * launch function is executed so gives a hook point to run any code before your Viewport is created.
     */
    init: function() {
        var me = this;

        me.control(                {
            'newsletter-manager-tabs-sender': {
                'editSender': me.onEditSender,
                'deleteSender': me.onDeleteSender,
                'createNewSender': me.onCreateNewSender
            },
            'newsletter-manager-sender_dialog': {
                'saveSender': me.onSaveSender
            },
            'newsletter-manager-tabs-recipients': {
                'showCustomer': me.onShowCustomer,
                'deleteRecipient': me.onDeleteRecipient,
                'saveRecipient': me.onSaveRecipient,
                'beforeRecipientEdit': me.onBeforeEdit,
                'addRecipient': me.onAddRecipient,
                'editingCanceled': me.onCancelEdit,
                'searchRecipient': me.onSearchRecipient
            },
            'newsletter-manager-tabs-recipient-groups': {
                'createNewsletterGroup': me.onCreateNewsletterGroup,
                'deleteSelected': me.onDeleteSelectedGroups
            }
        });

        me.callParent(arguments);
    },

    /**
     * Called when the users reaches in the recipient tab
     * @param field
     */
    onSearchRecipient: function(field) {
        if(!field) {
            return;
        }

        var me = this,
            searchString = Ext.String.trim(field.getValue()),
            store = me.subApplication.recipientStore;

            //scroll the store to first page
            store.currentPage = 1;

            //If the search-value is empty, reset the filter
            if ( searchString.length === 0 ) {
                store.clearFilter();
            } else {
                //This won't reload the store
                store.filters.clear();
                //Loads the store with a special filter
                store.filter('filter', searchString);
            }
    },

    /**
     * Called when the user cancels an edit. If it was a new, unsaved record, we'll remove it from store
     */
    onCancelEdit: function(editor, event) {
        var me = this,
            store = me.subApplication.recipientStore,
            record = event.record;

        if(record.get('id') == 0) {
            store.remove(record);
        }

    },

    /**
     * Called when the user clicked the 'add recipient' button in the recipient tab
     */
    onAddRecipient: function () {
        var me = this,
            record = Ext.create('Shopware.apps.NewsletterManager.model.Recipient'),
            grid = me.getRecipientGrid(), rowEdit = grid.plugins[0],
            store = me.subApplication.recipientStore;

        store.add(record);

        rowEdit.startEdit(record, 1);

    },

    /**
     * Called when the user clicks the 'delete selected' button
     * @param records
     */
    onDeleteSelectedGroups: function(records) {
        var me = this,
            store = me.subApplication.recipientGroupStore;

        if(!records.length > 0) {
            return;
        }
        Ext.MessageBox.confirm('{s name=deleteRecipientGroupTitle}Delete recipient group(s){/s}', '{s name=deleteRecipientGroupMessage}Do you really want to delete the selected recipient group(s)?{/s}', function (response) {
            if ( response !== 'yes' ) {
                return;
            }
            store.remove(records);
            Shopware.Notification.createGrowlMessage(me.snippets.deleteRecipient.successTitle, me.snippets.deleteRecipient.successMessage, me.snippets.growl);
            store.save();
        });
    },

    /**
     * Called when the user clicks the 'create own newsletter group' button in the 'recipient group' view.
     * Will ask for a name, check if this name is already in use and create the corresponding group if it does not
     * @param store
     */
    onCreateNewsletterGroup: function(store) {
        var me = this,
            found, record;

        Ext.MessageBox.prompt('{s name=newGroup}Create new newsletter group{/s}', '{s name=enterNameOfGroup}Please enter the name of the new group{/s}', function(btn, newGroupName) {
            if(btn == 'ok' && newGroupName != '') {
                // Make sure that there is no group with this name
                found = store.find('name', newGroupName, caseSensitive=false);
                if(found != -1){
                    Shopware.Notification.createGrowlMessage('{s name=alreadyExisting}Already existing{/s}', '{s name=groupAlreadyExisting}A group with this name is already existing{/s}', me.snippets.growl);
                    return;
                }

                // Create new record
                record = Ext.create('Shopware.apps.NewsletterManager.model.NewsletterGroup', {
                    name: newGroupName
                });

                // persist
                record.save({
                    callback: function(data, operation){
                        if(operation.success){
                            Shopware.Notification.createGrowlMessage(me.snippets.saveNewsletterGroup.successTitle, me.snippets.saveNewsletterGroup.successMessage, me.snippets.growl);
                            store.reload();
                            me.subApplication.newsletterGroupStore.reload();
                        }else{
                            Shopware.Notification.createGrowlMessage(me.snippets.saveNewsletterGroup.errorTitle, me.snippets.saveNewsletterGroup.errorMessage, me.snippets.growl);
                        }
                    }
                });
            }


        });
    },

    /**
     * Called when the user double clicked a row in the recipient view - but before the row becomes editable
     * @param ediotor
     * @param event
     */
    onBeforeEdit: function(editor, event) {
        var me = this,
            columns = editor.editor.items.items,
            isCustomer = event.record.get('isCustomer');

        //editing users is not possible. We have a nice "edit user" button for those
        if(isCustomer) {
            event.cancel = true;
        }
        columns[1].setValue(event.record.get('email'));
    },

    /**
     * Called after the user edited a row in the recipient view and clicked the 'update' button
     * @param editor
     * @param event
     */
    onSaveRecipient: function(editor, event) {
        var me = this,
            record = event.record,
            newGroupId = event.newValues['address.groupId'], newGroup,
            newMail = event.newValues['address.email'],
            store = me.subApplication.newsletterGroupStore;

        // make sure, that we have a valid group here...
        newGroup = store.getById(newGroupId);

        if(newGroup instanceof Ext.data.Model) {
            record.set('groupId', newGroupId);
            record.set('email', newMail);
            record.save({
                callback: function(data, operation){
                    if(operation.success){
                        var newRecord = operation.getRecords()[0];
                        Shopware.Notification.createGrowlMessage(me.snippets.saveRecipient.successTitle, me.snippets.saveRecipient.successMessage, me.snippets.growl);
                        if(record.get('id') == 0){
                            store.add(record);
                        }
                        record.set(record.data);
                        store.save();
                    }else{
                        Shopware.Notification.createGrowlMessage(me.snippets.saveRecipient.errorTitle, me.snippets.saveRecipient.errorMessage, me.snippets.growl);
                    }
                }
            });
        }

    },

    /**
     * Called when the user clicked the 'delete selected' button in the recipient view
     * @param records
     */
    onDeleteRecipient: function(records) {
        var me = this,
            store = me.subApplication.recipientStore;

        if(!records.length > 0) {
            return;
        }

        Ext.MessageBox.confirm('{s name=deleteRecipientTitle}Delete recipient(s){/s}', '{s name=deleteRecipientMessage}Do you really want to delete the selected recipient(s)?{/s}', function (response) {
            if ( response !== 'yes' ) {
                return;
            }
            store.remove(records);
            Shopware.Notification.createGrowlMessage(me.snippets.deleteRecipient.successTitle, me.snippets.deleteRecipient.successMessage, me.snippets.growl);
            store.save();
        });


    },

    /**
     * Called when the user clicked the 'show customer' action button in the recipients view.
     * Will open the Customer backend module
     * @param record
     */
    onShowCustomer: function(record) {
        var me = this,
            id,
            customer = record.getCustomer();

        if(customer instanceof Ext.data.Store && customer.first() instanceof Ext.data.Model) {
            id = customer.first().get('id');
            Shopware.app.Application.addSubApplication({
                name: 'Shopware.apps.Customer',
                action: 'detail',
                params: {
                    customerId: id
                }
            });
        }
    },

    /**
     * Called when the sender_dialog is submitted
     */
    onSaveSender: function(form) {
        var me = this,
            store = me.subApplication.senderStore,
            values = form.getValues(),
            record = form.getRecord();

        if(!record instanceof Ext.data.Model){
            return;
        }

        record.set(values);
        record.save({
            callback: function(data, operation){
                if(operation.success){
                    Shopware.Notification.createGrowlMessage(me.snippets.saveSender.successTitle, me.snippets.saveSender.successMessage, me.snippets.growl);
                    if(record.get('id') == 0){
                        store.add(record);
                    }
                    store.save();
//                    me.subApplication.senderStore.reload();
                }else{
                    Shopware.Notification.createGrowlMessage(me.snippets.saveSender.errorTitle, me.snippets.saveSender.errorMessage, me.snippets.growl);
                }
            }
        });
    },

    /**
     * Called when the user clicks the "create new sender" button in the toolbar
     */
    onCreateNewSender: function() {
        var me = this,
            senderDialog;

        // todo@dn: don't show a dialog: create a new model and edit it directly with a row editor
        senderDialog = me.getView('SenderDialog').create({
            record: Ext.create('Shopware.apps.NewsletterManager.model.Sender')
        });
    },

    /**
     * Called when the user clicks the 'edit' button in the action column.
     * Will open up a new window allowing the user to edit this record
     * @param record
     */
    onEditSender: function(record) {
        var me = this,
            senderDialog;

        senderDialog = me.getView('SenderDialog').create({
            record: record
        });

    },

    /**
     * Called when the users clicks the 'delete' button in the action column.
     * Will ask a for confirmation and then delete the record
     * @param record
     */
    onDeleteSender: function(records) {
        var me = this,
            store = me.subApplication.senderStore;

        if(!records.length > 0) {
            return;
        }

        Ext.MessageBox.confirm('{s name=deleteSenderTitle}Delete sender{/s}', '{s name=deleteSenderMessage}Do you really want to delete the selected senders?{/s}', function (response) {
            if ( response !== 'yes' ) {
                return;
            }
            store.remove(records);
            Shopware.Notification.createGrowlMessage(me.snippets.deleteSender.successTitle, me.snippets.deleteSender.successMessage, me.snippets.growl);
            //                        Shopware.Notification.createGrowlMessage(me.snippets.deleteSender.errorTitle, me.snippets.deleteSender.errorMessage, me.snippets.growl);
            store.save();
        });


    }

});
//{/block}
