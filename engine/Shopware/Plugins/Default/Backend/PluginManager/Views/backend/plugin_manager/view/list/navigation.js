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

//{block name="backend/plugin_manager/view/list/navigation"}
Ext.define('Shopware.apps.PluginManager.view.list.Navigation', {
    extend: 'Ext.container.Container',

    cls: 'plugin-manager-navigation',
    autoScroll: true,

    alias: 'widget.plugin-category-navigation',

    layout: 'anchor',

    initComponent: function() {
        var me = this;

        me.items = [
            me.createSearchField(),
            me.createAccountContainer(),
            { xtype: 'component', margin: 10, cls: 'navigation-headline', html: '{s name="administration"}Management{/s}' },
            me.createLocalContainer(),
            { xtype: 'component', margin: 10, cls: 'navigation-headline', html: '{s name="discover"}Discover{/s}' },
            me.createCategoryTree()
        ];

        me.callParent(arguments);

        Shopware.app.Application.on('refresh-account-data', function(response) {
            if (response.hasOwnProperty('shopwareId')) {
                me.shopwareIdField.update(response.shopwareId);
            }
        });
    },

    setUpdateCount: function(count) {
        var me = this;

        if (count <= 0) {
            me.localUpdatesLink.update(
                '<div class="content has-badge">{s name="updates"}Updates{/s}</div>'
            );
            me.localUpdatesLink.disable();
        } else {
            me.localUpdatesLink.update(
                '<div class="content has-badge">{s name="updates"}Updates{/s}<div class="badge">'+count+'</div></div>'
            );
            me.localUpdatesLink.enable();
        }
    },

    createSearchField: function() {
        var me = this;

        me.searchField = Ext.create('Ext.form.field.Text', {
            cls: 'searchfield',
            margin: '10 10 20',
            width: 220,
            disabled: !Shopware.app.Application.sbpAvailable,
            emptyText: '{s name="search"}Search{/s} ...',
            enableKeyEvents: true,
            checkChangeBuffer: 500,
            listeners: {
                change: function (field, value) {
                    me.fireEvent('search-plugin', value);
                }
            }
        });

        return me.searchField;
    },

    createAccountContainer: function() {
        var me = this, html = '';

        me.shopwareIdField = Ext.create('Ext.Component', {
            cls: 'domain',
            html: ''
        });

        var rightSide = Ext.create('Ext.container.Container', {
            cls: 'right-side',
            items: [{
                xtype: 'component',
                cls: 'picture',
                html: '&nbsp;'
            },{
                cls: 'headline',
                xtype: 'component',
                html: '{s name="account"}Account{/s}'
            },  me.shopwareIdField]
        });

        var cls = 'account-avatar';
        if (!Shopware.app.Application.sbpAvailable) {
            cls += ' disabled';
        }

        me.accountAvatar = Ext.create('PluginManager.container.Container', {
            cls: cls,
            items: [ rightSide ],
            margin: '0 0 10',
            handler: function() {
                if (Shopware.app.Application.sbpAvailable) {
                    Shopware.app.Application.fireEvent('open-login', function() { });
                }
            }
        });

        me.accountLink = Ext.create('PluginManager.container.Container', {
            html: '<div class="content">' +
                      '<a href="https://account.shopware.com/" target="_blank">{s name="open_account"}View account{/s}</a>' +
                  '</div>',
            cls: 'navigation-item',
            disabled: !Shopware.app.Application.sbpAvailable
        });

        me.accountLicenceLink = Ext.create('PluginManager.container.Container', {
            html: '<div class="content">{s name="my_purchases"}My purchases{/s}</div>',
            cls: 'navigation-item',
            disabled: !Shopware.app.Application.sbpAvailable,
            handler: function() {
                me.fireEvent('display-licences');
            }
        });

        me.accountContainer = Ext.create('Ext.container.Container', {
            cls: 'account-container',
            margin: '0 0 10',
            items: [
                me.accountAvatar,
                me.accountLink,
                me.accountLicenceLink
            ]
        });

        return me.accountContainer;
    },

    createLocalContainer: function() {
        var me = this;

        me.localHomeLink = Ext.create('PluginManager.container.Container', {
            cls: 'navigation-item active',
            html: '<div class="content">{s name="home"}Home{/s}</div>',
            disabled: !Shopware.app.Application.sbpAvailable,
            handler: function() {
                me.fireEvent('display-home');
            }
        });

        me.localInstalledLink = Ext.create('PluginManager.container.Container', {
            cls: 'navigation-item',
            html: '<div class="content">{s name="navigation_installed"}Installed{/s}</div>',
            handler: function() {
                me.fireEvent('display-installed');
            }
        });

        me.localUpdatesLink = Ext.create('PluginManager.container.Container', {
            cls: 'navigation-item',
            html: '<div class="content">{s name="updates"}Updates{/s}</div>',
            disabled: !Shopware.app.Application.sbpAvailable,
            handler: function() {
                me.fireEvent('display-updates');
            }
        });

        me.localContainer = Ext.create('Ext.container.Container', {
            cls: 'navigation-level',
            name: 'local-container',
            items: [
                me.localHomeLink,
                me.localInstalledLink,
                me.localUpdatesLink
            ]
        });

        return me.localContainer;
    },

    /**
     * @returns { PluginManager.container.Container }
     */
    createCategoryTree: function() {
        var me = this;

        me.categoryStore = Ext.create('Shopware.apps.PluginManager.store.Category');
        me.categoryStore.load();

        me.categoryTree = Ext.create('PluginManager.category.Tree', {
            store: me.categoryStore,
            flex: 1
        });

        return me.categoryTree;
    }
});
//{/block}