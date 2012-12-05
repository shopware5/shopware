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

/**
 * Shopware ExtJs controller.
 */
//{namespace name="backend/live_shopping/view/main"}
//{block name="backend/live_shopping/controller/main"}
Ext.define('Shopware.apps.LiveShopping.controller.Main', {
    /**
     * The parent class that this class extends.
     * @string
     */
    extend:'Ext.app.Controller',


    refs: [
        { ref: 'detailWindow', selector: 'live-shopping-detail-window' },
        { ref: 'listWindow', selector: 'live-shopping-list-window' }
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

        if (me.subApplication.params && me.subApplication.params.liveShoppingId > 0) {
            me.mainWindow = me.createDetailWindow(me.subApplication.params.liveShoppingId);
        } else {
            me.mainWindow = me.createListWindow();
        }

        me.callParent(arguments);

        return me.mainWindow;
    },

    /**
     * Creates and shows the list window of the LiveShopping module.
     * @return Shopware.apps.LiveShopping.store.List
     */
    createListWindow: function() {
        var me = this;

        return me.getView('list.Window').create({
            listStore: Ext.create('Shopware.apps.LiveShopping.store.List').load()
        }).show();
    },

    /**
     * Creates and shows the detail window of the LiveShopping module.
     *
     * @return Shopware.apps.LiveShopping.store.List
     */
    createDetailWindow: function(liveShoppingId) {
        var me = this, window;

        window = me.getView('detail.Window').create().show();

        me.loadRecordIntoView(liveShoppingId);

        return window;
    },

    /**
     * Helper function to load a single LiveShopping record
     * into the detail window.
     *
     * @param liveShoppingId
     */
    loadRecordIntoView: function(liveShoppingId) {
        var me = this, store;

        store = Ext.create('Shopware.apps.LiveShopping.store.Detail');

        store.getProxy().extraParams.liveShoppingId = liveShoppingId;

        store.load({
            callback: function() {
                if (store.getCount() > 0) {
                    var record = store.getAt(0);
                    var detailWindow = me.getDetailWindow();

                    if (record instanceof Ext.data.Model && detailWindow) {

                        //todo@dr: Hier die Daten in die View setzen

                    }
                }
            }
        });
    }
});
//{/block}
