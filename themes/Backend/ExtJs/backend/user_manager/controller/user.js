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
 * @package    UserManager
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/user_manager/view/main}

/**
 * Shopware - User-Manager user detail mask
 *
 * todo@all: Documentation
 */
//{block name="backend/user_manager/controller/user"}
Ext.define('Shopware.apps.UserManager.controller.User', {

    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
    extend: 'Enlight.app.Controller',

    /**
     * Contains all snippets for the controller
     * @object
     */
    snippets:{
        form:{
            errorTitle: '{s name=message/password/form/error_title}Error saving the form{/s}',
            errorMessage: '{s name=message/password/form/error_message}The field -[0]- is not valid{/s}'
        },
        growlMessage:'{s name=message/growlMessage}User manager{/s}',
        unlock: {
            successTitle: '{s name="create_user/unlock/success_title"}Success{/s}',
            successText: '{s name="create_user/unlock/success_text"}Successfully unlocked the user.{/s}',
            errorTitle: '{s name="create_user/unlock/error_title"}Failure{/s}',
            errorText: '{s name="create_user/unlock/error_text"}An error occurred while unlocking the user.{/s}'
        }
    },

    refs: [
        { ref: 'userCreateWindow', selector: 'usermanager-user-create' },
        { ref: 'userCreateForm', selector: 'usermanager-user-create form[name=main-form]' },
        { ref: 'attributeForm', selector: 'usermanager-user-create shopware-attribute-form' },
        { ref: 'userListGrid', selector: 'usermanager-user-list' }
    ],

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the subapplication
     *
     * @return void
     */
    init: function() {
        var me = this;

        me.control({
            'usermanager-user-create': {
                saveUser: me.onSaveUser,
                unlockUser: me.onUnlockUser
            },
            'button[action=addUser]': {
                click: me.onOpenAddUser
            },
            'usermanager-user-list': {
                editUser: me.onEditUser,
                deleteUser: me.onDeleteUser,
                deleteUsers: me.onDeleteUsers
            },
            'usermanager-user-list textfield[action=searchUser]':{
                change: me.onSearchUser
            }
        });
    },

    /**
     * Filters the grid with the passed search value to find the right voucher
     *
     * @param field
     * @param value
     * @return void
     */
    onSearchUser:function (field, value) {
        var me = this,
            searchString = Ext.String.trim(value),
            store = me.subApplication.getStore('User');

        store.getProxy().extraParams = {
            search: searchString
        };

        store.load({});
    },

    /**
     * Event to save user details back to store
     * @param record
     * @param formPnl
     */
    onSaveUser: function(record, formPnl) {
        var me = this,
            missingField = "Unknown field";

        if (!formPnl.getForm().isValid()){
            // check which field is not valid in order to tell the user, why the customer cannot be saved
            // SW-4322
            formPnl.getForm().getFields().each(function(f){
                 if (!f.validate()){
                    if (f.fieldLabel){
                        missingField = f.fieldLabel;
                    } else if (f.name){
                        missingField = f.name;
                    }
                    Shopware.Notification.createGrowlMessage(me.snippets.form.errorTitle, Ext.String.format(me.snippets.form.errorMessage, missingField), me.snippets.growlMessage)
                    return false;
                 }

             });
            return;

        }
        var values = formPnl.getForm().getValues();

        formPnl.getForm().updateRecord(record);

        if (values.apiActive === 0) {
            record.set('apiKey', '');
        }

        Shopware.app.Application.fireEvent('Shopware.ValidatePassword', function() {

            formPnl.up('window').setLoading(true);

            record.save({
                callback: function (record) {
                    me.getAttributeForm().saveAttribute(record.get('id'));

                    formPnl.up('window').setLoading(false);
                    me.getStore('User').load();
                    formPnl.up('window').destroy();

                    Shopware.Notification.createGrowlMessage(
                            '{s name=user/Success}Successful{/s}',
                            Ext.String.format('{s name="user/editSuccessful"}User \'[0]\' was updated{/s}', formPnl.getForm().getValues().name),
                            '{s name="user/userManager"}User Manager{/s}'
                    );
                    Ext.Ajax.request({
                        url: '{url controller=login action=getLoginStatus}',
                        success: function (response) {
                            var json = Ext.decode(response.responseText);
                            if (!json.success) {
                                window.location.href = '{url controller=index}';
                            }
                        },
                        failure: function () {
                            window.location.href = '{url controller=index}';
                        }
                    });
                }
            });
        });
    },

    /**
     * @param { Ext.container.Container } unlockContainer
     * @param { Ext.data.Model } record
     */
    onUnlockUser: function (unlockContainer, record, formPnl) {
        var me = this,
            displayField = unlockContainer.down('displayfield'),
            button = unlockContainer.down('button');

        Shopware.app.Application.fireEvent('Shopware.ValidatePassword', function() {

            formPnl.up('window').setLoading(true);

            Ext.Ajax.request({
                url: '{url action=unlockUser}',
                params: {
                    userId: record.get('id')
                },
                success: function (response) {
                    var result = Ext.JSON.decode(response.responseText);

                    if (!result.success) {
                        Shopware.Notification.createGrowlMessage(me.snippets.unlock.errorTitle, me.snippets.unlock.errorText, me.snippets.growlMessage);
                        return;
                    }

                    record.set('lockedUntil', new Date());
                    record.set('failedLogins', 0);

                    Shopware.Notification.createGrowlMessage(me.snippets.unlock.successTitle, me.snippets.unlock.successText, me.snippets.growlMessage);
                    displayField.setValue('');
                    button.setDisabled(true);
                    formPnl.up('window').setLoading(false);
                }
            });
        });
    },

    /**
     * Event that catches clicks on new user button - open user-detail view -
     * @param btn
     */
    onOpenAddUser: function(btn) {
        this.getView('user.Create').create({
            record: Ext.create('Shopware.apps.UserManager.model.UserDetail'),
            edit: false
        });
    },

    /**
     * Event that catches while deleting backend users from grid
     * @param view
     * @param rowIndex
     */
    onDeleteUser: function (view,rowIndex){
        var me = this,
        userStore = me.getStore('User'),
        message,
        record = userStore.getAt(rowIndex);

        message = Ext.String.format('{s name="user/messageDeleteUser"}Are you sure you want to delete the user [0]?{/s}', record.data.username);
        Ext.MessageBox.confirm('{s name="user/titleDeleteUser"}Delete user{/s}', message, function (response){
            if (response !== 'yes') return false;

            Shopware.app.Application.fireEvent('Shopware.ValidatePassword', function() {
                record.destroy({
                    success : function () {
                        userStore.load();
                        Shopware.Notification.createGrowlMessage('{s name=user/Success}Successful{/s}', '{s name="user/deletedSuccessfully"}User has been deleted{/s}', '{s name="user/userManager"}User Manager{/s}');
                    },
                    failure : function () {
                        Shopware.Notification.createGrowlMessage('{s name=user/Error}Error{/s}', '{s name="user/deletedError"}An error has occurred while deleting the user{/s}', '{s name="user/userManager"}User Manager{/s}');
                    }
                });
            });
        });
    },

    /**
     * Event that catches batch user deleting
     * @param view
     */
    onDeleteUsers: function(view) {
        var me = this,
            records = view.getSelectionModel().getSelection(),
            userStore = me.getStore('User');

        if(records.length > 0) {
            Ext.MessageBox.confirm('{s name="user/titleDeleteUser"}Delete user{/s}', '{s name="user/messageDeleteMultipleUsers"}Are you sure you want delete these users?{/s}', function (response) {
                if (response !== 'yes') return false;

                Shopware.app.Application.fireEvent('Shopware.ValidatePassword', function() {
                    me.deleteMultipleRecords(records, function () {
                        userStore.load();
                        Shopware.Notification.createGrowlMessage('{s name=user/Success}Successful{/s}', '{s name="user/multipleDeletedSuccessfully"}Users has been deleted{/s}', '{s name="user/userManager"}User Manager{/s}');
                    });
                });
            });
        }
    },

    /**
     * Event that will be fired on click the user edit symbol in grids
     * @param view
     * @param rowIndex
     */
    onEditUser: function (view, rowIndex){
        var me = this,
            store = view.getStore(),
            record = store.getAt(rowIndex);

        me.getStore('UserDetail').load({
            id: record.data.id,
            callback:function (records) {
                me.getView('user.Create').create({
                    record: records[0],
                    edit: true
                });
            }
        });
    },

    /**
     * Will delete a list of records one after another and finally call the callback method
     *
     * @param records
     * @param callback
     */
    deleteMultipleRecords: function(records, callback) {
        var me = this,
            record = records.pop();

        record.destroy({
            callback: function () {
                if (records.length === 0) {
                    callback();
                } else {
                    me.deleteMultipleRecords(records, callback);
                }
            }
        })
    }
});
//{/block}
