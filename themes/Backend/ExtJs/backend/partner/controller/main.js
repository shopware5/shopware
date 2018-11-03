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
 * @package    Partner
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Controller - partner main backend module
 *
 * The partner module main controller handles the initialisation of the backend list.
 */
//{block name="backend/partner/controller/main"}
Ext.define('Shopware.apps.Partner.controller.Main', {

    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
    extend: 'Ext.app.Controller',

    /**
     * Required sub-controller for this controller
     */
    requires: [
        'Shopware.apps.Partner.controller.Partner',
        'Shopware.apps.Partner.controller.Statistic'
    ],

    mainWindow: null,

    /**
     * Required stores for sub-application
     * @array
     */
    stores:[ 'List', 'Detail'],


    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the subapplication
     *
     * @return void
     */
    init: function() {
        var me = this;
        /** me.subApplication.listStore stores the list data*/
        me.subApplication.listStore =  me.subApplication.getStore('List');
        /** me.subApplication.detailStore stores the detail data*/
        me.subApplication.detailStore =  me.subApplication.getStore('Detail');
        /** me.subApplication.statisticListStore stores the statistic list data for the statistic page*/
        me.subApplication.statisticListStore =  me.subApplication.getStore('StatisticList');
        /** me.subApplication.statisticChartStore stores the statistic chart data for the statistic page*/
        me.subApplication.statisticChartStore =  me.subApplication.getStore('StatisticChart');

        me.subApplication.listStore.load();
        me.mainWindow = me.getView('main.Window').create({
            listStore: me.subApplication.listStore
        });

        me.callParent(arguments);
    }
});
//{/block}
