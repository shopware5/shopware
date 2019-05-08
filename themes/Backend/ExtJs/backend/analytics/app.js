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
 */

/**
 * Shopware Application - Analytics Module
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 */
//{block name="backend/analytics/app"}
//{block name="backend/analytics/application"}
Ext.define('Shopware.apps.Analytics', {

    /**
     * Extends from our special controller, which handles the
     * sub-application behavior and the event bus
     * @string
     */
    extend: 'Enlight.app.SubApplication',

    /**
     * Sets the loading path for the sub-application.
     *
     * Note that you'll need a "loadAction" in your
     * controller (server-side)
     * @string
     */
    loadPath: '{url action=load}',

    /**
     * Enable bulk loading
     */
    bulkLoad: true,

    /**
     * Requires controllers for sub-application
     * @array
     */
    controllers: [ 'Main', 'ReferrerVisitors' ],

    /**
     * Required stores for controller
     * @array
     */
    stores: [
        'Navigation',

        'Shop',
        'Data',

        'navigation.ArticleImpressions',
        'navigation.CalendarWeeks',
        'navigation.Categories',
        'navigation.Countries',
        'navigation.CustomerGroups',
        'navigation.Month',
        'navigation.Overview',
        'navigation.Payment',
        'navigation.Search',
        'navigation.ShippingMethods',
        'navigation.Time',
        'navigation.Vendors',
        'navigation.DeviceTypes',
        'navigation.Visitors',
        'navigation.Weekdays',
        'navigation.Rating',
        'navigation.ReferrerRevenue',
        'navigation.PartnerRevenue',
        'navigation.ReferrerVisitors',
        'navigation.ArticleSales',
        'navigation.Customers',
        'navigation.SearchTerms',
        'navigation.SearchUrls',
        'navigation.CustomerAge'
    ],

    /**
     * Required models for controller
     * @array
     */
    models: [ 'Navigation' ],

    /**
     * Required views for controller
     *
     * @array
     */
    views: [
        'main.Window',
        'main.Navigation',
        'main.Panel',
        'main.Toolbar',
        'main.Table',
        'main.Chart',

        'chart.Week',
        'chart.Weekday',
        'chart.Month',
        'chart.Daytime',
        'chart.Supplier',
        'chart.Category',
        'chart.Country',
        'chart.Dispatch',
        'chart.Payment',
        'chart.CustomerAge',
        'chart.Customers',
        'chart.CustomerGroup',
        'chart.PartnerRevenue',
        'chart.DeviceTypes',
        'chart.Visitors',

        'table.Week',
        'table.Weekday',
        'table.Month',
        'table.Daytime',
        'table.Supplier',
        'table.Category',
        'table.Country',
        'table.Dispatch',
        'table.CustomerGroup',
        'table.Payment',
        'table.Search',
        'table.Visitors',
        'table.ArticleImpression',
        'table.Overview',
        'table.Rating',
        'table.ReferrerRevenue',
        'table.PartnerRevenue',
        'table.DeviceTypes',
        'table.ReferrerVisitors',
        'table.ArticleSales',
        'table.Customers',
        'table.SearchTerms',
        'table.SearchUrls',
        'table.CustomerAge'
    ],
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
    launch: function () {
        var me = this,
            mainController = me.getController('Main');

        me.currencySign = '{$analyticsCurrency.sign}';
        me.currencyAtEnd = '{$analyticsCurrency.currencyAtEnd}';

        return mainController.mainWindow;
    }
});
//{/block}
//{/block}
