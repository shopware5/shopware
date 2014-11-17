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
 * Shopware UI - Recipient Groups
 * Configure groups of recipients
 */
//{block name="backend/newsletter_manager/view/tabs/recipient_groups"}
Ext.define('Shopware.apps.NewsletterManager.view.tabs.RecipientGroups', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.newsletter-manager-tabs-recipient-groups',
    title: '{s name=recipientGroups}Recipient groups{/s}',
    region: 'center',

    border: false,

    snippets : {
        columns : {
            groupName: '{s name=columns/groupName}Group name{/s}',
            recipients: '{s name=columns/recipients}# Recipients{/s}',
            groupId: '{s name=columns/id}ID{/s}',
            actions: '{s name=columns/actions}Actions{/s}'
        },
        numberOfGroups: '{s name=numberOfGroups}Group(s){/s}'
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

        me.features = [ me.createGroupingFeature() ];

        // register events
        me.addEvents(
            /**
             * Fired when the user clicks the 'create own recipient group' button
             * @param me.store The store of the grid
             */
            'createNewsletterGroup',

            /**
             * Fired when the user clicks the 'delete selected' button
             * @param selection model
             */
            'deleteSelected'
        );

        me.callParent(arguments);
    },

    /**
     * create the grouping feature for the grid
     * @return Ext.grid.feature.GroupingSummary
     */
    createGroupingFeature: function() {
        var me = this;

        return Ext.create('Ext.grid.feature.GroupingSummary', {
            groupHeaderTpl: Ext.create('Ext.XTemplate',
                '<span>{ name:this.formatHeader }</span>',
                '<span>&nbsp;({ rows.length } ' + me.snippets.numberOfGroups + ')</span>',
                {
                    formatHeader: function(field) {
                        if(field === false) {
                            return '{s name=ownNewsletterGroups}Own recipient groups{/s}';
                        }else{
                            return '{s name=customerGroups}Customer groups{/s}';
                        }
                    }
                }
            )
        });
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
                    var changed = 0;
                    Ext.each(selections, function(record){
                        if(record.get('isCustomerGroup') == true){
                            sm.deselect(record);
                            changed++;
                        }
                    });

                    me.deleteSelected.setDisabled(selections.length-changed == 0);
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
                header: me.snippets.columns.groupName,
                dataIndex: 'name',
                flex: 6
            },
            {
                header: me.snippets.columns.recipients,
                dataIndex: 'number',
                flex: 2
            },
            {
                header: me.snippets.columns.groupId,
                dataIndex: 'internalId',
                flex: 1
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
                    me.fireEvent('deleteSelected', [record]);
                },
                // Hide the "delete" button if the current row does not contain a valid customer
                getClass: function(value, metaData, record) {
                    if(record.get('isCustomerGroup') == true) {
                        return 'x-hide-display';
                    }
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

        me.deleteSelected = Ext.create('Ext.button.Button',{
            text: '{s name=deleteSelected}Delete selected{/s}',
            iconCls: 'sprite-minus-circle',
            disabled: true,
            handler: function() {
                var selectionModel = me.getSelectionModel(),
                    records = selectionModel.getSelection();

                if (records.length > 0) {
                    me.fireEvent('deleteSelected', records);
                }

            }
        });
        /*{if !{acl_is_allowed privilege=delete}}*/
        me.deleteSelected.hide();
        /*{/if}*/

        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            items: [
                /*{if {acl_is_allowed privilege=write}}*/
                {
                    xtype: 'button',
                    iconCls: 'sprite-plus-circle',
                    text: '{s name=createOwnNewsletterGroup}Create own newsletter group{/s}',
                    handler: function() {
                        me.fireEvent('createNewsletterGroup', me.store);
                    }

                },
                /*{/if}*/
                me.deleteSelected
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
