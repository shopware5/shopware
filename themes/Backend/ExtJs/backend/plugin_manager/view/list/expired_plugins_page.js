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
 * @subpackage List
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/plugin_manager/translation}

//{block name="backend/plugin_manager/view/list/expired_plugins_page"}
Ext.define('Shopware.apps.PluginManager.view.list.ExpiredPluginsPage', {
    extend: 'Ext.container.Container',
    autoScroll: true,
    cls: 'plugin-manager-listing-page',
    alias: 'widget.plugin-manager-expired-plugins-page',

    initComponent: function() {
        var me = this;

        me.expiredStore = Ext.create('Shopware.apps.PluginManager.store.ExpiredPlugins', {
            autoLoad: true
        });
        me.items = [ me.createFilterPanel(), me.createStoreListing() ];

        me.callParent(arguments);
    },

    createFilterPanel: function() {
        return Ext.create('Ext.container.Container', {
            border: false,
            items: [
                Ext.create('Ext.container.Container', {
                    html: '{s name="expired_plugins/headline"}We hope you enjoyed using the plugins{/s}',
                    cls: 'headline',
                    padding: '30 30 0 30'
                }),
                Ext.create('Ext.container.Container', {
                    html: '{s name="expired_plugins/description_text"}The test phase for the following plugins is expired. You can now easily buy the plugins via Plugin Manager. If you did not like the plugin you can easily uninstall it.{/s}',
                    padding: '10 30 0 30'
                })
            ]
        });
    },

    createStoreListing: function() {
        var me = this;

        me.listing = Ext.create('PluginManager.components.Listing', {
            store: me.expiredStore,
            padding: 30,
            width: 1007,
            addItems: function(records) {
                var me = this, plugins = [];

                Ext.each(records, function(record) {
                    var item = Ext.create('PluginManager.components.ExpiredPlugin', {
                        record: record
                    });

                    plugins.push(item);
                });
                me.listingContainer.add(plugins);
            }
        });

        me.content = Ext.create('Ext.container.Container', {
            items: [
                me.listing
            ]
        });

        return me.content;
    }
});
//{/block}