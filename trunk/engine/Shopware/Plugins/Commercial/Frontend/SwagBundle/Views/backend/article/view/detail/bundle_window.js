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

//{block name="backend/article/view/detail/window" append}
//{namespace name="backend/bundle/article/view/main"}
Ext.define('Shopware.apps.Article.view.detail.BundleWindow', {

    override: 'Shopware.apps.Article.view.detail.Window',

    /**
     * @Override
     * Creates the main tab panel which displays the different tabs for the article sections.
     * To extend the tab panel this function can be override.
     *
     * @return Ext.tab.Panel
     */
    createMainTabPanel: function() {
        var me = this, result;

        result = me.callParent(arguments);

        result.add(me.createBundleTab());

        return result;
    },

    /**
     * @Override
     * Creates the toolbar with the save and cancel button.
     */
    createToolbar: function() {
        var me = this, result;

        result = me.callParent(arguments);

        result.add(me.createBundleSaveButton());

        return result;
    },

    /**
     * Creates the save button for the bundle tab.
     * @return Ext.button.Button
     */
    createBundleSaveButton: function() {
        var me = this;

        me.bundleSaveButton = Ext.create('Ext.button.Button', {
            cls:'primary',
            name: 'save-bundle-button',
            text: '{s name=window/save_bundle_button}Save bundle{/s}',
            hidden: true,
            handler: function() {
                me.fireEvent('saveBundle', me);
            }
        });
        return me.bundleSaveButton;
    },

    /**
     * Creates the tab container for the bundle plugin.
     * @return Ext.container.Container
     */
    createBundleTab: function() {
        var me = this;

        var controller = me.subApplication.getController('Bundle');

        me.bundleListStore = Ext.create('Shopware.apps.Article.store.bundle.List');
        me.bundleListStore.getProxy().extraParams.articleId = me.article.get('id');

        me.bundleTab = Ext.create('Ext.container.Container', {
            title: 'Bundle',
            disabled: me.article.get('id') === null,
            layout: {
                type: 'hbox',
                align: 'stretch'
            },
            items: me.createBundleComponents(),
            listeners: {
                activate: function() {
                    me.fireEvent('bundleTabActivated', me, me.bundleListStore);
                },
                deactivate: function() {
                    me.fireEvent('bundleTabDeactivated', me);
                }
            }
        });

        return me.bundleTab;
    },

    /**
     * Creates all components for the bundle tab which displayed in the article detail window.
     * @return Array
     */
    createBundleComponents: function() {
        var me = this, items = [];

        items.push(me.createBundleList());
        items.push(me.createDetailContainer());

        return items;
    },

    /**
     * Creates the listing component which displays all bundles of the current article
     * @return Shopware.apps.Article.view.bundle.Tree
     */
    createBundleList: function() {
        var me = this;

        return Ext.create('Shopware.apps.Article.view.bundle.List', {
            width: 240,
            store: me.bundleListStore
        });
    },

    /**
     * Creates the bundle detail container.
     * The detail container contains the bundle configuration panel and
     * an additional tab panel for the associated data.
     * @return Ext.container.Container
     */
    createDetailContainer: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            disabled: true,
            cls: 'shopware-form',
            name: 'bundle-detail-container',
            flex: 1,
            items: me.createDetailContainerItems()
        });
    },

    /**
     * Creates the elements for the detail container.
     * The detail container contains the bundle configuration panel and
     * an additional tab panel for the associated data.
     * @return Array
     */
    createDetailContainerItems: function() {
        var me = this, items = [];

        items.push(me.createBundleConfiguration());
        items.push(me.createBundleTabPanel());

        return items;
    },

    /**
     * Creates the bundle configuration panel.
     * The configuration panel contains the data of the s_articles_bundles like the bundle typ, discount typ, etc.
     * @return Shopware.apps.Article.view.bundle.Configuration
     */
    createBundleConfiguration: function() {
        var me = this;

        return Ext.create('Shopware.apps.Article.view.bundle.Configuration', {
            height: 240,
            minHeight: 240,
            taxStore: me.taxStore
        });
    },

    /**
     * Creates the tab panel for the bundle associated data.
     * This tab panel contains the tab for the bundle articles, prices, allowed customer groups, etc.
     * @return Ext.tab.Panel
     */
    createBundleTabPanel: function() {
        var me = this;

        return Ext.create('Ext.tab.Panel', {
            plain: true,
            style: 'background: none repeat scroll 0 0 #F0F2F4;',
            items: me.createBundleTabPanelItems(),
            flex: 1,
            minHeight: 240
        });
    },

    /**
     * Creates all tabs for the bundle tab panel.
     * @return Array
     */
    createBundleTabPanelItems: function() {
        var me = this, items = [];

        items.push(me.createArticleTabPanelItem());
        items.push(me.createPriceTabPanelItem());
        items.push(me.createCustomerGroupTabPanelItem());
        items.push(me.createLimitedDetailTabPanelItem());

        return items;
    },

    /**
     * Creates the tab panel item for the bundle article listing.
     * @return Shopware.apps.Article.view.bundle.tabs.Article
     */
    createArticleTabPanelItem: function() {
        var me = this;

        return Ext.create('Shopware.apps.Article.view.bundle.tabs.Article', {
            customerGroupStore: me.customerGroupStore,
            article: me.subApplication.article,
            bundleController: me.subApplication.getController('Bundle')
        });
    },

    /**
     * Creates the tab panel item for the bundle prices.
     * @return Shopware.apps.Article.view.bundle.tabs.Price
     */
    createPriceTabPanelItem: function() {
        var me = this;

        return Ext.create('Shopware.apps.Article.view.bundle.tabs.Price', {
            customerGroupStore: me.customerGroupStore,
            article: me.subApplication.article,
            bundleController: me.subApplication.getController('Bundle')
        });
    },

    /**
     * Creates the tab panel item for the bundle customer group.
     * @return Shopware.apps.Article.view.bundle.tabs.CustomerGroup
     */
    createCustomerGroupTabPanelItem: function() {
        var me = this;

        return Ext.create('Shopware.apps.Article.view.bundle.tabs.CustomerGroup', {
            customerGroupStore: me.customerGroupStore,
            article: me.subApplication.article
        });
    },

    /**
     * Creates the tab panel item for the bundle stints.
     * @return Shopware.apps.Article.view.bundle.tabs.LimitedDetail
     */
    createLimitedDetailTabPanelItem: function() {
        var me = this;

        return Ext.create('Shopware.apps.Article.view.bundle.tabs.LimitedDetail', {
            article: me.subApplication.article
        });
    }

});
//{/block}

