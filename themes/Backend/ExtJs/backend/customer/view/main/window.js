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
 * @package    Customer
 * @subpackage Main
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/customer_stream/translation}

//{block name="backend/customer/view/main/window"}
Ext.define('Shopware.apps.Customer.view.main.Window', {
    extend: 'Enlight.app.Window',
    cls: Ext.baseCSSPrefix + 'customer-list-window',
    alias: 'widget.customer-list-main-window',
    border: false,
    autoShow: true,
    layout: 'border',
    width: '95%',
    height: '95%',
    title: '{s name=window_title}Customer list{/s}',

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function () {
        var me = this;

        Ext.suspendLayouts();
        me.listStore = Ext.create('Shopware.apps.Customer.store.Preview', { pageSize: 15 }).load({ conditions: null });

        me.gridPanel = Ext.create('Shopware.apps.Customer.view.list.List', {
            store: me.listStore
        });

        me.gridPanel.on('afterrender', function() {
            me.gridPanel.getEl().on('click', function(event, element) {
                element = Ext.get(element);
                event.preventDefault();

                me.streamListing.getSelectionModel().select([
                    me.streamListing.getStore().getById(
                        window.parseInt(element.getAttribute('data-id'))
                    )
                ]);
            }, me, {
                delegate: ".stream-inline"
            });
        });

        me.streamListing = Ext.create('Shopware.apps.Customer.view.customer_stream.Listing', {
            store: Ext.create('Shopware.apps.Customer.store.CustomerStream').load(),
            subApp: me.subApp,
            collapsible: true,
            hideHeaders: true,
            title: '{s name=window/defined_streams}Definied streams{/s}',
            height: 200,
            iconCls: 'sprite-product-streams',
            selectionChanged: function (selModel, selection) {
                me.fireEvent('stream-selected', selModel, selection);
            }
        });

        me.filterPanel = Ext.create('Shopware.apps.Customer.view.customer_stream.ConditionPanel', { flex: 4 });

        me.metaChart = Ext.create('Shopware.apps.Customer.view.chart.MetaChart');

        me.metaChartStore = me.metaChart.store;

        me.streamChartContainer = Ext.create('Ext.container.Container', {
            items: [],
            flex: 1,
            layout: 'border'
        });

        me.streamDetailForm = Ext.create('Ext.form.Panel', {
            bodyPadding: 20,
            dockedItems: [{
                xtype: 'toolbar',
                dock: 'bottom',
                items: ['->', me.createSaveStreamDetailButton()]
            }]
        });

        me.cardContainer = Ext.create('Ext.container.Container', {
            items: [ me.gridPanel, me.metaChart, me.streamChartContainer, me.streamDetailForm ],
            region: 'center',
            layout: 'card'
        });

        me.formPanel = Ext.create('Ext.form.Panel', {
            region: 'west',
            collapsible: true,
            cls: 'shopware-form customer-filter-panel',
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            width: 400,
            title: '{s name=window/filter_and_customer_streams}Filter & Customer streams{/s}',
            items: [
                me.filterPanel,
                me.streamListing
            ]
        });
        me.items = [
            me.formPanel,
            me.cardContainer
        ];
        me.dockedItems = [ me.getToolbar() ];

        Ext.resumeLayouts(true);

        me.callParent(arguments);

        me.fireEvent('reset-progressbar');
    },

    /**
     * Creates the grid toolbar with the add and delete button
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar:function () {
        var me = this;

        me.toolbar = Ext.create('Shopware.apps.Customer.view.main.Toolbar', {
            handlers: me.filterPanel.handlers
        });
        return me.toolbar;
    },

    customerSelected: function (selection) {
        var me = this;
        me.deleteCustomerButton.setDisabled(selection.length === 0);
    },

    resetFilterPanel: function() {
        var me = this;
        var newStream = Ext.create('Shopware.apps.Customer.model.CustomerStream', {
            id: null,
            name: '{s name=window/new_stream}New stream{/s}'
        });

        me.filterPanel.removeAll();
        me.filterPanel.loadRecord(null);
        me.formPanel.loadRecord(null);
    },

    loadListing: function() {
        var me = this;

        if (!me.filterPanel.getForm().isValid()) {
            return;
        }

        me.listStore.getProxy().extraParams = me.filterPanel.getSubmitData();
        me.listStore.load();
    },

    resetTitles: function() {
        var me = this;

        me.formPanel.setTitle('{s name=window/stream_filter}Stream filter{/s}');
        me.setTitle('{s name=window/customer_list }Customer list{/s}');
    },

    createSaveStreamDetailButton: function() {
        var me = this;

        return Ext.create('Ext.button.Button', {
            text: '{s name=save}Save{/s}',
            cls: 'primary',
            handler: function () {
                me.fireEvent('save-stream-details');
            }
        });
    }
});
//{/block}
