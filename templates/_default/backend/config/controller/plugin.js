/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * Shopware Controller - Config backend module
 *
 * todo@all: Documentation
 */
//{block name="backend/config/controller/plugin"}
Ext.define('Shopware.apps.Config.controller.Plugin', {

    extend: 'Enlight.app.Controller',

    views: [
        'form.Plugin',
        'plugin.Detail',
        'plugin.Table',
        'plugin.ManualInstall'
    ],

    stores:[
        'form.Plugin'
    ],

    models:[
        'form.Plugin'
    ],

    plugin: null,

    refs: [
        { ref: 'window', selector: 'config-main-window' },
        { ref: 'table', selector: 'config-plugin-table' },
        { ref: 'installButton', selector: 'config-plugin-table button[action=install]' },
        { ref: 'uninstallButton', selector: 'config-plugin-table button[action=uninstall]' },
        { ref: 'deleteButton', selector: 'config-plugin-table button[action=delete]' }
    ],

    init: function () {
        var me = this;
        me.control({
            'config-plugin-table': {
                selectionchange: me.onChangePlugin,
                itemdblclick: me.onOpenConfigForm,
                installPlugin: me.onButtonClick,
                uninstallPlugin: me.onButtonClick,
                updatePlugin: me.onButtonClick,
                editPlugin: me.onOpenConfigForm
            },
            'config-plugin-table button[action=install]': {
                click: me.onButtonClick
            },
            'config-plugin-table button[action=uninstall]': {
                click: me.onButtonClick
            },
            'config-plugin-table combobox[name=filter]': {
                select: me.onSelectFilter
            },
            'config-plugin-table button[action=upload]': {
                click: me.onOpenUploadPlugin
            },
            'config-plugin-manual-install': {
                uploadPlugin: me.onUploadPlugin
            }
        });

        me.getController('Form');

        me.callParent(arguments);
    },

    onButtonClick: function(button) {
        var me = this,
            win = me.getWindow(),
            store = me.plugin.store,
            plugin = me.plugin,
            title,
            message = '{s name=proceed_action_title}Do you want to proceed with this action?{/s}';

        switch (button.action) {
            case 'uninstall':
                title = '{s name=uninstall_plugin}Uninstall plugin: [name]{/s}';
                break;
            case 'install':
                title = '{s name=install_plugin}Install plugin: [name]{/s}';
                break;
            case 'activate':
                title = '{s name=activate_plugin}Activate plugin: [name]{/s}';
                break;
            case 'deactivate':
                title = '{s name=deactivate_plugin}Deactivate plugin: [name]{/s}';
                break;
            case 'update':
                title = '{s name=update_plugin}Update plugin: [name]{/s}';
                break;
        }

        title = new Ext.Template(title);
        message = new Ext.Template(message);
        title = title.applyTemplate(plugin.data);
        message = message.applyTemplate(plugin.data);

        Ext.MessageBox.confirm(title, message, function (response) {
            if (response !== 'yes') {
                return;
            }

            switch (button.action) {
                case 'uninstall':
                    plugin.set('active', false);
                    plugin.set('installed', null);
                    break;
                case 'install':
                    plugin.set('installed', new Date());
                    break;
                case 'activate':
                    plugin.set('active', true);
                    break;
                case 'deactivate':
                    plugin.set('active', false);
                    break;
                case 'update':
                    plugin.set('version', plugin.get('updateVersion'));
                    break;
                default:
                    return;
            }

            store.sync({
                success: function (operation) {
                    message = '{s name=action_successful}The action have been executed successfully.{/s}';
                    message = new Ext.Template(message).applyTemplate(plugin.data);
                    Shopware.Notification.createGrowlMessage(title, message, win.title);
                    store.load();
                },
                failure: function (batch) {
                    message = '{s name=action_failed}The action could not be executed.{/s}';
                    message = new Ext.Template(message).applyTemplate(plugin.data);
                    if(batch.proxy.reader.rawData.message) {
                        message += '<br />' + batch.proxy.reader.rawData.message;
                    }
                    Shopware.Notification.createGrowlMessage(title, message, win.title);
                    store.load();
                }
            });
        });
    },

    onChangePlugin: function(view, records) {
        var me = this,
            installButton = me.getInstallButton(),
            uninstallButton = me.getUninstallButton(),
            deleteButton = me.getDeleteButton(),
            plugin = records.length ? records[0] : null;

        me.plugin = plugin;

        deleteButton.hide();
        uninstallButton.hide();
        if(plugin) {
            if(plugin.get('installed')) {
                if(plugin.get('capabilityInstall')) {
                    uninstallButton.show();
                }
                installButton.hide();
            } else {
                if(plugin.get('source') == 'Community') {
                    deleteButton.show();
                }
                installButton.show().enable();
            }
        } else {
            installButton.show().disable();
        }
    },

    onOpenConfigForm: function(view, record) {
        var me = this;
        if(!record.get('active') || !record.get('installed')) {
            return;
        }
        if(!record.get('configFormId')) {
            Shopware.Notification.createGrowlMessage('{s name=plugin}Plugin configuration{/s}', "{s name=plugin_config_missing}The selected plugin doesn't contains a configuration.{/s}", { log: false });
            return;
        }
        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.Config',
            action: record.get('configFormId')
        });
    },

    onSelectFilter: function(combo) {
        var me = this,
            store = me.getTable().store,
            filter = combo.getValue();

        store.filters.clear();

        switch (filter) {
            case 'active':
                store.filter('active', 1);
                break;
            case 'inactive':
                store.filter('active', 0);
                break;
            case 'payment':
                store.filter('name', '%Payment%');
                break;
            case 'community':
                store.filter('source', 'Community');
                break;
            default:
                store.clearFilter();
                break;
        }
    },

    onUploadPlugin: function(win, formPanel, btn) {
        var me = this,
            form = formPanel.getForm();

        if(!form.isValid()) {
            return false;
        }
        form.submit({
            success: function(form, action) {
                Shopware.Notification.createGrowlMessage('{s name=plugin}Plugin configuration{/s}', '{s name=successful_uploaded}Plugin was uploaded successfully.{/s}');
                win.close();
            },
            failure: function(form, action) {
                var response = Ext.decode(action.response.responseText);
                if (response.noNamespace) {
                    Shopware.Notification.createGrowlMessage('{s name=plugin}Plugin configuration{/s}', '{s name=format_error}The plugin is not in the specified format. The namespace could not be determined.{/s}');
                } else {
                    var message = '{s name=failed_upload}An error occurred while uploading the plugin.{/s}';
                    if (response.message) {
                        message = message + ':<br>' + response.message;
                    }
                    Shopware.Notification.createGrowlMessage('{s name=plugin}Plugin configuration{/s}', message);
                }
            }
        });
    },

    onOpenUploadPlugin: function() {
        var me = this;
        me.getView('plugin.ManualInstall').create().show();
    }
});
//{/block}
