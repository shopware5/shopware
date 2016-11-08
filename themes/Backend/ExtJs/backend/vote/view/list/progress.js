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

//{block name="backend/vote/view/list/progress"}
Ext.define('Shopware.apps.Vote.view.list.Progress', {
    extend: 'Shopware.window.Progress',
    width: 800,
    title: '{s name="progress_title"}{/s}',
    requestResultTitle: '{s name="result"}{/s}',

    mixins: {
        helper: 'Shopware.apps.Vote.view.PointHelper'
    },

    createResultStore: function() {
        return Ext.create('Ext.data.Store', {
            model: 'Shopware.apps.Vote.model.AcceptResponse'
        });
    },

    createResponseRecord: function(vote, operation) {
        var record = this.callParent(arguments);
        var article = '';

        try {
            if (vote && vote.raw && vote.raw.article) {
                article = vote.raw.article.name;
            }
            return Ext.create('Shopware.apps.Vote.model.AcceptResponse', {
                success: record.get('success'),
                article: article,
                author: vote.get('name'),
                headline: vote.get('headline'),
                points: vote.get('points'),
                error: record.get('error')
            });
        } catch (e) {
            return record;
        }
    },

    createResultGridColumns: function() {
        var me = this;

        return [
            { xtype: 'rownumberer', width: 30 },
            { header: me.successHeader, dataIndex: 'success', width: 60, renderer: me.successRenderer },
            { header: '{s name="article"}{/s}', dataIndex: 'article', flex: 1 },
            { header: '{s name="result"}{/s}', dataIndex: 'result', renderer: me.resultRenderer, scope: me, flex: 3 }
        ];
    },

    resultRenderer: function(value, meta, record) {
        var me = this, html;

        if (!record.get('success')) {
            return record.get('error');
        }

        html = me.renderPoints(record.get('points'));
        html += '<span style="line-height: 15px; padding-left: 10px;"><b>' + record.get('author') + '</b> - ' + record.get('headline') + '</span>';
        return html;
    }
});
//{/block}
