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
 * @package    CanceledOrder
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/canceled_order/view/main}

/**
 * Shopware UI - Canceled baskets
 * main view for canceled baskets, contains tabs 'overview' and 'articles'
 */
//{block name="backend/canceled_order/view/tabs/baskets/main"}
Ext.define('Shopware.apps.CanceledOrder.view.tabs.baskets.Main', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.canceled-order-tabs-baskets-main',
    title: '{s name=canceledBaskets}Baskets{/s}',
    defaults: {
        bodyBorder: 0
    },


    layout: 'fit',

    snippets: {
        date: {
            from: '{s name=date/from}From{/s}',
            to: '{s name=date/to}To{/s}'
        }
    },

    /**
     * Initializes the component, adds Event, creates Tab and adds toolbar
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.items = me.createTab();
        me.dockedItems = [
            {
                xtype: 'canceled-order-toolbar',
                dock: 'top'
            }
        ];

        me.addEvents('tabChange');

        me.callParent(arguments);
    },

    /**
     * Creates the tab panel for the basket tab
     *
     * @return Ext.tab.Panel
     */
    createTab: function() {
        var me = this;

        me.tabPanel = Ext.create('Ext.tab.Panel', {
            items: me.getTabs(),
            alias: 'widget.canceled-order-tabs-baskets-main-tab',
            border: false,
            listeners: {
                tabChange: function(panel, newCard, oldCard) {
                    me.fireEvent('tabChange', panel, newCard, oldCard)
                }
            }
        });

        return me.tabPanel;
    },

    /**
     * Creates sub-tabs for the basket tab
     * @return Array
     */
    getTabs: function() {
        var me = this;

        return [{
            xtype: 'canceled-order-tabs-baskets-overview',
            store: me.overviewStore,
            internalTitle: 'overview'
        },
        {
            xtype: 'canceled-order-tabs-baskets-articles',
            store: me.articlesStore,
            internalTitle: 'articles'
        },
        {
            xtype: 'canceled-order-tabs-baskets-viewports',
            store: me.viewportStore,
            internalTitle: 'viewports'
        }
        ];

    },


    /**
     * Create the basket overview
     * @return Object
     */
    getGrid: function() {
        var me = this;

        return {
            xtype:'canceled-order-tabs-baskets-overview',
            store: me.store
        };
    }

});
//{/block}
