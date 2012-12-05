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
 * @package    Bundle
 * @subpackage Detail
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Controller - Bundle backend module
 */
//{namespace name="backend/bundle/view/main"}
//{block name="backend/bundle/controller/bundle"}
Ext.define('Shopware.apps.Bundle.controller.Bundle', {
    /**
     * The parent class that this class extends.
     * @string
     */
    extend:'Ext.app.Controller',

    refs: [
        { ref: 'bundleListing', selector: 'bundle-list-window bundle-bundle-list' },
        { ref: 'priceListing', selector: 'bundle-list-window bundle-price-list' },
        { ref: 'articleListing', selector: 'bundle-list-window bundle-article-list' },
        { ref: 'groupListing', selector: 'bundle-list-window bundle-group-list' }
    ],

    /**
     * A template method that is called when your application boots.
     * It is called before the Application's launch function is executed
     * so gives a hook point to run any code before your Viewport is created.
     *
     * @return Ext.window.Window
     */
    init:function () {
        var me = this;

        me.mainWindow = me.createMainWindow();

        me.addControls();

        me.callParent(arguments);

        return me.mainWindow;
    },

    /**
     *
     */
    addControls: function() {
        var me = this;

        me.control({
            'bundle-list-window bundle-bundle-list': {
                selectBundle: me.onBundleSelect,
                searchBundle: me.onSearchBundle,
                openArticle: me.onOpenArticle
            },
            'bundle-list-window bundle-article-list': {
                openArticle: me.onOpenArticle
            }
        });
    },

    /**
     * Event listener function of the articles, groups and bundle listing.
     * Fired over the action column of the grids.
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
     * Event listener function of the search field of the bundle listing.
     * The event fired when the user insert a search string in the search field.
     * @param value
     */
    onSearchBundle: function(value) {
        var me = this;
        var listing = me.getBundleListing();
        var store = listing.getStore();

        store.filters.clear();
        store.currentPage = 1;
        if (value.length > 0) {
            store.filter({ property: 'free', value: '%' + value + '%' });
        } else {
            store.load();
        }
    },


    /**
     * Event listener function of the bundle listing grid.
     * Fired when the user clicks on a grid item.
     * Reloads the associated stores and display the association data in the detail panel.
     */
    onBundleSelect: function(record) {
        var me = this;

        if (!(record instanceof Ext.data.Model)) {
            return false;
        }

        var priceListing = me.getPriceListing();
        var articleListing = me.getArticleListing();

        var articleStore = record.getArticles();
        articleStore.groupField = 'groupId';

        priceListing.bundle = record;
        priceListing.reconfigure(record.getPrices(), priceListing.createColumns());

        articleListing.reconfigure(articleStore);
        articleListing.show();
        return true;
    },

    /**
     * Creates and shows the list window of the bundle module.
     * @return Shopware.apps.Bundle.view.list.Window
     */
    createMainWindow: function() {
        var me = this, window;

        window = me.getView('list.Window').create({
            bundleStore: Ext.create('Shopware.apps.Bundle.store.Bundle').load()
        }).show();

        return window;
    }

});
//{/block}


