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
 * @package    SwagAboCommerce
 * @subpackage ExtJs
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name="backend/abo_commerce/article/view/main"}
Ext.define('Shopware.apps.Article.view.abo_commerce.tabs.Price', {

    extend: 'Ext.panel.Panel',

    title: 'Preise/Rabattierung',

    layout: 'fit',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets
     */
    alias: 'widget.abo-commerce-price-listing',

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.items = me.createElements();

//        me.items = [
//            {
//                xtype: 'container',
//                html: 'Sie haben in diesen Bereich die Möglichkeit die Rabattierung Ihres Abos einzustellen. Der Preis des Artikels wird entweder über die Laufzeit und den von Ihnen eingegeben prozentualen Rabatten berechnet oder Sie nutzen Sie Möglichkeit einen absoluten Rabatt einzugeben, welcher als monatlicher Preis für den Kunden fungiert.',
//                margin: '0 0 15',
//                style: 'color: #999; font-style: italic;'
//            },
//            me.createElements()
//        ];
        me.registerEvents();
        me.callParent(arguments);
    },

    setRecord: function(record)
    {
        var me = this;

        me.aboRecord = record;
        me.priceStore = me.aboRecord.getPrices();

        if (me.priceStore.getCount() === 0) {
            var newRecord = Ext.create('Shopware.apps.Article.model.abo_commerce.Price', {
                durationFrom: 1
            });

            me.priceStore.add(newRecord);
        }


        me.priceGrids[0].reconfigure(me.priceStore);
    },

    /**
     * Registers additional component events.
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * Event will be fired when the user change the tab panel in the price field set.
             *
             * @event
             * @param [object] The previous tab panel
             * @param [object] The clicked tab panel
             * @param [Ext.data.Store] The price store
             * @param [array] The price data of the first customer group.
             */

            'priceTabChanged',

            /**
             * Fired when the user clicks the remove action column of the price grid
             *
             * @event
             * @param [array] The row record
             */
            'removePrice'
        );
    },

    /**
     * Creates the elements for the description field set.
     * @return array Contains all Ext.form.Fields for the description field set
     */
    createElements: function () {
        var me = this, tabs = [];

//        me.priceStore = Ext.create('Shopware.apps.Article.store.abo_commerce.Detail');
//        me.preparePriceStore();

        me.customerGroupStore.each(function(customerGroup) {
            if (customerGroup.get('mode') === false) {
                var tab = me.createPriceGrid(customerGroup, me.priceStore);
                tabs.push(tab);
            }
        });

        me.priceGrids = tabs;

        return Ext.create('Ext.tab.Panel', {
//            height: 150,
            activeTab: 0,
            plain: true,
            items : tabs,
            listeners: {
                beforetabchange: function(panel, newTab, oldTab) {
                    me.fireEvent('priceTabChanged', oldTab, newTab, me.priceStore, me.customerGroupStore);
                }
            }
        });
    },

    /**
     * Prepares the price store items for the selected customer group
     */
    preparePriceStore: function() {
        var me = this, firstGroup = me.customerGroupStore.first();

        me.priceStore.clearFilter();
        me.priceStore.filter({
            filterFn: function(item) {
                return item.get("customerGroupKey") === firstGroup.get('key');
            }
        });

        if (me.priceStore.data.length === 0) {
            var price = Ext.create('Shopware.apps.Article.model.Price', {
                from: 1,
                to: me.snippets.any,
                price: 0,
                pseudoPrice: 0,
                basePrice: 0,
                percent: 0,
                customerGroupKey: firstGroup.get('key')
            });
            me.priceStore.add(price);
        }
    },

    /**
     * Creates a grid for the article prices.
     *
     * @param customerGroup
     * @param priceStore
     * @return Ext.grid.Panel
     */
    createPriceGrid: function(customerGroup, priceStore) {
        var me = this;

        var title = 'Netto';

        if (customerGroup.get('taxInput')) {
            title = 'Brutto';
        }

        title = Ext.String.format(title, customerGroup.get('name'));

        return Ext.create('Ext.grid.Panel', {
            alias:'widget.article-price-grid',
            cls: Ext.baseCSSPrefix + 'article-price-grid',
            height: 100,
            sortableColumns: false,
            plugins: [{
                ptype: 'cellediting',
                clicksToEdit: 1
            }],
            defaults: {
                align: 'right',
                flex: 2
            },
            title: title,
            store: priceStore,
            customerGroup: customerGroup,
            columns: me.getColumns()
        });
    },

    /**
     * Creates the elements for the description field set.
     * @return Array Contains all Ext.form.Fields for the description field set
     */
    getColumns: function () {
        var me = this;

        return [
            {
                header: 'Ab (Laufzeit / Monat)',
                dataIndex: 'durationFrom',
                flex: 1,
                editor: {
                    xtype: 'numberfield',
                    minValue: 0,
                    decimalPrecision: 0
                },
                renderer: function(v) {
                    return v + " Monat(e)";
                }
            }, {
                xtype: 'numbercolumn',
                flex: 1,
                dataIndex: 'to',
                header: 'Laufzeit bis',
                editor: {
                    xtype: 'numberfield',
                    decimalPrecision: 2,
                    minValue: 0
                },
                renderer: me.trendRenderer
            }, {
                xtype: 'numbercolumn',
                flex: 1,
                header: 'Prozentrabatt',
                dataIndex: 'dicountPercent',
                editor: {
                    xtype: 'numberfield',
                    decimalPrecision: 2,
                    minValue: 0,
                    maxValue: 99
                },
                renderer: function(v) {
                    if (!Ext.isNumeric(v)) {
                        return '-';
                    }
                    return Ext.util.Format.number(v) + ' %';
                }
            }, {
                xtype: 'numbercolumn',
                flex: 1,
                header: 'Absoluter Rabatt',
                dataIndex: 'dicountAbsolute',
                editor: {
                    xtype: 'numberfield',
                    decimalPrecision: 2,
                    minValue: 0
                },
                renderer: function(v) {
                    if (!Ext.isNumeric(v)) {
                        return '-';
                    }
                    return Ext.util.Format.number(v) + ' €';
                }
            }, {
                xtype: 'numbercolumn',
                flex: 1,
                header: 'monatlicher Preis',
                dataIndex: 'percent',
                renderer: function(value, metaData, record) {
                    return "todo";
                }
            }, {
                xtype: 'actioncolumn',
                width: 25,
                items: [
                    {
                        iconCls: 'sprite-minus-circle-frame',
                        action: 'delete',
                        tooltip: 'delete',
                        handler: function (view, rowIndex, colIndex, item, opts, record) {
                            var store = view.getStore();
                            store.remove(record);

                            me.priceGrids[0].reconfigure(store);
                            me.fireEvent('removePrice', record, view, rowIndex);
                        },
                        /**
                         * If the item has no leaf flag, hide the add button
                         * @param value
                         * @param metadata
                         * @param record
                         * @return string
                         * @param rowIdx
                         */
                        getClass: function(value, metadata, record, rowIdx) {
                            if (rowIdx === 0)  {
                                return 'x-hidden';
                            }
                        }
                    }
                ]
            }
        ];
    },

    /**
     * Renders Trendicons
     *
     * @param [object] - value
     * @param [object] - metaData
     * @param [object] - record
     * @param [number] - rowIndex
     * @param [number] - colIndex
     * @param [object] - store
     * @param [object] - view
     * @return [string]
     */
    trendRenderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
        var nextRecord = store.getAt(rowIndex + 1);

        if (nextRecord) {
            return nextRecord.get('durationFrom') - 1 + " Monate";
        } else {
            return "-";
        }
    },

    getItems: function() {
        return [
            {
                xtype: 'container',
                html: 'Sie haben in diesen Bereich die Möglichkeit die Rabattierung Ihres Abos einzustellen. Der Preis des Artikels wird entweder über die Laufzeit und den von Ihnen eingegeben prozentualen Rabatten berechnet oder Sie nutzen Sie Möglichkeit einen absoluten Rabatt einzugeben, welcher als monatlicher Preis für den Kunden fungiert.',
                margin: '0 0 15',
                style: 'color: #999; font-style: italic;'
            },
            {
                xtype: 'tabpanel',
                activeTab: 0,
                plain: true,
                items: [
                    {
                        xtype: 'panel',
                        layout: {
                            type: 'fit'
                        },
                        title: 'Shopkunden Brutto',
                        items: [
                            {
                                xtype: 'gridpanel',
                                border: 0,
                                columns: [
                                    {
                                        xtype: 'gridcolumn',
                                        dataIndex: 'string',
                                        flex: 1,
                                        text: 'Ab (Laufzeit / Monat)'
                                    },
                                    {
                                        xtype: 'datecolumn',
                                        dataIndex: 'date',
                                        flex: 1,
                                        text: 'Prozentrabatt'
                                    },
                                    {
                                        xtype: 'datecolumn',
                                        dataIndex: 'date',
                                        flex: 1,
                                        text: 'Absoluter Rabatt'
                                    },
                                    {
                                        xtype: 'booleancolumn',
                                        dataIndex: 'bool',
                                        flex: 1,
                                        text: 'monatlicher Preis'
                                    },
                                    {
                                        xtype: 'gridcolumn',
                                        flex: 1,
                                        text: 'Normalpreis (Shopkunden)'
                                    }
                                ]
                            }
                        ]
                    },
                    {
                        xtype: 'panel',
                        title: 'B2B / Händler netto Netto'
                    }
                ]
            }
        ];
    }
});
