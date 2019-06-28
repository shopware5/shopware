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
 * @package    PluginManager
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

// {block name="backend/plugin_manager/controller/navigation"}
Ext.define('Shopware.apps.PluginManager.controller.Navigation', {
    extend: 'Ext.app.Controller',

    refs: [
        { ref: 'categoryTree', selector: 'plugin-manager-listing-window container[name=category-tree]' },
        { ref: 'navigation', selector: 'plugin-manager-listing-window plugin-category-navigation' },
        { ref: 'listingWindow', selector: 'plugin-manager-listing-window' },
        { ref: 'storeListing', selector: 'plugin-manager-listing-window plugin-manager-listing[name=community-store-listing]' },
        { ref: 'cardContainer', selector: 'plugin-manager-listing-window container[name=card-container]' },
        { ref: 'updatePage', selector: 'plugin-manager-update-page' },
        { ref: 'licencePage', selector: 'plugin-manager-licence-page' },
        { ref: 'storePage', selector: 'plugin-manager-store-listing-page' }
    ],

    cards: {
        homePage: 0,
        localPluginPage: 1,
        pluginUpdatesPage: 2,
        listingPage: 3,
        accountPage: 4,
        licencePage: 5,
        premiumPluginsPage: 6,
        expiredPluginsPage: 7,
        connectIntroductionPage: 8,
        importExportTeaserPage: 9
    },

    animationSpeed: 150,
    mixins: {
        events: 'Shopware.apps.PluginManager.view.PluginHelper'
    },

    init: function () {
        var me = this;

        me.control({
            'plugin-manager-listing-window plugin-category-navigation': {
                'display-home': me.displayHomePage,
                'display-installed': me.displayLocalPluginPage,
                'display-updates': me.displayPluginUpdatesPage,
                'display-account': me.displayAccountPage,
                'display-licences': me.displayLicencePage,
                'search-plugin': me.searchPlugins
            },
            'plugin-manager-home-page': {
                'display-newcomer': me.displayNewcomer
            },
            'plugin-manager-category-tree': {
                'select-category': me.selectCategory
            },
            'plugin-manager-store-listing-page': {
                'filter-store-listing': me.filterStoreListing
            },
            'plugin-manager-local-plugin-listing': {
                'open-plugin-upload': me.displayUploadWindow
            }
        });

        Shopware.app.Application.on({
            'display-installed-plugins': me.displayLocalPluginPage,
            'display-plugin': me.displayDetailPage,
            'display-plugin-by-name': me.displayDetailPageByName,
            'plugin-manager-display-updates': me.displayPluginUpdatesPage,
            'display-premium-plugins': me.displayPremiumPluginsPage,
            'display-expired-plugins': me.displayExpiredPluginsPage,
            'display-connect-introduction': me.displayConnectIntroductionPage,
            'display-importexport-teaser': me.displayImportExportTeaserPage,
            'load-store-listing': me.loadListingWithParams,
            scope: me
        });

        me.callParent(arguments);
    },

    displayUploadWindow: function() {
        var me = this;

        me.getView('account.Upload').create().show();
    },

    filterStoreListing: function(callback) {
        var me = this,
            storePage = me.getStorePage(),
            storeListing = me.getStoreListing();

        me.addPriceFilter();

        me.addCertifiedFilter();

        me.addSorting();

        storeListing.setLoading(true);
        storeListing.resetListing();

        storeListing.store.load({
            callback: function() {
                storeListing.setLoading(false);
                if (Ext.isFunction(callback)) {
                    callback();
                }
            }
        });
    },

    addPriceFilter: function() {
        var me = this,
            storePage = me.getStorePage(),
            storeListing = me.getStoreListing();

        me.removeFilterByName('price');

        storeListing.store.filter({
            property: 'price',
            value: storePage.priceFilter.getValue()
        });
    },

    addCertifiedFilter: function() {
        var me = this,
            storePage = me.getStorePage(),
            storeListing = me.getStoreListing();

        me.removeFilterByName('certified');

        if (storePage.certifiedField.getValue()) {
            storeListing.store.filter({
                property: 'certified',
                value: true
            });
        }
    },

    addSorting: function() {
        var me = this,
            storePage = me.getStorePage(),
            storeListing = me.getStoreListing();

        storeListing.store.sort({
            property: storePage.sortField.getValue()
        });
    },

    removeFilterByName: function(name) {
        var me = this,
            store = me.getStoreListing().store;

        Ext.each(store.filters.items, function(filter, index) {
            if (Ext.isObject(filter) &&
                filter.hasOwnProperty('property') &&
                filter.property == name) {
                store.filters.removeAt(index);
            }
        });
    },

    searchPlugins: function(term) {
        var me = this,
            navigation = me.getNavigation(),
            storeListing = me.getStoreListing();

        if (!term || term.length === 0) {
            return;
        }

        storeListing.setLoading(true);
        storeListing.resetListing();
        storeListing.store.clearFilter();

        storeListing.store.filter({
            property: 'search',
            value: term
        });

        me.addPriceFilter();
        me.addCertifiedFilter();
        me.addSorting();

        storeListing.store.getProxy().extraParams.categoryId = null;

        storeListing.store.load({
            callback: function() {
                storeListing.setLoading(false);
            }
        });

        me.switchView(me.cards.listingPage);
        me.setActiveNavigationLink(navigation.localHomeLink);
    },

    displayNewcomer: function() {
        var me = this,
            tree = me.getCategoryTree();

        var category = tree.store.getById(-2);
        me.selectCategory(category);
        tree.selectActiveTreeNode(category);
    },

    displayHomePage: function () {
        var me = this,
            navigation = me.getNavigation();

        me.switchView(me.cards.homePage);
        me.setActiveNavigationLink(navigation.localHomeLink);
    },

    displayLocalPluginPage: function () {
        var me = this,
            navigation = me.getNavigation();

        me.switchView(me.cards.localPluginPage);
        me.setActiveNavigationLink(navigation.localInstalledLink);
    },

    displayPremiumPluginsPage: function () {
        var me = this,
            navigation = me.getNavigation();

        Shopware.app.Application.fireEvent('enable-premium-plugins-mode');

        me.switchView(me.cards.premiumPluginsPage);
    },

    displayExpiredPluginsPage: function() {
        var me = this;

        Shopware.app.Application.fireEvent('enable-expired-plugins-mode');

        me.switchView(me.cards.expiredPluginsPage);
    },

    displayConnectIntroductionPage: function() {
        var me = this;

        Shopware.app.Application.fireEvent('enable-connect-introduction-mode');

        me.switchView(me.cards.connectIntroductionPage);
    },

    displayImportExportTeaserPage: function() {
        var me = this;

        Shopware.app.Application.fireEvent('enable-importexport-teaser-mode');

        me.switchView(me.cards.importExportTeaserPage);
    },

    displayPluginUpdatesPage: function () {
        var me = this,
            updatePage = me.getUpdatePage(),
            navigation = me.getNavigation();

        me.switchView(me.cards.pluginUpdatesPage);
        me.setActiveNavigationLink(navigation.localUpdatesLink);
    },

    displayListingPage: function () {
        var me = this;

        me.switchView(me.cards.listingPage);
    },

    displayDetailPage: function (plugin, callback) {
        var me = this;

        var detailWindow = me.getView('detail.Window').create().show();
        detailWindow.loadRecord(plugin);

        if (Ext.isFunction(callback)) {
            callback(detailWindow);
        }
    },

    displayDetailPageByName: function (technicalName) {
        var me = this;

        me.communityStore = Ext.create('Shopware.apps.PluginManager.store.StorePlugin');
        me.communityStore.filter({
            property: 'search',
            value: technicalName
        });

        me.communityStore.load({
            callback: function(items) {
                var detailWindow = me.getView('detail.Window').create().show();
                detailWindow.loadRecord(items[0]);
            }
        });
    },

    displayAccountPage: function () {
        var me = this,
            navigation = me.getNavigation();

        me.switchView(me.cards.accountPage);
        me.setActiveNavigationLink(navigation.accountLink);
    },

    displayLicencePage: function () {
        var me = this,
            page = me.getLicencePage(),
            navigation = me.getNavigation();

        Shopware.app.Application.fireEvent('check-store-login', function() {
            page.getStore().getProxy().on('exception', function (proxy, response) {
                var responseText = Ext.decode(response.responseText);
                me.displayErrorMessage(responseText);
            }, me, { single: true });

            page.getStore().load();

            me.switchView(me.cards.licencePage);
            me.setActiveNavigationLink(navigation.accountLicenceLink);
        });
    },

    setActiveNavigationLink: function(item) {
        var me = this;

        me.removeNavigationSelection();
        me.removeTreeSelection();
        item.addCls('active');
    },

    switchView: function(nextItem, callback) {
        var me = this,
            listingWindow = me.getListingWindow(),
            layout = me.getCardContainer().getLayout();

        var activePage = layout.getActiveItem();
        var nextPage = listingWindow.cards[nextItem];

        if (activePage.cardIndex == nextItem) {
            return;
        }

        if (Ext.isFunction(nextPage.hideContent)) {
            nextPage.hideContent();
        }
        if (Ext.isFunction(activePage.hideContent)) {
            activePage.hideContent();
        }

        activePage.hide();

        if (Ext.isFunction(callback)) {
            callback();
        }
        if (Ext.isFunction(nextPage.displayContent)) {
            nextPage.displayContent();
        }

        layout.setActiveItem(nextPage);
    },

    removeTreeSelection: function() {
        var me = this,
            tree = me.getCategoryTree();

        tree.removeSelection();
    },

    selectCategory: function(category) {
        var me = this;

        me.displayListingPage();
        me.removeNavigationSelection();
        me.loadStoreListing(category);
    },

    loadListingWithParams: function(filters) {
        var me = this,
            navigation = me.getCategoryTree(),
            storeListing = me.getStoreListing();

        me.displayListingPage();
        me.removeNavigationSelection();

        storeListing.store.clearFilter();

        navigation.disable();
        storeListing.resetListing();
        storeListing.store.filter(filters);

        me.filterStoreListing(function() {
            navigation.enable();
        });
    },

    loadStoreListing: function(category) {
        var me = this,
            navigation = me.getCategoryTree(),
            storeListing = me.getStoreListing();

        storeListing.store.clearFilter();

        navigation.disable();
        storeListing.resetListing();
        storeListing.store.getProxy().extraParams.categoryId = category.get('id');

        me.filterStoreListing(function() {
            navigation.enable();
        });

        storeListing.category = category;
    },

    removeNavigationSelection: function() {
        var me = this,
            navigation = me.getNavigation(),
            tree = me.getCategoryTree();

        navigation.localUpdatesLink.removeCls('active');
        navigation.localHomeLink.removeCls('active');
        navigation.localInstalledLink.removeCls('active');
        navigation.accountLink.removeCls('active');
        navigation.accountLicenceLink.removeCls('active');
    }
});
// {/block}
