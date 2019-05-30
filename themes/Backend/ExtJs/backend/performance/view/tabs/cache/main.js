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
 * @package    CanceledOrder
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/performance/main}

//{block name="backend/performance/view/tabs/cache"}
Ext.define('Shopware.apps.Performance.view.tabs.cache.Main', {
    extend: 'Ext.form.Panel',
    alias: 'widget.performance-tabs-cache-main',
    title: '{s name=tabs/cache/title}Cache{/s}',

    layout:'vbox',

    defaults:  {
        width: '100%'
    },

    /**
     * Initializes the component, sets up toolbar and pagingbar and and registers some events
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        // Create the items of the container
        me.items = me.getItems();

        /*{if {acl_is_allowed privilege=clear}}*/
        me.dockedItems = [{
            xtype: 'toolbar',
            dock: 'bottom',
            ui: 'shopware-ui',
            cls: 'shopware-toolbar',
            items: me.getButtons()
        }];
        /*{/if}*/

        me.callParent(arguments);

    },

    /**
     * @return Array
     */
    getItems: function() {
        var me = this;
        var info = '{s name=cache/info/text}Erfahre genaueres über das Performance-Modul in der <a href=\'https://docs.shopware.com/de/shopware-5-de/einstellungen/cache-performance-modul\' title=\'Shopware Performance-Modul\' target=\'_blank\'>Dokumentation</a>{/s}';

        return [
        {
            xtype: 'container',
            html: info,
            height: 30,
            padding: 10
        },
        {
            xtype: 'performance-tabs-cache-info',
            store: me.infoStore,
            flex: 1
        }
        /*{if {acl_is_allowed privilege=clear}}*/
        ,{
            xtype: 'performance-tabs-cache-form',
            flex: 1
        }
        /*{/if}*/
        ];
    },

    /**
     * @return Array
     */
    getButtons: function() {
        return ['->', {
            text: '{s name=form/buttons/select_all}Select all{/s}',
            action: 'select-all',
            cls: 'secondary'
        },{
            text: '{s name=form/buttons/submit}Clear{/s}',
            action: 'clear',
            cls: 'primary'
        }];
    }

});
//{/block}
