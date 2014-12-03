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
        licencePage: 5
    },

    animationSpeed: 150,

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
            'display-plugin': me.displayDetailPage,
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

        var price = storePage.priceFilter.getValue();
        var sort = storePage.sortField.getValue();
        var certified = storePage.certifiedField.getValue();

        storeListing.store.clearFilter();

        storeListing.store.filter({
            property: 'price',
            value: price
        });

        if (certified) {
            storeListing.store.filter({
                property: 'certified',
                value: true
            });
        }

        storeListing.store.sort({
            property: sort
        });

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

    searchPlugins: function(term) {
        var me = this,
            navigation = me.getNavigation(),
            storeListing = me.getStoreListing();

        if (!term || term.length < 0) {
            return;
        }

        storeListing.setLoading(true);
        storeListing.resetListing();
        storeListing.store.clearFilter();

        storeListing.store.filter({
            property: 'search',
            value: term
        });
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

    displayPluginUpdatesPage: function () {
        var me = this,
            navigation = me.getNavigation();

        me.switchView(me.cards.pluginUpdatesPage);
        me.setActiveNavigationLink(navigation.localUpdatesLink);
    },

    displayListingPage: function () {
        var me = this;

        me.switchView(me.cards.listingPage);
    },

    displayDetailPage: function (plugin) {
        var me = this;

        var detailWindow = me.getView('detail.Window').create().show();
        detailWindow.loadRecord(plugin);
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

        activePage.getEl().slideOut('l', {
            duration: me.animationSpeed
        });

        nextPage.getEl().slideIn('r', {
            duration: me.animationSpeed,
            callback: function () {

                if (Ext.isFunction(nextPage.displayContent)) {
                    nextPage.displayContent();
                }

                Ext.Function.defer(function () {
                    layout.setActiveItem(nextPage);
                }, 500);

                if (Ext.isFunction(callback)) {
                    callback();
                }
            }
        });
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

    loadStoreListing: function(category) {
        var me = this,
            navigation = me.getCategoryTree(),
            storeListing = me.getStoreListing();

        storeListing.store.clearFilter();

        if (!storeListing.category || storeListing.category.get('id') !== category.get('id')) {

            navigation.disable();
            storeListing.resetListing();
            storeListing.store.getProxy().extraParams.categoryId = category.get('id');

            me.filterStoreListing(function() {
                navigation.enable();
            });
        }

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