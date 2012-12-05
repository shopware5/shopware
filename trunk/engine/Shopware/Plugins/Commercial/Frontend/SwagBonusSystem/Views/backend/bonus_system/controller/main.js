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
//{block name="backend/bonus_system/controller/main"}
Ext.define('Shopware.apps.BonusSystem.controller.Main', {

    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend: 'Ext.app.Controller',

    /**
     * Class property which holds the main application if it is created
     *
     * @default null
     * @object
     */
    mainWindow: null,

    /**
     * Set component references for easy access
     * @array
     */
    refs:[
        { ref:'settingsForm', selector: 'bonusSystem-main-settings' },
        { ref:'articleGrid', selector: 'bonusSystem-main-articles' }
    ],

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * @return void
     */
    init: function() {
        var me = this;

        me.control({
            'bonusSystem-main-window': {
                changeShop: me.onChangeShop
            },

            'bonusSystem-main-settings': {
                saveSetting: me.onSaveSetting
            },

            'bonusSystem-main-users': {
                saveUser:     me.onSaveUser,
                searchUser:   me.onSearchUser,
                openCustomer: me.onOpenCustomer
            },

            'bonusSystem-main-orders': {
                saveOrder:     me.onSaveOrder,
                searchOrder:   me.onSearchOrder,
                approveOrders: me.onApproveOrders,
                openCustomer:  me.onOpenCustomer,
                openOrder:     me.onOpenOrder
            },

            'bonusSystem-main-articles': {
                saveArticle:    me.onSaveArticle,
                addArticle:     me.onAddArticle,
                deleteArticles: me.onDeleteArticles,
                openArticle:    me.onOpenArticle
            }
        });

        me.mainWindow = me.getView('main.Window').create({
            settingStore: me.getStore('Setting'),
            userStore:    me.getStore('User'),
            articleStore: me.getStore('Article'),
            orderStore:   me.getStore('Order')
        }).show();

        me.callParent(arguments);
    },

    /**
     * Callback function for openOrder. Will open the order in subApplication
     *
     * @param [Ext.data.Model]
     */
    onOpenOrder: function(record) {
        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.Order',
            params: {
                orderId: record.get('orderID')
            }
        });
    },

    /**
     * Callback function for openCustomer. Will open the customer in subApplication
     *
     * @param [Ext.data.Model]
     */
    onOpenCustomer: function(record) {
        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.Customer',
            action: 'detail',
            params: {
                customerId: record.get('userID')
            }
        });
    },

    /**
     * Callback function for openArticle. Will open the Article subApplication.
     *
     * @param [Ext.data.Model]
     */
    onOpenArticle: function(record) {
        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.Article',
            action: 'detail',
            params: {
                articleId: record.get('articleID')
            }
        });
    },

    /**
     Event that will be fired when the user clicks the delete button in the toolbar
     *
     * @event deleteArticles
     * @param [array] records - The selected records
     */
    onDeleteArticles: function(records) {
        var me = this,
            store = me.getStore('Article');

        Ext.each(records, function(item) {
            store.remove(item);
        });

        store.sync({
            callback: function() {
                store.load();
            }
        });
    },

    /**
     * Event that will be fired when the user clicks the approve button in the toolbar
     *
     * @event approveOrders
     * @param [array] records - The selected records
     */
    onApproveOrders: function(records) {
        var me = this;

        Ext.each(records, function(item) {
            item.set('approval', true);
        });

        me.getStore('Order').sync({
            callback: function() {
                me.getStore('Order').load();
            }
        });
    },

    /**
     * @param [int] shopId
     */
    onChangeShop: function(shopId) {
        var me = this,
            settingsForm = me.getSettingsForm();

        me.getStore('Setting').getProxy().setExtraParam('shopID', shopId);
        me.getStore('User').getProxy().setExtraParam('shopID', shopId)
        me.getStore('Article').getProxy().setExtraParam('shopID', shopId)
        me.getStore('Order').getProxy().setExtraParam('shopID', shopId)

        if (settingsForm.isVisible()) {
            settingsForm.setLoading(true);
        }

        me.getStore('Setting').load({
            callback: function(records, operation, success) {
                settingsForm.setLoading(false);
                if (success) {
                    var record = records[0];
                    settingsForm.loadRecord(record);
                }
            }
        });
        me.getStore('User').load();
        me.getStore('Article').load();
        me.getStore('Order').load();

    },

    /**
     * Fired when the user select an article in the suggest search.
     *
     * @param [Ext.data.Model] recod -  The selected record
     */
    onAddArticle: function(record) {
        var me = this,
            articleGrid = me.getArticleGrid();

        var newRecord = Ext.create('Shopware.apps.BonusSystem.model.Article', {
            id: '',
            articleID: record.get('id'),
            ordernumber: record.get('number'),
            articleName: record.get('name'),
            required_points: '',
            position: '0'
        });

        var editor = articleGrid.getPlugin('myeditor');

        me.getStore('Article').add(newRecord);
        editor.startEdit(newRecord, 2);
    },

    /**
     * @param [string] value
     */
    onSearchOrder: function(value) {
        var me = this,
            store = me.getStore('Order');

        value = Ext.String.trim(value);
        store.filters.clear();
        store.currentPage = 1;
        if (value.length > 0) {
            store.filter({ property: 'filter', value: value });
        } else {
            store.load();
        }
    },

    /**
     * @param [string] value
     */
    onSearchUser: function(value) {
        var me = this,
            store = me.getStore('User');

        value = Ext.String.trim(value);
        store.filters.clear();
        store.currentPage = 1;
        if (value.length > 0) {
            store.filter({ property: 'filter', value: value });
        } else {
            store.load();
        }
    },

    /**
     * @event saveArticle
     * @param [Ext.grid.Panel] view
     * @param [Ext.data.Model] record - The selected record
     */
    onSaveArticle: function(view, record) {
        var me = this,
            store = me.getStore('Article');

        view.setLoading(true);
        record.save({
            callback: function() {
                view.setLoading(false);
            }
        });
    },

    /**
     * @param [Ext.grid.Panel] view
     * @param [Ext.data.Model] record - The selected record
     */
    onSaveUser: function(view, record) {
        view.setLoading(true);
        record.save({
            callback: function() {
                view.setLoading(false);
            }
        });
    },

    /**
     * @param [Ext.form.Panel] view
     */
    onSaveSetting: function(view) {
        var me = this,
            form = view.getForm(),
            record = form.getRecord();

        if (!form.isValid()) {
            return;
        }

        form.updateRecord(record);

        view.setLoading(true);
        record.save({
            callback: function() {
                view.setLoading(false);
                view.loadRecord(record);
            }
        });
    }
});
//{/block}
