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
 * Shopware UI - Vote view toolbar
 *
 * The toolbar contains a button to delete all marked entries and to accept all marked entries.
 */
//{block name="backend/vote/view/vote/toolbar"}
Ext.define('Shopware.apps.Vote.view.vote.Toolbar', {
    extend : 'Ext.toolbar.Toolbar',
    alias : 'widget.vote-main-toolbar',
    autoShow : true,
    region: 'north',
    ui: 'shopware-ui',

    initComponent: function(){
        var me = this;

        var searchField = me.createSearchField();
        me.items = me.createItems(searchField);

        me.callParent(arguments);
    },

    createSearchField: function(){
        var searchField = Ext.create('Ext.form.field.Text',{
            name : 'searchfield',
            cls : 'searchfield',
            action : 'searchVotes',
            width : 170,
            enableKeyEvents : true,
            emptyText : '{s name=toolbar/search}Search...{/s}',
            listeners: {
                buffer: 500,
                //needed to create an own event with a buffer
                keyup: function() {
                    if(this.getValue().length >= 3 || this.getValue().length<1) {
                        this.fireEvent('fieldchange', this);
                    }
                }
            }
        });
        searchField.addEvents('fieldchange');

        return searchField;
    },

    createItems: function(searchField){
        var me = this;

        var buttons = [];
        /*{if {acl_is_allowed privilege=accept}}*/
        buttons.push(Ext.create('Ext.button.Button', {
            iconCls: 'sprite-plus-circle',
            text: '{s name=toolbar/accept}Accept marked entries{/s}',
            disabled: true,
            action: 'acceptMultipleVotes'
        }));
        /*{/if}*/

        /*{if {acl_is_allowed privilege=delete}}*/
        buttons.push(Ext.create('Ext.button.Button',{
            iconCls: 'sprite-minus-circle',
            text: '{s name=toolbar/delete}Delete marked entries{/s}',
            disabled: true,
            action: 'deleteMultipleVotes'
        }));
        /*{/if}*/


        var items = buttons;
        items.push('->');
        items.push(searchField);


        return items;
    }
});
//{/block}
