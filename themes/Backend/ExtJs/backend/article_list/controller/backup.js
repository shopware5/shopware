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

/**
 * The backup controller handles the backup
 */
//{namespace name=backend/article_list/main}
//{block name="backend/article_list/controller/backup"}
Ext.define('Shopware.apps.ArticleList.controller.Backup', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend: 'Ext.app.Controller',

    refs: [
        { ref:'mainGrid', selector:'multi-edit-main-grid' },
        { ref:'backupGrid', selector:'multi-edit-backup-grid' }
    ],

    /**
     * A template method that is called when your application boots.
     * It is called before the Application's launch function is executed
     * so gives a hook point to run any code before your Viewport is created.
     *
     * @return void
     */
    init: function () {
        var me = this;

        me.control({
            'multiedit-main-window button[action=backup]': {
                click: me.onOpenBackupWindow
            },
            'multi-edit-backup-grid': {
                restoreBackup: me.onRestoreBackup,
                deleteBackup: me.onDeleteBackup
            }
        });

        me.subApplication.backupStore = me.getStore('Shopware.apps.ArticleList.store.Backup').load({
            params: {
                resource: 'product'
            }
        });
        me.subApplication.backupStore.getProxy().extraParams.resource = 'product';

        me.callParent(arguments);
    },

    /**
     * Called after the user clicked the 'delete backup' action button in the backup grid
     */
    onDeleteBackup: function(rowIdx) {
        var me = this,
            store = me.getBackupGrid().getStore(),
            record = store.getAt(rowIdx);

        Ext.MessageBox.confirm(
            '{s name=backup/deleteConfirm}Delete the selected backup?{/s}',
            Ext.String.format('{s name=backup/deleteConfirmMessage}You are about to delete the selected backup from [0]. Do you want to proceed?{/s}', record.get('date')),
            function (response) {
                if ( response !== 'yes' ) {
                    return;
                }

                record.destroy({
                    params: {
                        resource: 'product'
                    },
                    success: function() {
                        store.reload();
                        Shopware.Notification.createGrowlMessage('{s name=successTitle}Success{/s}', Ext.String.format('{s name=successDeleteMessage}Successfully deleted [0]{/s}', ''));
                    },
                    failure: function() {
                        store.reload();
                        Shopware.Notification.createGrowlMessage('{s name=errorTitle}Error{/s}', '{s name=errorDeleteMessage}Could not delete the backup folder - make sure, it is empty{/s}');
                    }
                });

            }
        );
    },

    /**
     * Callback function triggered, when the user clicked the 'restore' button in the main window
     */
    onOpenBackupWindow: function() {
        var me = this,
            window;

        window = me.getBackupWindow();

        window.show();
    },

    /**
     * Convenience method to show a sticky growl message
     *
     * @param message
     */
    showError: function(message) {
        var me = this;

        Shopware.Notification.createStickyGrowlMessage({
            title: '{s name=error}Error{/s}',
            text: message,
            log: true
        },
        'ArticleList');
    },


    /**
     * Callback method triggered, after the user presses the 'reset' action button.
     */
    onRestoreBackup: function(rowIdx) {
        var me = this;

        Ext.MessageBox.confirm(
            '{s name=backup/addConfirm}Revert these changes?{/s}',
            '{s name=backup/addConfirm/Message}You are about to revert the selected changes. Do you want to proceed?{/s}',
            function (response) {
                if ( response !== 'yes' ) {
                    return;
                }
                var config = me.initBackup(rowIdx);
                me.runRestore(config, 0)
            }
        );
    },

    /**
     * Init backup
     *
     * @returns Object
     */
    initBackup: function(rowIdx) {
        var me = this,
            record = me.subApplication.backupStore.getAt(rowIdx);

        me.cancel = false;

        me.createRestoreBackupWindow();

        return { id: record.get('id'), filterString: record.get('filterString'), operationString: record.get('operationString').replace(/[\r\n]+/g, '<br>') };
    },

    /**
     * Creates a Ext.MessageBox with a progressbar in order to show a process while restoring the backup
     */
    createRestoreBackupWindow: function() {
        var me = this;

        me.progressWindow = Ext.MessageBox.show({
            title        : '{s name=restoringTitle}Restoring the backup{/s}',
            msg          : "{s name=restoringMessage}Currently restoring the selected backup.{/s}",
            width        : 500,
            progress     : true,
            closable     : false,
            buttons      : Ext.MessageBox.CANCEL,
            fn           : function(buttonId, text, opt) {

                if (buttonId !== 'cancel') {
                    return;
                }

                // Set the cancel property to true in order to cancel the migration
                // after the next request
                me.cancel = true;
            }
        });

        // workaround to set the height of the MessageBox
        me.progressWindow.setSize(500, 150);
        me.progressWindow.doLayout();


        me.progressWindow.progressBar.reset();
        me.progressWindow.progressBar.animate = true;
        me.progressWindow.progressBar.updateProgress(0, '{s name=startingRevert}Begin restoring the selected file…{/s}');
    },

    /**
     * Called recursively until all items have been processed.
     *
     * @param config
     * @param offset
     */
    runRestore: function(config, offset) {
        var me = this;

        if (me.cancel) {
            me.cancel = false;
            return;
        }

        Ext.Ajax.request({
            url: '{url controller="ArticleList" action = "restore"}',
            timeout: 4000000,
            params : {
                resource: 'product',
                id: config.id,
                offset: offset
            },
            success: function (response, request) {
                if (!response.responseText) {
                    me.showError('{s name=unknownError}An unknown error occurred, please check your server logs{/s}');
                    return;
                }

                var result = Ext.JSON.decode(response.responseText);

                if(!result) {
                    me.progressWindow.close();
                    me.showError(response.responseText);
                }else if(!result.success) {
                    me.progressWindow.close();
                    me.showError(result.message);
                }else{
                    if (result.data.offset < result.data.totalCount) {
                        progressText =  Ext.String.format("{s name=backup/alreadyRestored}[0] out of [1] deltas restored{/s}", result.data.offset, result.data.totalCount);
                        me.progressWindow.progressBar.updateProgress(result.data.offset/result.data.totalCount, progressText);

                        me.runRestore(config, result.data.offset);
                    }else{
                        Shopware.Notification.createStickyGrowlMessage({
                                title: '{s name=restoredBackup}Backup restored{/s}',
                                text: Ext.String.format('{s name=createdRestoreMessage}The following changes have been undone:<br>[0]{/s}', config.operationString),
                                log: true
                            },
                            'ArticleList'
                        );

                        me.progressWindow.progressBar.updateProgress(1, "Done");
                        me.progressWindow.close();

                        // Reload the main grid - only possible if an AST was defined for that store
                        if (me.getMainGrid().getStore().getProxy().extraParams.ast) {
                            me.getMainGrid().getStore().reload();
                        }
                    }
                }
            },
            failure: function (response, request) {
                if(response.responseText) {
                    me.showError(response.responseText);
                } else {
                    me.showError('{s name=unknownError}An unknown error occurred, please check your server logs{/s}');
                }
            }
        });
    },

    /**
     * Return an existing instance of the backup window or create a new one
     *
     * @returns Ext.window
     */
    getBackupWindow: function() {
        var me = this;

        me.subApplication.backupStore.load();

        if (me.subApplication.backupWindow && !me.subApplication.backupWindow.isDestroyed) {
            return me.subApplication.backupWindow;
        } else {
            me.subApplication.backupWindow = me.getView('Backup.Window').create({
                backupStore: me.subApplication.backupStore
            });
        }

        return me.subApplication.backupWindow;
    }
});
//{/block}
