// {namespace name="backend/mail_log/view/detail"}
// {block name="backend/mail_log/view/detail/window"}
Ext.define('Shopware.apps.MailLog.view.detail.Window', {

    extend: 'Shopware.window.Detail',
    alias: 'widget.mail_log-detail-window',
    height: 600,
    width: 900,
    title: '{s name=window_title}{/s}',

    createSaveButton: function () {
        return null;
    }

});
// {/block}