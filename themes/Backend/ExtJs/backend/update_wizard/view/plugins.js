
//{namespace name=backend/update_wizard/translation}
Ext.define('Shopware.apps.UpdateWizard.view.Plugins', {
    extend: 'Ext.container.Container',
    border: false,
    alias: 'widget.update-wizard-plugins',
    cls: 'update-wizard-plugins-container',

    layout: { type: 'vbox', align: 'stretch' },

    padding: 20,

    initComponent: function() {
        var me = this;

        me.items = me.createItems();

        this.callParent(arguments);
    },

    refreshData: function(response) {
        var me = this, text;

        me.plugins = response.plugins;
        me.updatable = response.updatable;
        me.notUpdatable = response.notUpdatable;

        text = Ext.String.format('{s name="plugins_installed"}{/s}', me.plugins.length);
        me.headline.update(text);

        if (me.updatable.length > 0) {
            text = Ext.String.format('{s name="plugins_update_required"}{/s}', me.updatable.length, me.plugins.length);
            me.updateNotice.update(text);
        } else {
            me.updateNotice.hide();
            me.pluginManagerButton.hide();
        }

        if (me.notUpdatable.length > 0) {
            text = Ext.String.format('{s name="plugins_not_updatable"}{/s}', me.notUpdatable.length, me.plugins.length);
            me.notUpdatableNotice.update(text);
        } else {
            me.notUpdatableNotice.hide();
        }

        var plugins = [];
        Ext.each(me.notUpdatable, function(plugin) {
            plugins.push(plugin['name']);
        });

        me.notUpdatablePluginsContainer.update(
            plugins.join(', ')
        );
    },

    createItems: function() {
        var me = this, text;

        me.headline = Ext.create('Ext.Component', {
            html: '{s name="plugins_installed"}{/s}',
            cls: 'update-wizard-plugin-headline',
            margin: '0 0 15'
        });

        me.updateNotice = Ext.create('Ext.Component', {
            html: '{s name="plugins_update_required"}{/s}',
            cls: 'update-wizard-updatable-notice text'
        });

        me.pluginManagerButton = Ext.create('PluginManager.container.Container', {
            html: '{s name="plugins_button"}{/s}',
            cls: 'plugin-manager-action-button primary',
            margin: '30 80',
            handler: function() {
                me.fireEvent('close-update-wizard');
                me.startPluginManager();
            }
        });

        me.notUpdatableNotice = Ext.create('Ext.Component', {
            html: '{s name="plugins_not_updatable"}{/s}',
            cls: 'update-wizard-not-updatable-notice text',
            margin: '10 0'
        });

        me.notUpdatablePluginsContainer = Ext.create('Ext.Component', {
            html: '',
            cls: 'update-wizard-not-updatable-plugins text',
            margin: '10 0',
            height: 80
        });

        me.destroyButton = Ext.create('PluginManager.container.Container', {
            html: '{s name="plugins_accept"}{/s}',
            cls: 'plugin-manager-action-button destroy-button',
            margin: '0 80',
            handler: function() {
                me.fireEvent('close-update-wizard');
            }
        });

        return [
            me.headline ,
            me.updateNotice,
            me.pluginManagerButton,
            me.notUpdatableNotice,
            me.notUpdatablePluginsContainer,
            me.destroyButton
        ];
    },

    startPluginManager: function() {
        var me = this;

        Shopware.app.Application.addSubApplication({
                name: 'Shopware.apps.PluginManager'
            },
            undefined,
            function() {
                Shopware.app.Application.on('load-update-listing', function() {
                    Shopware.app.Application.fireEvent('plugin-manager-display-updates');
                }, me, { single: true });
            }
        );
    }
});
