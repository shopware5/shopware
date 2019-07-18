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
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/plugin_manager/translation}

Ext.define('Shopware.window.ExpiredPluginWarning', {
    extend: 'Enlight.app.Window',
    autoScroll: true,
    layout: 'fit',
    height: 430,
    width: 870,
    autoShow: true,
    title: '{s name="expired_plugins_popup/title"}{/s}',
    footerButton: false,

    initComponent: function() {
        var me = this;

        me.items = [
            {
                xtype: 'form',
                items: [me.createContentPage()],
                dockedItems: [{
                    xtype: 'toolbar',
                    dock: 'bottom',
                    ui: 'shopware-ui',
                    cls: 'shopware-toolbar',
                    items: me.getWindowButtons()
                }]
            },
        ];

        me.callParent(arguments);
    },

    createContentPage: function () {
        return {
            xtype: 'container',
            html: '{literal}<style>.plugin-window { color: #475C6A; } .plugin-window h2 { color: #495B67; font-weight: bold; font-size: 24px; margin-bottom: 12px } .plugin-window p { margin-bottom: 10px; line-height: 140%;} .plugin-window img { width: calc(100% - 100px); padding: 50px; margin: 40px 0 0 -30px; position: absolute; top: 50%; left: 50%; transform: translateX(-50%); } .plugin-window .bar { width: 50%; float: left; position: relative; } .plugin-window .bar:last-child { margin-left: 50%; } .plugin-window strong { font-weight: bold; } .plugin-window ul li { list-style: inherit; margin: 3px 0}</style>{/literal}<div class="plugin-window"><div class="bar"><img src="' + this.getImage() + '"></div><div class="bar"><div style="margin: 30px 0 0 -40px;">' + this.getText() + '</div></div></div>'
        }
    },

    getImage: function() {
        return '{link file="backend/_resources/images/plugin_manager/warning.svg"}';
    },

    getText: function() {
        return '{s name="expired_plugins_popup/content"}{/s}';
    },

    getWindowButtons: function() {
        var me = this;

        return [
            '->',
            {
                xtype: 'button',
                text: '{s name="expired_plugins_popup/cancel"}{/s}',
                scope: me,
                cls: 'secondary',
                handler: function () {
                    me.destroy();
                }
            },
            {
                xtype: 'button',
                text: '{s name="expired_plugins_popup/goToPluginManager"}{/s}',
                scope: me,
                cls: 'primary',
                handler: function () {
                    Shopware.app.Application.addSubApplication({
                        name: 'Shopware.apps.PluginManager',
                        action: 'ExpiredPlugins'
                    });

                    me.destroy();
                }
            }
        ];
    }
});
