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
 * @package    Article
 * @subpackage Detail
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Article detail page
 * The prices component contains the definition of the prices field set.
 * In the field set, a tab panel displayed, which contains a tab for each shop customer group.
 * Within the different tabs a grid displayed to define the article prices for each customer group.
 * The component events handled in the detail controller.
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/view/detail/prices"}
Ext.define('Shopware.apps.Article.view.detail.Prices', {
    /**
     * Define that the billing field set is an extension of the Ext.form.FieldSet
     * @string
     */
    extend:'Ext.form.FieldSet',
    /**
     * The Ext.container.Container.layout for the fieldset's immediate child items.
     * @object
     */
    layout: 'anchor',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.article-prices-field-set',
    /**
     * Set css class for this component
     * @string
     */
    cls: Ext.baseCSSPrefix + 'article-prices-field-set',
    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        title:'{s name=detail/prices/title}Prices{/s}',
        any:'{s name=detail/prices/any}Arbitrary{/s}',
        grid: {
            titleGross:'{s name=detail/price/title_gross}[0] Gross{/s}',
            titleNet:'{s name=detail/price/title_net}[0] Net{/s}',
            columns: {
                from: '{s name=detail/price/from}From{/s}',
                to: '{s name=detail/price/to}To{/s}',
                percent: '{s name=detail/price/percent}Percent discount{/s}',
                price: '{s name=detail/price/price}Price{/s}',
                pseudoPrice: '{s name=detail/price/pseudo_price}Pseudo price{/s}',
                percentPseudo: '{s name=detail/price/percent_pseudo_price}Savings vs. pseudo price{/s}'
            },
            any:'{s name=detail/price/any}Arbitrary{/s}'
        }
    },

    attributeTable: 's_articles_prices_attributes',

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
    initComponent:function () {
        var me = this,
            mainWindow = me.subApp.articleWindow;

        mainWindow.on('storesLoaded', me.onStoresLoaded, me);
        me.title = me.snippets.title;
        me.registerEvents();
        me.callParent(arguments);
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

        me.preparePriceStore();

        me.customerGroupStore.each(function(customerGroup) {
            if (customerGroup.get('mode') === false) {
                var tab = me.createPriceGrid(customerGroup, me.priceStore);
                tabs.push(tab);
            }
        });
        me.priceGrids = tabs;

        me.tabPanel = Ext.create('Ext.tab.Panel', {
            height: 150,
            activeTab: 0,
            plain: true,
            items : tabs,
            listeners: {
                beforetabchange: function(panel, newTab, oldTab) {
                    me.fireEvent('priceTabChanged', oldTab, newTab, me.priceStore, me.customerGroupStore)
                }
            }
        });

        return me.tabPanel;
    },

    /**
     * Prepares the price store items for the selected customer group
     */
    preparePriceStore: function() {
        var me = this, firstGroup = me.customerGroupStore.first();

        /**
         * we have to calculate the percentPseudo, data is not saved in database
         */
        me.priceStore.data.each(function(item) {
            var percentPseudo = 0,
                pseudoPrice = item.get('pseudoPrice'),
                price = item.get('price');

            percentPseudo = 100 - 100 / pseudoPrice * price;
            percentPseudo = percentPseudo.toFixed(2);
            item.set('percentPseudo', percentPseudo);
        });

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
                percent: 0,
                customerGroupKey: firstGroup.get('key')
            });
            me.priceStore.add(price)
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

        var title = me.snippets.grid.titleNet;
        if (customerGroup.get('taxInput')) {
            title = me.snippets.grid.titleGross;
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
            }, {
                ptype: 'grid-attributes',
                table: me.attributeTable
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
     * @return Array -  Contains all Ext.form.Fields for the description field set
     */
    getColumns: function () {
        var me = this;

        return [
            {
                header: me.snippets.grid.columns.from,
                dataIndex: 'from',
            }, {
                xtype: 'numbercolumn',
                header: me.snippets.grid.columns.to,
                dataIndex: 'to',
                flex: 1,
                editor: {
                    xtype: 'numberfield',
                    minValue: 0,
                    decimalPrecision: 0
                },
                renderer: function(v) {
                    if (Ext.isNumeric(v)) {
                        return v;
                    } else {
                        return me.snippets.grid.any;
                    }
                }

            }, {
                xtype: 'numbercolumn',
                header: me.snippets.grid.columns.percent,
                dataIndex: 'percent',
                editor: {
                    xtype: 'numberfield',
                    minValue: 0,
                    decimalPrecision: 2,
                    maxValue: 100
                },
                renderer: function(v) {
                    if (!Ext.isNumeric(v) || v === 0) {
                        return ''
                    }
                    return Ext.util.Format.number(v) + ' %'
                }
            }, {
                xtype: 'numbercolumn',
                header: me.snippets.grid.columns.price,
                dataIndex: 'price',
                editor: {
                    xtype: 'numberfield',
                    decimalPrecision: 2,
                    minValue: 0
                }
            }, {
                xtype: 'numbercolumn',
                header: me.snippets.grid.columns.pseudoPrice,
                dataIndex: 'pseudoPrice',
                editor: {
                    xtype: 'numberfield',
                    decimalPrecision: 2,
                    minValue: 0
                },
                renderer: function(v) {
                    if (!Ext.isNumeric(v) || v === 0) {
                        return ''
                    }
                    return Ext.util.Format.number(v)
                }
            }, {
                xtype: 'numbercolumn',
                header: me.snippets.grid.columns.percentPseudo,
                dataIndex: 'percentPseudo',
                width: 150,
                editor: {
                    xtype: 'numberfield',
                    minValue: 0,
                    decimalPrecision: 2,
                    maxValue: 100
                },
                renderer: function(v) {
                    if (!Ext.isNumeric(v) || v === 0) {
                        return '';
                    }
                    return Ext.util.Format.number(v) + ' %';
                }
            }, {
                xtype: 'actioncolumn',
                width: 25,
                items: [
                    {
                        iconCls: 'sprite-minus-circle-frame',
                        action: 'delete',
                        tooltip: me.snippets.grid.delete,
                        handler: function (view, rowIndex, colIndex, item, opts, record) {
                            me.fireEvent('removePrice', record, view, rowIndex);
                        },
                        /**
                         * If the item has no leaf flag, hide the add button
                         *
                         * @param value
                         * @param metadata
                         * @param record
                         * @param rowIdx
                         *
                         * @return string
                         */
                        getClass: function(value, metadata, record, rowIdx) {
                            if (Ext.isNumeric(record.get('to')) || rowIdx === 0)  {
                                return 'x-hidden';
                            }
                        }
                    }
                ]
            }
        ];
    },

    onStoresLoaded: function(article, stores) {
        var me = this;
        me.article = article;

        me.customerGroupStore = stores['customerGroups'];
        me.priceStore = me.priceStore = me.article.getPrice();
        me.add(me.createElements());
    }
});
//{/block}
