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

//{block name="backend/vote/view/list/extensions/info"}
Ext.define('Shopware.apps.Vote.view.list.extensions.Info', {
    extend: 'Shopware.listing.InfoPanel',
    alias:  'widget.vote-listing-info-panel',
    width: 320,
    mixins: {
        helper: 'Shopware.apps.Vote.view.PointHelper'
    },
    configure: function() {
        return {
            model: 'Shopware.apps.Vote.model.Vote'
        };
    },

    createTemplate: function() {
        var me = this;

        var rowStyle = ' style="padding: 5px 0;"';

        return new Ext.XTemplate(
            '<tpl for=".">',
                '<div class="info-view">',
                    '<div class="base-info">',
                        '<p '+rowStyle+'>',
                            '<b>{s name=headline}{/s}:</b> ',
                            '<span>{literal}{headline}{/literal}</span>',
                        '</p>',
                        '<p'+rowStyle+'>',
                            '<b>{s name=author}{/s}:</b> ',
                            '<span>{literal}{[this.nameRenderer(values.name)]}{/literal}</span>',
                        '</p>',
                        '<tpl if="email">',
                            '<p'+rowStyle+'>',
                                '<b>{s name=email}{/s}:</b> ',
                                '<span>{literal}{email}{/literal}</span>',
                            '</p>',
                        '</tpl>',
                        '<p'+rowStyle+'>',
                            '<b>{s name=article}{/s}:</b> ',
                            '<span>{literal}{articleName}{/literal}</span>',
                        '</p>',
                        '<p'+rowStyle+'>',
                            '<b>{s name=date}{/s}:</b> ',
                            '<span>{literal}{[this.formatDate(values.datum)]}{/literal}</span>',
                        '</p>',
                        '<p'+rowStyle+'>',
                            '<b>{s name=active}{/s}:</b> ',
                            '<tpl if="active==1"><span style="color: green"><b>{s name=active}{/s}</b></span></tpl>',
                            '<tpl if="active==0"><span style="color: red"><b>{s name=not_accepted}{/s}</b></span></tpl>',
                        '</p>',
                        '<p'+rowStyle+'>',
                            '<b>{s name=points}{/s}:</b> ',
                            //function to create a star-rating
                            '<span>{literal}{[this.formatPoints(values.points)]}{/literal}</span>',
                        '</p>',
                        '<p'+rowStyle+'>',
                            '<b>{s name=comment}{/s}:</b> ',
                            '<br />',
                            '<span>{literal}{comment}{/literal}</span>',
                        '</p>',
                        '<p'+rowStyle+'>',
                            '<tpl if="answer"><b>{s name=answer}{/s}:</b> ',
                            '<br />',
                            '<span>{literal}{answer}{/literal}</span></tpl>',
                        '</p>',
                    '</div>',
                '</div>',
            '</tpl>',
            {
                /**
                 * Member function of the template which formats a date string
                 *
                 * @param { string } value - Date string in the following format: Y-m-d H:i:s
                 * @return { string } formatted date string
                 */
                formatDate: function(value) {
                    return Ext.util.Format.date(value);
                },

                /**
                 * Function to format the points as a stars-rating
                 * @param { int } points Contains the points
                 * @return { string }
                 */
                formatPoints: function(points) {
                    return me.renderPoints(points);
                },

                /**
                 * Function to replace an empty name
                 * @param { string } value
                 * @return { string }
                 */
                nameRenderer: function(value) {
                    if (!value) {
                        return '{s name=DetailCommentAnonymousName namespace=frontend/detail/comment}{/s}';
                    }
                    return value;
                }
            }
        )

    }
});
//{/block}
