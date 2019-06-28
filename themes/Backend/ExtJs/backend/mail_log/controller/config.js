// {namespace name="backend/mail_log/config"}
// {block name="backend/mail_log/controller/config"}
Ext.define('Shopware.apps.MailLog.controller.Config', {

    extend: 'Enlight.app.Controller',

    init: function() {
        this.control({
            'mail_log-config-tab-container': {
                save: this.onSave,
                afterrender: this.onLoad,
            },
        });
    },

    onSave: function (formPanel) {
        var form = formPanel.getForm(),
            record = form.getRecord();

        form.updateRecord(record);

        record.save({
            callback: function (record, operation) {
                if (operation.success) {
                    Shopware.Notification.createGrowlMessage(
                        '{s name="notification_save_title_success"}{/s}',
                        '{s name="notification_save_message_success"}{/s}'
                    )
                } else {
                    var message = [operation.response.status, operation.response.statusText];
                    Shopware.Notification.createGrowlMessage(
                        '{s name="notification_save_title_error"}{/s}',
                        message.join(' ')
                    )
                }
            }
        });
    },

    onLoad: function (container) {
        var me = this,
            configStore = me.getStore('Config');

        configStore.load({
            callback: function (records, operation, success) {
                if (success) {
                    container.formPanel.loadRecord(records.pop());
                } else {
                    var message = [operation.response.status, operation.response.statusText];
                    Shopware.Notification.createGrowlMessage(
                        '{s name="notification_load_title_error"}{/s}',
                        message.join(' ')
                    );
                }
            }
        });
    },

});
// {/block}