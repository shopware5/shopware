// {namespace name="backend/mail_log/view/list"}
// {block name="backend/mail_log/view/list/mail_log"}
Ext.define('Shopware.apps.MailLog.view.list.MailLog', {

    extend: 'Shopware.grid.Panel',
    alias: 'widget.mail_log-listing-grid',
    region: 'center',
    viewConfig: {
        markDirty: false,
    },

    configure: function () {
        var me = this;

        return {
            columns: {
                subject: {
                    header: '{s name=listing_column_header_subject}{/s}',
                    flex: 2
                },
                sentAt: {
                    header: '{s name=listing_column_header_date}{/s}',
                    flex: 1,
                    renderer: me.dateColumnRenderer
                },
                sender: {
                    header: '{s name=listing_column_header_sender}{/s}',
                    flex: 1,
                    renderer: me.contactColumnRenderer
                },
                recipients: {
                    header: '{s name=listing_column_header_recipients}{/s}',
                    flex: 2,
                    renderer: me.contactColumnRenderer
                }
            },
            addButton: false,
            deleteColumn: false,
            detailWindow: 'Shopware.apps.MailLog.view.detail.Window'
        };
    },

    registerEvents: function () {
        this.addEvents('openOrder', 'resendMailDialog');
    },

    createEditColumn: function () {
        var me = this,
            col = me.callParent(arguments);

        col.iconCls = 'sprite-magnifier-left';

        return col;
    },

    createActionColumn: function() {
        var me = this,
            column = me.callParent(arguments);

        column.align = 'right';

        return column;
    },

    createActionColumnItems: function () {
        var me = this,
            items = this.callParent(arguments);
        /*{if {acl_is_allowed privilege=resend}}*/
        items.unshift({
            action: 'resendMail',
            tooltip: '{s name=listing_column_action_resend}{/s}',
            width: 30,
            iconCls: 'sprite-mail-send',
            handler: function (view, rowIndex, colIndex, item, opts, record) {
                me.fireEvent('resendMailDialog', record);
            }
        });
        /*{/if}*/
        /*{if {acl_is_allowed resource=order privilege=read}}*/
        items.unshift({
            action: 'openOrder',
            tooltip: '{s name=listing_column_action_open_order}{/s}',
            width: 30,
            iconCls: 'sprite-sticky-notes-pin',
            handler: function (view, rowIndex, colIndex, item, opts, record) {
                me.fireEvent('openOrder', record);
            },
            getClass: function (value, metaData, record) {
                if (!record.get('order')) {
                    return 'x-hidden';
                }
            }
        });
        /*{/if}*/
        return items;
    },

    dateColumnRenderer:function (value) {
        return Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, timeFormat);
    },

    /**
     * @param { String|Array } contacts
     *
     * @returns { String }
     */
    contactColumnRenderer: function(contacts) {
        if (typeof contacts === 'string') {
            return '<a href="mailto:' + contacts + '">' + contacts + '</a>';
        }

        return contacts.map(function (contact) {
            return '<a href="mailto:' + contact.mailAddress + '">' + contact.mailAddress + '</a>';
        }).join('&nbsp;');
    }

});
// {/block}
