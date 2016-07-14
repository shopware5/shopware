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
 * Shopware UI - Vote view edit window
 *
 * This is the window to edit/answer to votes.
 * It contains a textarea to answer to the vote.
 */
//{block name="backend/vote/view/vote/edit"}
Ext.define('Shopware.apps.Vote.view.vote.Edit', {
    extend : 'Ext.panel.Panel',
    alias : 'widget.vote-main-edit',
    layout:'fit',
    stateful:true,
    stateId:'shopware-vote-edit',
    footerButton: false,
    cls : 'editWindow',
    title : '{s name=form/edit/title}Reply{/s}',

    initComponent: function(){
        var me = this;

        me.voteForm = me.createFormPanel();
        me.dockedItems = [{
            xtype: 'toolbar',
            ui: 'shopware-ui',
            dock: 'bottom',
            cls: 'shopware-toolbar',
            items: me.createButtons()
        }];
        me.items = [ me.voteForm];
        me.voteForm.loadRecord(me.record);

        me.callParent(arguments);
    },

    createFormPanel: function() {
        var voteForm = Ext.create('Ext.form.Panel', {
            collapsible : false,
            split : false,
            region : 'center',
            autoScroll: true,
            border: 0,
            defaults : {
                labelStyle : 'font-weight: 700; text-align: left;',
                labelWidth : 90,
                anchor : '100%'
            },
            bodyPadding : 10,
            items : [{
                xtype: 'htmleditor',
                name: 'answer',
                fieldLabel: '{s name=form_answer}Answer{/s}',
                defaultValue: '',
                supportText: '{s name=form_answer/supporttext}We do not recommend to use many formations for the text{/s}'
            }]
        });

        return voteForm;
    },

    createButtons: function(){
        var me = this,
            buttons = [{
            text : '{s name=edit_cancel}Abbrechen{/s}',
            scope : this,
            cls: 'secondary',
            handler : me.destroy
        }, {
            text : '{s name=edit_save}Speichern{/s}',
            action : 'saveVoteEdit',
            cls : 'primary'
        }];

        return buttons;
    }
});
//{/block}
