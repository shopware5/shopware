/**
 * Shopware 4
 * Copyright © shopware AG
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
 * @package    Order
 * @subpackage Controller
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stephan Pohl
 * @author     $Author$
 */

/**
 *
 */
//{namespace name=backend/plugin_manager/main}
//{block name="backend/plugin_manager/controller/Store"}
Ext.define('Shopware.apps.PluginManager.controller.Store', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend:'Ext.app.Controller',

    /**
     * References for the controller for easier accessing.
     * @array
     */
    refs: [
        { ref: 'mainWindow', selector: 'plugin-manager-main-window' },
        { ref: 'detailWindow', selector: 'plugin-manager-detail-window' },
        { ref: 'managerNavigation', selector: 'plugin-manager-manager-navigation' },
        { ref: 'storeNavigation', selector: 'plugin-manager-store-navigation' },
        { ref: 'storeView', selector: 'plugin-manager-store-view' },
        { ref: 'versionCombo', selector: 'plugin-manager-detail-description combobox[name=versionCombo]' }
    ],

	snippets: {
		store:{
			title: '{s name=store/title}Plugin manager{/s}',
			warning: '{s name=store/warning}Warning{/s}',
			notice: '{s name=store/notice}Notice{/s}',
            multiShopNotice: '{s name=store/multi_shop_notice}You have bought a sub shop licence. Please configure the domain to assign licence to in your Shopware account at the menu <a href=[0], target=[1]>download and licence overview</a>.{/s}',
			failed_tax: '{s name=store/failed_tax}Product could not be determined{/s}',
			successful_install: '{s name=store/successful_install}Plugin have been installed successfully{/s}',
			need_licence_plugin_title: '{s name=store/need_licence_plugin_title}The license plugin is needed{/s}',
			need_licence_plugin: '{s name=store/need_licence_plugin}You need the license plugin to proceed. Please confirm the plugin installation with a click on yes or the buying process will be aborted{/s}',
			data_not_complete: '{s name=store/data_not_complete}Account data is not complete{/s}',
			need_ioncube_title: '{s name=store/need_ioncube_title}Ioncube is needed{/s}',
			need_ioncube: '{s name=store/need_ioncube}You need Ioncube-Loader to proceed. Click on yes to get to the Ioncube download page.{/s}',
			article_not_aviable: '{s name=store/article_not_available}The wanted article is not available yet{/s}'
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
            'plugin-manager-main-window tabpanel[name=main-tab]': {
                beforetabchange: me.onMainTabChange
            },
            'plugin-manager-manager-navigation': {
                'searchCommunityStore': me.onSearch
            },
            'plugin-manager-store-navigation': {
                'changeCategory': me.onChangeCategory,
                'searchCommunityStore': me.onSearch
            },
            'plugin-manager-store-view': {
                'changeCategory': me.onChangeCategory,
                'openArticle': me.onOpenArticle
            },
            'plugin-manager-detail-window': {
                installPlugin: me.onInstallPlugin
            },
            'plugin-manager-account-confirm': {
                'confirmbuy': me.sendBuyRequest
            }
        });

        me.getSessionLogin();
        me.registerUpdateStoreListener();
    },

    registerUpdateStoreListener: function() {
        var me = this;

        me.subApplication.updatesStore.on('load', function() {
            var managerNav = me.getManagerNavigation();
            var storeNav = me.getStoreNavigation();

            managerNav.accountCategoryStore.getAt(2).set('badge', me.subApplication.updatesStore.getCount());
            storeNav.accountCategoryStore.getAt(2).set('badge', me.subApplication.updatesStore.getCount());
            managerNav.accountNavigation.refresh();
            storeNav.accountNavigation.refresh();
        });
    },

    /**
     * Helper function which sends a ajax request to get the account data if the session login
     * is already valid.
     */
    getSessionLogin: function() {
        var me = this;

        Ext.Ajax.request({
            url:'{url controller="Store" action="getLogin"}',
            method: 'GET',
            callback: function(request, opts, operation) {
                var response = Ext.decode(operation.responseText),
                    accountCtl = me.subApplication.getController('Account');

                if (response.success === true) {
                    me.subApplication.myAccount = Ext.create('Shopware.apps.PluginManager.model.Account', response.data);
                    me.refreshAccountNavigation();
                }
            }
        });
    },

    refreshAccountNavigation: function() {
        var me = this;
        var managerNav = me.getManagerNavigation();
        var storeNav = me.getStoreNavigation();

        managerNav.accountCategoryStore.getAt(0).set('badge', me.subApplication.myAccount.get('balance'));
        storeNav.accountCategoryStore.getAt(0).set('badge', me.subApplication.myAccount.get('balance'));
        managerNav.accountNavigation.refresh();
        storeNav.accountNavigation.refresh();
    },


    onMainTabChange: function(panel, newCard) {
        var me = this;
        if (newCard.name === 'store' && me.subApplication.communityStore.getCount() === 0) {
            me.subApplication.communityStore.load();
            me.subApplication.categoryStore.load();
            me.subApplication.topSellerStore.load();
        }
    },

    /**
     * Event listener function of the plugin detail page. Fired when the user clicks on the "buy and install" button.
     *
     * @param record
     */
    onInstallPlugin: function(window, button) {
        var me = this;
        var record = window.record;
        var detail = record.getDetail().first();
        var comboBox = me.getVersionCombo();

        if (comboBox && comboBox.valueModels[0]) {
            var detailId = comboBox.valueModels[0];
            detail = record.getDetail().getById(detailId.data.articleId);
        }

        //check if the plugin isn't a free plugin
        if (detail.get('price') > 0) {
            me.sendTaxRequest(record, detail);
        } else {
            me.sendBuyRequest(record, detail);
        }
    },

    /**
     * Sends an ajax request to get the updated price for the current product.
     *
     * @param record
     * @param detail
     */
    sendTaxRequest: function(record, detail) {
        var me = this;

        var window = me.getMainWindow();
        if (window) {
            window.setLoading(true);
        }
        Ext.Ajax.request({
            url:'{url controller="Store" action="tax"}',
            method: 'POST',
            params:{
                productId: record.get('id'),
                detail: detail.get('id')
            },
            callback: function(request, opts, operation) {
                var response = Ext.decode(operation.responseText),
                    accountCtl = me.subApplication.getController('Account');

                if (window) {
                    window.setLoading(false);
                }

                if (response.success === true) {
                    //updated price
                    var price = response.price;
                    accountCtl.onOpenConfirm(price, record, detail);
                } else if (response.noId) {
                    //no valid product id passed
                    Shopware.Notification.createStickyGrowlMessage({
                       title: me.snippets.store.title,
                       text: me.snippets.store.failed_tax,
                       log: true
                    });
                } else if (response.loginRequired) {
                    //user isn't logged in or token is invalid
                    accountCtl.onOpenLogin({
                        controller: 'Store',
                        action: 'sendTaxRequest',
                        record: record,
                        detail: detail
                    });
                } else if (response.code) {
                    //exception occurred
                    Shopware.Notification.createGrowlMessage('Plugin-Manager', response.message);
                }
            }
        });

    },

    /**
     *
     * @param record
     * @param detail
     */
    sendBuyRequest: function(record, detail) {
        var me = this,
            licenceKey = '';

        var win = me.getMainWindow();
        if (win) {
            win.setLoading(true);
        }

        if (record instanceof Ext.data.Model && record.getAttribute() instanceof Ext.data.Store && record.getAttribute().first() instanceof Ext.data.Model) {
            licenceKey = record.getAttribute().first().get('licence_key');
        }

        Ext.Ajax.request({
            url:'{url controller="Store" action="buy"}',
            method: 'POST',
            params:{
                productId: ~~(1 * record.get('id')),
                rentVersion: detail.get('rent_version'),
                licenceKey: licenceKey,
                plugin_names: record.get('plugin_names')
            },
            callback: function(request, opts, operation) {
                var response = Ext.decode(operation.responseText);

                if (win) {
                    win.setLoading(false);
                }

                if (response.success === true) {
                    var pluginNames = record.get('plugin_names');
                    if (Ext.isArray(pluginNames) && Ext.Array.contains(pluginNames, 'SwagLicense')) {
                        Ext.Ajax.request({
                            url:'{url controller="PluginManager" action="refreshPluginList"}',
                            method: 'POST',
                            callback: function(request, opts, operation) {
                                Ext.Ajax.request({
                                    url:'{url controller="PluginManager" action="installLicensePlugin"}',
                                    method: 'POST'
                                });
                            }
                        });
                    }

                    Shopware.Notification.createGrowlMessage(me.snippets.store.title, me.snippets.store.successful_install);
                    if (response.license && response.license.length > 0) {
                        me.insertProductLicense(response.license);
                    }


                    if (response.isMultiShopPlugin) {
                        message = me.snippets.store.multiShopNotice;
                        message = Ext.String.format(message, '"http://store.shopware.de/downloads"'  , '"_blank"');
                        Ext.MessageBox.alert(me.snippets.store.notice, message, function(btn) {
                            return false;
                        });
                    }

                    var detailWindow = me.getDetailWindow();
                    if (detailWindow) {
                        detailWindow.destroy();
                    }

                } else if (response.noId) {
                    //no valid product id passed
                    Shopware.Notification.createGrowlMessage(me.snippets.store.title, me.snippets.store.failed_tax);
                } else if (response.loginRequired) {
                    //user isn't logged in or token is invalid
                    me.subApplication.getController('Account').onOpenLogin({
                        controller: 'Store',
                        action: 'sendBuyRequest',
                        record: record,
                        detail: detail
                    });

                } else if (response.licensePluginRequired) {
                    Ext.MessageBox.confirm(me.snippets.store.need_licence_plugin_title, me.snippets.store.need_licence_plugin , function(btn) {
                        if(btn == 'yes') {
                            me.buyLicensePlugin(record, detail, win);
                        } else {
                            return false;
                        }
                    });
                } else if (response.displayInWindow) {
                    //not all account requirements satisfied
                    var link = response.message.link + '';
                    var message = response.message.message;
                    if (Ext.isString(link) && link.length > 0 && link != 'null') {
                        Ext.MessageBox.confirm(me.snippets.store.warning, message, function(btn) {
                            if(btn == 'yes') {
                                window.open(link);
                            } else {
                                return false;
                            }
                        });
                    } else {
                        Ext.MessageBox.alert(me.snippets.store.warning, message, function(btn) {
                            return false;
                        });
                    }
                } else if (response.noDecoder) {
                    //plugin is encoded, but it is no decoder installed
                    Ext.MessageBox.confirm(me.snippets.store.need_ioncube_title, me.snippets.store.need_ioncube , function(btn) {
                        if(btn == 'yes') {
                            window.open('http://www.ioncube.com/loaders.php');
                        } else {
                            return false;
                        }
                    });
                } else if (response.code || response.message) {
                    message = response.message;
                    if (response.source) {
                        message = message + '<br> (Source > ' + response.source + ')';
                    }
                    if (response.url) {
                        message = message + '<br> (URL > ' + response.url + ')';
                    }
                    Shopware.Notification.createGrowlMessage('Plugin-Manager', response.message);
                }
            }
        });
    },

    insertProductLicense: function(license) {
        Ext.Ajax.request({
            url:'{url controller="License" action="save"}',
            method: 'POST',
            params: {
                license: license
            }
        });
    },

    buyLicensePlugin: function(record, detail, win) {
        var me = this;

        if (win) {
            win.setLoading(true);
        }
        Ext.Ajax.request({
            url:'{url controller="Store" action="buyLicensePlugin"}',
            method: 'POST',
            callback: function(request, opts, operation) {
                var response = Ext.decode(operation.responseText);
                if (win) {
                    win.setLoading(false);
                }
                if (response.success === true) {
                    me.refreshPluginList(record, detail, win);
                }
            }
        });
    },

    refreshPluginList: function(record, detail) {
        var me = this;

        Ext.Ajax.request({
            url:'{url controller="PluginManager" action="refreshPluginList"}',
            method: 'POST',
            callback: function(request, opts, operation) {
                if (record !== null) {
                    me.installLicensePlugin(record, detail);
                }
            }
        });
    },

    installLicensePlugin: function(record, detail) {
        var me = this;

        Ext.Ajax.request({
            url:'{url controller="PluginManager" action="installLicensePlugin"}',
            method: 'POST',
            callback: function(request, opts, operation) {
                me.sendBuyRequest(record, detail);
            }
        });
    },

    /**
     * Event listener method which will be trigged when the user selects a new
     * category in the navigation panel.
     *
     * Deselects all other navigation and sets an extra parameter to the plugin store.
     *
     * @public
     * @event changeCategory
     * @param [object] view - Shopware.apps.PluginManager.view.manager.Navigation
     * @param [object] record - Shopware.apps.PluginManager.model.Plugin
     * @param [object] dom - HTML DOM node of the clicked element
     * @return void
     */
    onChangeCategory: function(view, record, dom) {
        var me = this, store = me.subApplication.categoryStore, el = Ext.get(dom), category, communityStore = me.subApplication.communityStore,
            storeView = me.getStoreView(),
            managerSearch = me.getManagerNavigation().searchField,
            storeSearch = me.getStoreNavigation().searchField,
            mainWindow = me.getMainWindow();

        // Set record active
        store.each(function(item) {
            item.set('selected', false);
        });
        record.set('selected', true);

        var store = me.getStoreNavigation().accountCategoryStore;
        store.each(function(item) {
           item.set('selected', false);
        });

        // Terminate the category
        category = el.getAttribute('data-action');
        if(category === 'null') {
            category = null;
            communityStore.pageSize = 6;
            storeView.topSellerView.show();
        } else {
            communityStore.pageSize = 1000;
            storeView.topSellerView.hide();
        }

        var items = mainWindow.storeContainer.items,
            length = items.length;

        mainWindow.storeContainer.getLayout().setActiveItem(0);
        if(length > 1) {
            items.getAt(length-1).destroy();
        }

        communityStore.getProxy().extraParams = { categoryId: category };
        managerSearch.setRawValue('');
        storeSearch.setRawValue('');
        communityStore.filters.clear();
        communityStore.load();
    },

    /**
     * Event listener method which will be triggered when the user changes
     * the value of the search field in the grid (upper left corner).
     *
     * Filters the plugin store with the typed value.
     *
     * @public
     * @event change
     * @param [object] field - Ext.form.field.Text
     * @param [string] value - typed value of the user
     * @return void
     */
    onSearch: function(field, value) {
        var me = this,
            storeView = me.getStoreView(),
            mainWindow = me.getMainWindow(),
            activeTab = mainWindow.tabPanel.getActiveTab(),
            store = me.subApplication.communityStore,
            managerSearch = me.getManagerNavigation().searchField,
            storeSearch = me.getStoreNavigation().searchField;

        if(!activeTab.initialTitle !== 'store') {
            mainWindow.tabPanel.setActiveTab(1);
        }

        if(managerSearch == field) {
            storeSearch.setRawValue(value);
        }
        if(storeSearch == field) {
            managerSearch.setRawValue(value);
        }

        storeView.topSellerView.hide();
        store.pageSize = 1000;
        store.getProxy().extraParams = { categoryId: null };
        store.filters.clear();
        store.filter( { property: 'free', value: value } );
    },

    /**
     * Event listener method which will be triggered when the user clicks
     * on the details button in the store view.
     *
     * This method loads the detail store and opens the detail page of the associated
     * plugin.
     *
     * @event click
     * @param [object] grid - Shopware.apps.PluginManager.view.manager.Grid
     * @param [object] record - Shopware.apps.PluginManager.model.Plugin
     * @param [string] flag - Indicates if the article detail page is from the community store or from a local plugin
     * @return void
     */
    onOpenArticle: function(articleId, categoryRecord, flag) {
        var me = this, products, article;
        articleId = ~~(1* articleId);

        if(categoryRecord == null) {
            var store = me.subApplication.topSellerStore.first();
            products = store.getProductStore;
        } else {
            products = categoryRecord.getProductStore;
        }

        article = products.getById(articleId);
        if(!article) {
            Shopware.Notification.createGrowlMessage(me.snippets.store.title, me.snippets.store.article_not_aviable);
            return false;
        }
        var voteStore = me.getStore('Votes');
        voteStore.getProxy().extraParams.productId = articleId;
        voteStore.load();

        me.getView('detail.Window').create({
            record: article,
            flag: flag,
            voteStore: voteStore
        });
    }
});
//{/block}
