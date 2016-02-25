
//{namespace name=backend/plugin_manager/translation}
Ext.define('Shopware.apps.PluginManager.view.detail.Actions', {

    extend: 'Ext.container.Container',

    cls: 'plugin-meta-data-container-actions',

    defaults: {
        minWidth: 270,
        margin: '15 10 0'
    },

    layout: 'vbox',

    margin: '10 0',

    padding: '0 0 10',

    mixins: {
        events: 'Shopware.apps.PluginManager.view.PluginHelper'
    },

    initComponent: function() {
        var me = this,
            items = [],
            button;

        if (me.plugin.allowUpdate()) {
            button = Ext.create('PluginManager.container.Container', {
                html: '{s name="install_update"}Install update{/s} (v ' + me.plugin.get('availableVersion') + ')',
                cls: 'plugin-manager-action-button primary',
                handler: function() {
                    me.updatePluginEvent(me.plugin);
                }
            });
            items.push(button);
        }

        if (me.plugin.allowDummyUpdate()) {
            button = Ext.create('PluginManager.container.Container', {
                html: '{s name="install"}Install{/s}',
                cls: 'plugin-manager-action-button primary',
                handler: function() {
                    me.updateDummyPluginEvent(me.plugin);
                }
            });
            items.push(button);
        }

        if (me.plugin.allowInstall()) {
            button = Ext.create('PluginManager.container.Container', {
                html: '{s name="install"}Install{/s}',
                cls: 'plugin-manager-action-button primary',
                handler: function() {
                    me.installPluginEvent(me.plugin);
                }
            });
            items.push(button);
        }

        if (me.plugin.allowActivate()) {
            button = Ext.create('PluginManager.container.Container', {
                html: '{s name="activate"}Activate{/s}',
                cls: 'plugin-manager-action-button primary',
                handler: function() {
                    me.activatePluginEvent(me.plugin);
                }
            });

            items.push(button);
        }

        if (me.plugin.allowReinstall()) {
            button = Ext.create('PluginManager.container.Container', {
                html: '{s name="reinstall"}Reinstall{/s}',
                cls: 'plugin-manager-action-button',
                handler: function() {
                    me.reinstallPluginEvent(me.plugin);
                }
            });
            items.push(button);
        }

        if (me.plugin.allowUninstall()) {
            button = Ext.create('PluginManager.container.Container', {
                html: '{s name="uninstall"}Uninstall{/s}',
                cls: 'plugin-manager-action-button',
                handler: function() {
                    me.uninstallPluginEvent(me.plugin);
                }
            });
            items.push(button);
        }

        if (me.plugin.allowDeactivate()) {
            button = Ext.create('PluginManager.container.Container', {
                html: '{s name="deactivate"}Deactivate{/s}',
                cls: 'plugin-manager-action-button',
                handler: function() {
                    me.deactivatePluginEvent(me.plugin);
                }
            });
            items.push(button);
        }

        if (me.plugin.allowDelete()) {
            button = Ext.create('PluginManager.container.Container', {
                html: '{s name="delete"}Delete{/s}',
                cls: 'plugin-manager-action-button',
                handler: function() {
                    me.deletePluginEvent(me.plugin);
                }
            });
            items.push(button);
        }

        me.items = items;

        me.callParent(arguments);
    }
});