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
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/vote/main}

/**
 * Shopware UI - Vote view info panel
 *
 * This infopanel contains some information about the chosen vote.
 * For example it contains the articles name, the authors name, the rating and the comment.
 */
//{block name="backend/vote/view/vote/infopanel"}
Ext.define('Shopware.apps.Vote.view.vote.Infopanel', {
    extend : 'Ext.form.Panel',
    alias : 'widget.vote-main-infopanel',
    autoScroll  : true,
    region: 'east',
    name:  'infopanel',
    cls: 'detail-view',
    bodyPadding: 10,
    title: '{s name=infopanel/title}More information{/s}',
    width: 250,
    collapsible: true,
    dockedItems: {
        dock: 'bottom',
        xtype: 'toolbar',
        cls: Ext.baseCSSPrefix + 'info-toolbar',
        displayInfo: true,
        items: [
        {
            /*{if {acl_is_allowed privilege=accept}}*/
            iconCls: 'sprite-plus-circle',
            text: '{s name=infopanel_save}Accept{/s}',
            action: 'acceptVote',
            cls: 'small secondary',
            disabled: true
            /*{/if}*/
        }, '->', {
            /*{if {acl_is_allowed privilege=delete}}*/
            iconCls: 'sprite-minus-circle',
            text: '{s name=infopanel_cancel}Delete{/s}',
            action: 'deleteVote',
            cls: 'small secondary',
            disabled: true
            /*{/if}*/
        }]
    },

    initComponent: function(){
        var me = this;

        me.infoView = me.createInfoView();
        me.items = [me.infoView];

        me.callParent(arguments);
    },

    createInfoView: function(){
        var me = this;
        var infoView = Ext.create('Ext.view.View', {
            name: 'infoView',
            emptyText: 'No additional informations found',
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
                        '<p>',
                            '<b>{s name=infopanel_headline}Headline:{/s}</b> ',
                            '<span>{literal}{headline}{/literal}</span>',
                        '</p>',
                        '<p>',
                            '<b>{s name=infopanel_author}Author:{/s}</b> ',
                            '<span>{literal}{name}{/literal}</span>',
                        '</p>',
                        '<tpl if="email">',
                            '<p>',
                                '<b>{s name=infopanel_email}Email:{/s}</b> ',
                                '<span>{literal}{email}{/literal}</span>',
                            '</p>',
                        '</tpl>',
                        '<p>',
                            '<b>{s name=infopanel_article}Article:{/s}</b> ',
                            '<span>{literal}{articleName}{/literal}</span>',
                        '</p>',
                        '<p>',
                            '<b>{s name=infopanel_datum}Datum:{/s}</b> ',
                            '<span>{literal}{[this.formatDate(values.datum)]}{/literal}</span>',
                        '</p>',
                        '<p>',
                            '<b>{s name=infopanel_status}Status:{/s}</b> ',
                            '<tpl if="active==1"><span style="color: green"><b>{s name=infopanel_statusAccepted}Accepted{/s}</b></span></tpl>',
                            '<tpl if="active==0"><span style="color: red"><b>{s name=infopanel_statusNotAccepted}Not accepted yet{/s}</b></span></tpl>',
                        '</p>',
                        '<p>',
                            '<b>{s name=infopanel_points}Points:{/s}</b> ',
                            //function to create a star-rating
                            '<span>{literal}{[this.formatPoints(values.points)]}{/literal}</span>',
                        '</p>',
                        '<p>',
                            '<b>{s name=infopanel_comment}Comment:{/s}</b> ',
                            '<br />',
                            '<span>{literal}{comment}{/literal}</span>',
                        '</p>',
                        '<p>',
                            '<tpl if="answer"><b>{s name=infopanel_answer}Answer:{/s}</b> ',
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
                 * @param [string] value - Date string in the following format: Y-m-d H:i:s
                 * @return [string] formatted date string
                 */
                formatDate: function(value) {

                    return Ext.util.Format.date(value);
                },

                /**
                 * Function to format the points as a stars-rating
                 * @param points Contains the points
                 */
                formatPoints: function(points) {
                    var html = '';
                    var count = 0;
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
