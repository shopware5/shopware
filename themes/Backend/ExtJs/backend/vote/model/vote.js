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

//{block name="backend/vote/model/vote"}
Ext.define('Shopware.apps.Vote.model.Vote', {
    extend: 'Shopware.data.Model',

    configure: function() {
        return {
            controller: 'Vote',
            detail: 'Shopware.apps.Vote.view.detail.Vote'
        };
    },

    fields: [
        //{block name="backend/vote/model/vote/fields"}{/block}
        { name: 'id', type : 'int' },
        { name: 'active', type : 'boolean' },
        { name: 'shopId', type: 'int', useNull: true },
        { name: 'articleId', type: 'int' },
        { name: 'points', type: 'float' },

        { name: 'name', type: 'string' },
        { name: 'email', type: 'string' },
        { name: 'headline', type: 'string' },
        { name: 'comment', type: 'string' },
        { name: 'answer', type: 'string' },
        { name: 'answer_date', type : 'date', useNull: true },
        { name: 'datum', type : 'date' },
        {
            name: 'articleName',
            type: 'string',
            convert: function(value, record) {
                if (record && record.raw && record.raw.article) {
                    return record.raw.article.name;
                }
                return value;
            }
        }
    ],

    associations: [
    //{block name="backend/vote/model/vote/associations"}{/block}
    {
        relation: 'ManyToOne',
        field: 'shopId',

        type: 'hasMany',
        model: 'Shopware.apps.Base.model.Shop',
        name: 'getShop',
        associationKey: 'shop'
    }, {
        relation: 'ManyToOne',
        field: 'articleId',

        type: 'hasMany',
        model: 'Shopware.apps.Base.model.Article',
        name: 'getArticle',
        associationKey: 'article'
    }]
});
//{/block}
