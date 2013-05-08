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
 * @package    Shopware_Config
 * @subpackage Config
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Controller - Performance backend module
 *
 * todo@all: Documentation
 */
//{block name="backend/performance/controller/main"}
Ext.define('Shopware.apps.Performance.controller.Main', {
    extend: 'Enlight.app.Controller',

    refs: [
        { ref: 'info', selector: 'performance-tabs-cache-info' },
        { ref: 'settings', selector: 'performance-tabs-settings-main' },
        { ref: 'cacheTime', selector: 'performance-tabs-settings-elements-cache-time' },
        { ref: 'noCache', selector: 'performance-tabs-settings-elements-no-cache' }
    ],

    /**
     * The main window instance
     * @object
     */
    mainWindow: null,

    /**
     * Called by the SubApplication to create and show the window
     */
    run: function() {
        var me = this;

        me.mainWindow = me.subApplication.getView('main.Window').create().show();

        me.loadStores();
    },

    /**
     * Helper method to load the stores and bind them to the corresponding fields when ready
     */
    loadStores: function() {
        var me = this;

        me.infoStore = me.getStore('Info').load(function() {
            me.getInfo().bindStore(me.infoStore);
        });

        me.getStore('Config').load(function (records) {
            var storeData = records[0];

            me.injectSettingStores(storeData);
        });

    },

    /**
     * Helper method to inject the config stores into the settings tab
     * @param Ext.data.Store
     */
    injectSettingStores: function(storeData) {
        var me = this;

        me.configStore = storeData;

        me.getSettings().loadRecord(me.configStore);
        me.getCacheTime().reconfigure(me.configStore.getHttpCache().first().getCacheControllers());
        me.getNoCache().reconfigure(me.configStore.getHttpCache().first().getNoCacheControllers());
    }


});
//{/block}
