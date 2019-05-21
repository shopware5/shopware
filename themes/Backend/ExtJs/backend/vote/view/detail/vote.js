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

//{block name="backend/vote/view/detail/vote"}
Ext.define('Shopware.apps.Vote.view.detail.Vote', {
    extend: 'Shopware.model.Container',
    alias: 'widget.vote-detail-container',
    padding: 20,
    mixins: {
        helper: 'Shopware.apps.Vote.view.PointHelper'
    },
    flex: 1,
    layout: 'anchor',
    defaults: {
        anchor: '100%'
    },

    configure: function() {
        var me = this;

        return {
            controller: 'Vote',
            splitFields: false,
            fieldSets: [{
                title: '{s name="meta"}{/s}',
                fields: {
                    active: {
                        fieldLabel: '{s name="active"}{/s}'
                    },
                    shopId: {
                        fieldLabel: '{s name="shop"}{/s}'
                    },
                    articleName: {
                        xtype: 'displayfield',
                        fieldLabel: '{s name="article"}{/s}'
                    }
                }
            }, {
                title: '{s name="content"}{/s}',
                fields: {
                    points: {
                        xtype: 'displayfield',
                        fieldLabel: '{s name="points"}{/s}',
                        renderer: this.pointRenderer,
                        scope: me
                    },
                    datum: {
                        xtype: 'displayfield',
                        fieldLabel: '{s name="date"}{/s}',
                        renderer: me.dateRenderer
                    },
                    email: {
                        xtype: 'displayfield',
                        fieldLabel: '{s name="email"}{/s}'
                    },
                    name: {
                        xtype: 'displayfield',
                        fieldLabel: '{s name="author"}{/s}',
                        renderer: me.nameRenderer
                    },
                    headline: {
                        xtype: 'displayfield',
                        fieldLabel: '{s name="headline"}{/s}'
                    },
                    comment: {
                        xtype: 'displayfield',
                        fieldLabel: '{s name="comment"}{/s}'
                    }
                }
            }, {
                title: '{s name="answer"}{/s}',
                fields: {
                    answer_date: {
                        xtype: 'displayfield',
                        fieldLabel: '{s name="answer_date"}{/s}',
                        renderer: me.dateRenderer
                    },
                    answer: {
                        margin: '20 0',
                        xtype: 'tinymce',
                        fieldLabel: ''
                    }
                }
            }]
        };
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
    },


    dateRenderer: function(value) {
        if (!value) {
            return '';
        }
        return Ext.util.Format.date(value);
    },

    pointRenderer: function(value, record) {
        var me = this;
        return me.renderPoints(value);
    }
});
//{/block}
