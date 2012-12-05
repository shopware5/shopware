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
 * @subpackage Bundle
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Controller - Article backend module - Bundle plugin
 */
//{namespace name="backend/bundle/article/view/main"}
//{block name="backend/article/controller/bundle"}
Ext.define('Shopware.apps.Article.controller.Bundle', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend:'Ext.app.Controller',

    refs: [
        { ref: 'detailWindow', selector: 'article-detail-window' },
        { ref: 'bundleListing', selector: 'article-detail-window article-bundle-list' },
        { ref: 'configurationPanel', selector: 'article-detail-window bundle-configuration-panel' },
        { ref: 'detailContainer', selector: 'article-detail-window container[name=bundle-detail-container]' },
        { ref: 'articleListing', selector: 'article-detail-window bundle-article-listing' },
        { ref: 'priceListing', selector: 'article-detail-window bundle-price-listing' },
        { ref: 'customerGroupListing', selector: 'article-detail-window bundle-customer-group-listing' },
        { ref: 'limitedDetailListing', selector: 'article-detail-window bundle-limited-detail-listing' }
    ],

    /**
     * Helper property to prevent the change events while loading the
     * form panel record.
     * @boolean
     */
    onLoadRecord: false,

    /**
     * A template method that is called when your application boots.
     * It is called before the Application's launch function is executed
     * so gives a hook point to run any code before your Viewport is created.
     */
    init:function () {
        var me = this;

        me.control({
            'article-detail-window article-bundle-list': {
                selectBundle: me.onSelectBundle,
                addBundle: me.onAddBundle,
                deleteBundle: me.onDeleteBundle
            },
            'article-detail-window bundle-configuration-panel': {
                discountTypeChanged: me.onDiscountTypeChanged
            },
            'article-detail-window bundle-article-listing': {
                addBundleArticle: me.onAddBundleArticle,
                deleteBundleArticle: me.onDeleteBundleArticle,
                changeBundleArticle: me.onChangeBundleArticle,
                openArticle: me.onOpenArticle
            },
            'article-detail-window bundle-customer-group-listing': {
                addCustomerGroup: me.onAddCustomerGroup,
                deleteCustomerGroup: me.onDeleteCustomerGroup
            },
            'article-detail-window bundle-price-listing': {
                addPrice: me.onAddPrice,
                deletePrice: me.onDeletePrice,
                changePrice: me.onChangePrice
            },
            'article-detail-window bundle-limited-detail-listing': {
                addLimitedDetail: me.onAddLimitedDetail,
                deleteLimitedDetail: me.onDeleteLimitedDetail
            },
            'article-detail-window': {
                bundleTabActivated: me.onBundleTabActivated,
                bundleTabDeactivated: me.onBundleTabDeactivated,
                saveBundle: me.onSaveBundle
            }
        });
        me.callParent(arguments);
    },

    /**
     * @Event
     * Custom component event.
     * Fired when the user clicks the "open article" action column item in the
     * bundle article listing.
     * @param articleId
     */
    onOpenArticle: function(articleId) {
        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.Article',
            action: 'detail',
            params: {
                articleId: articleId
            }
        });
    },

    /**
     * @Event
     * Custom component event.
     * Event listener function of the bundle listing component.
     * Fired when the user clicks on the delete action column item,
     * to delete one or many bundles.
     */
    onDeleteBundle: function(records) {
        var me = this,
            listing = me.getBundleListing();

        me.resetDetailContainer();
        listing.getStore().remove(records);
        listing.getStore().sync({
            callback: function() {
                if (listing.getStore().getCount() > 0) {
                    listing.getSelectionModel().select(listing.getStore().getAt(0));
                }
            }
        });
    },

    /**
     * Internal helper function to reset the detail container items.
     */
    resetDetailContainer: function() {
        var me = this,
            detailContainer = me.getDetailContainer(),
            configurationPanel = me.getConfigurationPanel(),
            articleListing = me.getArticleListing(),
            priceListing = me.getPriceListing(),
            customerGroupListing = me.getCustomerGroupListing(),
            limitedDetailListing = me.getLimitedDetailListing();

        configurationPanel.getForm().reset();
        articleListing.reconfigure(Ext.create('Ext.data.Store', { model: 'Shopware.apps.Article.model.bundle.Article' }));
        priceListing.reconfigure(Ext.create('Ext.data.Store', { model: 'Shopware.apps.Article.model.Price' }));
        customerGroupListing.reconfigure(Ext.create('Ext.data.Store', { model: 'Shopware.apps.Base.model.CustomerGroup' }));
        limitedDetailListing.reconfigure(Ext.create('Ext.data.Store', { model: 'Shopware.apps.Article.model.Detail' }));
        detailContainer.setDisabled(true);
    },

    /**
     * @Event
     * Custom component event.
     * Fired when the user clicks the add button of the listing toolbar
     * to create a new article bundle.
     */
    onAddBundle: function() {
        var me = this,
            listing = me.getBundleListing();

        var bundle = me.createDefaultBundle();

        listing.getStore().add(bundle);
        listing.getSelectionModel().select(bundle, false, true);
        me.loadBundleIntoView(bundle);
    },

    /**
     * Helper function to create a new bundle record.
     * @return Shopware.apps.Article.model.bundle.Bundle
     */
    createDefaultBundle: function() {
        var me = this;

        return Ext.create('Shopware.apps.Article.model.bundle.Bundle', {
            id: null,
            active: 1,
            discountType: 'pro',
            type: 1,
            name: 'New Bundle',
            articleId: me.subApplication.article.get('id')
        });

    },

    /**
     * @Event
     * Custom component event.
     * Fired over the "save bundle" button which displayed in the article detail window
     * toolbar.
     * @param options
     */
    onSaveBundle: function(options) {
        var me = this,
            detailWindow = me.getDetailWindow(),
            listing = me.getBundleListing(),
            configurationPanel = me.getConfigurationPanel();

        console.log(configurationPanel);
        if (!configurationPanel.getForm().isValid()) {
            return;
        }

        var record = configurationPanel.getRecord();

        configurationPanel.getForm().updateRecord(record);

        detailWindow.setLoading(true);
        
        record.save({
            /**
             * Success handler function of the save function of a single bundle
             * @param result Ext.data.Model
             * @param operation Ext.data.Operation
             */
            success: function(result, operation) {
                listing.getStore().load({
                    callback: function() {
                        listing.getSelectionModel().select(record, false);
                    }
                });
                detailWindow.setLoading(false);
                if (options && Ext.isFunction(options.callback)) {
                    options.callback(true, result);
                }
            },
            /**
             * Error handler function of the save function of a single bundle
             * @param result Ext.data.Model
             * @param operation Ext.data.Operation
             */
            failure: function(result, operation) {
                var rawData = result.getProxy().getReader().rawData;
                var message = '{s name=messages/on_save_bundle_failure}An error occurred while attempting to save the bundle:{/s}' + '<br>' + rawData.message;

                Shopware.Notification.createGrowlMessage(me.getMessageTitle(), message);

                detailWindow.setLoading(false);
                if (options && Ext.isFunction(options.callback)) {
                    options.callback(false, result);
                }
            }
        });
    },

    /**
     * @Event
     * Custom component event.
     * Fired when the user change the grid selection.
     * @param Ext.data.Model The record of the first selected grid row
     */
    onSelectBundle: function(record) {
        var me = this;
        var detailStore = Ext.create('Shopware.apps.Article.store.bundle.Detail');
        var detailContainer = me.getDetailContainer();

        if (!(record instanceof Ext.data.Model)) {
            me.resetDetailContainer();
            return false;
        }
        if (!record.get('id') > 0) {
            var bundle = me.createDefaultBundle();
            me.loadBundleIntoView(bundle);
            return true;
        }

        detailContainer.setLoading(true);
        detailStore.getProxy().extraParams.id = record.get('id');
        var name = record.get('name');

        detailStore.load({
            callback: function(records, operation) {
                detailContainer.setLoading(false);
                if (operation.wasSuccessful() && records.length > 0) {
                    var bundle = records[0];
                    me.loadBundleIntoView(bundle);
                } else {
                    var message = '{s name=messages/on_select_bundle}"You were trying to load a specific bundle, but an error has occurred. Please restart the product modules in order to avoid inconsistent data.{/s}';
                    Shopware.Notification.createGrowlMessage(me.getMessageTitle(name), message);
                }
            }
        });
    },

    /**
     * Event listener function.
     * Fired when the user clicks the add button after he selects an article in the
     * search field.
     * @param record Ext.data.Model
     */
    onAddBundleArticle: function(record) {
        var me = this;
        var articleListing = me.getArticleListing();

        if (!(record instanceof Ext.data.Model)) {
            var message = '{s name=messages/on_add_bundle_article_failure}You were trying to add a specific product to the bundle, but the data set of the product search could not be correctly identified. Please restart the product modules in order to avoid inconsistent data.{/s}';
            Shopware.Notification.createGrowlMessage(me.getMessageTitle(), message);
            return false;
        }

        var bundleArticle = Ext.create('Shopware.apps.Article.model.bundle.Article', {
            articleDetailId: record.get('id'),
            quantity: 1,
            configurable: false
        });
        bundleArticle.getArticleDetailStore = Ext.create('Ext.data.Store', { model: 'Shopware.apps.Article.model.Detail' });
        bundleArticle.getArticleDetailStore.add(record);
        articleListing.getStore().add(bundleArticle);

        articleListing.articleSearch.searchField.reset();
        articleListing.articleSearch.selectedValue = null;
        me.refreshPrices();

        articleListing.articleSearch.searchField.focus(false, 125);

        return true;
    },

    /**
     * Event listener function which fired when the user clicks the delete action
     * column item in the article listing to delete an bundle article.
     *
     * @param records Ext.data.Model[]
     */
    onDeleteBundleArticle: function(records) {
        var me = this,
            articleListing = me.getArticleListing();

        articleListing.getStore().remove(records);
        me.refreshPrices();

        var message = '{s name=messages/on_delete_bundle_article}The bundle product has been successfully deleted. Please note that the bundle prices have been updated.{/s}';
        Shopware.Notification.createGrowlMessage(me.getMessageTitle(), message);
    },

    /**
     * Event listener function which fired when the user use the row editor
     * in the article listing to change the quantity.
     */
    onChangeBundleArticle: function() {
        var me = this;
        me.refreshPrices();
        var message = '{s name=messages/on_change_bundle_article}The bundle product has been successfully modified. Please note that the bundle prices have been updated.{/s}';
        Shopware.Notification.createGrowlMessage(me.getMessageTitle(), message);
    },

    /**
     * Internal helper function which refreshs all view components which displays
     * the bundle prices or the total of the article prices.
     */
    refreshPrices: function() {
        var me = this;
        var articleListing = me.getArticleListing();
        var priceListing = me.getPriceListing();

        articleListing.reconfigure(articleListing.getStore());
        priceListing.reconfigure(priceListing.getStore());
    },

    /**
     * Event listener function.
     * Fired when the user change the discount type combo box.
     *
     * @param discountType
     */
    onDiscountTypeChanged: function(discountType) {
        var me = this,
            priceListing = me.getPriceListing();

        priceListing.bundle.set('discountType', discountType);
        priceListing.reconfigure(priceListing.getStore(), priceListing.createColumns());

        if (me.onLoadRecord) {
            return;
        }

        var message = '{s name=messages/on_discount_type_changed}You have changed the bundle discount type. Please note that the bundle prices may have also been changed.{/s}';
        Shopware.Notification.createGrowlMessage(me.getMessageTitle(), message);
    },

    /**
     * The loadBundleIntoView function refresh all view component with the passed bundle data.
     * @param bundle
     * @return boolean
     */
    loadBundleIntoView: function(bundle) {
        var me = this;

        //check if a bundle passed
        if (!(bundle instanceof Ext.data.Model)) {
            return false;
        }

        try {
            me.onLoadRecord = true;
            me.subApplication.selectedBundle = bundle;

            var configurationPanel = me.getConfigurationPanel();
            var articleListing = me.getArticleListing();
            var priceListing = me.getPriceListing();
            var customerGroupListing = me.getCustomerGroupListing();
            var limitedDetailListing = me.getLimitedDetailListing();

            articleListing.reconfigure(bundle.getArticles());
            articleListing.bundle = bundle;

            priceListing.reconfigure(bundle.getPrices());
            priceListing.bundle = bundle;

            customerGroupListing.reconfigure(bundle.getCustomerGroups());
            customerGroupListing.bundle = bundle;

            limitedDetailListing.reconfigure(bundle.getLimitedDetails());
            limitedDetailListing.bundle = bundle;

            configurationPanel.numberField.validationRequestParam = bundle.get('id');
            configurationPanel.loadRecord(bundle);

            var detailContainer = me.getDetailContainer();
            detailContainer.setDisabled(false);
            me.onLoadRecord = false;
        } catch (e) {

        }

        return true;
    },

    /**
     * Helper function to get the global shopware notfication title.
     *
     * @return string
     */
    getMessageTitle: function(name) {
        var me = this;
        var title = '{s name=messages/title_full}Produktbundle [0]{/s}';
        name = name + '';

        if (name !== Ext.undefined && name !== 'undefined' && name.length > 0) {
            return Ext.String.format(title, name);
        } else if (me.subApplication.selectedBundle instanceof Ext.data.Model) {
            return Ext.String.format(title, me.subApplication.selectedBundle.get('name'));
        } else {
            return '{s name=messages/title}Produktbundle{/s}';
        }
    },

    /**
     * Get the price for the customer group and the passed quantiy.
     * @param prices
     * @param customerGroup
     * @param quantity
     */
    getPriceForCustomerGroupAndQuantity: function(prices, customerGroup, quantity) {
        var me = this;

        if (!Ext.isNumeric(quantity)) {
            quantity = 1;
        }
        var customerGroupPrice = null;

        if (prices instanceof Ext.data.Store && prices.getCount() > 0) {
            prices.each(function(price) {
                //if the customer group key of the column equals the customer group key of the price
                //and the "to" property is not numeric or the "to" value is numeric and greater eqauls the selected quantity display the price
                if (price.get('customerGroupKey') == customerGroup.get('key')) {

                    //check if the "to" property is set to "beliebig" or the quantity is smaller equals the price "to" property.
                    if (!Ext.isNumeric(price.get('to')) || (Ext.isNumeric(price.get('to')) && quantity <= price.get('to'))) {
                        customerGroupPrice = price;
                        return false;
                    }
                }
            });
        }

        return customerGroupPrice;
    },

    /**
     * Internal helper function to get the total amount for the passed customer group
     * which used for the summary row.
     * @param customerGroup
     * @param customerGroupStore
     */
    getTotalAmountForCustomerGroup: function(customerGroup, customerGroupStore) {
        var me = this, total = 0, price, articleDetail, quantity;

        var articleListing = me.getArticleListing();
        var articleStore = articleListing.getStore();

        //iterate all articles of the passed store.
        articleStore.each(function(row) {
            //the store contains one or many bundle articles (Shopware\CustomModels\Bundle\Article).
            //this articles has an association to the assigned article variant (Shopware\Models\Article\Detal)
            if (row.getArticleDetail() instanceof Ext.data.Store && row.getArticleDetail().first() instanceof Ext.data.Model) {
                articleDetail = row.getArticleDetail().first();
                quantity = row.get('quantity');
                if (!quantity > 0) {
                    quantity = 1;
                }
                price = me.getPriceForCustomerGroupAndQuantity(articleDetail.getPrice(), customerGroup, quantity);
                if (price === null) {
                    price = me.getPriceForCustomerGroupAndQuantity(articleDetail.getPrice(), customerGroupStore.first(), quantity);
                }
                total += price.get('price') * quantity;
            }
        });

        //to get the total amount for the bundle, we have to add the price of the article.
        var article = me.subApplication.article;
        var priceStore = article.getPrice();
        var lastFilter = priceStore.filters.items;
        priceStore.clearFilter();

        price = me.getPriceForCustomerGroupAndQuantity(article.getPrice(), customerGroup, 1);
        if (price === null) {
            price = me.getPriceForCustomerGroupAndQuantity(article.getPrice(), customerGroupStore.first(), 1);
        }
        priceStore.filter(lastFilter);
        total += price.get('price');

        return total;
    },

    /**
     * Event listener function.
     * Fired when the user opens the toolbar customer group combo box
     * and select a combo box row.
     * @param Ext.data.Model The selected record
     */
    onAddCustomerGroup: function(record) {
        var me = this,
            customerGroupListing = me.getCustomerGroupListing();

        if (customerGroupListing.getStore().getById(record.get('id'))) {

        } else {
            customerGroupListing.getStore().add(record);
        }
        customerGroupListing.customerGroupComboBox.reset();
    },

    /**
     * Event listener function.
     * Fired when the user clicks the delete action column item
     * in the listing.
     * @param Ext.data.Model The row record
     */
    onDeleteCustomerGroup: function(record) {
        var me = this,
            customerGroupListing = me.getCustomerGroupListing();

        customerGroupListing.getStore().remove(record);
    },

    /**
     * Event listener function of the bundle price listing.
     * Fired when the user select a customer group in the combo box
     * in the price listing toolbar.
     *
     * @param customerGroup
     */
    onAddPrice: function(customerGroup) {
        var me = this,
            priceListing = me.getPriceListing();

        var price = Ext.create('Shopware.apps.Article.model.Price', {
            customerGroupKey: customerGroup.get('key'),
            price: 1
        });
        price.getCustomerGroupStore = Ext.create('Ext.data.Store', {
            model: 'Shopware.apps.Base.model.CustomerGroup'
        });
        price.getCustomerGroupStore.add(customerGroup);

        if (me.isCustomerGroupPriceInStore(priceListing.getStore(), customerGroup) > -1) {
            var message = '{s name=messages/on_add_price_failure}A defined price already exists for this customer group.{/s}';
            Shopware.Notification.createGrowlMessage(me.getMessageTitle(), message);
        } else {
            priceListing.getStore().add(price);
            priceListing.cellEditor.startEdit(price, me.getColumnByDataIndex(priceListing.columns, 'price'));
        }
        priceListing.customerGroupComboBox.reset();

    },

    /**
     * Internal helper function to check if a customer group price is already defined.
     * @param store
     * @param customerGroup
     * @return int Position of the customer group price in the store.
     */
    isCustomerGroupPriceInStore: function(store, customerGroup) {
        return store.findBy(function(record, id) {
            if (record.getCustomerGroup() instanceof Ext.data.Store &&
                record.getCustomerGroup().getCount() > 0) {

                var priceCustomerGroup = record.getCustomerGroup().first();
                return (priceCustomerGroup.get('key') === customerGroup.get('key'));
            } else {
                return false;
            }
        });
    },


    /**
     * Event listener function of the bundle price listing.
     * Fired when the user clicks the delete action column item.
     * Removes the passed record from the price store.
     * @param record
     */
    onDeletePrice: function(record) {
        var me = this,
            priceListing = me.getPriceListing();
 
        priceListing.getStore().remove(record);
        var message = '{s name=messages/on_delete_price}The price for this customer group has been successfully deleted.{/s}';
        Shopware.Notification.createGrowlMessage(me.getMessageTitle(), message);
    },

    /**
     * Event listener function of the bundle price listing.
     * Fired when the user change a customer group price
     * over the cell editor.
     * @param record
     */
    onChangePrice: function(record) {
        var me = this;


    },

    /**
     * Internal helper function to get a grid column identified over the column data index.
     * Used for the summary feature.
     * @param dataIndex
     * @param columns
     */
    getColumnByDataIndex: function(columns, dataIndex) {
        var me = this, result = null;

        Ext.each(columns, function(column) {
            if (column.dataIndex == dataIndex) {
                result = column;
                return false;
            }
        });
        return result;
    },

    /**
     * Event listener function which fired when the user select a record
     * of the variant combo box which displayed in the toolbar of the limited detail
     * listing.
     * The function checks if the passed article variant is already in the limited detail store,
     * if this is not the case the variant record will be added.
     * In the other case, the function throws a growl message to inform the user.
     *
     * @param record
     */
    onAddLimitedDetail: function(record) {
        var me = this,
            detailListing = me.getLimitedDetailListing();

        if (detailListing.getStore().getById(record.get('id'))) {

        } else {
            detailListing.getStore().add(record);
        }
    },

    /**
     * Event listener function which fired when the user clicks
     * the delete action column item of the limited detail listing to remove a record.
     * @param record
     */
    onDeleteLimitedDetail: function(record) {
        var me = this,
            limitedListing = me.getLimitedDetailListing();

        limitedListing.getStore().remove(record);
    },

    /**
     * Event listener function of the article detail window.
     * Fired when the user activate the bundle tab.
     *
     * @param window Shopware.apps.Article.view.detail.Window
     * @param bundleListStore Ext.data.Store
     */
    onBundleTabActivated: function(window, bundleListStore) {

        window.bundleSaveButton.show();
        window.saveButton.hide();
        window.configuratorSaveButton.hide();
        bundleListStore.load();
    },

    /**
     * Event listener function of the article detail window.
     * Fired when the user change the active tab of the main tab panel from bundle to
     * another tab.
     * @param window Shopware.apps.Article.view.detail.Window
     */
    onBundleTabDeactivated: function(window) {
        window.bundleSaveButton.hide();
    }

});
//{/block}
