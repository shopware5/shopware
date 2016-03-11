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
 * @package    PluginManager
 * @subpackage Detail
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/plugin_manager/translation}

//{block name="backend/plugin_manager/view/detail/action"}
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
//{/block}