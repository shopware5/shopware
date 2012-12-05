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

//{block name="backend/article/view/detail/window" append}
//{namespace name="backend/live_shopping/article/view/main"}
Ext.define('Shopware.apps.Article.view.detail.LiveShoppingWindow', {

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

        result.add(me.createLiveShoppingTab());

        return result;
    },

    /**
     * @Override
     * Creates the toolbar with the save and cancel button.
     */
    createToolbar: function() {
        var me = this, result;

        result = me.callParent(arguments);

        result.add(me.createLiveShoppingSaveButton());

        return result;
    },

    /**
     * Creates the save button for the live shopping tab.
     * @return Ext.button.Button
     */
    createLiveShoppingSaveButton: function() {
        var me = this;

        me.liveShoppingSaveButton = Ext.create('Ext.button.Button', {
            cls:'primary',
            name: 'save-live-shopping-button',
            text: '{s name=window/save_live_shopping_button}Save live shopping{/s}',
            hidden: true,
            handler: function() {
                me.fireEvent('saveLiveShopping');
            }
        });
        return me.liveShoppingSaveButton;
    },

    /**
     * Creates the tab container for the live shopping plugin.
     * @return Ext.container.Container
     */
    createLiveShoppingTab: function() {
        var me = this;

        var controller = me.subApplication.getController('LiveShopping');

        me.liveShoppingListStore = Ext.create('Shopware.apps.Article.store.live_shopping.List');
        me.liveShoppingListStore.getProxy().extraParams.articleId = me.article.get('id');

        me.liveShoppingTab = Ext.create('Ext.container.Container', {
            title: '{s name=window/live_shopping_tab}Live shopping{/s}',
            disabled: me.article.get('id') === null,
            layout: {
                type: 'hbox',
                align: 'stretch'
            },
            items: me.createLiveShoppingComponents(),
            listeners: {
                activate: function() {
                    me.fireEvent('liveShoppingTabActivated', me, me.liveShoppingListStore);
                },
                deactivate: function() {
                    me.fireEvent('liveShoppingTabDeactivated', me);
                }
            }
        });

        return me.liveShoppingTab;
    },

    /**
     * Creates all components for the live shopping tab which displayed in the article detail window.
     * @return Array
     */
    createLiveShoppingComponents: function() {
        var me = this, items = [];

        items.push(me.createLiveShoppingList());
        items.push(me.createLiveShoppingContainer());

        return items;
    },

    /**
     * Creates the listing component which displays all live shoppings of the current article
     * @return Shopware.apps.Article.view.live_shopping.List
     */
    createLiveShoppingList: function() {
        var me = this;

        return Ext.create('Shopware.apps.Article.view.live_shopping.List', {
            width: 240,
            store: me.liveShoppingListStore
        });
    },

    /**
     * Creates the live shopping detail container.
     * The detail container contains the live shopping configuration panel and
     * an additional tab panel for the associated data.
     * @return Ext.container.Container
     */
    createLiveShoppingContainer: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            name: 'live-shopping-detail-container',
            flex: 1,
            items: me.createLiveShoppingDetailContainerItems()
        });
    },

    /**
     * Creates the elements for the detail container.
     * The detail container contains the live shopping configuration panel and
     * an additional tab panel for the associated data.
     * @return Array
     */
    createLiveShoppingDetailContainerItems: function() {
        var me = this, items = [];

        items.push(me.createLiveShoppingConfiguration());
        items.push(me.createLiveShoppingTabPanel());

        return items;
    },

    /**
     * Creates the live shopping configuration panel.
     * The configuration panel contains the data of the s_articles_lives like the live shopping typ, discount typ, etc.
     * @return Shopware.apps.Article.view.live_shopping.Configuration
     */
    createLiveShoppingConfiguration: function() {
        var me = this;

        return Ext.create('Shopware.apps.Article.view.live_shopping.Configuration', {
            height: 240,
            minHeight: 240
        });
    },

    /**
     * Creates the tab panel for the live shopping associated data.
     * This tab panel contains the tab for the live shopping prices, allowed customer groups, etc.
     * @return Ext.tab.Panel
     */
    createLiveShoppingTabPanel: function() {
        var me = this;

        return Ext.create('Ext.tab.Panel', {
            plain: true,
            style: 'background: none repeat scroll 0 0 #F0F2F4;',
            name: 'live-shopping-tab-panel',
            items: me.createLiveShoppingTabPanelItems(),
            flex: 1,
            minHeight: 240
        });
    },

    /**
     * Creates all tabs for the live shopping tab panel.
     * @return Array
     */
    createLiveShoppingTabPanelItems: function() {
        var me = this, items = [];

        items.push(me.createLiveShoppingPriceTabPanelItem());
        items.push(me.createLiveShoppingCustomerGroupTabPanelItem());
        items.push(me.createLiveShoppingLimitedVariantTabPanelItem());
        items.push(me.createLiveShoppingShopTabPanelItem());

        return items;
    },


    /**
     * Creates the tab panel item for the live shopping prices.
     * @return Shopware.apps.Article.view.live_shopping.tabs.Price
     */
    createLiveShoppingPriceTabPanelItem: function() {
        var me = this;

        return Ext.create('Shopware.apps.Article.view.live_shopping.tabs.Price', {
            customerGroupStore: me.customerGroupStore,
            article: me.subApplication.article,
            liveShoppingController: me.subApplication.getController('LiveShopping')
        });
    },

    /**
     * Creates the tab panel item for the live shopping customer group.
     * @return Shopware.apps.Article.view.live_shopping.tabs.CustomerGroup
     */
    createLiveShoppingCustomerGroupTabPanelItem: function() {
        var me = this;

        return Ext.create('Shopware.apps.Article.view.live_shopping.tabs.CustomerGroup', {
            customerGroupStore: me.customerGroupStore,
            article: me.subApplication.article
        });
    },

    /**
     * Creates the tab panel item for the live shopping stints.
     * @return Shopware.apps.Article.view.live_shopping.tabs.LimitedVariant
     */
    createLiveShoppingLimitedVariantTabPanelItem: function() {
        var me = this;

        return Ext.create('Shopware.apps.Article.view.live_shopping.tabs.LimitedVariant', {
            article: me.subApplication.article
        });
    },

    /**
     * Creates the tab panel item for the live shopping stints.
     * @return Shopware.apps.Article.view.live_shopping.tabs.LimitedVariant
     */
    createLiveShoppingShopTabPanelItem: function() {
        var me = this;

        return Ext.create('Shopware.apps.Article.view.live_shopping.tabs.Shop', {
            article: me.subApplication.article,
            shopStore: me.shopStore
        });
    }
});
//{/block}

