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
 * Analytics ReferrerVisitors Controller
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/controller/referrer_visitors"}
Ext.define('Shopware.apps.Analytics.controller.ReferrerVisitors', {

    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
    extend: 'Enlight.app.Controller',

    /**
     * References to specific elements in the module
     * @array
     */
    refs: [
        { ref: 'panel', selector: 'analytics-panel' }
    ],

    /**
     * Creates the necessary event listener for this specific controller
     * to control the switch from the referrer listing and the search term table
     *
     * @return void
     */
    init: function () {
        var me = this;

        me.control({
            'analytics-table-referrer_visitors': {
                viewSearchTerms: me.onViewSearchTerms,
                viewSearchUrl: me.onViewSearchUrls
            }
        });
    },

    /**
     * Switches the view to a tables which shows all search terms of the referrer
     * @param grid
     * @param rowIndex
     * @param colIndex
     */
    onViewSearchTerms: function (grid, rowIndex, colIndex) {
        var me = this,
            store = grid.store,
            record = store.getAt(rowIndex),
            referrer = record.get('referrer');

        me.openDetailWindow('search-terms', referrer, 400, 400);
    },

    /**
     * Switches the view to a table that shows all referrer urls where visitors are coming from
     * @param grid
     * @param rowIndex
     * @param colIndex
     */
    onViewSearchUrls: function (grid, rowIndex, colIndex) {
        var me = this,
            store = grid.store,
            record = store.getAt(rowIndex),
            referrer = record.get('referrer');

        me.openDetailWindow('search-urls', referrer, 600, 400);
    },

    /**
     * Creates a new Subwindow which contains a new table created by the given widget name
     *
     * @param widget - the name of the statistic e.g. 'search-terms'
     * @param title - the title of the Subwindow
     * @param width - width of the Subwindow
     * @param height - height of the Subwindow
     */
    openDetailWindow: function (widget, title, width, height) {
        var me = this,
            widgetName = 'widget.analytics-table-' + widget,
            store = Ext.widget('analytics-store-navigation-' + widget);

        store.getProxy().extraParams.selectedReferrer = title;

        Ext.create('Enlight.app.SubWindow', {
            subApp: me.subApplication,
            width: width,
            height: height,
            layout: 'fit',
            title: title,
            items: [Ext.create(widgetName, {
                store: store.load()
            })]
        }).show();
    }
});
//{/block}
