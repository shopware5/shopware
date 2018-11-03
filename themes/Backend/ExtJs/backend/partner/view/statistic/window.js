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
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/partner/view/partner}

/**
 * Shopware UI - Partner statistic main window.
 *
 * Displays all Statistic Partner Information
 */
//{block name="backend/partner/view/statistic/window"}
Ext.define('Shopware.apps.Partner.view.statistic.Window', {
    extend: 'Enlight.app.Window',
    title: '{s name=window/statistic_title}Partner statistics{/s}',
    alias: 'widget.partner-statistic-window',
    border: false,
    autoShow: true,
    layout: 'fit',
    height: 620,
    /**
     * Display no footer button for the detail window
     * @boolean
     */
    footerButton:false,
    width: 925,

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.registerEvents();
        me.items = me.createPanel();
        me.callParent(arguments);
    },

    /**
     * Defines additional events which will be
     * fired from the component
     *
     * @return void
     */
    registerEvents:function () {
        this.addEvents(
                /**
                 * Event will be fired wenn the downloadStatistic Button is clicked
                 *
                 * @event downloadStatistic
                 * @param [object] View - Associated Ext.view.Table
                 * @param [integer] rowIndex - Row index
                 * @param [integer] colIndex - Column index
                 * @param [object] item - Associated HTML DOM node
                 */
                'downloadStatistic'
        );

        return true;
    },

    /**
     * creates the form panel
     */
    createPanel:function () {
        var me = this;
        return Ext.create('Ext.panel.Panel', {
            layout:{
                layout:'fit',
                type:'vbox',
                align:'stretch'
            },
            defaults:{ flex:1 },
            items:[
                {
                    xtype:'partner-statistic-chart',
                    region:'center',
                    store:me.statisticChartStore.load()
                },
                {
                    xtype:'partner-statistic-list',
                    listStore:me.statisticListStore.load()
                }
            ],
            dockedItems:[ me.createToolbar() ]

        });
    },


    /**
     * Creates the toolbar for the order tab.
     * The toolbar contains two date fields (from, to) which allows the user to filter the chart store.
     *
     * @return [Ext.toolbar.Toolbar] - Toolbar for the order tab which contains the from and to date field to filter the chart
     */
    createToolbar:function () {
        var me = this,
            today = new Date();

        me.fromDateField = Ext.create('Ext.form.field.Date', {
            labelWidth:45,
            name:'fromDate',
            fieldLabel:'{s name=statistic/window/date/from}From{/s}',
            value:new Date(today.getFullYear() - 1, today.getMonth() , today.getDate())
        });

        me.toDateField = Ext.create('Ext.form.field.Date', {
            labelWidth:30,
            name:'toDate',
            fieldLabel:'{s name=statistic/window/date/to}To{/s}',
            value:today
        });



        return Ext.create('Ext.toolbar.Toolbar', {
            ui:'shopware-ui',
            padding: '10 0 5',
            items:[
                { xtype:'tbspacer', width:10 },
                me.fromDateField,
                { xtype:'tbspacer', width:10 },
                me.toDateField,
                { xtype:'tbspacer', width:10 },
                {
                    iconCls:'sprite-drive-download',
                    text:'{s name=statistic/button/download_csv}Download statistics{/s}',
                    action:'downloadStatistic'
                }
            ]
        });
    }
});
//{/block}
