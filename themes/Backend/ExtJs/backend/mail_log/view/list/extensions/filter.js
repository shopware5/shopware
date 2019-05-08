// {namespace name="backend/mail_log/view/list/extensions"}
// {block name="backend/mail_log/view/list/extensions/filter"}
Ext.define('Shopware.apps.MailLog.view.list.extensions.Filter', {

    extend: 'Shopware.listing.FilterPanel',
    alias: 'widget.mail_log-listing-filter-panel',
    width: 360,
    collapsed: true,

    configure: function () {
        return {
            controller: 'MailLog',
            model: 'Shopware.apps.MailLog.model.MailLog',
            fields: {
                subject: {
                    fieldLabel: '{s name="filter_label_subject"}{/s}',
                    expression: 'LIKE',
                    valueField: 'subject'
                },
                sender: {
                    fieldLabel: '{s name="filter_label_sender"}{/s}',
                    expression: 'LIKE',
                    valueField: 'sender'
                },
                recipients: {
                    xtype: 'pagingcombobox',
                    fieldLabel: '{s name="filter_label_recipients"}{/s}',
                    expression: '=',
                    valueField: 'id',
                    displayField: 'mailAddress',
                    store: Ext.create('Shopware.apps.MailLog.store.MailLogContact')
                },
                sentAt: {
                    xtype: 'datefield',
                    fieldLabel: '{s name="filter_label_date_sent_at"}{/s}',
                    expression: '>=',
                },
                /*{if {acl_is_allowed resource=order privilege=read}}*/
                order: {
                    xtype: 'pagingcombobox',
                    fieldLabel: '{s name="filter_label_order"}{/s}',
                    expression: '=',
                    valueField: 'id',
                    displayField: 'number',
                    store: Ext.create('Shopware.apps.Order.store.Order')
                }
                /*{/if}*/
            }
        };
    }

});
// {/block}