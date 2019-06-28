// {namespace name="backend/mail_log/view/detail"}
// {block name="backend/mail_log/view/detail/mail_log"}
Ext.define('Shopware.apps.MailLog.view.detail.MailLog', {

    extend: 'Shopware.model.Container',
    padding: 20,
    contentFieldGenerated: false,
    layout: {
        type: 'vbox',
        align : 'stretch',
        pack  : 'start',
    },
    mixins: {
        form: 'Shopware.apps.MailLog.view.detail.FormMixin',
    },

    configure: function () {
        var me = this;

        return {
            controller: 'MailLog',
            fieldSets: me.createFieldSets({
                editableFields: false,
            }),
        };
    },
});
// {/block}