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
 * @package    SwagAboCommerce
 * @subpackage ExtJs
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */

//{block name="backend/article/view/detail/window" append}
//{namespace name="backend/abo_commerce/article/view/main"}
Ext.define('Shopware.apps.Article.view.detail.AboCommerceWindow', {


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

        result.add(me.createAboCommerceTab());

        return result;
    },

    /**
     * @Override
     * Creates the toolbar with the save and cancel button.
     */
    createToolbar: function() {
        var me = this, result;

        result = me.callParent(arguments);

        result.add(me.createAboCommerceSaveButton());

        return result;
    },

    /**
     * Creates the save button for the abo commerce tab.
     * @return Ext.button.Button
     */
    createAboCommerceSaveButton: function() {
        var me = this;

        me.aboCommerceSaveButton = Ext.create('Ext.button.Button', {
            cls:'primary',
            name: 'save-abo-commerce-button',
            text: '{s name=window/save_abo_commerce_button}Save Abo{/s}',
            hidden: true,
            handler: function() {
                me.fireEvent('saveAboCommerce', me);
            }
        });

        return me.aboCommerceSaveButton;
    },

    /**
     * Creates the tab container for the abo commerce plugin.
     * @return Ext.container.Container
     */
    createAboCommerceTab: function() {
        var me = this;

        var controller = me.subApplication.getController('AboCommerce');

        var aboCommerceDetailStore = Ext.create('Shopware.apps.Article.store.abo_commerce.Detail');
        aboCommerceDetailStore.getProxy().extraParams.articleId = me.article.get('id');

        controller.aboCommerceDetailStore = aboCommerceDetailStore;


        me.aboCommerceTab = Ext.create('Ext.container.Container', {
            title: 'Abo',
            disabled: me.article.get('id') === null,
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            items: me.createAboCommerceComponents(),
            listeners: {
                activate: function() {
                    me.fireEvent('aboCommerceTabActivated', me);
                },
                deactivate: function() {
                    me.fireEvent('aboCommerceTabDeactivated', me);
                }
            }
        });

        return me.aboCommerceTab;
    },


    /**
     * Creates the elements for the detail container.
     * The detail container contains the abo commerce configuration panel and
     * an additional tab panel for the associated data.
     * @return Array
     */
    createAboCommerceComponents: function() {
        var me = this, items = [];

        items.push(me.createAboCommerceConfiguration());
        items.push(me.createAboCommerceTabPanel());

        return items;
    },

    /**
     * Creates the abo commerce configuration panel.
     * The configuration panel contains the data of the s_articles_lives like the abo commerce typ, discount typ, etc.
     * @return Shopware.apps.Article.view.abo_commerce.Configuration
     */
    createAboCommerceConfiguration: function() {
        var me = this;

        return Ext.create('Shopware.apps.Article.view.abo_commerce.Configuration', {
            flex: 0,
            article: me.subApplication.article,
            aboCommerceController: me.subApplication.getController('AboCommerce')
        });
    },

    /**
     * Creates the tab panel for the abo commerce associated data.
     * This tab panel contains the tab for the abo commerce prices, allowed customer groups, etc.
     * @return Ext.tab.Panel
     */
    createAboCommerceTabPanel: function() {
        var me = this;

        return Ext.create('Ext.tab.Panel', {
            plain: true,
            items: [ me.createAboCommerceSettingsTabPanelItem(), me.createAboCommercePriceTabPanelItem() ],
            flex: 1
        });
    },

    /**
     * Creates the tab panel item for the abo commerce prices.
     * @return Shopware.apps.Article.view.abo_commerce.tabs.Price
     */
    createAboCommercePriceTabPanelItem: function() {
        var me = this;

        return Ext.create('Shopware.apps.Article.view.abo_commerce.tabs.Price', {
            customerGroupStore: me.customerGroupStore,
            article: me.subApplication.article,
            aboCommerceController: me.subApplication.getController('AboCommerce')
        });
    },


    /**
     * Creates the tab panel item for the abo commerce settings.
     * @return Shopware.apps.Article.view.abo_commerce.tabs.Settings
     */
    createAboCommerceSettingsTabPanelItem: function() {
        var me = this;

        return Ext.create('Shopware.apps.Article.view.abo_commerce.tabs.Settings', {
            customerGroupStore: me.customerGroupStore,
            article: me.subApplication.article,
            aboCommerceController: me.subApplication.getController('AboCommerce')
        });
    }
});
//{/block}

