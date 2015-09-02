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
 * @package    Overview
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/overview/controller/main}

/**
 * todo@all: Documentation
 */
//{block name="backend/overview/controller/main"}
Ext.define('Shopware.apps.Overview.controller.Main', {

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
     * Define references for the different parts of our application. The
     * references are parsed by ExtJS and Getter methods are automatically created.
     *
     * @array
     */
    refs: [
        { ref: 'gridPanel', selector: 'overview-main-grid' }
    ],


    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     *
     * @return void
     */
    init: function() {
        var me = this;

        me.control({
            'overview-main-grid': {
                dateChange: me.onDateChange
            }
        });

        me.subApplication.overviewStore =  me.getStore('Overview');
        me.mainWindow = me.getView('main.Window').create({
            overviewStore: me.subApplication.overviewStore.load()
        });

        me.mainWindow.show();

        me.callParent(arguments);
    },

    /**
     * Event will be fired when the date-range changes
     *
     * @event dateChange
     * @param [Date] fromDate
     * @param [Date] toDate
     */
    onDateChange: function(fromDate, toDate) {
        var me      = this,
            store   = me.getStore('Overview'),
            gridPnl = me.getGridPanel();

        Ext.apply(store.getProxy().extraParams, {
            fromDate: fromDate,
            toDate: toDate
        });

        gridPnl.setLoading(true);
        store.load({
            callback: function() {
                gridPnl.setLoading(false);
            }
        });
    }
});
//{/block}
