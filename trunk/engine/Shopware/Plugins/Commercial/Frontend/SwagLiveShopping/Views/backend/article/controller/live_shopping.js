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
 * @package    SwagLiveShopping
 * @subpackage ExtJs
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware ExtJs controller
 */
//{namespace name="backend/live_shopping/article/view/main"}
//{block name="backend/live_shopping/controller/live_shopping"}
Ext.define('Shopware.apps.Article.controller.LiveShopping', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend:'Ext.app.Controller',

    refs: [
        { ref: 'detailWindow', selector: 'article-detail-window' },
        { ref: 'liveShoppingListing', selector: 'article-detail-window article-live-shopping-list' },
        { ref: 'liveShoppingDetailContainer', selector: 'article-detail-window container[name=live-shopping-detail-container]' },
        { ref: 'liveShoppingPriceListing', selector: 'article-detail-window live-shopping-price-listing' },
        { ref: 'liveShoppingCustomerGroupListing', selector: 'article-detail-window live-shopping-customer-group-listing' },
        { ref: 'liveShoppingShopListing', selector: 'article-detail-window live-shopping-shop-listing' },
        { ref: 'liveShoppingLimitedVariantListing', selector: 'article-detail-window live-shopping-limited-detail-listing' },
        { ref: 'liveShoppingTabPanel', selector: 'article-detail-window tabpanel[name=live-shopping-tab-panel]' },
        { ref: 'liveShoppingConfigurationPanel', selector: 'article-detail-window live-shopping-configuration-panel' }
    ],

    /**
     * Custom component property, contains a flag to identify if events fired
     * while loading a record into the view.
     */
    onLoadRecord: false,

    /**
     * Custom component property, contains a flag to identify if events fired
     * while deleting a record.
     */
    onDeleteRecord: false,

    /**
     * A template method that is called when your application boots.
     * It is called before the Application's launch function is executed
     * so gives a hook point to run any code before your Viewport is created.
     */
    init: function () {
        var me = this;

        me.control({
            'article-detail-window': {
                liveShoppingTabActivated: me.onLiveShoppingTabActivated,
                liveShoppingTabDeactivated: me.onLiveShoppingTabDeactivated,
                saveLiveShopping: me.onSaveLiveShopping
            },
            'article-detail-window article-live-shopping-list': {
                addLiveShopping: me.onAddLiveShopping,
                deleteLiveShopping: me.onDeleteLiveShopping,
                selectLiveShopping: me.onSelectLiveShopping
            },
            'article-detail-window live-shopping-customer-group-listing': {
                addCustomerGroup: me.onAddCustomerGroup,
                deleteCustomerGroup: me.onDeleteCustomerGroup
            },
            'article-detail-window live-shopping-price-listing': {
                addPrice: me.onAddPrice,
                deletePrice: me.onDeletePrice
            },
            'article-detail-window live-shopping-limited-detail-listing': {
                addLimitedVariant: me.onAddLimitedVariant,
                deleteLimitedVariant: me.onDeleteLimitedVariant
            },
            'article-detail-window live-shopping-shop-listing': {
                addShop: me.onAddShop,
                deleteShop: me.onDeleteShop
            },
            'article-detail-window live-shopping-configuration-panel': {
                validFromDateChanged: me.configurationDateChanged,
                validFromTimeChanged: me.configurationDateChanged,
                validToDateChanged: me.configurationDateChanged,
                validToTimeChanged: me.configurationDateChanged,
                liveShoppingTypeChanged: me.onLiveShoppingTypeChanged
            }
        });
    },

    /**
     * @EventListener
     * Event listener function of the save button of the article detail window.
     * Fired when the user clicks the save Liveshopping button which displayed
     * in the bottom bar of the article detail window.
     */
    onSaveLiveShopping: function(options) {
        var me = this;
        var listing = me.getLiveShoppingListing();
        var configPanel = me.getLiveShoppingConfigurationPanel();
        var tabPanel = me.getLiveShoppingTabPanel();
        var record = configPanel.getRecord();

        if (!configPanel.getForm().isValid()) {
            return;
        }

        configPanel.getForm().updateRecord(record);

        var validFromTime = configPanel.validFromTimeField.getValue();
        var validToTime = configPanel.validToTimeField.getValue();
        record.set('validFromTime', validFromTime.getHours() + ':' + validFromTime.getMinutes());
        record.set('validToTime', validToTime.getHours() + ':' + validToTime.getMinutes());

        var name = record.get('name');

        listing.setDisabled(true);
        configPanel.setDisabled(true);
        tabPanel.setDisabled(true);

        record.save({
            /**
             * Success handler function of the save function of a single live shopping
             * @param result Ext.data.Model
             * @param operation Ext.data.Operation
             */
            success: function(result, operation) {
                listing.getStore().load({
                    callback: function() {
                        var selectedRecord = listing.getStore().getById(result.get('id'));
                        if (selectedRecord) {
                            listing.getSelectionModel().select(record, false, true);
                            me.onSelectLiveShopping(selectedRecord);
                        } else {
                            me.onSelectLiveShopping(result);
                        }

                        var message = '{s name=messages/saved}Live shopping article saved successfully{/s}';
                        Shopware.Notification.createGrowlMessage(me.getMessageTitle(), message);

                        listing.setDisabled(false);
                        configPanel.setDisabled(false);
                        tabPanel.setDisabled(false);

                        if (options && Ext.isFunction(options.callback)) {
                            options.callback(true, result, record, operation);
                        }
                    }
                });
            },

            /**
             * Error handler function of the save function of a single bundle
             * @param result Ext.data.Model
             * @param operation Ext.data.Operation
             */
            failure: function(result, operation) {
                var rawData = result.getProxy().getReader().rawData;

                var message = '{s name=messages/failure}An error occurded while saving the live shopping article{/s}';
                if (rawData && rawData.message && rawData.message.length > 0) {
                    message = message + ':<br>' + rawData.message;
                }
                Shopware.Notification.createGrowlMessage(me.getMessageTitle(name), message);

                listing.setDisabled(false);
                configPanel.setDisabled(false);
                tabPanel.setDisabled(false);

                if (options && Ext.isFunction(options.callback)) {
                    options.callback(false, result, record, operation);
                }
            }
        });
    },

    /**
     * @EventListener
     * Event listener of the live shopping configuration panel.
     * Fired when the user change the live shopping type combo box.
     */
    onLiveShoppingTypeChanged: function(value) {
        var me = this;
        me.configurationDateChanged();

        if (me.onLoadRecord || me.onDeleteRecord) {
            return;
        }
        var message = '{s name=messages/type_changed}You have changed the live shopping type. Please note that the live shopping price calculation has also been changed.{/s}';
        Shopware.Notification.createGrowlMessage(me.getMessageTitle(), message);
    },


    /**
     * Helper function to update the price grid
     * if a configuration date/time changed
     */
    configurationDateChanged: function() {
        var me = this;
        var configPanel = me.getLiveShoppingConfigurationPanel();
        var validFrom = configPanel.validFromField.getValue();
        var validTo = configPanel.validToField.getValue();

        if (Ext.isDate(validFrom) && Ext.isDate(validTo) &&
            validFrom.getTime() == validTo.getTime() && !Ext.isEmpty(configPanel.validFromTimeField.getValue())) {

            var minValue = configPanel.validFromTimeField.getValue();

            configPanel.validToTimeField.setMinValue(
                minValue.getHours() + ':' + minValue.getMinutes() + 1
            );
            configPanel.validToTimeField.validate();
        }

        if (!me.onLoadRecord && !me.onDeleteRecord) {
            me.refreshPriceGrid();
        }
    },

    /**
     * @EventListener
     * Event listener function of the article detail window.
     * Fired when the user activate the live shopping tab.
     *
     * @param window Shopware.apps.Article.view.detail.Window
     * @param liveShoppingListStore Ext.data.Store
     */
    onLiveShoppingTabActivated: function(window, liveShoppingListStore) {
        window.liveShoppingSaveButton.show();
        window.saveButton.hide();
        window.configuratorSaveButton.hide();
        liveShoppingListStore.load();
        this.initDetailContainer();
    },

    /**
     * @EventListener
     * Event listener function of the article detail window.
     * Fired when the user change the active tab of the main tab panel from live shopping to
     * another tab.
     * @param window Shopware.apps.Article.view.detail.Window
     */
    onLiveShoppingTabDeactivated: function(window) {
        window.liveShoppingSaveButton.hide();
    },

    /**
     * @EventListener
     * Event listener function of the live shopping list.
     * Fired when the user clicks the add button in the list toolbar.
     */
    onAddLiveShopping: function() {
        var me = this;
        var listing = me.getLiveShoppingListing();
        var newRecord = me.createDefaultLiveShoppingRecord();

        listing.getStore().add(newRecord);
        listing.getSelectionModel().select(newRecord, false, true);

        me.loadRecordIntoView(newRecord);
    },

    /**
     * @EventListener
     * Event listener function of the live shopping list.
     * Fired when the user clicks the delete action column to remove
     * a single live shopping definition or if the user selects one or many
     * list rows and uses the delete all selected button.
     * @param records
     */
    onDeleteLiveShopping: function(records) {
        var me = this,
            name = '',
            listing = me.getLiveShoppingListing(),
            store = listing.getStore();

        me.onDeleteRecord = true;
        me.initDetailContainer();

        Ext.each(records, function(record) {
            if (!(record instanceof Ext.data.Model)) {
                return;
            }
            name = record.get('name');
            store.remove(record);
            record.destroy({
                callback: function(result, operation) {
                    var message = '';

                    if (operation.wasSuccessful()) {
                        message = '{s name=messages/delete}Live shopping article deleted.{/s}';
                        Shopware.Notification.createGrowlMessage(me.getMessageTitle(name), message);
                    } else {
                        var rawData = result.getProxy().getReader().rawData;
                        message = '{s name=messages/delete_failure}An error occurded while deleting the live shopping article{/s}';
                        if (rawData && rawData.message && rawData.message.length > 0) {
                            message = message + ':<br>' + rawData.message;
                        }
                        Shopware.Notification.createGrowlMessage(me.getMessageTitle(name), message);
                        store.load();
                    }
                }
            });
        });
        me.initDetailContainer();
        store.load();
        me.onDeleteRecord = false;
    },

    /**
     * @EventListener
     * Event listener function of the live shopping list.
     * Fired when the user clicks on a listing row. This function loads
     * the live shopping detail data and load the data into the form panel.
     */
    onSelectLiveShopping: function(listRecord) {
        var me = this;
        var detailStore = Ext.create('Shopware.apps.Article.store.live_shopping.Detail');
        var detailContainer = me.getLiveShoppingDetailContainer();

        if (!(listRecord instanceof Ext.data.Model)) {
            return false;
        }

        if (me.onDeleteRecord) {
            return false;
        }

        if (listRecord.get('id') === null) {
            var newRecord = me.createDefaultLiveShoppingRecord();
            me.loadRecordIntoView(newRecord);
            return true;
        }

        detailContainer.setLoading(true);
        detailStore.getProxy().extraParams.id = listRecord.get('id');
        var name = listRecord.get('name');

        detailStore.load({
            callback: function(records, operation) {
                detailContainer.setLoading(false);
                if (operation.wasSuccessful() && records.length > 0) {
                    me.loadRecordIntoView(records[0]);
                } else {
                    var message = '{s name=messages/load_failure}Live shopping article can not be loaded.{/s}';
                    Shopware.Notification.createGrowlMessage(me.getMessageTitle(name), message);
                }
            }
        });
        return true;
    },

    /**
     * @EventListener
     * Event listener function.
     * Fired when the user opens the toolbar customer group combo box
     * and select a combo box row.
     * @param Ext.data.Model The selected record
     */
    onAddCustomerGroup: function(record) {
        var me = this,
            customerGroupListing = me.getLiveShoppingCustomerGroupListing();

        if (customerGroupListing.getStore().getById(record.get('id'))) {
            var message = '{s name=messages/customer_group_exist}Customer group already added{/s}';
            Shopware.Notification.createGrowlMessage(me.getMessageTitle(), message);
        } else {
            customerGroupListing.getStore().add(record);
        }

        customerGroupListing.customerGroupComboBox.reset();
    },

    /**
     * @EventListener
     * Event listener function.
     * Fired when the user clicks the delete action column item
     * in the listing.
     * @param Ext.data.Model The row record
     */
    onDeleteCustomerGroup: function(record) {
        var me = this,
            customerGroupListing = me.getLiveShoppingCustomerGroupListing();

        customerGroupListing.getStore().remove(record);
        var message = '{s name=messages/customer_group_delete}Customer group removed{/s}';
        Shopware.Notification.createGrowlMessage(me.getMessageTitle(), message);
    },

    /**
     * @EventListener
     * Event listener function.
     * Fired when the user opens the toolbar shop combo box
     * and select a combo box row.
     * @param Ext.data.Model The selected record
     */
    onAddShop: function(record) {
        var me = this,
            shopListing = me.getLiveShoppingShopListing();

        if (shopListing.getStore().getById(record.get('id'))) {
            var message = '{s name=messages/shop_exist}Sub shop already added{/s}';
            Shopware.Notification.createGrowlMessage(me.getMessageTitle(), message);
        } else {
            shopListing.getStore().add(record);
        }

        shopListing.shopComboBox.reset();
    },

    /**
     * @EventListener
     * Event listener function.
     * Fired when the user clicks the delete action column item
     * in the listing.
     * @param Ext.data.Model The row record
     */
    onDeleteShop: function(record) {
        var me = this,
            shopListing = me.getLiveShoppingShopListing();

        shopListing.getStore().remove(record);
        var message = '{s name=messages/shop_remove}Sub shop removed{/s}';
        Shopware.Notification.createGrowlMessage(me.getMessageTitle(), message);
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
    onAddLimitedVariant: function(record) {
        var me = this,
                detailListing = me.getLiveShoppingLimitedVariantListing();

        if (detailListing.getStore().getById(record.get('id'))) {
            var message = '{s name=messages/variant_exist}Variant already added{/s}';
            Shopware.Notification.createGrowlMessage(me.getMessageTitle(), message);
        } else {
            detailListing.getStore().add(record);
        }
    },

    /**
     * Event listener function which fired when the user clicks
     * the delete action column item of the limited detail listing to remove a record.
     * @param record
     */
    onDeleteLimitedVariant: function(record) {
        var me = this,
                limitedListing = me.getLiveShoppingLimitedVariantListing();

        limitedListing.getStore().remove(record);
        var message = '{s name=messages/variant_removed}Variant removed{/s}';
        Shopware.Notification.createGrowlMessage(me.getMessageTitle(), message);
    },

    /**
     * @EventListener
     * Event listener function of the live shopping price listing.
     * Fired when the user select a customer group in the combo box
     * in the price listing toolbar.
     *
     * @param customerGroup
     */
    onAddPrice: function(customerGroup) {
        var me = this,
            priceListing = me.getLiveShoppingPriceListing();

        var defaultPrice = me.getPriceForCustomerGroupAndQuantity(me.subApplication.article.getPriceStore, priceListing.customerGroupStore.first(), 1);

        var price = Ext.create('Shopware.apps.Article.model.live_shopping.Price', {
            customerGroupKey: customerGroup.get('key'),
            price: defaultPrice.get('price')
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
     * @EventListener
     * Event listener function of the live shopping price listing.
     * Fired when the user clicks the delete action column item.
     * Removes the passed record from the price store.
     * @param record
     */
    onDeletePrice: function(record) {
        var me = this,
                priceListing = me.getLiveShoppingPriceListing();

        priceListing.getStore().remove(record);
        var message = '{s name=messages/on_delete_price}The price for this customer group has been successfully deleted.{/s}';
        Shopware.Notification.createGrowlMessage(me.getMessageTitle(), message);
    },

    /**
     * Helper function to calculate the per minute discount/surcharges for the passed
     * price.
     * @param priceRecord
     * @return Number
     */
    getPerMinute: function(priceRecord) {
        var me = this;
        var perMinute = 0;
        var timeDiff = me.getTimeDiff();
        var configurationPanel = me.getLiveShoppingConfigurationPanel();

        if (!Ext.isNumeric(priceRecord.get('price')) || !Ext.isNumeric(priceRecord.get('endPrice'))
              || priceRecord.get('price') < 0 || priceRecord.get('endPrice') < 0) {
            return 0;
        }

        if (timeDiff === 0) {
            return priceRecord.get('endPrice');
        }

        //we have to check if a customer group is set into the price object.
        if (priceRecord.getCustomerGroupStore instanceof Ext.data.Store && priceRecord.getCustomerGroupStore.getCount() > 0) {
            var liveShoppingType = configurationPanel.liveShoppingTypeComboBox.getValue();
            var priceDiff = 0;
            switch (liveShoppingType) {
                //standard live shopping article (fix price)
                case 1:
                    perMinute = priceRecord.get('endPrice');
                    break;
                //discount per minute
                case 2:
                    priceDiff = priceRecord.get('price') - priceRecord.get('endPrice');
                    perMinute = priceDiff / timeDiff;
                    break;
                //surcharge per minute
                case 3:
                    priceDiff = priceRecord.get('endPrice') - priceRecord.get('price');
                    perMinute = priceDiff / timeDiff;
                    break;
                default:
                    perMinute = priceRecord.get('endPrice');
                    break;
            }
        }
        return perMinute;
    },

    /**
     * Helper function to get the time diff of the current selected
     * dates/times in the configuration panel.
     * Returns the minutes between the both dates.
     * @return Number
     */
    getTimeDiff: function() {
        var me = this;
        var configPanel = me.getLiveShoppingConfigurationPanel();

        //time calcualtion
        var validFrom = configPanel.validFromField.getValue();
        var validTo = configPanel.validToField.getValue();
        var validFromTime = configPanel.validFromTimeField.getValue();
        var validToTime = configPanel.validToTimeField.getValue();

        if (!(Ext.isDate(validFrom))
                || !(Ext.isDate(validTo))
                || !(Ext.isDate(validFromTime))
                || !(Ext.isDate(validToTime))) {
            return 0;
        }

        validFrom.setHours(validFromTime.getHours());
        validFrom.setMinutes(validFromTime.getMinutes());

        validTo.setHours(validToTime.getHours());
        validTo.setMinutes(validToTime.getMinutes());

        //timeDiff property contains now the minute value between the two date objects
        return (validTo.getTime() - validFrom.getTime()) / 1000 / 60;
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
     * Helper function to refresh the price grid.
     */
    refreshPriceGrid: function() {
        var me = this;
        var priceListing = me.getLiveShoppingPriceListing();
        var configPanel = me.getLiveShoppingConfigurationPanel();

        var validFrom = configPanel.validFromField.getValue();
        var validTo = configPanel.validToField.getValue();
        var validFromTime = configPanel.validFromTimeField.getValue();
        var validToTime = configPanel.validToTimeField.getValue();

        if (!(Ext.isDate(validFrom))|| !(Ext.isDate(validTo)) || !(Ext.isDate(validFromTime)) || !(Ext.isDate(validToTime))) {
            return;
        }

        priceListing.reconfigure(priceListing.getStore());
    },

    /**
     * Helper function which loads the live shopping data of the passed record
     * into the configuration panel and in the association grids.
     * @return boolean
     */
    loadRecordIntoView: function(record) {
        var me = this;
        var detailContainer = me.getLiveShoppingDetailContainer();
        var configurationPanel = me.getLiveShoppingConfigurationPanel();
        var priceListing = me.getLiveShoppingPriceListing();
        var shopListing = me.getLiveShoppingShopListing();
        var limitedVariantListing = me.getLiveShoppingLimitedVariantListing();
        var customerGroupListing = me.getLiveShoppingCustomerGroupListing();

        if (!(record instanceof Ext.data.Model)) {
            return false;
        }

        try {
            me.onLoadRecord = true;

            me.subApplication.selectedLiveShopping = record;

            priceListing.reconfigure(record.getPrices());
            priceListing.liveShopping = record;

            shopListing.reconfigure(record.getShops());
            shopListing.liveShopping = record;

            customerGroupListing.reconfigure(record.getCustomerGroups());
            customerGroupListing.liveShopping = record;

            limitedVariantListing.reconfigure(record.getLimitedVariants());
            limitedVariantListing.liveShopping = record;

            configurationPanel.numberField.validationRequestParam = record.get('id');
            configurationPanel.loadRecord(record);

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
        var title = '{s name=messages/title_full}Liveshopping [0]{/s}';
        name = name + '';

        if (name !== Ext.undefined && name !== 'undefined' && name.length > 0) {
            return Ext.String.format(title, name);
        } else if (me.subApplication.selectedLiveShopping instanceof Ext.data.Model) {
            return Ext.String.format(title, me.subApplication.selectedLiveShopping.get('name'));
        } else {
            return '{s name=messages/title}Liveshopping{/s}';
        }
    },

    /**
     * Helper function which creates a new live shopping record with
     * an default offset of settings.
     * @return Ext.data.Model
     */
    createDefaultLiveShoppingRecord: function() {
        var me = this;

        return Ext.create('Shopware.apps.Article.model.live_shopping.LiveShopping', {
            id: null,
            active: true,
            type: 1,
            name: '{s name=messages/default_name}My live shopping{/s}',
            articleId: me.subApplication.article.get('id')
        });
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
     * Helper function to initial the detail container of the live shopping panel.
     */
    initDetailContainer: function() {
        var me = this,
            detailContainer = me.getLiveShoppingDetailContainer(),
            configurationPanel = me.getLiveShoppingConfigurationPanel(),
            priceListing = me.getLiveShoppingPriceListing(),
            customerGroupListing = me.getLiveShoppingCustomerGroupListing(),
            limitedVariantListing = me.getLiveShoppingLimitedVariantListing();

        me.onLoadRecord = true;
        configurationPanel.getForm().reset();
        priceListing.reconfigure(Ext.create('Ext.data.Store', { model: 'Shopware.apps.Article.model.live_shopping.Price' }));
        customerGroupListing.reconfigure(Ext.create('Ext.data.Store', { model: 'Shopware.apps.Base.model.CustomerGroup' }));
        limitedVariantListing.reconfigure(Ext.create('Ext.data.Store', { model: 'Shopware.apps.Article.model.Detail' }));
        detailContainer.setDisabled(true);
        me.onLoadRecord = false;
    }
});
//{/block}