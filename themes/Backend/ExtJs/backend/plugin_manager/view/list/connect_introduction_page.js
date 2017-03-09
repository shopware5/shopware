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
    layout: 'fit',
    border: false,
    bodyCls: 'plugin-manager-listing-page',

    initComponent: function() {
        var me = this,
            url = Ext.String.format('//cdn.shopware.de/connect_introducing/index_[0].html', Ext.userLanguage !== 'de' ? 'en' : Ext.userLanguage);

        me.items = [{
            xtype: 'container',
            html: '<iframe src="' + url + '" width="100%" height="100%"></iframe>'
        }];
        me.dockedItems = me.buildDockedItems();

        me.callParent(arguments);
    },

    buildDockedItems: function() {
        var me = this;

        me.bottomToolbar = Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            items: [{
                xtype: 'button',
                cls: 'secondary',
                text: '{s name="connect_introduction/remove_connect"}{/s}',
                handler: function() {
                    me.fireEvent('connect-introduction-remove');
                }
            }, '->', {
                xtype: 'button',
                cls: 'primary',
                text: '{s name="connect_introduction/connect_now"}{/s}',
                handler: function() {
                    me.fireEvent('connect-introduction-install');
                }
            }]
        });

        return [ me.bottomToolbar ];
    }
});
//{/block}