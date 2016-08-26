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

//{block name="backend/plugin_manager/view/list/connect_introduction_page"}
Ext.define('Shopware.apps.PluginManager.view.list.ConnectIntroductionPage', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.plugin-manager-connect-introduction-page',

    border: false,
    bodyCls: 'plugin-manager-listing-page',
    autoScroll: true,
    bodyPadding: '20 40 10 40',

    initComponent: function() {
        var me = this;

        me.items = me.buildItems();
        me.dockedItems = me.buildDockedItems();

        me.callParent(arguments);
    },

    buildItems: function() {
        var me = this;

        me.headLineText = Ext.create('Ext.container.Container', {
            cls: 'headline',
            html: '{s name="connect_introduction/headline"}{/s}'
        });

        me.descriptionText = Ext.create('Ext.container.Container', {
            html: '{s name="connect_introduction/description_text"}{/s}',
            padding: '20 0 0 0'
        });

        me.pillarOne = Ext.create('Ext.container.Container', {
            html: '<div style="float: left;"><div class="shopware-connect-pillar-one"></div></div><div style="margin-left: 120px; padding-top: 20px;">{s name="connect_introduction/pillar_one"}{/s}</div><div style="clear: both;"></div><br>'
        });

        me.pillarTwo = Ext.create('Ext.container.Container', {
            html: '<div style="float: left;"><div class="shopware-connect-pillar-two"></div></div><div style="margin-left: 120px; padding-top: 20px;">{s name="connect_introduction/pillar_two"}{/s}</div><div style="clear: both;"></div><br>'
        });

        me.pillarThree = Ext.create('Ext.container.Container', {
            html: '<div style="float: left;"><div class="shopware-connect-pillar-three"></div></div><div style="margin-left: 120px; padding-top: 20px;">{s name="connect_introduction/pillar_three"}{/s}</div><div style="clear: both;"></div>'
        });

        me.noteText = Ext.create('Ext.container.Container', {
            cls: 'block-message',
            html: '<div class="notice">{s name="connect_introduction/note"}{/s}</div>',
            padding: '20 0 0 0'
        });

        me.linkText = Ext.create('Ext.container.Container', {
            html: '{s name="connect_introduction/link"}{/s}',
            padding: '5 0 0 0'
        });

        me.installInfoText = Ext.create('Ext.container.Container', {
            padding: '54 0 0 0',
            html: '{s name="connect_introduction/install_information"}{/s}'
        });

        return [
            me.headLineText,
            me.descriptionText,
            me.pillarOne,
            me.pillarTwo,
            me.pillarThree,
            me.noteText,
            me.linkText,
            me.installInfoText
        ];
    },

    buildDockedItems: function() {
        var me = this;

        me.bottomToolbar = Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            items: [{
                xtype: 'button',
                cls: 'secondary',
                text: '{s name="connect_introduction/cancel"}{/s}',
                handler: function() {
                    me.up('window').close();
                }
            }, '->', {
                xtype: 'button',
                cls: 'secondary',
                text: '{s name="connect_introduction/remove_connect"}{/s}',
                handler: function() {
                    me.fireEvent('connect-introduction-remove');
                }
            }, {
                xtype: 'button',
                cls: 'primary',
                text: '{s name="connect_introduction/connect_now"}{/s}',
                handler: function() {
                    me.fireEvent('connect-introduction-install');
                }
            }]
        });

        return [me.bottomToolbar];
    }
});
//{/block}