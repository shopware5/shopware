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
 * Shopware UI - Recipients
 * View for the list of recipients
 */
//{block name="backend/newsletter_manager/view/tabs/recipients"}
Ext.define('Shopware.apps.NewsletterManager.view.tabs.Recipients', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.newsletter-manager-tabs-recipients',
    title: '{s name=recipients}Recipients{/s}',
    region: 'center',

    border: false,

    snippets : {
        columns : {
            mail: '{s name=columns/mailAddress}Mail address{/s}',
            group: '{s name=columns/group}Group{/s}',
            actions: '{s name=columns/actions}Actions{/s}',
            lastNewsletter: '{s name=columns/lastNewsletter}Last newsletter{/s}',
            doubleOptInDate: '{s name=columns/doubleOptInDate}Register date{/s}',
            doubleOptInConfirmed: '{s name=columns/doubleOptInConfirmed}Opt-In confirmed{/s}'
        }
    },

    plugins: [
        Ext.create('Ext.grid.plugin.RowEditing', {
           clicksToEdit: 2,
            autoCancel: true
        }
    )
    ],

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
             * Fired when the user clicks the "show customer" action button
             * @param record
             */
            'showCustomer',

            /**
             * Fired when the users clicks the 'delete selected' button
             */
            'deleteRecipient',


            /**
             * Fired when the user double clicks a recipient row, edits the data and clicks "update"
             * @param editor
             * @param event
             */
            'saveRecipient',


            /**
             * Fired when the user double clicks a row in order to edit it
             * @param editor
             * @param event
             */
            'beforeRecipientEdit',

            /**
             * Fired when the user clicks the 'add recipient' button
             */
            'addRecipient',

            /**
             * Fired when the user cancels editing a cell
             * @param editor
             *  @param event
             */
            'editingCanceled',

            /**
             * Fired when the users types into the search field
             * @param field
             */
            'searchRecipient'
        );

        // Define the event being fired when the row was changed
        me.on('edit', function(editor, e) {
            me.fireEvent('saveRecipient', editor, e);
        });
        me.on('beforeedit', function(editor, e) {
            /*{if !{acl_is_allowed privilege=write}}*/
            e.cancel = true;
            return;
            /*{/if}*/
            me.fireEvent('beforeRecipientEdit', editor, e)
        });
        me.on('canceledit', function(editor, e) {
            me.fireEvent('editingCanceled', editor, e)
        });
        me.on('validateedit', function(editor, event) {
            var newGroupId = event.newValues['address.groupId'],
            newMail = event.newValues['address.email'];
            if(newMail == "" || newGroupId == null) {
                event.cancel = true;
                return false;
            }
            event.cancel = false;
            return true;
        });

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
                    me.deleteRecipientButton.setDisabled(selections.length == 0);
                }
            }
        });
    },


    /**
     * Creates the grid columns
     * Data indices where chosen in order to match the database scheme for sorting in the PHP backend.
     * Therefore each Column requieres its own renderer in order to display the correct value.
     *
     * @return Array grid columns
     */
    getColumns: function() {
        var me = this;

        return [
            {
                header: me.snippets.columns.mail,
                dataIndex: 'address.email',
                renderer: function(value, metaData, record) {
                    return record.get('email');
                },
                flex: 1,
                editor: {
                    xtype: 'textfield',
                    vtype: 'remote',
                    validationUrl: '{url controller="base" action="validateEmail"}',
                    validationErrorMsg: '{s name=invalid_email namespace=backend/base/vtype}The email address entered is not valid{/s}',
                    allowBlank: true,
                    editable: true
                }
            },
            {
                header: me.snippets.columns.group,
                dataIndex: 'address.groupId',
                flex: 1,
                renderer: me.groupRenderer,
                editor: {
                    xtype: 'combobox',
                    queryMode: 'local',
                    allowBlank: true,
                    valueField: 'id',
                    displayField: 'name',
                    store : me.newsletterGroupStore,
                    editable: false

                }
            },
            {
                header: me.snippets.columns.lastNewsletter,
                dataIndex: 'lastNewsletter.subject',
                flex: 1,
                renderer: me.lastNewsletterRenderer
            },
            {
                header: me.snippets.columns.doubleOptInDate,
                dataIndex: 'added',
                flex: 1,
                xtype: 'datecolumn',
                getSortParam: function() {
                    return 'address.added';
                },
                renderer: function(value) {
                    if (!Ext.isDefined(value)) {
                        return value;
                    }
                    return Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, timeFormat);
                }
            },
            {
                header: me.snippets.columns.doubleOptInConfirmed,
                dataIndex: 'doubleOptinConfirmed',
                flex: 1,
                xtype: 'datecolumn',
                getSortParam: function() {
                    return 'address.doubleOptinConfirmed';
                },
                renderer: function(value) {
                    if (!Ext.isDefined(value)) {
                        return value;
                    }
                    return Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, timeFormat);
                }
            },
            {
                header: me.snippets.columns.actions,
                xtype : 'actioncolumn',
                width : 60,
                items : me.getActionColumn()
            }
        ];
    },


    /**
     * Returns the subject of the last read mail
     *
     * @param value
     * @param metaData
     * @param record
     */
    lastNewsletterRenderer: function(value, metaData, record) {
        var me = this,
            lastNewsletter,
            lastNewsletterId = record.get('lastNewsletterId');

        lastNewsletter = me.mailingStore.getById(lastNewsletterId);
        if(lastNewsletter instanceof Ext.data.Model) {
            return lastNewsletter.get('subject');
        }

        return '';
    },

    /**
     * Returns the group of a given record
     *
     * @param value
     * @param metaDate
     * @param record
     * @return string
     */
    groupRenderer: function(value, metaDate, record, rowIdx, colIdx, store, view){
        var me = this,
            customer = record.getCustomer(),
            group = record.getGroup(),
            isCustomer = record.get('isCustomer');

        if (!record) {
            return '';
        }

        // Recipient is a customer
        if(customer !== null && customer.first() instanceof Ext.data.Model) {
            return "<strong>" + customer.first().get('groupKey') + '{s name=customerGroup} (Customer group){/s}</strong>' ;
        }

        //  Non-Customer recipient
        group = me.newsletterGroupStore.findRecord('id', record.get('groupId'));
        if(group !== null) {
            return group.get('name');
        }

        if(group !== null && group.first() instanceof Ext.data.Model) {
            return group.first().get('name');
//            return group.first().get('id');
        }

        // If the customer wasn't found, print this in order to prevent confusion.
        if(isCustomer && !customer) {
            return 'Customer not found';
        }

        return 'Group not found';

    },

    /**
     * Returns an array of icons for the action column
     *
     * @return Array of buttons
     */
    getActionColumn : function() {
        var me = this;

        return [
            {
                iconCls:'sprite-user--plus',
                action:'view',
                tooltip:'{s name=action/showCustomer}Show customer{/s}',
                handler: function (view, rowIndex, colIndex, item, opts, record) {
                    me.fireEvent('showCustomer', record);
                },
                // Hide the "view customer" button if the current row does not contain a valid customer
                getClass: function(value, metaData, record) {
                    var customer = record.getCustomer();
                    if(customer === Ext.undefined || customer.first() === Ext.undefined) {
                        return 'x-hide-display';
                    }
                }
            }

        ];
    },

    /**
     * Creates the default toolbar and adds the deleteSelectedOrdersButton
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar: function() {
        var me = this;

        me.deleteRecipientButton = Ext.create('Ext.button.Button', {
            text: '{s name=deleteSelected}Delete selected{/s}',
            iconCls: 'sprite-minus-circle',
            disabled: true,
            handler: function() {
                var selectionModel = me.getSelectionModel(),
                    records = selectionModel.getSelection();

                if (records.length > 0) {
                    me.fireEvent('deleteRecipient', records);
                }
            }
        });

        /*{if !{acl_is_allowed privilege=delete}}*/
        me.deleteRecipientButton.hide();
        /*{/if}*/

        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            items: [
                /*{if {acl_is_allowed privilege=write}}*/
                {
                    xtype: 'button',
                    text: '{s name=addRecipient}Add recipient{/s}',
                    iconCls: 'sprite-plus-circle',
                    handler: function() {
                        me.fireEvent('addRecipient');
                    }
                },
                /*{/if}*/
                me.deleteRecipientButton,
                '->',
                {
                    xtype    : 'textfield',
                    name     : 'searchfield',
                    emptyText: '{s name=searchfield}Search{/s}',
                    cls: 'searchfield',
                    checkChangeBuffer: 700,
                    listeners: {
                        change: function(field, value) {
                            me.fireEvent('searchRecipient', field);
                        }
                    }
                }
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
