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
 * @package    NewsletterManager
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name="backend/newsletter_manager/main"}

/**
 * Shopware UI - Sender
 * Configure available sender
 */
//{block name="backend/newsletter_manager/view/tabs/sender"}
Ext.define('Shopware.apps.NewsletterManager.view.tabs.Sender', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.newsletter-manager-tabs-sender',
    title: '{s name=sender}Sender{/s}',
    region: 'center',

    border: false,

    snippets : {
        columns : {
            mail: '{s name=columns/mailAddress}Mail address{/s}',
            senderName: '{s name=columns/senderName}Sender\'s name{/s}',
            actions: '{s name=columns/actions}Actions{/s}'
        }
    },

    /**
     * Initializes the component, sets up toolbar and pagingbar and and registers some events
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.selModel = me.getGridSelModel();
        me.columns = me.getColumns();
        me.tbar = me.getToolbar();
        me.bbar = me.getPagingbar();

        // register events
        me.addEvents(
            /**
             * Fired when the user clicks the "delete" action button or the "deleteSelected" button
            */
            'deleteSender',

            /**
             *  Fired when the user clicks the "edit" action button
             */
            'editSender',

            /**
             * Fired when the user clicks the "create new sender" button in the toolbar
             */
            'createNewSender'
        );

        me.callParent(arguments);
    },

    /**
     * Return the selection model for this grid.
     *
     * @return Ext.selection.CheckboxModel
     */
    getGridSelModel : function() {
        var me = this;
        return Ext.create('Ext.selection.CheckboxModel', {
            listeners:{
                // Unlocks the delete button if the user has checked at least one checkbox
                selectionchange:function (sm, selections) {
                    me.deleteSenderButton.setDisabled(selections.length === 0);
                }
            }
        });
    },


    /**
     * Creates the grid columns
     * Data indices where chosen in order to match the database scheme for sorting in the PHP backend.
     * Therefore each Column requieres its own renderer in order to display the correct value.
     *
     * @return [array] grid columns
     */
    getColumns: function() {
        var me = this;

        return [
            {
                header: me.snippets.columns.mail,
                dataIndex: 'sender.email',
                flex: 1,
                renderer: function(value, metaData, record) {
                    return record.get('email');
                }
            },
            {
                header: me.snippets.columns.senderName,
                dataIndex: 'sender.name',
                flex: 1,
                renderer: function(value, metaData, record) {
                    return record.get('name');
                }
            },
            {
                header: me.snippets.columns.actions,
                xtype : 'actioncolumn',
                width : 80,
                items : me.getActionColumn()
            }
        ];
    },


    /**
     * Returns an array of icons for the action column
     *
     * @return Array of buttons
     */
    getActionColumn : function() {
        var me = this;

        return [
            /*{if {acl_is_allowed privilege=delete}}*/
            {
                iconCls:'sprite-minus-circle-frame',
                action:'delete',
                tooltip:'{s name=action/delete}Delete{/s}',
                handler: function (view, rowIndex, colIndex, item, opts, record) {
                    me.fireEvent('deleteSender', [record]);
                }
            },
            /*{/if}*/
            /*{if {acl_is_allowed privilege=write}}*/
            {
                iconCls:'sprite-pencil',
                action:'view',
                tooltip:'{s name=action/edit}Edit mail{/s}',
                handler: function (view, rowIndex, colIndex, item, opts, record) {
                    me.fireEvent('editSender', record);
                }
            }
            /*{/if}*/

        ];
    },

    /**
     * Creates the default toolbar and adds the deleteSelectedOrdersButton
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar: function() {
        var me = this;

        me.deleteSenderButton = Ext.create('Ext.button.Button', {
            text: '{s name=deleteSelected}Delete selected{/s}',
            disabled: true,
            iconCls: 'sprite-minus-circle',
            handler: function() {
                var selectionModel = me.getSelectionModel(),
                    records = selectionModel.getSelection();

                if (records.length > 0) {
                    me.fireEvent('deleteSender', records);
                }
            }
        });
        /*{if !{acl_is_allowed privilege=delete}}*/
        me.deleteSenderButton.hide();
        /*{/if}*/

        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            items: [
                /*{if {acl_is_allowed privilege=write}}*/
                {
                    xtype: 'button',
                    iconCls: 'sprite-plus-circle',
                    text: '{s name=createNewSender}Create new sender{/s}',
                    handler: function () {
                        me.fireEvent('createNewSender');
                    }

                },
                /*{/if}*/
                me.deleteSenderButton
            ]
        });

        return me.toolbar;

    },

    /**
     * Creates pagingbar
     *
     * @return Ext.toolbar.Paging
     */
    getPagingbar: function() {
        var me = this;

        return [{
            xtype: 'pagingtoolbar',
            displayInfo: true,
            store: me.store
        }];
    }
});
//{/block}
