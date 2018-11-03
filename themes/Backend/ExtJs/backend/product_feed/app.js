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
 * @package    ProductFeed
 * @subpackage App
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Application - ProductFeed list backend module
 *
 * Contains the configuration for the product_feed list backend module.
 * This component defines which controllers belong to the application or whether the bulk loading is activated.
 */
//{block name="backend/product_feed/app"}
Ext.define('Shopware.apps.ProductFeed', {
    /**
     * Extends from our special controller, which handles the
     * sub-application behavior and the event bus
     * @string
     */
    extend:'Enlight.app.SubApplication',
    /**
     * The name of the module. Used for internal purpose
     * @string
     */
    name:'Shopware.apps.ProductFeed',
    /**
     * Sets the loading path for the sub-application.
     *
     * Note that you'll need a "loadAction" in your
     * controller (server-side)
     * @string
     */
    bulkLoad: true,

    loadPath:'{url action=load}',

    /**
     * Requires controllers for sub-application
     * @array
     */
    controllers:[ 'Main', 'Feed' ],

    /**
     * Requires models for sub-application
     * @array
     */
    models:['Detail','Main','Category','CategoryForComboTree', 'Shop'],

    /**
     * Required views for this sub-application
     * @array
     */
    views: [ 'main.Window', 'feed.List', 'feed.Window', 'feed.Detail', 'feed.tab.Format', 'feed.tab.Header', 'feed.tab.Body', 'feed.tab.Footer', 'feed.tab.Supplier', 'feed.tab.Article', 'feed.tab.Category', 'feed.tab.Filter' ],
    /**
     * Required stores for sub-application
     * @array
     */
    stores:[ 'List', 'Detail', 'Category', 'Supplier', 'Article', 'CategoryForComboTree', 'Shop'],


    /**
     * Returns the main application window for this is expected
     * by the Enlight.app.SubApplication class.
     * The class sets a new event listener on the "destroy" event of
     * the main application window to perform the destroying of the
     * whole sub application when the user closes the main application window.
     *
     * This method will be called when all dependencies are solved and
     * all member controllers, models, views and stores are initialized.
     *
     * @private
     * @return [object] mainWindow - the main application window based on Enlight.app.Window
     */
    launch: function() {
        var me = this,
            mainController = me.getController('Main');

        return mainController.mainWindow;
    }
});
//{/block}
