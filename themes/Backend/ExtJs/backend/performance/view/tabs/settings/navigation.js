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
 * @package    Performance
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/performance/main}

//{block name="backend/performance/view/tabs/settings/navigation"}
Ext.define('Shopware.apps.Performance.view.tabs.settings.Navigation', {
    extend: 'Ext.tree.Panel',
    alias: 'widget.performance-tabs-settings-navigation',
    rootVisible: false,
    title: '{s name=tabs/settings/title}Settings{/s}',

    /**
     * Mark the navigation bar as not collapsible
     */
    collapsed: false,
    collapsible: false,

    width: 200,
    expanded: true,
    useArrows: true,
    displayField: 'text',

    data: {
        expanded: true,
        children: [{
            text: "{s name=navigation/general}General{/s}",
            expanded: true,
            children: [
            {
                text: "{s name=navigation/home}Performance checks{/s}",
                leaf: true,
                internalName: 'performance-tabs-settings-home'
            },
            {
                text: "{s name=navigation/cache}HTTP Cache{/s}",
                leaf: true,
                internalName: 'performance-tabs-settings-http-cache'
            }, {
                text: "{s name=navigation/theme_cache}Theme cache{/s}",
                leaf: true,
                internalName: 'performance-tabs-settings-theme-cache'
            }, {
                text: "{s name=navigation/seo}SEO{/s}",
                leaf: true,
                internalName: 'performance-tabs-settings-seo'
            }, {
                text: "{s name=navigation/search}Search{/s}",
                leaf: true,
                internalName: 'performance-tabs-settings-search'
            }, {
                text: "{s name=navigation/categories}Categories{/s}",
                leaf: true,
                internalName: 'performance-tabs-settings-categories'
            }, {
                text: "{s name=navigation/filter}Filters{/s}",
                leaf: true,
                internalName: 'performance-tabs-settings-filter'
            }, {
                text: "{s name=navigation/sitemap}Sitemap{/s}",
                leaf: true,
                internalName: 'performance-tabs-settings-sitemap'
            },{
                text: "{s name=navigation/various}Various{/s}",
                leaf: true,
                internalName: 'performance-tabs-settings-various'
            }]
        }, {
            text: "{s name=navigation/crossselling}Marketing{/s}",
            expanded: true,
            children: [{
                text: "{s name=navigation/topseller}TopSeller{/s}",
                name: '21',
                leaf: true,
                internalName: 'performance-tabs-settings-topseller'
            }, {
                text: "{s name=navigation/otherCustomers}Empfehlungsmarketing{/s}",
                leaf: true,
                internalName: 'performance-tabs-settings-customers'
            }]
        }]
    },

    /*
     * The internalName of each item is the xtype of the corresponding fieldset
     *
     * If internalName is empty or has no fieldSet associated, all fieldSets will be hidden
     */
    listeners: {
        itemclick: function(tree, record, item, index, e, eOpts) {
            var internalName = record.raw.internalName;
            this.fireEvent('itemClicked', internalName)
        },
        beforeitemclick: function(tree, record, item, index, e, eOpts) {
            if (record.childNodes.length > 0) {
                return false;
            }
            return true;
        }

    },

    /*
     * Initialize the component and define the event fired
     */
    initComponent: function() {
        var me = this;

        me.root = Ext.clone(me.data);
        me.addEvents('itemClicked');

        me.callParent(arguments);
    }

});
//{/block}
