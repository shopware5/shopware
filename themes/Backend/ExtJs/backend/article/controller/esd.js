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
 * @package    Article
 * @subpackage Esd
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Controller - Article backend module
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/controller/esd"}
Ext.define('Shopware.apps.Article.controller.Esd', {
    /**
     * The parent class that this class extends.
     * @string
     */
    extend: 'Ext.app.Controller',

    /**
     * Set component references for easy access
     * @array
     */
    refs: [
        { ref: 'mainWindow', selector: 'article-detail-window' },
        { ref: 'saveButton', selector: 'article-detail-window button[name=save-article-button]' },
        { ref: 'cancelButton', selector: 'article-detail-window button[name=cancel-button]' },
        { ref: 'esdSaveButton', selector: 'article-detail-window button[name=esd-save-button]' },
        { ref: 'esdBackButton', selector: 'article-detail-window button[name=esd-back-button]' },
        { ref: 'esdListing', selector: 'article-detail-window article-esd-list' },
        { ref: 'mediaDropZone', selector: 'article-detail-window article-esd-detail html5fileupload' },
        { ref: 'esdSerialsListing', selector: 'article-detail-window article-esd-serials' },
        { ref: 'esdTab', selector: 'article-detail-window container[name=esd-tab]' }
    ],

    /**
     * Contains all snippets for the component.
     * @object
     */
    snippets: {
        growlMessage: '{s name=growl_message}Article{/s}',
        addSerialsTitle: '{s name=esd/add_serials_title}Add Serials{/s}',
        error: {
            title: '{s name=esd/error/title}Error{/s}',
            noFolder: '{s name=esd/error/no_folder}The ESD folder could not be found.{/s}'
        },
        success: {
            title: '{s name=esd/success/title}Success{/s}',
            esdSaved: '{s name=esd/success/esd_saved}The ESD has been saved.{/s}',
            esdCreated: '{s name=esd/success/esd_created}The ESD has been created.{/s}',
            esdRemoved: '{s name=esd/success/esd_removed}The selected ESDs have been removed{/s}',
            serialsAdded: '{s name=esd/success/serials_added}Serialnumbers have been added{/s}',
            unusedSerialsRemoved: '{s name=esd/success/unused_serials_removed}All unused serialnumbers have been removed{/s}',
            serialRemoved: '{s name=esd/success/serial_removed}The selected serialnumbers have been removed{/s}'
        },
        messages: {
            esdRemove: '{s name=esd/message/remove_esd}Are you sure you want to delete the selected ESD(s)?{/s}',
            serialsRemoveUnused: '{s name=esd/message/remove_unused_serials}Are you sure you want to delete all unused serialnumbers?{/s}',
            serialRemove: '{s name=esd/message/remove_serial}Are you sure you want to delete the selected serialnumber(s)?{/s}',
            addSerials: '{s name=esd/message/add_serials}Add new Serials (separated by newlines){/s}'
        },
        buttons: {
            addSerials: '{s name=esd/buttons/add_serials}Add serials{/s}',
            cancel: '{s name=esd/message/cancel}Cancel{/s}'
        }
    },

    /**
     * A template method that is called when your application boots.
     * It is called before the Application's launch function is executed
     * so gives a hook point to run any code before your Viewport is created.
     *
     * @params orderId - The main controller can handle a orderId parameter to open the order detail page directly
     * @return void
     */
    init: function () {
        var me = this;

        me.control({
            'article-detail-window tabpanel[name=main-tab-panel]': {
                beforetabchange: me.onMainTabChange
            },

            'article-detail-window article-esd-list': {
                addEsd: me.onAddEsd,
                editEsd: me.onEditEsd,
                deleteEsd: me.onDeleteEsd,
                searchEsd: me.onSearchEsd,
                backToList: me.onBackToList,
                saveEsd: me.onSaveEsd
            },

            'article-detail-window article-esd-detail': {
                activate: me.onActivate,
                deactivate: me.onDeactivate,
                downloadFile: me.onDownloadFile,
                mediaUpload: me.onMediaUpload,
                hasSerialsChanged: me.onHasSerialsChanged,
                fileChanged: me.onFileChanged
            },

            'article-esd-detail html5fileupload': {
                uploadReady: me.onUploadReady
            },

            'article-detail-window article-esd-serials': {
                addSerials: me.onAddSerials,
                deleteUnusedSerials: me.onDeleteUnusedSerials,
                deleteSerials: me.onDeleteSerials,
                searchSerials: me.onSearchSerials,
                openCustomer: me.onOpenCustomer
            }
        });
        me.callParent(arguments);
    },

    /**
     * Event listener function of the main tab panel in the detail window.
     * Fired when the user changes the tab.
     */
    onMainTabChange: function(panel, newTab, oldTab) {
        if (newTab.name !== 'esd-tab' && oldTab.name !== 'esd-tab') {
            return;
        }

        var me = this;
        var esdTab = me.getEsdTab();
        var store = me.getEsdListing().getStore();
        var activeCard = esdTab.getLayout().getActiveItem();
        var isDetail = (activeCard.xtype === 'article-esd-detail');

        if (newTab.name === 'esd-tab') {
            store.load();
        } else {
            if (isDetail) {
                esdTab.getLayout().setActiveItem(0);
                esdTab.remove(activeCard);
            }
        }
    },

    /**
     * Event listener function of the detail panel
     */
    onActivate: function() {
        var me = this;

        me.enableEsdButtons();
    },

    /**
     * Event listener function of the detail panel
     */
    onDeactivate: function() {
        var me = this;

        me.disableEsdButtons();
    },

    /**
     * Event listener function of the detail panel
     */
    onSaveEsd: function() {
        var me = this,
            record = me.detailWindow.esdRecord;

        record.save({
            callback: function() {
                Shopware.Notification.createGrowlMessage(me.snippets.success.title, me.snippets.success.esdSaved, me.snippets.growlMessage);
                me.detailWindow.infoView.update(record.data);
            }
        });
    },

    /**
     * Internal helper function to enable esd related buttons
     */
    enableEsdButtons: function() {
        var me = this,
            saveButton = me.getSaveButton(),
            cancelButton = me.getCancelButton(),
            esdBackButton = me.getEsdBackButton(),
            esdSaveButton = me.getEsdSaveButton();

        saveButton.hide();
        cancelButton.hide();
        esdBackButton.show();
        esdSaveButton.show();
    },

    /**
     * Internal helper function to disable esd related buttons
     */
    disableEsdButtons: function() {
        var me = this,
            cancelButton = me.getCancelButton(),
            esdBackButton = me.getEsdBackButton(),
            esdSaveButton = me.getEsdSaveButton();

        cancelButton.show();
        esdBackButton.hide();
        esdSaveButton.hide();
    },

    /**
     * Event will be fired when the user clicks the add button in the toolbar
     * @param [string] articleDetailId
     */
    onAddEsd: function(articleDetailId) {
        var me = this,
            store = me.getEsdListing().getStore();

        Ext.Ajax.request({
            url: '{url action="createEsd"}',
            method: 'POST',
            params: {
                articleDetailId: articleDetailId
            },
            success: function(response, opts) {
                Shopware.Notification.createGrowlMessage(me.snippets.success.title, me.snippets.success.esdCreated, me.snippets.growlMessage);
                store.load();
            }
        });
    },

    /**
     * Event will be fired when the user clicks the back button in the toolbar
     */
    onBackToList: function() {
        var me = this,
            esdTab = me.getEsdTab(),
            saveButton = me.getSaveButton(),
            cardToRemove = esdTab.getLayout().getActiveItem();

        esdTab.getLayout().setActiveItem(0);
        esdTab.remove(cardToRemove);

        me.getEsdListing().getStore().load();

        saveButton.show();
    },

    resetToList: function() {
        var me = this,
            esdTab = me.getEsdTab(),
            saveButton = me.getSaveButton(),
            cardToRemove = esdTab.getLayout().getActiveItem();

        if(cardToRemove.$className === 'Shopware.apps.Article.view.esd.List') {
            return false;
        }
        esdTab.getLayout().setActiveItem(0);
        esdTab.remove(cardToRemove);
        me.getEsdListing().getStore().load();

        saveButton.show();
    },

    /**
     * @param [string] value
     */
    onSearchEsd: function(value) {
        var me = this,
            esdListing = me.getEsdListing(),
            store = esdListing.getStore();

        value = Ext.String.trim(value);
        store.filters.clear();
        store.currentPage = 1;

        if (value.length > 0) {
            store.filter({ property: 'free', value: value });
        } else {
            store.load();
        }
    },

    /**
     * Event listener function of the esd list. Fired when the
     * user clicks on the pencil action column.
     *
     * @param [Ext.data.Model] The selected record
     */
    onEditEsd: function(record) {
        var me = this,
            esdTab = me.getEsdTab(),
            serialStore = Ext.create('Shopware.apps.Article.store.Serial'),
            fileStore = Ext.create('Shopware.apps.Article.store.EsdFile').load(
                function(records, operation, success) {
                    if (success == false && operation.error == 'noFolder') {
                        Shopware.Notification.createGrowlMessage(me.snippets.error.title, me.snippets.error.noFolder, me.snippets.growlMessage);
                    }
                }
            );

        me.fileStore = fileStore;

        serialStore.getProxy().extraParams.esdId = record.get('id');
        serialStore.load();

        me.detailWindow = Ext.create('Shopware.apps.Article.view.esd.Detail', {
            esdRecord: record,
            serialStore: serialStore,
            fileStore: fileStore,
            article: me.article
        });

        esdTab.add(me.detailWindow);
        esdTab.getLayout().setActiveItem(1);
    },

    /**
     * Event listener function which fired when the user selects esd in the listing
     * and clicks the delete button in the toolbar.
     * @param records
     */
    onDeleteEsd: function(records) {
        var me = this,
            esdListing = me.getEsdListing(),
            store = esdListing.getStore();

        if (records.length > 0) {
            // we do not just delete - we are polite and ask the user if he is sure.
            Ext.MessageBox.confirm(me.snippets.growlMessage, me.snippets.messages.esdRemove , function (response) {
                if ( response !== 'yes' ) {
                    return;
                }
                store.remove(records);
                store.sync({
                    callback: function() {
                        Shopware.Notification.createGrowlMessage(me.snippets.success.title, me.snippets.success.esdRemoved, me.snippets.growlMessage);
                        store.currentPage = 1;
                        store.load();
                    }
                });
            });
        }
    },

    /**
     * Creates window
     *
     * @return [Enlight.app.Window]
     */
    getAddSerialsWindow: function() {
        var me = this;

        var win = Ext.create('Enlight.app.Window', {
            title: me.snippets.addSerialsTitle,
            width: 400,
            height: 400,
            layout: 'fit',
            subApplication: me.subApplication,
            subApp: me.subApplication,
            items: [{
                xtype: 'form',
                layout: 'anchor',
                bodyPadding: 10,
                border: false,
                defaults: {
                    anchor: '100%'
                },
                dockedItems: [{
                    xtype: 'toolbar',
                    dock: 'bottom',
                    cls: 'shopware-toolbar',
                    items: [{
                        text: me.snippets.buttons.addSerials,
                        cls: 'primary',
                        formBind: true,
                        handler: function() {
                            var form = this.up('form').getForm();
                            if (form.isValid()) {
                                var serials = form.getValues().serials;
                                me.saveNewSerials(serials);
                                win.close();
                            }
                        }
                    }, {
                        text: me.snippets.buttons.cancel,
                        cls: 'secondary',
                        handler: function() {
                            win.close();
                        }
                    }]
                }],
                items: [{
                    bodyPadding: 10,
                    html: me.snippets.messages.addSerials
                }, {
                    name: 'serials',
                    xtype: 'textarea',
                    height: 250,
                    allowBlank: false
                }]
            }]
        });

        return win;
    },

    /**
     * Event will be fired when the user clicks the add button in the toolbar
     */
    onAddSerials: function() {
        var me = this;

        me.getAddSerialsWindow().show();
    },

    /**
     * @param [string] serials
     */
    saveNewSerials: function(serials) {
        var me = this;

        Ext.Ajax.request({
            url: '{url action="saveSerials"}',
            method: 'POST',
            params: {
                esdId: me.detailWindow.esdRecord.get('id'),
                serials: serials
            },
            success: function(response, opts) {
                Shopware.Notification.createGrowlMessage(me.snippets.success.title, me.snippets.success.serialsAdded, me.snippets.growlMessage);
                me.getEsdSerialsListing().getStore().load();
            }
        });
    },

    /**
     * Event will be fired when the user clicks the add button in the toolbar
     */
    onDeleteUnusedSerials: function() {
        var me = this;

        Ext.MessageBox.confirm(me.snippets.growlMessage, me.snippets.messages.serialsRemoveUnused , function (response) {
            if ( response !== 'yes' ) {
                return;
            }
            Ext.Ajax.request({
                url: '{url action="deleteUnusedSerials"}',
                method: 'POST',
                params: {
                    esdId: me.detailWindow.esdRecord.get('id')
                },
                success: function(response, opts) {
                    Shopware.Notification.createGrowlMessage(me.snippets.success.title, me.snippets.success.unusedSerialsRemoved, me.snippets.growlMessage);
                    me.getEsdSerialsListing().getStore().load();
                }
            });
        });
    },

    /**
     * Event listener function which fired when the user selects esd in the listing
     * and clicks the delete button in the toolbar.
     * @param records
     */
    onDeleteSerials: function(records) {
        var me = this,
            serialsListing = me.getEsdSerialsListing(),
            store = serialsListing.getStore();

        if (records.length > 0) {
            Ext.MessageBox.confirm(me.snippets.growlMessage, me.snippets.messages.serialRemove , function (response) {
                if ( response !== 'yes' ) {
                    return;
                }
                store.remove(records);
                store.sync({
                    callback: function() {
                        Shopware.Notification.createGrowlMessage(me.snippets.success.title, me.snippets.success.serialRemoved, me.snippets.growlMessage);
                        store.currentPage = 1;
                        store.load();
                    }
                });
            });
        }
    },

    /**
     * Event will be fired when the user clicks the download button in the toolbar
     */
    onDownloadFile: function() {
        var me = this;

        var record = me.detailWindow.esdRecord;
        var url = '{url action="getEsdDownload"}' + '/filename/' + record.get('file');
        window.open(url, '_blank');
    },

    /**
     * @param [string] value
     */
    onSearchSerials: function(value) {
        var me = this,
            serialsListing = me.getEsdSerialsListing(),
            store = serialsListing.getStore();

        value = Ext.String.trim(value);
        store.filters.clear();
        store.currentPage = 1;
        if (value.length > 0) {
            store.filter({ property: 'free', value: value });
        } else {
            store.load();
        }
    },

    /**
     * Event listener method which fired when the user clicks the customer button
     * in the order list to show the customer detail page.
     *
     * @param [Ext.data.Model] record - The row record
     */
    onOpenCustomer: function(record) {
        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.Customer',
            action: 'detail',
            params: {
                customerId: record.get('customerId')
            }
        });
    },

    /**
     * @param [boolean]
     */
    onHasSerialsChanged: function(checked) {
        var me = this,
            record = me.detailWindow.esdRecord,
            serialsListing = me.getEsdSerialsListing();

        if (checked) {
            record.set('hasSerials', true);
            serialsListing.enable();

        } else {
            record.set('hasSerials', false);
            serialsListing.disable();
        }
    },

    /**
     * @param [string]
     */
    onFileChanged: function(filename) {
        var me = this,
            record = me.detailWindow.esdRecord;

        record.set('file', filename);
    },


    onUploadReady: function() {
        var me = this;

        me.fileStore.load();
    },

    /**
     * Event will be fired when the user want to upload images over the button on the image tab.
     *
     * @event
     * @param [object]
     */
    onMediaUpload: function(field) {
        var dropZone = this.getMediaDropZone();
        this.uploadMedia(field, dropZone);
    },

    /**
     * Internal helper function to upload article images.
     * @param field
     * @param dropZone
     */
    uploadMedia: function(field, dropZone) {
        var fileField = field.getEl().down('input[type=file]').dom;
        dropZone.iterateFiles(fileField.files);
    }

});
//{/block}
