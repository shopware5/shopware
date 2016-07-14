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
 * Shopware UI - Overview
 * View for existing newsletters
 */
//{block name="backend/newsletter_manager/view/tabs/overview"}
Ext.define('Shopware.apps.NewsletterManager.view.tabs.Overview', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.newsletter-manager-tabs-overview',
    title: '{s name=overview}Overview{/s}',
    region: 'center',

    border: false,

    snippets : {
        columns : {
            date: '{s name=columns/date}Date{/s}',
            subject: '{s name=columns/subject}Subject{/s}',
            state: '{s name=columns/state}State{/s}',
            recipients: '{s name=columns/recipients}# Recipients{/s}',
            read: '{s name=columns/read}# read{/s}',
            clicked: '{s name=columns/clicked}# clicked{/s}',
            revenue: '{s name=columns/revenue}Revenue{/s}',
            actions: '{s name=columns/actions}Actions{/s}',
            timedDelivery: '{s name=columns/timed_delivery}Timed delivery{/s}',
            active: '{s name=columns/active}Released for cronjob{/s}'
        },
        error: {
            active_title: '{s name=error/active_title}Error - can\'t toggle to released{/s}',
            active_text: '{s name=error/active_text}A delivered newsletter can\'t be unreleased.{/s}',
            privilege_title: '{s name=error/privilege_title}Missing rights{/s}',
            privilege_text: '{s name=error/privilege_text}You need write rules to change this field.{/s}'
        }
    },

    /**
     * Initializes the component, sets up toolbar and pagingbar and and registers some events
     *
     * @return void
     */
    initComponent: function() {
        var me = this;
        me.columns = me.getColumns();
        me.tbar = me.getToolbar();
        me.bbar = me.getPagingbar();

        // register events
        me.addEvents(
            /**
             * Fired when the users chooses to create a new newsletter
             */
            'createNewNewsletter',

            /**
             * Fired when the user clicks the "edit mail" button in the action column
             * @param record
             */
            'editNewsletter',

            /**
             * Fired when the user presses the send button in the action column
             * @param record
             */
            'startSendingNewsletter',

            /**
             * Fired when the user clicks the 'delete' button in the action column
             * @param record
             */
            'deleteNewsletter',


            /**
             * Fired when the honorable user chooses to duplicate a newsletter
             * @param record
             */
            'duplicateNewsletter',

            /**
             * Fired when the user types into the newsletter-search field
             * @param field
             */
            'searchNewsletter'
        );

        me.callParent(arguments);
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
                header: me.snippets.columns.date,
                dataIndex: 'mailing.date',
                renderer: function(value, metaData, record) {
                    return Ext.util.Format.date(record.get('date'));
                },
                flex: 1
            },
            {
                header: me.snippets.columns.subject,
                dataIndex: 'mailing.subject',
                flex: 2,
                renderer: function(value, metaData, record) {
                    return '<strong>' + record.get('subject') + '</strong>';
                }
            },
            {
                header: me.snippets.columns.state,
                dataIndex: 'mailing.status',
                flex: 2,
                renderer: function(value, metaData, record){
                    var me = this,
                        addresses = record.get('addresses'),
                        recipients = record.get('recipients'),
                        status = record.get('status'),
                        active = record.get('active');

                    var timedDelivery = Date.parse(record.get('timedDelivery'));
                    var currentDate = new Date();

                    if(status == 1){
                        if (currentDate < timedDelivery) {
                            return '{s name=state/willBeSent}Will be send{/s}'
                        }

                        var done = addresses,
                            percentage = 0;
                        if(done > 0) {
                            if(!recipients || !recipients > 0) {
                                percentage = 0;
                            }else{
                                percentage = done/recipients*100;
                            }
                            // sw-3197: Percentage might become > 100% if recipients are deleted after sending a mail
                            // as the recipient-count in the mail data is constant
                            if(percentage > 100) {
                                percentage = 100;
                            }
                        }
                        return Ext.String.format('{s name=state/percentage}{literal}{0}{/literal}% mails sent{/s}', percentage.toFixed(0));
                    }
                    if(status == 2){
                        return '{s name=state/sendingDone}All mails sent{/s}'
                    }

                    return '{s name=state/notSending}Not sending{/s}';

                }
            },
            {
                header: me.snippets.columns.recipients,
                dataIndex: 'mailing.recipients',
                renderer: function(value, metaData, record) {
                    return record.get('recipients');
                },
                flex: 1
            },
            {
                header: me.snippets.columns.read,
                dataIndex: 'mailing.read',
                renderer: function(value, metaData, record) {
                    return record.get('read');
                },
                flex: 1
            },
            {
                header: me.snippets.columns.clicked,
                dataIndex: 'mailing.clicked',
                renderer: function(value, metaData, record) {
                    return record.get('clicked');
                },
                flex: 1
            },
            {
                header: me.snippets.columns.revenue,
                dataIndex: 'revenue',
                flex: 1,
                sortable: false
            },
            {
                xtype: 'actioncolumn',
                header: me.snippets.columns.active,
                dataIndex: 'mailing.status',
                flex: 1,
                items: [{
                    getClass: function (value, metaData, record) {
                        value = record.get('status');

                        if (value == 0) {
                            return 'sprite-ui-check-box-uncheck';
                        } else {
                            return 'sprite-ui-check-box';
                        }
                    },
                    handler: function(grid, rowIndex, colIndex, item, eOpts, record) {
                        /*{if {acl_is_allowed privilege=write}}*/
                            if (record.get('status') == 2) {
                                Shopware.Notification.createGrowlMessage(me.snippets.error.active_title, me.snippets.error.active_text);
                                return false;
                            }

                            if (record.get('status') == 1) {
                                Ext.Msg.show({
                                    title:'{s name=cancel_sending/title}Cancel sending{/s}',
                                    msg: '{s name=cancel_sending/msg}Do you want to cancel the sending of the newsletter?{/s}',
                                    buttons: Ext.Msg.YESNO,
                                    icon: Ext.Msg.QUESTION,
                                    fn: function(response) {
                                        if(response == 'yes') {
                                            me.fireEvent('releaseNewsletter', record, grid, rowIndex);
                                        } else {
                                            return false;
                                        }
                                    }
                                });
                            } else {
                                me.fireEvent('releaseNewsletter', record, grid, rowIndex);
                            }
                        /*{else}*/
                            Shopware.Notification.createGrowlMessage(me.snippets.error.privilege_title, me.snippets.error.privilege_text);
                        /*{/if}*/


                    }
                }]
            },
            {
                header: me.snippets.columns.timedDelivery,
                dataIndex: 'mailing.timedDelivery',
                width: 110,
                renderer: function(value, metaData, record) {
                    value = record.get('timedDelivery');

                    if (!value) {
                        return '{s name=grid/send_immediately}Immediately{/s}';
                    }
                    return Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, timeFormat);
                }
            },
            {
                header: me.snippets.columns.actions,
                xtype : 'actioncolumn',
                width : 120,
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
            /*{if {acl_is_allowed privilege=write}}*/
            {
                iconCls:'sprite-mail--pencil',
                action:'view',
                tooltip:'{s name=action/edit}Edit newsletter{/s}',
                handler: function (view, rowIndex, colIndex, item, opts, record) {
                    me.fireEvent('editNewsletter', record);
                }
            },
            /*{/if}*/
            /*{if {acl_is_allowed privilege=delete}}*/
            {
                iconCls:'sprite-minus-circle-frame',
                action:'delete',
                tooltip:'{s name=action/deleteNewsletter}Delete newsletter{/s}',
                handler: function (view, rowIndex, colIndex, item, opts, record) {
                    me.fireEvent('deleteNewsletter', record);
                }
            },
            /*{/if}*/

//            {
//                iconCls:'sprite-documents',
//                action:'duplicate',
//                tooltip:'{s name=action/duplicateNewsletter}Duplicate newsletter{/s}',
//                handler: function (view, rowIndex, colIndex, item, opts, record) {
//                    me.fireEvent('duplicateNewsletter', record);
//                }
//            },
            /*{if {acl_is_allowed privilege=write}}*/
            {
                iconCls:'sprite-mail-send',
                action:'view',
                tooltip:'{s name=action/sendNewsletter}Send the newsletter{/s}',
                handler: function (view, rowIndex, colIndex, item, opts, record) {
                    me.fireEvent('startSendingNewsletter', record);
                },
                // Hide the "send" button if the current row does not contain a valid customer
                getClass: function(value, metaData, record) {
                    //The user cannot send newsletter which are already send
                    var status = record.get('status');
                    if(status > 0 && status != 3) {
                        return 'x-hide-display';
                    }

                    //The user cannot send newsletter which should be send in the future
                    var timedDelivery = Date.parse(record.get('timedDelivery'));
                    var currentDate = new Date();
                    currentDate = Date.parse(currentDate);

                    if (currentDate < timedDelivery) {
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

        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            items: [
                /*{if {acl_is_allowed privilege=write}}*/
                {
                    xtype: 'button',
                    text: '{s name=createNewNewsletter}Create new newsletter{/s}',
                    iconCls: 'sprite-plus-circle',
                    handler: function() {
                        me.fireEvent('createNewNewsletter');
                    }

                },
                /*{/if}*/
                '->',
                {
                    xtype    : 'textfield',
                    name     : 'searchfield',
                    emptyText: '{s name=searchfield}Search{/s}',
                    cls: 'searchfield',
                    checkChangeBuffer: 700,
                    listeners: {
                        change: function(field, value) {
                            me.fireEvent('searchNewsletter', field);
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
     * @return Array
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
