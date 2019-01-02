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

//{namespace name=backend/index/view/widgets}

/**
 * Shopware UI - Rating Widget
 *
 * This file holds off the rating widget.
 */
//{block name="backend/index/view/widgets/rating"}
Ext.define('Shopware.apps.Index.view.widgets.Rating', {
    extend: 'Shopware.apps.Index.view.widgets.Base',
    alias: 'widget.swag-rating-widget',
    title: '{s name=rating/title}Unverified product ratings{/s}',
    layout: 'fit',

    /**
     * Snippets for this widget.
     * @object
     */
    snippets: {
        headers: {
            product: '{s name=rating/headers/product}Product{/s}',
            date: '{s name=rating/headers/date}Date{/s}',
            author: '{s name=rating/headers/author}Author{/s}',
            title: '{s name=rating/headers/title}Title{/s}'
        },
        tooltips: {
            rating: '{s name=merchant/tooltips/rating}Open Rating{/s}',
        }
    },

    ratingStore: null,

    constructor: function() {
        var me = this;

        me.ratingStore = Ext.create('Ext.data.Store', {
            model: 'Shopware.apps.Index.model.Rating',
            remoteFilter: true,
            autoLoad: true,

            proxy: {
                type: 'ajax',
                url: '{url controller="widgets" action="getUnverifiedRatings"}',
                reader: {
                    type: 'json',
                    root: 'data'
                }
            }
        });

        me.callParent(arguments);
    },

    /**
     * Initializes the widget.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.tools = [{
            type: 'refresh',
            scope: me,
            handler: me.refreshView
        }];

        me.items = [{
            xtype: 'grid',
            viewConfig: {
                hideLoadingMsg: true
            },
            border: 0,
            store: me.ratingStore,
            columns: me.createColumns()
        }];

        me.createTaskRunner();
        me.callParent(arguments);


        Shopware.app.Application.on('vote-save-successfully', function () {
            me.refreshView();
        })
    },

    /**
     * Registers a new task runner to refresh
     * the store after a given time interval.
     *
     * @public
     * @param [object] store - Ext.data.Store
     * @return void
     */
    createTaskRunner: function() {
        var me = this;

        me.storeRefreshTask = Ext.TaskManager.start({
            scope: me,
            run: me.refreshView,
            interval: 300000
        });
    },

    /**
     * Helper method which will be called by the
     * task runner and when the user clicks the
     * refresh icon in the panel header.
     *
     * @public
     * @return void
     */
    refreshView: function() {
        var me = this;

        if(!me.ratingStore) {
            return false;
        }
        me.ratingStore.load();
    },

    /**
     * Helper method which creates the columns for the
     * grid panel in this widget.
     *
     * @return [array] generated columns
     */
    createColumns: function() {
        var me = this;

        return [{
            dataIndex: 'datum',
            header: me.snippets.headers.date,
            renderer: me.dateColumn,
            flex: 1.5
        }, {
            dataIndex: 'productTitle',
            header: me.snippets.headers.product,
            flex: 2.5
        }, {
            dataIndex: 'headline',
            header: me.snippets.headers.title,
            flex: 1.5
        }, {
            xtype: 'actioncolumn',
            width: 80,
            items: [{
                iconCls:'sprite-pencil',
                tooltip: me.snippets.tooltips.rating,
                handler: function(view, rowIndex, colIndex, item, event, record) {
                    Shopware.app.Application.addSubApplication({
                        name: 'Shopware.apps.Vote',
                        params: {
                            voteId: ~~(1 * record.get('id'))
                        }
                    });
                }
            }]
        }]
    },

    /**
     * Formats the date column
     *
     * @param [string] - The order time value
     * @return [string] - The passed value, formatted with Ext.util.Format.date()
     */
    dateColumn:function (value) {
        if ( value === Ext.undefined ) {
            return value;
        }

        return Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, timeFormat);
    }
});
//{/block}
