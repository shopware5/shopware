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
 * @package    ProductFeed
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/product_feed/view/feed}

/**
 * Shopware UI - Tab View.
 *
 * Displays all ProductFeed filter Information
 */
//{block name="backend/product_feed/view/feed/tab/filter"}
Ext.define('Shopware.apps.ProductFeed.view.feed.tab.Filter', {
    extend:'Ext.container.Container',
    alias:'widget.product_feed-feed-tab-filter',
    title:'{s name=tab/title/filter}Further filters{/s}',
    border: 0,
    layout: 'anchor',
    padding: 10,
    cls: 'shopware-toolbar product-feed--tab-filter',
    defaults:{
        anchor:'100%',
        labelStyle:'font-weight: 700;',
        labelWidth: 150,
        xtype:'numberfield'
    },

    /**
     * Initialize the Shopware.apps.ProductFeed.view.feed.tab.Footer and defines the necessary
     * default configuration
     */
    initComponent:function () {
        var me = this;
        me.items = me.getItems();
        me.callParent(arguments);
    },
    /**
     * creates all fields for the tab
     */
    getItems:function () {
        return [
            {
                fieldLabel:'{s name=tab/filter/field/price_less}Price higher than{/s}',
                name:'priceFilter',
                hideTrigger:true,
                keyNavEnabled:false,
                mouseWheelEnabled:false
            },
            {
                fieldLabel:'{s name=tab/filter/field/instock_less}Stock larger than{/s}',
                name:'instockFilter',
                hideTrigger:true,
                allowDecimals:false,
                keyNavEnabled:false,
                mouseWheelEnabled:false
            },
            {
                fieldLabel:'{s name=tab/filter/field/max_article_count}Max. number of articles{/s}',
                name:'countFilter',
                hideTrigger:true,
                allowDecimals:false,
                keyNavEnabled:false,
                mouseWheelEnabled:false
            },
            {
                xtype:'checkbox',
                fieldLabel:'{s name=tab/filter/field/instock_under_inventory}Stock under minimum inventory{/s}',
                inputValue:1,
                uncheckedValue:0,
                name:'stockMinFilter'
            },
            {
                xtype:'checkbox',
                fieldLabel:'{s name=tab/filter/field/only_active_articles}Activated articles only{/s}',
                inputValue:1,
                uncheckedValue:0,
                name:'activeFilter'
            },
            {
                xtype: 'codemirrorfield',
                fieldLabel:'{s name=tab/filter/field/own_filter}Own filters{/s}',
                mode: 'sql',
                anchor:'100%',
                height: 80,
                name: 'ownFilter',
                /*{if !{acl_is_allowed privilege=sqli}}*/
                readOnly: true,
                helpText: '{s name="insufficient_permissions" namespace="backend/application/main"}{/s}',
                /*{/if}*/
            },
            {
                xtype:'checkbox',
                fieldLabel:'{s name=tab/filter/field/only_image_articles}Articles with images only{/s}',
                inputValue:1,
                uncheckedValue:0,
                name:'imageFilter'
            }
        ];
    }

});
//{/block}
