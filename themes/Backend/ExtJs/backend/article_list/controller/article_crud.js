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
 * This controller takes care of all CRUD actions for products
 */
//{namespace name=backend/article_list/main}
//{block name="backend/article_list/controller/article_crud"}
Ext.define('Shopware.apps.ArticleList.controller.ArticleCrud', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend: 'Ext.app.Controller',

    refs: [
        { ref:'grid', selector:'multi-edit-main-grid' },
        { ref:'showVariantsCheckbox', selector:'multi-edit-category-tree checkbox[name=displayVariants]' }
    ],

    /**
     * Contains all snippets for the component.
     * @object
     */
    snippets: {
        growlMessage: '{s name=growl_message}Article{/s}',
        messages: {
            successTitle: '{s name=messages/success}Success{/s}',
            deleteSuccess: '{s name=messages/delete_success}The selected articles have been removed{/s}',
            deleteArticleTitle: '{s name=messages/delete_article_title}Delete selected Article(s)?{/s}',
            deleteArticle: '{s name=messages/delete_article}Are you sure you want to delete the selected Article(s)?{/s}'
        }
    },

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
            'multi-edit-main-grid': {
                deleteProduct: me.onDeleteArticle,
                deleteMultipleProducts: me.onDeleteMultipleArticles,
                saveProduct: me.onSaveProduct
            }
        });

        me.callParent(arguments);
    },

    /**
     * @param record
     */
    onDeleteArticle: function(record) {
        var me    = this,
            store = me.getGrid().getStore();

        Ext.MessageBox.confirm(me.snippets.messages.deleteArticleTitle, me.snippets.messages.deleteArticle, function (response) {
            if (response !== 'yes') {
                return false;
            }
            record.destroy({
                callback: function() {
                    Shopware.Notification.createGrowlMessage(me.snippets.messages.successTitle, me.snippets.messages.deleteSuccess, me.snippets.growlMessage);
                    store.load();
                }
            });
        });
    },

    /**
     * @param records
     */
    onDeleteMultipleArticles: function(records) {
        var me    = this;

        if (records.length > 0) {
            // we do not just delete - we are polite and ask the user if he is sure.
            Ext.MessageBox.confirm(me.snippets.messages.deleteArticleTitle, me.snippets.messages.deleteArticle, function (response) {
                if ( response !== 'yes' ) {
                    return;
                }
                me.deleteMultipleRecords(records, function() {
                    var store = me.getGrid().getStore();
                    store.reload();

                    Shopware.Notification.createGrowlMessage(me.snippets.messages.successTitle, me.snippets.messages.deleteSuccess, me.snippets.growlMessage);
                });
            });
        }
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
                if (records.length == 0) {
                    callback();
                } else {
                    me.deleteMultipleRecords(records, callback);
                }
            }
        })
    },


    /**
     * Called after the user edited a grow in the main grid
     *
     * @param editor
     * @param context
     */
    onSaveProduct: function(editor, context) {
        var me = this,
            record = context.record,
            isActiveChange = false,
            changes = record.getChanges();

        isActiveChange = typeof changes.Article_active !== 'undefined'
                         && record.raw.Article_active != changes.Article_active;

        record.save({
            params: {
                resource: 'product'
            },
            success: function(record, operation) {
                if (operation.success) {
                    Shopware.Notification.createGrowlMessage(
                            '{s name=successTitle}Success{/s}',
                            Ext.String.format('{s name=successMessage}Saved [0]{/s}', record.get('Article_name')),
                            'ArticleList',
                            'growl',
                            true
                    );

                    if (isActiveChange && me.getShowVariantsCheckbox().getValue()) {
                        // If the global product active flag has changed and the "show Variants" toggle is on,
                        // the store should reload to reflect this change to all related products
                        record.store.load();
                    } else {
                        // Update the modified record by the data, the controller returned
                        // This way we make sure, that the record shows the data which is stored
                        // in the database
                        Ext.each(Object.keys(record.getData()), function (key) {
                            record.set(key, operation.records[0].data[key]);
                        });
                    }

                }
            },
            failure: function(record, operation) {
                Shopware.Notification.createStickyGrowlMessage({
                    title: '{s name=error}Error{/s}',
                    text: '{s name=unknownError}An unknown error occurred, please check your server logs{/s}',
                    log: true
                },
                'ArticleList');
            }

        });
    },

});
//{/block}
