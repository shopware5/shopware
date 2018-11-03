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

//{namespace name=backend/mail/controller/attachment}

/**
 * todo@all: Documentation
 * @subpackage Controller
 */
//{block name="backend/mail/controller/attachment"}
Ext.define('Shopware.apps.Mail.controller.Attachment', {

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
        { ref: 'formPanel', selector: 'mail-main-form' },
        { ref: 'tree', selector: 'mail-main-attachments' }
    ],

    /**
     * Contains all snippets for the controller
     * @object
     */
    snippets:{
        multipleDeleteTitle: '{s name=message_delete_multiple_title}Delete selected attachments{/s}',
        multipleDeleteMessage: '{s name=message_delete_multiple_content}[0] attachments have been selected. Are you sure you want to delete all selected attachments?{/s}',

        deleteSuccessTitle: '{s name=message_delete_success_message}Successful{/s}',
        deleteSuccessMessage: '{s name=message_delete_success_title}Attachments have been removed{/s}',

        deleteErrorTitle: '{s name=message_delete_error_title}Error{/s}',
        deleteErrorMessage: '{s name=message_delete_error_message}An error has occurred while deleting.{/s}'  ,

        singleDeleteTitle: '{s name=message_delete_single_title}Delete selected attachment{/s}',
        singleDeletMessage: '{s name=message_delete_single_content}Are you sure that you want to delete the selected attachment?{/s}',

        growlMessage: '{s name=growlMessage}Mail{/s}'
    },

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * @return void
     */
    init: function() {
        var me = this;

        me.control({
            'mail-main-attachments': {
                checkchange:    me.onCheckChange,
                onDeleteSingle: me.onDeleteSingle
            },

            'mail-main-attachments mediafield': {
                selectMedia: me.selectMedia
            },

            'mail-main-attachments dataview': {
                drop: me.onDrop
            },

            'mail-main-attachments button[action=main-attachments-delete]' : {
                click: me.onDelete
            }
        });

        me.callParent(arguments);
    },

    /**
     * Event listener which deletes a single attachment
     *
     * @param [Ext.grid.View] grid - The grid on which the event has been fired
     * @param [integer] rowIndex - On which row position has been clicked
     * @return void
     */
    onDeleteSingle: function (grid, rowIndex, colIndex, item) {
        var me      = this,
            store   = grid.getStore(),
            record  = store.getAt(rowIndex),
            message = me.snippets.singleDeletMessage,
            title   = me.snippets.singleDeleteTitle;

        Ext.MessageBox.confirm(title, message, function (response) {
            if (response !== 'yes') {
                return false;
            }

            record.destroy({
                callback: function() {
                    store.load();
                }
            });
        });
    },

    /**
     * Event listener which deletes selected attachments
     *
     * @param [object] btn - the btn that fired the event
     * @event click
     * @return void
     */
    onDelete: function(btn) {
        var me           = this,
            checkedItems = me.getTree().getChecked(),
            message      = Ext.String.format(me.snippets.multipleDeleteMessage, checkedItems.length),
            title        = me.snippets.multipleDeleteTitle;

        Ext.MessageBox.confirm(title, message, function (response) {
            if (response !== 'yes') {
                return false;
            }

            Ext.each(checkedItems, function(record, index) {
                var isLast = (index + 1 === checkedItems.length);
                record.destroy({
                    callback: function() {
                        if (isLast) {
                            me.reloadAttachmentTree();
                        }
                    }
                });
            });

            btn.setDisabled(true);

            Shopware.Notification.createGrowlMessage(me.snippets.deleteSuccessTitle, me.snippets.deleteSuccessMessage, me.snippets.growlMessage);
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
        var me           = this,
            window       = me.getMainWindow(),
            deleteBtn    = window.down('button[action=main-attachments-delete]'),
            checkedItems = me.getTree().getChecked();

        // Delete button should be only enabled if one or more items are checked
        deleteBtn.setDisabled(checkedItems.length < 1);
    },

    /**
     * @event drop
     * @param [HTMLElement ] node The GridView node if any over which the mouse was positioned.
     * @param [Object] The data object gathered at mousedown time
     * @param [Ext.data.Model] - The Model over which the drop gesture took place.
     * @return void
     */
    onDrop: function(node, data, overModel) {
        var record = data.records[0];

        record.getProxy().extraParams.destinationShopId = overModel.get('shopId');
        record.save();
    },

    /**
     * @event selectMedia
     * @param [object] mediaManager - Shopware.MediaManager.DropZone
     * @param [array] selected - Array of the selected Ext.data.Model's
     */
    selectMedia: function(mediaManager, selected) {
        var me = this;

        Ext.each(selected, function(item, index) {
            var isLast = (index + 1 === selected.length);

            var attachment = Ext.create('Shopware.apps.Mail.model.Attachment', {
                checked: false,
                filename: item.get('name') + item.get('extension'),
                leaf: true
            });

            var mailRecord = me.getFormPanel().getForm().getRecord();

            attachment.getProxy().extraParams.mailId = mailRecord.get('id');
            attachment.getProxy().extraParams.mediaId = item.get('id');
            attachment.save({
                callback: function() {
                    if (isLast) {
                        me.reloadAttachmentTree();
                    }
                }
            });
        });
    },

    /**
     * @return void
     */
    reloadAttachmentTree: function() {
        var me           = this,
            store        = me.getStore('Attachment'),
            rootNode     = me.getTree().getRootNode(),
            mailRecord   = me.getFormPanel().getRecord();

        store.getProxy().extraParams.mailId = mailRecord.get('id');

        rootNode.removeAll(false);
        store.load();
    }
});
//{/block}
