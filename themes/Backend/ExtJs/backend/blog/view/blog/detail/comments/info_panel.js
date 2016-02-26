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
 * @package    Blog
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/blog/view/blog}
/**
 * Shopware UI - Blog view infopanel
 *
 * This infopanel contains some information about the chosen comment.
 */
//{block name="backend/blog/view/vote/info_panel"}
Ext.define('Shopware.apps.Blog.view.blog.detail.comments.InfoPanel', {
    extend : 'Ext.form.Panel',
    alias : 'widget.blog-blog-detail-comments-info_panel',
    autoShow : true,
    name:  'infopanel',
    cls: 'detail-view',
    region: 'east',
    style:'background: #FFFFFF !important',
    split: true,
    bodyPadding: 10,
    title: '{s name=blog/detail/comments/info_panel/title}More information{/s}',
    width: 300,
    collapsible: true,
    autoScroll: true,


    initComponent: function(){
        var me = this;

        me.infoView = me.createInfoView();
        me.items = [ me.infoView ];

        me.callParent(arguments);
    },

    createInfoView: function(){
        var me = this;
        var infoView = Ext.create('Ext.view.View', {
            name: 'infoView',
            emptyText: 'No additional information found',
            tpl: me.createInfoPanelTemplate(),
            region: 'center',
            itemSelector: 'div.info-view',
            height: '100%',
            renderData: []
        });

        return infoView;
    },

    /**
     * The template for the infopanel
     */
    createInfoPanelTemplate: function(){
        return new Ext.XTemplate(
            '<tpl for=".">',
                '<div class="info-view">',
                    '<div class="base-info">',
                        '<p style="margin-top: 10px;">',
                            '<b>{s name=blog/detail/comments/info_panel/headline}Headline: {/s}</b>',
                            '<span>{literal}{headline}{/literal}</span>',
                        '</p>',
                        '<p style="margin-top: 10px;">',
                            '<b>{s name=blog/detail/comments/info_panel/author}Author: {/s}</b>',
                            '<span>{literal}{name}{/literal}</span>',
                        '</p>',
                        '<p style="margin-top: 10px;">',
                            '<b>{s name=blog/detail/comments/info_panel/email}Email: {/s}</b>',
                            '<span>{literal}{eMail}{/literal}</span>',
                        '</p>',
                        '<p style="margin-top: 10px;">',
                            '<b>{s name=blog/detail/comments/info_panel/creation_date}Creation Date: {/s}</b>',
                            '<span>{literal}{[this.formatDate(values.creationDate)]}{/literal}</span>',
                        '</p>',
                        '<p style="margin-top: 10px;">',
                            '<b>{s name=blog/detail/comments/info_panel/status}Status: {/s}</b>',
                            '<tpl if="active==1"><span style="color: green"><b>{s name=blog/detail/comments/info_panel/statusAccepted}Accepted{/s}</b></span></tpl>',
                            '<tpl if="active==0"><span style="color: red"><b>{s name=blog/detail/comments/info_panel/statusNotAccepted}Not accepted yet{/s}</b></span></tpl>',
                        '</p>',
                        '<p style="margin-top: 10px;">',
                            '<b>{s name=blog/detail/comments/info_panel/points}Points: {/s}</b>',
                            //function to create a star-rating
                            '<span>{literal}{[this.formatPoints(values.points)]}{/literal}</span>',
                        '</p>',
                        '<p style="margin-top: 10px;">',
                            '<b>{s name=blog/detail/comments/info_panel/comment}Comment: {/s}</b>',
                            '<br />',
                            '<span>{literal}{content}{/literal}</span>',
                        '</p>',
                    '</div>',
                '</div>',
            '</tpl>',
            {
                /**
                 * Member function of the template which formats a date string
                 *
                 * @param [string] value - Date string in the following format: Y-m-d H:i:s
                 * @return [string] formatted date string
                 */
                formatDate: function(value) {
                    return Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, timeFormat);
                },

                /**
                 * Function to format the points as a stars-rating
                 * @param points Contains the points
                 */
                formatPoints: function(points) {
                    var html = '';
                    var count = 0;
                    points = points/2;
                    for(var i=0; i<points; i++){
                        if((i-points) == -0.5) {
                            //create half-star
                            html = html + '<div style="height: 16px; width: 16px; display: inline-block;" class="sprite-star-half"></div>';
                        }else{
                            //create full stars
                            html = html + '<div style="height: 16px; width: 16px; display: inline-block;" class="sprite-star"></div>';
                        }
                        count++;
                    }

                    //add empty stars, so 5 stars are displayed
                    for(var i=0; i<(5-count); i++){
                        html = html + '<div style="height: 16px; width: 16px; display: inline-block;" class="sprite-star-empty"></div>';
                    }
                    return html;
                }
            }
        )
    }
});
//{/block}
