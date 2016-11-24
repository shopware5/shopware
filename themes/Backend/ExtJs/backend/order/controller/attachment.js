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
 * @author shopware AG
 */

//{namespace name=backend/order/main}
//{block name="backend/order/controller/attachment"}
Ext.define('Shopware.apps.Order.controller.Attachment', {

    /**
     * Extend from the standard ExtJS 4 controller
     *
     * @type { String }
     */
    extend: 'Ext.app.Controller',

    /**
     * all references to get the elements by the applicable selector
     *
     * @type { Array }
     */
    refs: [
        { ref: 'attachmentGrid', selector: 'order-mail-attachment' }
    ],

    /**
     * A template method that is called when your application boots.
     * It is called before the Application's launch function is executed
     * so gives a hook point to run any code before your Viewport is created.
     */
    init: function() {
        var me = this;

        me.control({
            'order-mail-window order-mail-attachment': {
                'selectionModel-selection-change': me.onSelectionChanged,
                'create-and-add-document': me.createDocument
            },
            'order-mail-window order-mail-form': {
                afterSendMail: me.onAfterSendMail
            }
        });

        me.callParent(arguments);
    },

    /**
     * Sorts the store and sets active to true for all selected records.
     *
     * @param { Ext.data.Store } store
     * @param { Ext.selection.CheckboxModel } selectionModel
     * @param { Ext.data.Model[] } selected
     */
    onSelectionChanged: function(store, selectionModel, selected) {
        var me = this;

        store.each(function(record) {
            record.set('active', me.getAttachmentGrid().isSelected(selected, record.get('id')));
        });

        store.sort();
    },

    /**
     * Resets all records to inactive for a clean state.
     *
     * @param { Ext.data.Store } attachmentStore
     */
    onAfterSendMail: function(attachmentStore) {
        attachmentStore.resetDocuments();
    },

    /**
     * Creates a new document and save them.
     * If add true add the new document to the mail attachment
     *
     * @param { Ext.grid.Panel } attachmentGrid
     * @param { boolean } addAsAttachment
     * @param { number } orderId
     * @param { number } documentType
     * @param { Ext.data.Store } listStore
     */
    createDocument: function(attachmentGrid, addAsAttachment, orderId, documentType, listStore) {
        var me = this,
            store = Ext.create('Shopware.apps.Order.store.Configuration'),
            config = Ext.create('Shopware.apps.Order.model.Configuration');

        if (!documentType) {
            return;
        }

        attachmentGrid.setLoading(true);

        config.set('orderId', orderId);
        config.set('documentType', documentType);
        store.add(config);

        store.sync({
            callback: Ext.bind(me.callStoreReload, me, [attachmentGrid, addAsAttachment, listStore])
        });
    },

    /**
     * Reloads the attachmentGrid.listStore.
     *
     * @param { Ext.grid.Panel } attachmentGrid
     * @param { boolean|null } addAsAttachment
     * @param { Ext.data.Store } listStore
     */
    callStoreReload: function(attachmentGrid, addAsAttachment, listStore) {
        var me = this;

        listStore.reload({
            callback: Ext.bind(me.applyNewDocument, me, [attachmentGrid, addAsAttachment])
        });
    },

    /**
     * Callback function to apply the new document to the store.
     *
     * @param { Ext.grid.Panel } attachmentGrid
     * @param { boolean|null } addAsAttachment
     */
    applyNewDocument: function(attachmentGrid, addAsAttachment) {
        var orderRecord = attachmentGrid.getRecord(
                attachmentGrid.listStore,
                attachmentGrid.record.get('id')
            );

        if (!orderRecord) {
            attachmentGrid.setLoading(false);
            return;
        }

        orderRecord.getReceipt().each(function(record) {
            attachmentGrid.store.add(record);
        });

        if (addAsAttachment) {
            attachmentGrid.store.getLastDocument().set('active', true);
            attachmentGrid.selectDocument(attachmentGrid.store.getLastDocument());
        }

        attachmentGrid.setLoading(false);
    }
});
//{/block}
