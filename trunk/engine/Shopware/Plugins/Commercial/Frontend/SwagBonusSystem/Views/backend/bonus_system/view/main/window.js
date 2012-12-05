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
 * @package    BonusSystem
 * @subpackage Main
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     shopware AG
 */

//{namespace name=backend/bonus_system/view/main}
//{block name="backend/bonus_system/view/window"}
Ext.define('Shopware.apps.BonusSystem.view.main.Window', {
    extend: 'Enlight.app.Window',
    alias : 'widget.bonusSystem-main-window',
    layout: 'fit',
    width: 850,
    height: 700,

    /**
     * Contains all snippets for the component
     * @object
     */
    snippets: {
        title: '{s name=window/title}Bonus System{/s}',
        chooseShop: '{s name=window/chooseShop}Choose shop{/s}',
        tab: {
            settings: '{s name=window/tab/settings}Settings{/s}',
            users: '{s name=window/tab/users}Users{/s}',
            articles: '{s name=window/tab/articles}Articles{/s}',
            orders: '{s name=window/tab/orders}Orders{/s}'
        }
    },

    /**
     * Sets up the ui component
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.title = me.snippets.title;

        me.registerEvents();

        var tabPanel = Ext.create('Ext.tab.Panel', {
            plain: false,
            items: [
                {
                    title: me.snippets.tab.settings,
                    xtype: 'bonusSystem-main-settings'
                },
                {
                    title: me.snippets.tab.users,
                    xtype: 'bonusSystem-main-users',
                    store: me.userStore
                },
                {
                    title: me.snippets.tab.articles,
                    xtype: 'bonusSystem-main-articles',
                    store: me.articleStore
                },
                {
                    title: me.snippets.tab.orders,
                    xtype: 'bonusSystem-main-orders',
                    store: me.orderStore
                }
            ]
        });

        me.dockedItems = [ me.getToolbar() ];
        me.items = [ tabPanel ];

        me.callParent(arguments);
    },

    /**
     * Defines additional events which will be
     * fired from the component
     *
     * @return void
     */
    registerEvents: function () {
        var me = this;

        me.addEvents(
            /**
             * @event changeShop
             * @param [int] shopId
             */
            'changeShop'
        );
    },




    /**
     * Creates the grid toolbar with the add and delete button
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar: function() {
        var me = this;

        var shopStore = Ext.create('Shopware.apps.Base.store.Shop');

        shopStore.filters.clear();
        shopStore.load({
            callback: function(records) {
                shopCombo.setValue(records[0].get('id'));
            }
        });

        var shopCombo = Ext.create('Ext.form.field.ComboBox', {
            fieldLabel: me.snippets.chooseShop,
            store: shopStore,
            labelWidth: 115,
            margin: '3px 6px 3px 0',
            queryMode: 'local',
            valueField: 'id',
            editable: false,
            displayField: 'name',
            listeners: {
                'change': function(view, newValue) {
                    if (this.store.getAt('0')) {
                        me.fireEvent('changeShop', newValue);
                    }
                }
            }
        });

        var toolbar = Ext.create('Ext.toolbar.Toolbar', {
            dock: 'top',
            ui : 'shopware-ui',
            items: [
                '->',
                shopCombo
            ]
        });

        return toolbar;
    }
});
//{/block}
