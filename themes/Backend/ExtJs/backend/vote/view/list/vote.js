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
 * @package    Vote
 * @subpackage App
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/vote/main}

//{block name="backend/vote/view/list/vote"}
Ext.define('Shopware.apps.Vote.view.list.Vote', {
    extend: 'Shopware.grid.Panel',
    alias:  'widget.vote-listing-grid',
    region: 'center',
    mixins: {
        helper: 'Shopware.apps.Vote.view.PointHelper'
    },
    configure: function() {
        return {
            addButton: false,
            displayProgressOnSingleDelete: false,
            detailWindow: 'Shopware.apps.Vote.view.detail.Window',
            columns: {
                active: {
                    header: '{s name="active"}{/s}'
                },
                shopId: {
                    header: '{s name="shop"}{/s}'
                },
                articleId: {
                    header: '{s name="article"}{/s}'
                },
                datum: {
                    header: '{s name="date"}{/s}'
                },
                name: {
                    header: '{s name="author"}{/s}',
                    renderer: this.nameRenderer
                },
                headline: {
                    header: '{s name="headline"}{/s}'
                },
                points: {
                    header: '{s name="points"}{/s}',
                    renderer: this.pointRenderer
                }
            }
        };
    },

    initComponent: function() {
        var me = this;
        me.callParent(arguments);

        me.on('vote-selection-changed', function(grid, selModel, selection) {
            var hasInactive = false;
            Ext.each(selection, function(item) {
                if (!item.get('active')) {
                    hasInactive = true;
                    return false;
                }
            });
            me.acceptButton.setDisabled(!hasInactive);
        });
    },

    createActionColumnItems: function() {
        var me = this;
        var items = me.callParent(arguments);

        var item = {
            iconCls: 'sprite-plus-circle-frame',
            text: '{s name="column/actioncolumn/add"}{/s}',
            getClass: function(value, metadata, record) {
                if (record.get('active')) {
                    return 'x-hidden';
                }
            },
            handler: function(view, rowIndex, colIndex, item, opts, record) {
                me.activateVote({ }, record, function() {
                    me.getStore().load();
                });
            }
        };
        items = Ext.Array.insert(items, 0, [item]);
        return items;
    },

    createToolbarItems: function() {
        var me = this;
        var items = me.callParent(arguments);

        me.acceptButton = Ext.create('Ext.button.Button', {
            disabled: true,
            iconCls: 'sprite-plus-circle-frame',
            text: '{s name="column/actioncolumn/add"}{/s}',
            handler: function() {
                me.acceptVotes(me.selModel.getSelection());
            }
        });

        items = Ext.Array.insert(items, 0, [me.acceptButton]);
        return items;
    },

    acceptVotes: function(records) {
        var me = this;

        Shopware.app.Application.on('activate-vote', me.activateVote);
        Ext.create('Shopware.apps.Vote.view.list.Progress', {
            configure: function() {
                return {
                    tasks: [{
                        event: 'activate-vote',
                        data: me.getInactiveRecords(records),
                        text: '{s name="progress_bar"}{/s}'
                    }],
                    infoText: '{s name="progress_info"}{/s}'
                }
            },
            listeners: {
                'close': function() {
                    me.getStore.load();
                }
            }
        }).show();
    },

    activateVote: function(task, record, callback) {
        record.set('active', true);
        record.save({
            callback: function(record, operation) {
                callback(record, operation);
            }
        });
    },

    getInactiveRecords: function(records) {
        return Ext.Array.filter(records, function(record) {
            return !record.get('active');
        });
    },

    pointRenderer: function(value) {
        return this.renderPoints(value);
    },

    /**
     * Function to replace an empty name
     * @param { string } value
     * @return { string }
     */
    nameRenderer: function(value) {
        if (!value) {
            return '{s name="DetailCommentAnonymousName" namespace="frontend/detail/comment"}{/s}';
        }
        return value;
    }
});
//{/block}
