//{block name="backend/mail_log/app"}
//{block name="backend/mail_log/application"}
Ext.define('Shopware.apps.MailLog', {

    name: 'Shopware.apps.MailLog',
    extend: 'Enlight.app.SubApplication',
    loadPath: '{url action=load}',
    bulkLoad: true,

    controllers: [ 'Main', 'Config' ],

    views: [
        'list.Window',
        'list.MailLog',
        'detail.Window',
        'detail.MailLog',
        'list.extensions.Filter',
        'resend.Window',
        'resend.MailLog',
        'config.Container',
    ],

    models: [
        'MailLog',
        'MailLogContact',
        'Config',
        'Filter',
    ],

    stores: [
        'MailLog',
        'MailLogContact',
        'Config',
        'Filter',
    ],

    launch: function() {
        return this.getController('Main').mainWindow;
    }

});
//{/block}
//{/block}