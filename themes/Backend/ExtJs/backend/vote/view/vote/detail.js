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

//{namespace name=backend/vote/main}

/**
 * Shopware UI - Vote view edit window
 *
 * This is the window to edit/answer to votes.
 * It contains some main information and a textarea to answer to the vote.
 */
//{block name="backend/vote/view/vote/detail"}
Ext.define('Shopware.apps.Vote.view.vote.Detail', {
    extend : 'Ext.panel.Panel',
    alias : 'widget.vote-main-detail',
    layout:'fit',
    border:0,
    stateful:true,
    stateId:'shopware-vote-detail',
    footerButton: false,
    cls : 'detailWindow',
    title : '{s name=form/detail/title}Details{/s}',

    initComponent: function(){
        var me = this;
        var template = me.createTemplate();

        me.infoView = me.createInfoView(template);
        me.items = [me.infoView];

        me.callParent(arguments);
    },

    createTemplate: function(){
        var me = this,
            values = me.record.data,
            template;

        template = new Ext.XTemplate(
            '<tpl for=".">',
                '<div class="info-view">',
                    '<div class="base-info">',
                        '<p>',
                        '   <b>{s name=edit_headline}Headline:{/s}</b> {literal}{headline}{/literal}',
                        '</p>',
                        '<p>',
                        '   <b>{s name=edit_author}Author:{/s}</b> {literal}{name}{/literal}',
                        '</p>',
                        '<tpl if="email">',
                            '<p>',
                            '   <b>{s name=edit_email}Email:{/s}</b> {literal}{email}{/literal}',
                            '</p>',
                        '</tpl>',
                        '<p>',
                        '   <b>{s name=edit_article}Article:{/s}</b> {literal}{articleName}{/literal}',
                        '</p>',
                        '<p>',
                        '   <b>{s name=edit_datum}Date:{/s}</b> {literal}{[this.formatDate(values.datum)]}{/literal}',
                        '</p>',
                        '<p>',
                            '<b>{s name=edit_status}Status:{/s}</b> ',
                            '<tpl if="active==1"><span style="color: green"><b>{s name=edit_statusAccepted}Accepted{/s}</b></span></tpl>',
                            '<tpl if="active==0"><span style="color: red"><b>{s name=edit_statusNotAccepted}Not accepted yet{/s}</b></span></tpl>',
                        '</p>',
                        '<p>',
                            '<b>{s name=edit_points}Points:{/s}</b> {literal}{[this.formatPoints(values.points)]}{/literal}',
                        '</p>',
                        '<p>',
                            '<b>{s name=edit_comment}Comment:{/s}</b>',
                        '<br />',
                        '{literal}{comment}{/literal}',
                        '</p>',
                    '</div>',
                '</div>',
            '</tpl>',
            {
                /**
                 * Member function which formats a date string
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
                    var html = '',
                        count = 0;

                    for(var i=0; i<points; i++){
                        if((i-points) == -0.5){
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
        );
        return template;
    },

    createInfoView: function(template) {
        var me = this,
            infoView = Ext.create('Ext.container.Container', {
                name: 'infoView',
                emptyText: 'No additional informations found',
                padding: '10px',
                renderTpl: template,
                region: 'north',
                autoScroll: true,
                itemSelector: 'b',
                renderData: me.record.data
            });

        return infoView;
    }
});
//{/block}
