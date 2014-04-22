/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @subpackage Detail
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Controller - Detail
 * The detail controller handles all events of the detail page main form element and the sidebar.
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/controller/detail"}
Ext.define('Shopware.apps.Article.controller.Detail', {

    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
    extend: 'Enlight.app.Controller',

    /**
     * Required stores for controller
     * @array
     */
    stores: [
        'Property', 'PropertyValue'
    ],

    refs: [
        { ref: 'mainWindow', selector: 'article-detail-window' },
        { ref: 'baseFieldSet', selector: 'article-detail-window article-base-field-set' },
        { ref: 'configurator', selector: 'article-detail-window article-variant-configurator' },
        { ref: 'customerGroupCombo', selector: 'article-detail-window article-settings-field-set boxselect[name=avoidCustomerGroups]' },
        { ref: 'variantListing', selector: 'article-detail-window article-variant-list' },
        { ref: 'detailForm', selector: 'article-detail-window form[name=detail-form]' },
        { ref: 'variantTab', selector: 'article-detail-window panel[name=variant-tab]' },
        { ref: 'esdTab', selector: 'article-detail-window panel[name=esd-tab]' },
        { ref: 'esdListing', selector: 'article-detail-window article-esd-list' },
        { ref: 'propertyGrid', selector: 'article-detail-window grid[name=property-grid]' },
        { ref: 'priceFieldSet', selector: 'article-detail-window article-prices-field-set' }
    ],

    snippets: {
        growlMessage: '{s name=growl_message}Article{/s}',
        existTitle: '{s name=sidebar/accessory/already_assigned_title}Already exists{/s}',
        similar: {
            exist: '{s name=sidebar/similar/already_assigned_message}The article [0] has been assigned as similar article!{/s}'
        },
        accessory: {
            exist: '{s name=sidebar/accessory/already_assigned_message}The article [0] has been already assigned as accessory article!{/s}'
        },
        removeArticle: '{s name=article_remove/message}Are you sure you want to delete the article?{/s}',
        alreadyExist: {
            title: '{s name=category/already_exist/title}Failed{/s}',
            message: '{s name=category/already_exist/message}Category: [0] has already been assigned{/s}'
        },
        saved: {
            title: '{s name=article_saved/title}Successful{/s}',
            message: '{s name=article_saved/message}Article [0] has been saved successfully{/s}',
            noPriceGiven: '{s name=article_saved/no_price_given}Please insert a price for the first customer group.{/s}',
            fieldsViolation: '{s name=article_saved/field_errors}The following fields are not valid: {/s}',
            errorMessage: '{s name=article_saved/error_message}An error has occurred while saving the article:{/s}',
            errorTitle: '{s name=article_saved/error_title}Error{/s}',
            removeMessage: '{s name=article_removed/message}Article has been removed{/s}'
        }
    },

    /**
     * A template method that is called when your application boots.
     * It is called before the Application's launch function is executed
     * so gives a hook point to run any code before your Viewport is created.
     *
     * @params  - The main controller can handle a orderId parameter to open the order detail page directly
     * @return void
     */
    init:function () {
        var me = this;

        me.control({
            'article-detail-window': {
                saveArticle: me.onSaveArticle,
                cancel: me.onCancel
            },
            'article-detail-window article-base-field-set checkbox[name=isConfigurator]': {
                change: me.onEnableConfigurator
            },
            'article-detail-window combo[name=filterGroupId]': {
                select: me.onSelectPropertyGroup,
                change: me.onChangePropertyGroup
            },
            'article-detail-window article-properties-field-set grid': {
                beforeedit: me.onBeforePropertyEdit
            },
            'article-detail-window article-sidebar-similar': {
                addSimilarArticle: me.onAddSimilarArticle,
                removeSimilarArticle: me.onRemoveSimilarArticle
            },
            'article-detail-window article-sidebar-accessory': {
                addAccessoryArticle: me.onAddAccessoryArticle,
                removeAccessoryArticle: me.onRemoveAccessoryArticle
            },
            'article-detail-window article-sidebar-link': {
                addLink: me.onAddLink,
                removeLink: me.onRemoveLink,
                addDownload: me.onAddDownload,
                removeDownload: me.onRemoveDownload
            },
            'article-detail-window article-sidebar-option': {
                articlePreview: me.onArticlePreview,
                deleteArticle: me.onDeleteArticle,
                duplicateArticle: me.onDuplicateArticle,
                translateArticle: me.onTranslate,
                addCategory: me.onAddCategory
            },
            'article-prices-field-set': {
                priceTabChanged: me.onPriceTabChanged,
                removePrice: me.onRemovePrice
            },
            'article-prices-field-set grid': {
                edit: me.onAfterEditPrice,
                beforeedit: me.onBeforeEditPrice
            }
        });

        me.callParent(arguments);
    },

    /**
     * Event listener function of the configurator checkbox in the detail tab.
     * Enables or disables the variant tab.
     * @param field
     * @param newValue
     */
    onEnableConfigurator: function(field, newValue) {
        var me = this,
            mainWindow = me.getMainWindow(),
            article = me.subApplication.article,
            variantTab = mainWindow.variantTab;

        if (me.subApplication.splitViewActive) {
            variantTab.setDisabled(true)
        } else {
            variantTab.setDisabled((article.get('id') === null || newValue === false || article.get('configuratorSetId') === null));
        }
    },

    /**
     * Event listener function of the save button of the main window.
     * Saves the current article
     *
     * @param { Object } win
     * @param { Object } article
     * @param { Object } options
     * @return { Boolean|void }
     */
    onSaveArticle: function(win, article, options) {
        var me = this, priceStore, lastFilter, message, mainWindow = me.getMainWindow(),
            form = me.getDetailForm();

        //first, check if the detail form panel is valid, otherwise return.
        if ( !form.getForm().isValid() ) {
            if (options !== Ext.undefined && options !== null && Ext.isFunction(options.callback)) {
                options.callback(null, false, 'no_valid_form');
            }
            return;
        }

        priceStore = article.getPrice();

        //update the article record with the form data.
        form.getForm().updateRecord(article);
        article = me.prepareAvoidCustomerGroups(article);

        if (!article.get('isConfigurator')) {
            article.set('configuratorSetId', null);
        }

        // If supplierId is string we want to create a new supplier in our backend
        var baseField = me.getBaseFieldSet();
        var supplierId = baseField.supplierCombo.getModelData().supplierId;
        var supplierNeedsReload = false;
        if (typeof supplierId === "string") {
            article.set('supplierName', supplierId);
            supplierNeedsReload = true;
        }

        article.getConfiguratorSetStore = Ext.create('Ext.data.Store', { model: 'Shopware.apps.Article.model.ConfiguratorSet' });

        //save last price store filter to filter again after the article saved.
        lastFilter = priceStore.filters.items;

        priceStore.clearFilter();

        if (article.getConfiguratorTemplateStore instanceof Ext.data.Store && article.getConfiguratorTemplateStore.getCount() > 0) {
            var template = article.getConfiguratorTemplateStore.first();
            if (template.getPrice() instanceof Ext.data.Store) {
                template.getPrice().clearFilter();
            }
        }

        if (!me.hasArticlePrice(priceStore)) {
            priceStore.filter(lastFilter);
            Shopware.Notification.createGrowlMessage(me.snippets.saved.errorTitle, me.snippets.saved.noPriceGiven, me.snippets.growlMessage);
            if (options !== Ext.undefined && options !== null && Ext.isFunction(options.callback)) {
                options.callback(null, false);
            }
            return false;
        }

        //remove all prices with a clone flag.
        me.removeClonedPrices(priceStore);
        article.setDirty();
        article.save({
            success: function(record, operation) {
                var newArticle = operation.getResultSet().records[0],
                    message = Ext.String.format(me.snippets.saved.message, article.get('name'));

                if (supplierNeedsReload) {
                    mainWindow.supplierStore.filters.clear();
                    mainWindow.supplierStore.load();
                }

                me.prepareArticleProperties(record);

                newArticle.getPrice().filter(lastFilter);
                me.reconfigureAssociationComponents(newArticle);
                Shopware.Notification.createGrowlMessage(me.snippets.saved.title, message, me.snippets.growlMessage);
                me.refreshArticleList();
                if (options !== Ext.undefined && options !== null && Ext.isFunction(options.callback)) {
                    options.callback(newArticle, true);
                }
            },
            failure: function(record, operation) {
                var rawData = record.getProxy().getReader().rawData,
                    fields = rawData.fields,
                    message = rawData.message;

                if (fields && fields.length > 0) {
                    Shopware.Notification.createGrowlMessage(me.snippets.saved.errorTitle, me.snippets.saved.fieldsViolation, me.snippets.growlMessage);
                } else {
                    Shopware.Notification.createGrowlMessage(me.snippets.saved.errorTitle, me.snippets.saved.errorMessage + message, me.snippets.growlMessage);
                }

                priceStore.filter(lastFilter);
                if (options !== Ext.undefined && options !== null && Ext.isFunction(options.callback)) {
                    options.callback(null, false);
                }
            }
        });
    },

    /**
     *
     * @param priceStore
     * @return Boolean
     */
    hasArticlePrice: function(priceStore) {
        var me = this, priceExist = false;

        var firstCustomerGroup = me.subApplication.firstCustomerGroup;

        priceStore.each(function(price) {
            if (price.get('customerGroupKey') == firstCustomerGroup.get('key') && price.get('price') > 0) {
                priceExist = true;
                return true;
            }
        });
        return priceExist;
    },

    refreshArticleList: function() {
        var me = this,
            subApps = Shopware.app.Application.subApplications,
            articleList = subApps.findBy(function(item) {
            if(item.$className == 'Shopware.apps.ArticleList') {
                return true;
            }
        });
        if(articleList) {
            var grid = articleList.articleGrid,
                selModel = grid.getSelectionModel(),
                selection = selModel.getLastSelected();

            articleList.getStore('List').load({
                scope: me,
                callback: function() {
                    if (selection) {
                        selModel.select(selection.index, false, true);
                    }
                }
            });
        }
    },

    prepareAvoidCustomerGroups: function(article) {
        var me = this,
            customerGroupCombo = me.getCustomerGroupCombo(),
            store = customerGroupCombo.getStore();

        var newStore = Ext.create('Ext.data.Store', { model: 'Shopware.apps.Base.model.CustomerGroup' });
        newStore.add(customerGroupCombo.getValueRecords());
        article.getCustomerGroupsStore = newStore;
        return article;
    },

    prepareArticleProperties: function(article) {
        var me = this,
            propertyStore = me.getStore('Property');

        if(article.get('id')) {
            propertyStore.getProxy().extraParams.articleId = article.get('id');
        }
        propertyStore.each(function(property) {
            property.setDirty();
        });
        propertyStore.save({
            success: function () {
                var propertyValueStore = me.getStore('PropertyValue');
                propertyValueStore.getProxy().extraParams.optionId = '';
                //reload the property list after finish saving
                propertyValueStore.load({
                    callback: function () {
                        propertyStore.load();
                    }
                });
            }
        });
        return article;
    },

    /**
     * Helper function to reload all components which uses the article association stores
     * @param article
     */
    reconfigureAssociationComponents: function(article) {
        var me = this,
            mainWindow = me.getMainWindow(),
            variantTab = mainWindow.variantTab,
            esdTab = mainWindow.esdTab,
            esdListing = me.getEsdListing(),
            variantListing = me.getVariantListing(),
            configurator = me.getConfigurator(),
            priceFieldSet = me.getPriceFieldSet();


        if (article === null && me.subApplication.article) {
            me.reloadArticle(me.subApplication.article.get('id'));
            return;
        }
        var baseField = mainWindow.down('article-base-field-set');
        baseField.numberField.validationRequestParam = article.getMainDetail().first().get('id');

        mainWindow.article = article;
        me.subApplication.article = article;

        mainWindow.detailForm.loadRecord(article);

        me.loadPropertyStore(article);

        esdTab.setDisabled(article.get('id') === null);
        esdListing.esdStore.getProxy().extraParams.articleId = article.get('id');
        esdListing.filteredStore.getProxy().extraParams.articleId = article.get('id');
        esdListing.article = article;

        if (me.subApplication.splitViewActive) {
            variantTab.setDisabled(true);
        } else {
            variantTab.setDisabled(article.get('id') === null || article.get('isConfigurator') === false || article.get('configuratorSetId') === null);
        }

        var showAdditionalText = (variantTab.isDisabled()) ? !Ext.isEmpty(baseField.mainDetailAdditionalText.getValue(), false) : false;
        baseField.mainDetailAdditionalText.setVisible(showAdditionalText);
        variantListing.getStore().getProxy().extraParams.articleId = article.get('id');

        configurator.articleConfiguratorSet = article.getConfiguratorSet().first();

        priceFieldSet.priceStore = article.getPrice();
        priceFieldSet.preparePriceStore();
        Ext.each(priceFieldSet.priceGrids, function(grid) {
            grid.reconfigure(article.getPrice());
        });
        priceFieldSet.tabPanel.setActiveTab(0);

        //reconfigure the category grid in the option panel of the sidebar.
        mainWindow.down('article-sidebar article-sidebar-option article-category-list').reconfigure(article.getCategory());

        //reconfigure the article link listing
        mainWindow.down('article-sidebar article-sidebar-link grid[name=link-listing]').reconfigure(article.getLink());

        //reconfigure the article download listing
        mainWindow.down('article-sidebar article-sidebar-link grid[name=download-listing]').reconfigure(article.getDownload());

        //reconfigure the article accessory articles listing
        mainWindow.down('article-sidebar article-sidebar-accessory grid[name=accessory-listing]').reconfigure(article.getAccessory());

        //reconfigure the article similar articles listing
        mainWindow.down('article-sidebar article-sidebar-similar grid[name=similar-listing]').reconfigure(article.getSimilar());

        //reconfigure the category listing in the category tab
        mainWindow.down('container[name=category-tab] article-category-list').reconfigure(article.getCategory());

        //reconfigure the image listing
        var imageListing = mainWindow.down('article-image-list dataview[name=image-listing]');
        var listingComponent = mainWindow.down('article-image-list');
        imageListing.bindStore(article.getMedia());
        listingComponent.mediaStore = article.getMedia();

        mainWindow.detailForm.getForm().isValid();
    },

    loadPropertyStore: function(article) {
        var me = this,
            propertyStore = me.getStore('Property'),
            valueStore = me.getStore('PropertyValue');

        var filterGroupId = article.get('filterGroupId');
        me.loadPropertyGrid(filterGroupId);


    },

    /**
     * Helper function to reload the article data
     * @param articleId
     */
    reloadArticle: function(articleId) {
        var me = this, lastFilter = null;

        if (me.subApplication.article) {
            lastFilter = me.subApplication.article.getPrice().filters.items;
        }
        //the batch store is responsible to load all required stores for the detail page in one request
        me.batchStore = me.getStore('Batch');
        me.batchStore.getProxy().extraParams.articleId = articleId;
        me.batchStore.load({
            callback: function(records, operation) {
                var storeData = records[0];
                //when store has been loaded use the first record as data array to create the required stores
                if (operation.success === true) {
                    //prepare the associated stores to use them in the detail page
                    var article = storeData.getArticle().first();
                    me.subApplication.article = article;

                    if (article) {
                        if (lastFilter != null) {
                            article.getPrice().filter(lastFilter);
                        }
                        me.reconfigureAssociationComponents(article);
                    }
                }
            }
        });
    },

    onCancel: function(win) {
        var me = this;
        win.destroy();
    },

    /**
     * Event will be fired when the user select an item of the category combo box.
     *
     * @event
     * @param [Ext.data.Model] - The category model
     * @param [Ext.grid.Panel] - The category list
     */
    onAddCategory: function(categories, grid) {
        var me = this,
            store = grid.getStore();

        if (!Ext.isArray(categories)) {
            return true;
        }

        Ext.each(categories, function(model) {
            var category = Ext.create('Shopware.apps.Article.model.Category', {
                id: model.data.id,
                name: model.data.name
            });

            if (!store.getById(category.get('id'))) {
                store.add(category);
            } else {
                var message = Ext.String.format(me.snippets.alreadyExist.message, category.get('name'));
                Shopware.Notification.createGrowlMessage(me.snippets.alreadyExist.title, message, me.snippets.growlMessage);
            }
        });
    },

    /**
     * Event listener function which fired when the user clicks the duplicate button
     * in the side bar.
     * @return void
     */
    onDuplicateArticle: function(article) {
        var me = this,
            detailRecord = me.getDetailForm().getRecord();

        //use the detailRecord for the id because article in split view mode can be outdated
        if (!detailRecord || !detailRecord.get('id')) {
            return;
        }

        Ext.Ajax.request({
            url: '{url controller="article" action="duplicateArticle"}',
            method: 'POST',
            params: {
                articleId: detailRecord.get('id')
            },
            success: function(response, opts) {
                var operation = Ext.decode(response.responseText);
                if (operation.success == true && operation.articleId) {
                    Shopware.app.Application.addSubApplication({
                        name: 'Shopware.apps.Article',
                        action: 'detail',
                        params: {
							needGenerate: operation.isConfigurator,
                            articleId: operation.articleId
                        }
                    });

                }
            }
        });

    },

    /**
     * Event listener function which fired when the user clicks the translate button.
     * @param record
     */
    onTranslate: function(record) {
        var me = this,
            formPnl = me.getDetailForm();

        formPnl.getPlugin('translation').onOpenTranslationWindow();
    },

    /**
     * Event listener function which fired when the user clicks the delete button.
     * @param window
     * @param article
     */
    onDeleteArticle: function(article) {
        var me = this,
            win = me.getMainWindow(),
            articleModel = me.getDetailForm().getRecord();

        //use the model from the record because article in split view mode can be outdated
        if (articleModel instanceof Ext.data.Model && articleModel.get('id') > 0) {
            Ext.MessageBox.confirm(me.snippets.growlMessage, me.snippets.removeArticle , function (response) {
                if ( response !== 'yes' ) {
                    return;
                }
                articleModel.destroy({
                    callback: function(operation) {
                        Shopware.Notification.createGrowlMessage(me.snippets.saved.title, me.snippets.saved.removeMessage, me.snippets.growlMessage);
                        win.destroy();
                        me.refreshArticleList();
                    }
                });
            });
        }
    },

    /**
     * Event will be fired when the user clicks the preview button.
     */
    onArticlePreview: function(article, combo) {
        var me = this,
            shopId = combo.getValue();

        article = me.subApplication.article;

        if (!(article instanceof Ext.data.Model) || !Ext.isNumeric(shopId)) {
            return;
        }

        var url = '{url action=previewDetail}'
                + '?shopId=' + shopId
                + '&articleId=' + article.get('id');
        window.open(url);
    },

    /**
     * Event listener function of the sidebar option component. Fired
     * when the user clicks the add link button.
     * @event
     * @param [Ext.grid.Panel] The link grid
     * @param [Ext.form.Panel] The form panel for the link
     */
    onAddLink: function(grid, form) {
        var me = this, model,
            store = grid.getStore(),
            values = form.getValues();

        if (!form.getForm().isValid()) {
            return;
        }
        if (form.getForm().getRecord()) {
            model = form.getForm().getRecord();
            form.getForm().updateRecord(model);
        } else {
            model = Ext.create('Shopware.apps.Article.model.Link', values);
        }
        store.add(model);
        form.getForm().reset();
    },

    /**
     * Event listener function of the sidebar option component. Fired
     * when the user clicks the remove link action column.
     * @event
     * @param [Ext.grid.Panel] The link grid
     * @param [Ext.data.Model] The link record
     */
    onRemoveLink: function(grid, record) {
        var me = this,
            store = grid.getStore();

        if (store instanceof Ext.data.Store && record instanceof Ext.data.Model) {
            store.remove(record);
        }
    },


    /**
     * Event listener function of the sidebar option component. Fired
     * when the user clicks the add download button.
     *
     * @event
     * @param [Ext.grid.Panel] The download grid
     * @param [Ext.form.Panel] The download panel for the link
     */
    onAddDownload: function(grid, form) {
        var me = this, model,
            store = grid.getStore(),
            values = form.getValues();

        if (!form.getForm().isValid()) {
            return;
        }
        if (form.getForm().getRecord()) {
            model = form.getForm().getRecord();
            form.getForm().updateRecord(model);
        } else {
            model = Ext.create('Shopware.apps.Article.model.Download', values);
        }
        store.add(model);
        form.getForm().reset();
    },

    /**
     * Event listener function of the sidebar option component. Fired
     * when the user clicks the remove download action column.
     *
     * @event
     * @param [Ext.grid.Panel] The download grid
     * @param [Ext.data.Model] The download record
     */
    onRemoveDownload: function(grid, record) {
        var me = this,
            store = grid.getStore();

        if (store instanceof Ext.data.Store && record instanceof Ext.data.Model) {
            store.remove(record);
        }
    },

    /**
     * Event will be fired when the user want to add a similar article
     *
     * @event
     */
    onAddSimilarArticle: function(form, grid, searchField) {
        var me = this,
            selected = searchField.returnRecord,
            store = grid.getStore(),
            values = form.getValues();

        if (!form.getForm().isValid() || !(selected instanceof Ext.data.Model)) {
            return false;
        }
        var model = Ext.create('Shopware.apps.Article.model.Similar', values);
        model.set('id', selected.get('id'));
        model.set('name', selected.get('name'));
        model.set('number', selected.get('number'));

        //check if the article is already assigned
        var exist = store.getById(model.get('id'));
        if (!(exist instanceof Ext.data.Model)) {
            store.add(model);
            form.getForm().reset();
        } else {
            Shopware.Notification.createGrowlMessage(me.snippets.existTitle,  Ext.String.format(me.snippets.similar.exist, model.get('number')), me.snippets.growlMessage);
        }
    },

    /**
     * Event will be fired when the user want to remove an assigned similar article
     *
     * @event
     */
    onRemoveSimilarArticle: function(grid, record) {
        var me = this,
            store = grid.getStore();

        if (record instanceof Ext.data.Model) {
            store.remove(record);
        }
    },

    /**
     * Event will be fired when the user want to add a similar article
     *
     * @event
     */
    onAddAccessoryArticle: function(form, grid, searchField) {
        var me = this,
            selected = searchField.returnRecord,
            store = grid.getStore(),
            values = form.getValues();

        if (!form.getForm().isValid() || !(selected instanceof Ext.data.Model)) {
            return false;
        }
        var model = Ext.create('Shopware.apps.Article.model.Accessory', values);
        model.set('id', selected.get('id'));
        model.set('name', selected.get('name'));
        model.set('number', selected.get('number'));

        //check if the article is already assigned
        var exist = store.getById(model.get('id'));
        if (!(exist instanceof Ext.data.Model)) {
            store.add(model);
            form.getForm().reset();
        } else {
            Shopware.Notification.createGrowlMessage(me.snippets.existTitle,  Ext.String.format(me.snippets.similar.exist, model.get('number')), me.snippets.growlMessage);
        }

    },

    /**
     * Event will be fired when the user want to remove an assigned similar article
     *
     * @event
     */
    onRemoveAccessoryArticle: function(grid, record) {
        var me = this,
            store = grid.getStore();

        if (record instanceof Ext.data.Model) {
            store.remove(record);
        }
    },

    /**
     * Event listener method which is fired when the user change the property combo box.
     * The event will reload the values stores and updates the property grid.
     *
     * @param [string] value
     * @param [Ext.grid.Panel] grid
     */
    onSelectPropertyGroup: function (combo, records) {
        var me = this,
            propertyGroupId = records.length > 0 ? records[0].getId() : null;

        me.loadPropertyGrid(propertyGroupId);

    },

    loadPropertyGrid: function(propertyGroupId) {
        var me = this,
            grid = me.getPropertyGrid(),
            propertyStore = me.getStore('Property'),
            valueStore = me.getStore('PropertyValue');

        if (propertyGroupId) {
            propertyStore.getProxy().extraParams.propertyGroupId = propertyGroupId;
            valueStore.getProxy().extraParams.propertyGroupId = propertyGroupId;

            valueStore.load({
                callback: function() {
                    propertyStore.load({
                        params: {
                            articleId: me.subApplication.article.get('id')
                        }
                    });
                }
            });
            grid.show();
        } else {
            grid.hide();
        }
    },

    /**
     * Event listener method which is fired when the user change the property combo box.
     *
     * @param [Ext.form.field.ComboBox] combo
     */
    onChangePropertyGroup: function (combo) {
        var me = this,
            grid = me.getPropertyGrid(),
            form = me.getDetailForm(),
            record = form.getRecord();

        if (combo.getValue() === null) {
            record.set('filterGroupId', null);
            grid.hide();
        }
    },

    /**
     * Event listener function of the property grid which fired before the user
     * edit a cell.
     */
    onBeforePropertyEdit: function (editor, event) {
        var me = this,
            store = me.getStore('PropertyValue');
        if (event.column.dataIndex == "value") {
            store.getProxy().extraParams.optionId = event.record.getId();
            store.load({
                callback: function () {
                    //reload the store again to convert the ids to values
                    store.load();
                }
            });
        }
    },

    /**
     * Event listener function which fired when the user want to remove a price row.
     *
     * @param record
     * @param view
     * @param rowIndex
     */
    onRemovePrice: function(record, view, rowIndex) {
        var me = this,
            store = view.getStore(),
            previousPrice = store.getAt(rowIndex-1);

        if (rowIndex > 1) {
            var column = view.panel.columns[view.panel.columns.length-1],
                cell = view.getCell(previousPrice, column),
                icon = Ext.get(cell.query('.x-action-col-icon'));

            icon.removeCls('x-hidden');
            icon.addCls('sprite-minus-circle-frame');
        }

        me.removeCloneFlag(store);
        store.remove(record);
        previousPrice.set('to', null);
    },

    /**
     * Helper function to remove the cloned flag for the current customer group.
     * @param store
     */
    removeCloneFlag: function(store) {
        store.each(function(price) {
            price.set('cloned', false);
        });
    },

    /**
     * Event listener function which fired when the user
     * edit a column of the price grid.
     * This function handles the calculation for the
     * prices and discounts.
     *
     * @param editor
     * @param event
     */
    onAfterEditPrice: function(editor, event) {
        var me = this,
            record = event.record,
            store = event.grid.store,
            firstRecord = store.getAt(0),
            firstPrice = firstRecord.get('price'),
            nextRecord = store.getAt(event.rowIdx + 1),
            price = record.get('price'),
            row = Ext.get(event.row),
            icon = Ext.get(row.query('.x-action-col-icon')),
            percent;

        me.removeCloneFlag(store);

        //user changed the "to" field?
        if ( event.field === 'to') {
            //check if the user insert a numeric to value
            if (Ext.isNumeric(event.value)) {
                icon.addCls('x-hidden');

                //if this is the case we need to check if the current row is the last row.
                if (!nextRecord) {
                    //if the current row is the last row, we need to add a new row with "to any"
                    var newRecord = Ext.create('Shopware.apps.Article.model.Price', {
                        from: event.value + 1,
                        customerGroupKey: record.get('customerGroupKey', null)
                    });
                    store.add(newRecord);
                } else {
                    //if the current row is not the last row we have to increase the from value of the next row
                    nextRecord.set('from', event.value + 1);
                }
            } else {
                icon.removeCls('x-hidden');
                icon.addCls('sprite-minus-circle-frame');
            }
        } else if ( event.field === 'price') {
            if (price && firstPrice > price) {
                percent = (firstPrice - price) / firstPrice * 100;
                percent = percent.toFixed(2);
                record.set('percent', percent);
            } else {
                record.set('percent', null);
            }
        //if the user has edit the percent column, we have to calculate the price
        } else if (event.field == 'percent') {
            if (firstPrice == price) {
                firstRecord.set('percent', null);
            } else if(event.value > 0) {
                price = firstPrice / 100 * (100 - event.value);
                price = price.toFixed(2);
                record.set('price', price);
            }
        }
    },

    /**
     * Event listener function which fired when the user
     * starts the edit of a price row.
     *
     * @param plugin
     * @param event
     * @return
     */
    onBeforeEditPrice: function(plugin, event) {
        var store = event.grid.store,
            maxValue = null,
            minValue = 1,
            price = event.record,
            editor = event.column.getEditor(event.record),
            previousPrice = store.getAt(event.rowIdx -1),
            nextPrice = store.getAt(event.rowIdx + 1);

        //check if the current row is the last row
        if ( event.field === "to") {
            //if the current row isn't the last row, we want to cancel the edit.
            if (nextPrice) {
                return false;
            }
            //check if the current row has a previous row.
            if (previousPrice) {
                //if this is the case we have to set the min value for the "to" field
                //+1 of the previous price
                minValue = ~~(previousPrice.get('to') * 1) + 1;
            }
            editor.setMinValue(minValue);
        }
        //check if the user want to edit the price field.
        if ( event.field === "price" ) {
            if (previousPrice && previousPrice.get('price') > 0) {
                maxValue = previousPrice.get('price') - 0.01;
            }
            editor.setMaxValue(maxValue);
        }
    },


    /**
     * Removes the cloned prices of the article price store.
     * @param article
     */
    removeClonedPrices: function(priceStore) {
        var me = this,
            toRemove = [];

        priceStore.clearFilter();
        priceStore.each(function(price) {
            if (price instanceof Ext.data.Model && price.get('cloned')) {
                toRemove.push(price);
            }
        });

        priceStore.remove(toRemove);
    },

    /**
     * Event will be fired when the user change the tab panel in the price field set.
     *
     * @event
     * @param [object] The previous tab panel
     * @param [object] The clicked tab panel
     * @param [Ext.data.Store] The price store
     * @param [array] The price data of the first customer group.
     */
    onPriceTabChanged: function(oldTab, newTab, priceStore, customerGroupStore) {
        var me = this,
            toRemove = [],
            firstGroupPrices = [],
            customerGroup,
            firstGroup = customerGroupStore.first();

        customerGroup = newTab.customerGroup;
        priceStore.clearFilter();

        //first we remove all prices which have a cloned flag and save the prices of the first group.
        priceStore.each(function(item) {
            if (item instanceof Ext.data.Model && item.get('cloned')) {
                toRemove.push(item);
            }
            if (item.get('customerGroupKey') == firstGroup.get('key')) {
                firstGroupPrices.push(item);
            }
        });

        //we have to collect the records, because if we remove the items in the for each,
        //the store can't iterate the records correctly.
        priceStore.remove(toRemove);

        //now we can filter the price store for the current customer group.
        priceStore.filter({
            filterFn: function(item) {
                return item.get("customerGroupKey") == customerGroup.get('key');
            }
        });

        //if the current customer group is the first/main customer group, we can return now.
        if (customerGroup.get('id') === firstGroup.get('id')) {
            return false;
        }

        //if no prices given for the current customer group, we have to copy the prices of the main customer group
        if (priceStore.data.length === 0) {
            priceStore.add(me.clonePrices(firstGroupPrices, customerGroup));
        }
    },

    /**
     * Clones the passed price array, sets the cloned flag for the cloned prices and
     * sets the customer group equals the key of the passed group.
     * @param firstGroupPrices
     * @param customerGroup
     */
    clonePrices: function(firstGroupPrices, customerGroup) {
        var me = this,
            clonedPrices = [];

        Ext.each(firstGroupPrices, function(price) {
            var priceCopy = Ext.create('Shopware.apps.Article.model.Price', price.data);
            priceCopy.set('customerGroupKey', customerGroup.get('key'));
            priceCopy.set('cloned', true);
            priceCopy.set('id', null);
            clonedPrices.push(priceCopy);
        });

        return clonedPrices;
    }

});
//{/block}
