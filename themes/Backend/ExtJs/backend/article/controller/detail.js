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
 * @subpackage Detail
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

    stores: [
        'Property'
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
        disableVariantForbidden: {
            title: '{s name=disable_variant_forbidden/title}Disable variants{/s}',
            message: '{s name=disable_variant_forbidden/message}To disable variant support you must delete all non-main variants first{/s}',
            error: '{s name=disable_variant_forbidden/error}An error occurred while disabling variant support{/s}'
        },
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
        },
        overwriteArticle: {
            title: '{s name=overwriteArticle/title}Overwrite most recent changes{/s}',
            message: '{s name=overwriteArticle/message}Do you really want to overwrite the latest changes?{/s}',
        },
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
                change: me.onChangePropertyGroup
            },
            'article-detail-window article-resources-links': {
                addLink: me.onAddLink,
                removeLink: me.onRemoveLink
            },
            'article-detail-window article-resources-downloads': {
                addDownload: me.onAddDownload,
                removeDownload: me.onRemoveDownload
            },
            'article-detail-window article-actions-toolbar': {
                articlePreview: me.onArticlePreview,
                deleteArticle: me.onDeleteArticle,
                duplicateArticle: me.onDuplicateArticle
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
     * Deprecated. Use onChangeConfigurator instead
     * @param field
     * @param newValue
     */
    onEnableConfigurator: function(field, newValue) {
        this.onChangeConfigurator(field, newValue);
    },

    /**
     * Event listener function of the configurator checkbox in the detail tab.
     * Enables or disables the variant tab.
     *
     * @param field
     * @param newValue
     */
    onChangeConfigurator: function(field, newValue) {
        var me = this,
            mainWindow = me.getMainWindow(),
            article = me.subApplication.article,
            variantTab = mainWindow.variantTab;

        if (newValue === false && !Ext.isEmpty(article.get('id'))) {
            var variantStore = me.getStore('Variant');
            variantStore.getProxy().extraParams.articleId = article.get('id');

            variantStore.load({
                callback: function(records, operation, success) {
                    if (success && records.length > 1) {
                        Shopware.Notification.createGrowlMessage(me.snippets.disableVariantForbidden.title, me.snippets.disableVariantForbidden.message, me.snippets.growlMessage);
                        field.setValue(true);
                    } else if (!success) {
                        Shopware.Notification.createGrowlMessage(me.snippets.disableVariantForbidden.title, me.snippets.disableVariantForbidden.error, me.snippets.growlMessage);
                        field.setValue(true);
                    }
                }
            });
        }

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
        var me = this, priceStore, lastFilter,
            mainWindow = me.getMainWindow(),
            form;

        if (Ext.isEmpty(win)) {
            mainWindow = me.getMainWindow();
        } else {
            mainWindow = win;
        }

        form = mainWindow.detailForm;

        // First, check if the detail form panel is valid, otherwise return.
        if ( !form.getForm().isValid() ) {
            if (options !== Ext.undefined && options !== null && Ext.isFunction(options.callback)) {
                options.callback(null, false, 'no_valid_form');
            }
            return;
        }

        priceStore = article.getPrice();

        // Update the article record with the form data.
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

        // Save last price store filter to filter again after the article saved.
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

        // Remove all prices with a clone flag.
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

                mainWindow.attributeForm.saveAttribute(newArticle.get('mainDetailId'));

                me.prepareArticleProperties(record, function() {
                    newArticle.getPrice().filter(lastFilter);
                    me.reconfigureAssociationComponents(newArticle);
                    Shopware.Notification.createGrowlMessage(me.snippets.saved.title, message, me.snippets.growlMessage);
                    me.refreshArticleList();

                    if (record.get('isConfigurator') && record.get('id')) {
                        me.subApplication.getController('Variant').getVariantListing().getStore().reload();
                    }

                    if (options !== Ext.undefined && options !== null && Ext.isFunction(options.callback)) {
                        options.callback(newArticle, true);
                    }
                });
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

                if (rawData.overwriteAble) {
                    Ext.MessageBox.confirm(me.snippets.overwriteArticle.title, me.snippets.overwriteArticle.message, function (response) {
                        if (response === 'yes') {
                            record.set('changed', rawData.data[0].changed);
                            me.onSaveArticle(win, article, options);
                        }
                    });
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
            if (price.get('customerGroupKey') === firstCustomerGroup.get('key') && price.get('price') > 0) {
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
                if(item.$className === 'Shopware.apps.ArticleList') {
                    return true;
                }
            });
        if(articleList) {
            var grid = articleList.articleGrid,
                selModel = grid.getSelectionModel(),
                selection = selModel.getLastSelected();

            articleList.articleStore.reload({
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

        // Reconfigure the article link listing
        mainWindow.down('article-resources-links grid[name=link-listing]').reconfigure(article.getLink());

        // Reconfigure the article download listing
        mainWindow.down('article-resources-downloads grid[name=download-listing]').reconfigure(article.getDownload());

        // Reconfigure the article accessory articles listing
        mainWindow.down('article-crossselling-tab grid[name=accessory-listing]').reconfigure(article.getAccessory());

        // Reconfigure the article similar articles listing
        mainWindow.down('article-crossselling-tab grid[name=similar-listing]').reconfigure(article.getSimilar());

        // Reconfigure the category listing in the category tab
        mainWindow.down('container[name=category-tab] article-category-list').reconfigure(article.getCategory());

        // Reconfigure the stream listing in the crossselling tab
        mainWindow.down('article-crossselling-tab grid[name=streams-listing]').reconfigure(article.getStreams());

        //reconfigure the seo category listing and the selection store of the listing
        var seoListing = mainWindow.down('container[name=category-tab] article-category-seo-list');

        seoListing.reconfigure(article.getSeoCategories());

        seoListing.setCategoryStore(article.getCategory());

        // Reconfigure the image listing
        var imageListing = mainWindow.down('article-image-list dataview[name=image-listing]');
        var listingComponent = mainWindow.down('article-image-list');
        imageListing.bindStore(article.getMedia());
        listingComponent.mediaStore = article.getMedia();

        mainWindow.detailForm.getForm().isValid();
    },

    /**
     * Helper function to reload the article data
     *
     * @param { Integer } articleId
     */
    reloadArticle: function(articleId) {
        var me = this, lastFilter = null;

        if (me.subApplication.article) {
            lastFilter = me.subApplication.article.getPrice().filters.items;
        }
        // The batch store is responsible to load all required stores for the detail page in one request
        me.batchStore = me.getStore('Batch');
        me.batchStore.getProxy().extraParams.articleId = articleId;
        me.batchStore.load({
            callback: function(records, operation) {
                var storeData = records[0];
                // When store has been loaded use the first record as data array to create the required stores
                if (operation.success === true) {
                    // Prepare the associated stores to use them in the detail page
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
        win.destroy();
    },

    /**
     * Event listener function which fired when the user clicks the duplicate button in the side bar.
     */
    onDuplicateArticle: function(article) {
        var me = this,
            detailRecord = me.getDetailForm().getRecord();

        // Use the detailRecord for the id because article in split view mode can be outdated
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
     * Event listener function which fired when the user clicks the delete button.
     * @param article
     */
    onDeleteArticle: function(article) {
        var me = this,
            win = me.getMainWindow(),
            articleModel = me.getDetailForm().getRecord();

        // Use the model from the record because article in split view mode can be outdated
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
     * Event listener function of the resources component.
     * Fired when the user clicks the add link button.
     * @event
     * @param { Ext.grid.Panel } grid The link grid
     * @param { Object } form The link record
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
     * Event listener function of the resources component.
     * Fired when the user clicks the remove link action column.
     * @event
     * @param { Ext.grid.Panel } grid The link grid
     * @param { Ext.data.Model } record The link record
     */
    onRemoveLink: function(grid, record) {
        var me = this,
            store = grid.getStore();

        if (store instanceof Ext.data.Store && record instanceof Ext.data.Model) {
            store.remove(record);
        }
    },

    /**
     * Event listener function of the resources component.
     * Fired when the user clicks the add download button.
     *
     * @event
     * @param { Ext.grid.Panel } grid The download grid
     * @param { Ext.data.Model } record The download record
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
     * Event listener function of the resources component.
     * Fired when the user clicks the remove download action column.
     *
     * @event
     * @param { Ext.grid.Panel } grid The download grid
     * @param { Ext.data.Model } record The download record
     */
    onRemoveDownload: function(grid, record) {
        var me = this,
            store = grid.getStore();

        if (store instanceof Ext.data.Store && record instanceof Ext.data.Model) {
            store.remove(record);
        }
    },

    loadPropertyGrid: function(propertyGroupId) {
        var me = this,
            grid = me.getPropertyGrid(),
            propertyStore = me.getStore('Property');

        if (propertyGroupId) {
            propertyStore.getProxy().extraParams.propertyGroupId = propertyGroupId;
            propertyStore.load({
                params: {
                    articleId: me.subApplication.article.get('id')
                }
            });

            grid.show();
            return;
        }
        grid.hide();
    },

    loadPropertyStore: function(article) {
        var me = this;

        var filterGroupId = article.get('filterGroupId');
        me.loadPropertyGrid(filterGroupId);
    },

    prepareArticleProperties: function(article, callback) {
        var me = this,
            propertyStore = me.getStore('Property'),
            opts = {
                callback: function () {
                    if (Ext.isFunction(callback)) {
                        callback();
                    }
                }
            };

        if(article.get('id')) {
            propertyStore.getProxy().extraParams.articleId = article.get('id');
        }
        propertyStore.each(function(property) {
            property.setDirty();
        });

        propertyStore[propertyStore.getUpdatedRecords().length ? 'save' : 'load'](opts);
    },

    /**
     * Property set combo box changed => Toggle grid
     */
    onChangePropertyGroup: function (combo) {
        var me = this,
            grid = me.getPropertyGrid(),
            record = me.subApplication.article;

        if (combo.getValue() === null) {
            record.set('filterGroupId', null);
            grid.hide();
        } else {
            me.loadPropertyGrid(record.get('filterGroupId'));
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
            nextRecord = store.getAt(event.rowIdx + 1),
            row = Ext.get(event.row),
            icon = Ext.get(row.query('.x-action-col-icon'));

        me.removeCloneFlag(store);

        // User changed the "to" field?
        if ( event.field === 'to') {
            // Check if the user insert a numeric to value
            if (Ext.isNumeric(event.value)) {
                icon.addCls('x-hidden');

                // If this is the case we need to check if the current row is the last row.
                if (!nextRecord) {
                    // If the current row is the last row, we need to add a new row with "to any"
                    var newRecord = Ext.create('Shopware.apps.Article.model.Price', {
                        from: event.value + 1,
                        customerGroupKey: record.get('customerGroupKey', null)
                    });
                    store.add(newRecord);
                } else {
                    // If the current row is not the last row we have to increase the from value of the next row
                    nextRecord.set('from', event.value + 1);
                }
            } else {
                icon.removeCls('x-hidden');
                icon.addCls('sprite-minus-circle-frame');
            }
        } else if ( event.field === 'price') {
           this.updatedPriceField(event);
            // If the user has edit the percent column, we have to calculate the price
        } else if (event.field === 'percent') {
            this.updatedPercentField(event);
        } else if (event.field === 'percentPseudo') {
            this.updatedPseudoPercentField(event);
        } else if (event.field === 'pseudoPrice') {
            this.updatedPseudoPriceField(event);
        }
    },

    /**
     * Helper function to update related fields of percent
     * @param event
     */
    updatedPercentField: function(event) {
        var me = this,
            record = event.record,
            store = event.grid.store,
            firstRecord = store.getAt(0),
            firstPrice = firstRecord.get('price'),
            price = record.get('price');

        if (firstPrice === price) {
            firstRecord.set('percent', null);
        } else if(event.value > 0) {
            price = firstPrice / 100 * (100 - event.value);
            price = price.toFixed(2);
            record.set('price', price);
        }

        // We need to trigger update on PriceField without copy&paste all that checks and updates
        me.updatedPriceField(event);
    },

    /**
     * Helper function to update related fields of price
     *
     * @param event
     */
    updatedPriceField: function(event) {
        var me = this,
            record = event.record,
            store = event.grid.store,
            firstRecord = store.getAt(0),
            firstPrice = firstRecord.get('price'),
            previousPrice = store.getAt(event.rowIdx -1),
            price = record.get('price'),
            percent,
            pseudoPrice = record.get('pseudoPrice'),
            percentPseudo = record.get('percentPseudo');

        if(price && previousPrice && previousPrice.get('price') < price) {
            record.set('price', previousPrice.get('price') - 0.01);
            // We need to trigger update on PriceField without copy&paste all that checks and updates
            me.updatedPriceField(event);
        } else if(price && firstPrice > price) {
            percent = (firstPrice - price) / firstPrice * 100;
            percent = percent.toFixed(2);
            record.set('percent', percent);
        } else {
            record.set('percent', null);
        }

        if(price && pseudoPrice > 0) {
            percentPseudo = 100 - 100 / pseudoPrice * price;
            percentPseudo = percentPseudo.toFixed(2);
            record.set('percentPseudo', percentPseudo);
        }
    },

    /**
     * Helper function to update related fields of pseudoPercent
     *
     * @param event
     */
    updatedPseudoPercentField: function(event) {
        var me = this,
            record = event.record,
            store = event.grid.store,
            firstRecord = store.getAt(0),
            firstPrice = firstRecord.get('price'),
            price = record.get('price'),
            pseudoPrice = record.get('pseudoPrice');

        if(pseudoPrice > 0) {
            // If the user enters 0 or nothing, the price has to be the pseudoPrice
            if(!Ext.isNumeric(event.value) || event.value === 0) {
                record.set('pseudoPrice', null);
            } else if(event.value > 0) {
                pseudoPrice = (price / (100 - event.value)) * 100;
                pseudoPrice = pseudoPrice.toFixed(2);
                record.set('pseudoPrice', pseudoPrice);
            }

            // We need to trigger update on PriceField without copy&paste all that checks and updates
            me.updatedPriceField(event);
        } else {
            /**
             * If the user enters a value when no pseudoPrice exists, we have to discard it
             * because we strictly calculate by pseudoPrice - not revers by price
             */
            record.set('percentPseudo', 0);
        }
    },

    /**
     * Helper function to update related fields of pseudoPrice
     *
     * @param event
     */
    updatedPseudoPriceField: function(event) {
        var me = this,
            record = event.record,
            price = record.get('price'),
            percentPseudo = record.get('percentPseudo');

        // If the user enters 0 or nothing, the percentPseudo has to be 0
        if(!Ext.isNumeric(event.value) || event.value === 0) {
            record.set('percentPseudo', 0);
        } else if(event.value > 0) {
            percentPseudo = 100 - (100 / event.value * price);
            percentPseudo = percentPseudo.toFixed(2);
            record.set('percentPseudo', percentPseudo);
        }

        // We need to trigger update on PriceField without copy&paste all that checks and updates
        me.updatedPriceField(event);
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

        // Check if the current row is the last row
        if ( event.field === 'to') {
            // If the current row isn't the last row, we want to cancel the edit.
            if (nextPrice) {
                return false;
            }
            // Check if the current row has a previous row.
            if (previousPrice) {
                // If this is the case we have to set the min value for the "to" field
                // +1 of the previous price
                minValue = ~~(previousPrice.get('to') * 1) + 1;
            }
            editor.setMinValue(minValue);
        }
        // Check if the user want to edit the price field.
        if ( event.field === "price" ) {
            if (previousPrice && previousPrice.get('price') > 0) {
                maxValue = previousPrice.get('price') - 0.01;
            }
            editor.setMaxValue(maxValue);
        }
    },

    /**
     * Removes the cloned prices of the article price store.
     * @param priceStore
     */
    removeClonedPrices: function(priceStore) {
        var toRemove = [];

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
     * @param { Object } oldTab The previous tab panel
     * @param { Object } newTab The clicked tab panel
     * @param { Ext.data.Store } priceStore The price store
     * @param { Array } customerGroupStore The price data of the first customer group.
     */
    onPriceTabChanged: function(oldTab, newTab, priceStore, customerGroupStore) {
        var me = this,
            toRemove = [],
            firstGroupPrices = [],
            customerGroup,
            firstGroup = customerGroupStore.first();

        customerGroup = newTab.customerGroup;
        priceStore.clearFilter();

        // First we remove all prices which have a cloned flag and save the prices of the first group.
        priceStore.each(function(item) {
            if (item instanceof Ext.data.Model && item.get('cloned')) {
                toRemove.push(item);
            }
            if (item.get('customerGroupKey') === firstGroup.get('key')) {
                firstGroupPrices.push(item);
            }
        });

        // We have to collect the records, because if we remove the items in the for each,
        // the store can't iterate the records correctly.
        priceStore.remove(toRemove);

        // Now we can filter the price store for the current customer group.
        priceStore.filter({
            filterFn: function(item) {
                return item.get('customerGroupKey') === customerGroup.get('key');
            }
        });

        // If the current customer group is the first/main customer group, we can return now.
        if (customerGroup.get('id') === firstGroup.get('id')) {
            return false;
        }

        // If no prices given for the current customer group, we have to copy the prices of the main customer group
        if (priceStore.data.length === 0) {
            priceStore.add(me.clonePrices(firstGroupPrices, customerGroup));
        }
    },

    /**
     * Clones the passed price array, sets the cloned flag for the cloned prices and
     * sets the customer group equals the key of the passed group.
     *
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
