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

//{namespace name=backend/partner/view/partner}

/**
 * Shopware Controller - Partner backend module
 *
 * The statistic controller managed and controls all partner statistic specific events and methods
 */
//{block name="backend/partner/controller/statistic"}
Ext.define('Shopware.apps.Partner.controller.Statistic', {
    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend:'Ext.app.Controller',

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the sub-application
     *
     * @return void
     */
    init:function () {
        var me = this;

        me.control({
            'partner-statistic-window datefield':{
                change:me.onChangeDate
            },
            'partner-statistic-window button[action=downloadStatistic]':{
                click:me.onDownloadStatistic
            }
        });
    },


    /**
     * Event listener method which is fired when the user change
     * the to date field to filter the order chart data.
     * The to date field is placed on top of the chart.
     *
     * @param [Ext.form.Field.Date] - The date field which changed
     * @param [Ext.Date] - The new value
     * @return void
     */
    onChangeDate:function (field, value) {
        var me = this,
        extraParams = null;
        if ( Ext.typeOf(value) != 'date' ) {
            return;
        }
        var chartStore = me.subApplication.statisticChartStore;
        var listStore = me.subApplication.statisticListStore;

        if(field.name == "toDate") {
            extraParams = {
                partnerId:chartStore.getProxy().extraParams.partnerId,
                toDate:value,
                fromDate:chartStore.getProxy().extraParams.fromDate
            };
            chartStore.getProxy().extraParams = extraParams;
            listStore.getProxy().extraParams = extraParams;
        }
        else {
            extraParams = {
                partnerId:chartStore.getProxy().extraParams.partnerId,
                toDate:chartStore.getProxy().extraParams.toDate,
                fromDate:value
            };
            chartStore.getProxy().extraParams = extraParams;
            listStore.getProxy().extraParams = extraParams;
        }

        chartStore.load();
        listStore.load();
    },

    /**
     * Listener Method for the download codes button
     * to get access to the download window
     *
     * @return void
     */
    onDownloadStatistic:function () {
        var me = this;
        var listStore = me.subApplication.statisticListStore,
        partnerId = listStore.getProxy().extraParams.partnerId,
        fromDate = listStore.getProxy().extraParams.fromDate,
        toDate = listStore.getProxy().extraParams.toDate,
        requestStringFromDate = '',
        requestStringToDate = '';

        if(fromDate != "undefined" && fromDate != null) {
            fromDate = me.convertDate(fromDate);
            requestStringFromDate = '&fromDate='+fromDate;
        }

        if(toDate != "undefined" && toDate != null) {
            toDate = me.convertDate(toDate);
            requestStringToDate = '&toDate='+toDate;
        }

        window.open(' {url action="downloadStatistic"}?partnerId='+partnerId+requestStringFromDate+requestStringToDate);
    },

    /**
     * convert the date object for the request
     *
     * @param date
     */
    convertDate:function(date){
        var day = (date.getDate() < 10) ? "0" + date.getDate() : date.getDate();
        var month = (date.getMonth() +1 < 10) ? "0" + (date.getMonth() +1) : date.getMonth() +1;
        var year = date.getFullYear();

        return year+"-"+month+"-"+day;
    }
});
//{/block}
