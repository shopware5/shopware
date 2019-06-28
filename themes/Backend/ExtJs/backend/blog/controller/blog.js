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
 * @package    Blog
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Controller - Blog backend module
 *
 * Detail controller of the blog module. Handles all action around to
 * edit or create and list a blog.
 */
//{namespace name=backend/blog/view/blog}
//{block name="backend/blog/controller/blog"}
Ext.define('Shopware.apps.Blog.controller.Blog', {
    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend:'Ext.app.Controller',

    /**
     * All references to get the elements by the applicable selector
     */
    refs:[
        { ref:'grid', selector:'blog-blog-list' },
        { ref:'detailWindow', selector:'blog-blog-window' },
        { ref:'optionsPanel', selector:'blog-blog-detail-sidebar-options' },
        { ref:'commentPanel', selector:'blog-blog-detail-comments' },
        { ref:'attributeForm', selector: 'blog-blog-window shopware-attribute-form' }
    ],

    /**
     * Contains all snippets for the controller
     */
    snippets: {
        confirmDeleteSingleBlogArticleTitle: '{s name=message/delete/confirm_single_blog_article_title}Delete this blog article{/s}',
        confirmDeleteSingleBlogArticle: '{s name=message/delete/confirm_single_blog_article}Are you sure you want to delete the selected blog article ([0])?{/s}',
        deleteSingleBlogArticleSuccess: '{s name=message/delete/single_blog_article/success}The Blog article has been successfully deleted{/s}',
        deleteSingleBlogArticleError: '{s name=message/delete/single_blog_article/error}An error has occurred while deleting the selected blog article: {/s}',
        confirmDeleteMultipleBlogArticles: '{s name=message/delete/multiple_blog_articles}[0] blog articles selected. Are you sure you want to delete the selected blog articles?{/s}',
        deleteMultipleBlogArticlesSuccess: '{s name=message/delete/multiple_blog_articles/success}The blog articles have been successfully deleted.{/s}',
        deleteMultipleBlogArticlesError: '{s name=message/delete/multiple_blog_articles/error}An error has occurred while deleting the selected blog articles: {/s}',
        onSaveChangesSuccess: '{s name=message/save/success}Blog article saved successfully{/s}',
        onSaveChangesNotValid: '{s name=message/save/not_valid}There were not filled in all required fields{/s}',
        assignedArticleExist: '{s name=message/add/assigned_article/exist}The article [0] has been already assigned to this blog article{/s}',
        assignedArticleExistTitle: '{s name=message/add/assigned_article/exist/title}Already exists{/s}',
        onSaveChangesError: '{s name=message/save/error}An error has occurred while saving your changes.{/s}',
        chars: '{s name=seo_description/chars}Chars{/s}',
        growlMessage: '{s name=growlMessage}Blog{/s}'
    },

    /**
     * saves the selected category record
     */
    selectedCategoryRecord: null,

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the sub-application
     *
     * @return void
     */
    init:function () {
        var me = this;

        me.control({
            'blog-blog-tree':{
                // event will be fired if an tree item is clicked
                'itemclick': me.onItemClick
            },
            'blog-blog-list button[action=add]':{
                click:me.onCreateBlogArticle
            },
            'blog-blog-list textfield[action=searchBlogArticles]':{
                change:me.onSearchBlog
            },
            'blog-blog-list button[action=deleteBlogArticles]':{
                click:me.onDeleteMultipleBlogArticles
            },
            'blog-blog-list': {
                deleteBlogArticle: me.onDeleteSingleBlogArticle,
                editBlogArticle: me.onEditItem,
                duplicateColumn: me.onDuplicateBlogArticle
            },
            'blog-blog-detail-sidebar-assigned_articles': {
                addAssignedArticle: me.onAddAssignedArticle,
                removeAssignedArticle: me.onRemoveAssignedArticle,
                openArticleModule: me.onOpenArticleModule
            },
            'blog-blog-detail-sidebar-seo': {
                metaDescriptionChanged: me.onMetaDescriptionChange
            },
            'blog-blog-window button[action=save]': {
                click: me.onSaveBlogArticle
            }
        });
    },

    /**
     * Loads the blog list
     *
     * @param view [Ext.tree.View]
     * @param record [Ext.data.Model]
     * @event editSettings
     * @return void
     */
    onItemClick:function (view, record) {
        var me = this,
            listStore = me.subApplication.listStore;
            me.selectedCategoryRecord = record;
            listStore.getProxy().extraParams = {
                categoryId: record.getId()
            };
            listStore.load();
    },

    /**
     * Opens the Ext.window.window which displays
     * the Ext.form.Panel to create a new blog
     *
     * @return void
     */
    onCreateBlogArticle:function () {
        var me = this,
            model = Ext.create('Shopware.apps.Blog.model.Detail');

        // Reset the detail Record
        me.detailRecord = null;

        if(me.selectedCategoryRecord && me.selectedCategoryRecord.get("blog")) {
            model.set("categoryId",me.selectedCategoryRecord.getId());
        }

        me.getView('blog.Window').create({
            record: model,
            categoryPathStore: me.subApplication.categoryPathStore,
            templateStore: me.subApplication.templateStore
        });
        me.getDetailWindow().formPanel.loadRecord(model);
    },

    /**
     * Opens the Ext.window.window which displays
     * the Ext.form.Panel to modify an existing blog article
     *
     * @param { object }  view - The view. Is needed to get the right
     * @param { integer } rowIndex - The row number
     * @return void
     */
    onEditItem:function (view, rowIndex) {
        var me = this,
            store = me.subApplication.detailStore,
            record = me.subApplication.listStore.getAt(rowIndex),
            commentStore = me.subApplication.commentStore;

        store.load({
            filters : [{
                property: 'id',
                value: record.get("id")
            }],
            callback: function(records, operation) {
                if (operation.success !== true || !records.length) {
                    return;
                }
                me.detailRecord = records[0];
                commentStore.getProxy().extraParams = {
                    blogId:record.get("id")
                };

                me.getView('blog.Window').create({
                    record: me.detailRecord,
                    categoryPathStore: me.subApplication.categoryPathStore,
                    templateStore: me.subApplication.templateStore,
                    commentStore: commentStore.load()
                });

                me.getCommentPanel().enable();
            }
        });
    },


    /**
     * Opens the Ext.window.window which displays
     * the Ext.form.Panel to duplicate an existing blog article
     *
     * @param { object }  view - The view. Is needed to get the right f
     * @param { integer} rowIndex - The row number
     * @return void
     */
    onDuplicateBlogArticle:function (view, rowIndex) {
        var me = this,
            store = me.subApplication.detailStore,
            record = me.subApplication.listStore.getAt(rowIndex),
            id = record.get("id");

        store.load({
            filters : [{
                property: 'id',
                value: id
            }],
            callback: function(records, operation) {
                if (operation.success !== true || !records.length) {
                    return;
                }
                me.detailRecord = records[0];

                //delete id to save a new blog with the data of the duplicated one
                me.detailRecord.set('id', null);
                store.filters.clear();

                me.getView('blog.Window').create({
                    record: me.detailRecord,
                    categoryPathStore: me.subApplication.categoryPathStore,
                    templateStore: me.subApplication.templateStore
                });
            }
        });
    },

    /**
     * Filters the grid with the passed search value to find the right blog article
     *
     * @param field
     * @param value
     * @return void
     */
    onSearchBlog:function (field, value) {
        var me = this,
            searchString = Ext.String.trim(value),
            store = me.subApplication.listStore;
        store.filters.clear();
        store.currentPage = 1;
        store.filter('filter',searchString);
    },

    /**
     * Event listener which deletes a single blog based on the passed
     * grid (e.g. the grid store) and the row index
     *
     * @param { object } grid - The grid on which the event has been fired
     * @param { integer } rowIndex - Position of the event
     * @return void
     */
    onDeleteSingleBlogArticle:function (grid, rowIndex) {
        var me = this,
                store = me.subApplication.listStore,
                record = store.getAt(rowIndex);
        store.currentPage = 1;
        // we do not just delete - we are polite and ask the user if he is sure.
        Ext.MessageBox.confirm(
            me.snippets.confirmDeleteSingleBlogArticleTitle,
            Ext.String.format(me.snippets.confirmDeleteSingleBlogArticle, record.get('title')), function (response) {
            if (response !== 'yes') {
                return false;
            }
            record.destroy({
                callback:function (data, operation) {
                    var records = operation.getRecords(),
                            record = records[0],
                            rawData = record.getProxy().getReader().rawData;

                    if ( operation.success === true ) {
                        Shopware.Notification.createGrowlMessage('',me.snippets.deleteSingleBlogArticleSuccess, me.snippets.growlMessage);
                    } else {
                        Shopware.Notification.createGrowlMessage('',me.snippets.deleteSingleBlogArticleError + rawData.errorMsg, me.snippets.growlMessage);
                    }
                }
            });
            store.load();
        });

    },

    /**
     * Event listener method which deletes multiple blog articles
     *
     * @return void
     */
    onDeleteMultipleBlogArticles:function () {
        var me = this,
                grid = me.getGrid(),
                sm = grid.getSelectionModel(),
                selection = sm.getSelection(),
                store = me.subApplication.listStore,
                noOfElements = selection.length;

        store.currentPage = 1;

        // Get the user to confirm the delete process
        Ext.MessageBox.confirm(
                me.snippets.confirmDeleteSingleBlogArticleTitle,
                Ext.String.format(me.snippets.confirmDeleteMultipleBlogArticles, noOfElements), function (response) {
            if (response !== 'yes') {
                return false;
            }
            if (selection.length > 0) {
                store.remove(selection);
                store.save({
                    callback: function(batch) {
                        var rawData = batch.proxy.getReader().rawData;
                        if (rawData.success === true) {
                            store.load();
                            Shopware.Notification.createGrowlMessage('', me.snippets.deleteMultipleBlogArticlesSuccess, me.snippets.growlMessage);
                        } else {
                            Shopware.Notification.createGrowlMessage('',me.snippets.deleteMultipleBlogArticlesError + rawData.errorMsg , me.snippets.growlMessage);
                        }
                    }
                });
            }
        })
    },

    /**
     * Event will be fired when the user want to add a similar article
     */
    onAddAssignedArticle: function(form, grid, searchField) {
        var me = this,
            selected = searchField.returnRecord,
            store = grid.getStore(),
            values = form.getValues();

        if (!form.getForm().isValid() || !(selected instanceof Ext.data.Model)) {
            return false;
        }
        var model = Ext.create('Shopware.apps.Blog.model.AssignedArticles', values);
        model.set('id', selected.get('id'));
        model.set('name', selected.get('name'));
        model.set('number', selected.get('number'));

        //check if the article is already assigned
        var exist = store.getById(model.get('id'));
        if (!(exist instanceof Ext.data.Model)) {
            store.add(model);
            //to hide the red flags
            model.commit();
        } else {
            Shopware.Notification.createGrowlMessage(me.snippets.assignedArticleExistTitle,  Ext.String.format(me.snippets.assignedArticleExist, model.get('number')), me.snippets.growlMessage);
        }
    },

    /**
     * Event will be fired when the user want to remove an assigned similar article
     */
    onRemoveAssignedArticle: function(record, grid) {
        var me = this,
            store = grid.getStore();

        if (record instanceof Ext.data.Model) {
            store.remove(record);
        }
    },

    /**
     * open the specific article module page
     *
     * @param record
     */
    onOpenArticleModule:function (record) {
        var me = this;
        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.Article',
            action: 'detail',
            params: {
                articleId: record.getId()
            }
        });
    },

    /**
     * Event will be fired when the user changed the seo description
     */
    onMetaDescriptionChange: function(textField) {
        var me = this;
        var metaDescriptionLength = parseInt('{config name=metaDescriptionLength}');
        textField.supportTextEl.update(textField.value.length + "/" + metaDescriptionLength + " " + me.snippets.chars);
    },

    /**
     * Event listener method which will be fired when the user
     * clicks the "save"-button in the edit-window.
     *
     * @event click
     *
     * @param { object } btn - pressed Ext.button.Button
     */
    onSaveBlogArticle: function (btn) {
        var me = this,
            formPanel = me.getDetailWindow().formPanel,
            form = formPanel.getForm(),
            listStore = me.subApplication.listStore,
            record = form.getRecord();

        // Check if all required fields are valid
        if (!form.isValid()) {
            Shopware.Notification.createGrowlMessage('',me.snippets.onSaveChangesNotValid, me.snippets.growlMessage);
            return;
        }

        var values = form.getFieldValues();

        form.updateRecord(record);

        // Just to save empty values
        record.set('authorId', values.authorId);

        record.save({
            callback: function (self,operation) {
                if (operation.success) {
                    // save attributes
                    var response = Ext.JSON.decode(operation.response.responseText);
                    var data = response.data;

                    me.getAttributeForm().saveAttribute(data.id);

                    Shopware.app.Application.fireEvent('blog-save-successfully', me, data, form);

                    record.set('id', data.id);

                    // Enable the tabpanel
                    me.getCommentPanel().enable();
                    listStore.load();
                    // To remove all red flags
                    Shopware.Notification.createGrowlMessage('',me.snippets.onSaveChangesSuccess, me.snippets.growlMessage);
                } else {
                    Shopware.Notification.createGrowlMessage('',me.snippets.onSaveChangesError, me.snippets.growlMessage);
                }
            }
        });
    }
});
//{/block}
