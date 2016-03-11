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
 * @package    PluginManager
 * @subpackage Detail
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/plugin_manager/translation}

//{block name="backend/plugin_manager/view/detail/comments"}
Ext.define('Shopware.apps.PluginManager.view.detail.Comments', {
    extend: 'Ext.container.Container',
    commentCount: 0,

    mixins: {
        events: 'Shopware.apps.PluginManager.view.PluginHelper'
    },

    initComponent: function() {
        var me = this, items = [];

        me.commentCount = 0;

        if (!me.plugin) {
            me.callParent(arguments);
            return;
        }

        if (!me.plugin['getCommentsStore']) {
            me.callParent(arguments);
            return;
        }

        me.commentCount = me.plugin['getCommentsStore'].getCount();

        if (me.commentCount <= 0) {
            me.callParent(arguments);
            return;
        }

        items.push({
            xtype: 'component',
            cls: 'headline',
            html: '{s name="customer_rating_for"}Customer rating for{/s} ' + me.plugin.get('label')
        });

        items.push({
            xtype: 'component',
            cls: 'rating-average',
            html:  '<div class="label">{s name="rating_average"}Average customer rating:{/s}</div>' +
                    '<div class="store-plugin-rating star' + me.plugin.get('rating') + '">&nbsp;</div>' +
                    '<div class="suffix">('+ me.commentCount +' {s name="rating"}customer reviews{/s})</div>'
        });

        if (me.plugin['getCommentsStore']) {
            me.plugin['getCommentsStore'].each(function(item) {
                items.push(me.createCommentItem(item));
            });
        }

        me.items = items;

        me.callParent(arguments);
    },

    getCommentCount: function() {
        return this.commentCount;
    },

    createCommentItem: function(comment) {
        var me = this;

        var date = me.formatDate(comment.get('creationDate').date);

        var left = Ext.create('Ext.container.Container', {
            cls: 'comment-left',
            defaults: { xtype: 'component' },
            items: [{
                cls: 'store-plugin-rating star' + comment.get('rating'),
                html: '&nbsp'
            }, {
                cls: 'comment-name',
                html: '{s name="rating_author"}From{/s}: ' + comment.get('author')
            }, {
                cls: 'comment-date',
                html: Ext.util.Format.date(date) + ' ' + Ext.util.Format.date(date, timeFormat)
            }]
        });

        var right = Ext.create('Ext.container.Container', {
            cls: 'comment-right',
            defaults: { xtype: 'component' },
            items: [{
                cls: 'comment-headline',
                html: comment.get('headline')
            }, {
                cls: 'comment-text',
                html: comment.get('text')
            }]
        });

        return Ext.create('Ext.container.Container', {
            cls: 'store-plugin-comment',
            items: [ left, right ]
        });
    }
});
//{/block}