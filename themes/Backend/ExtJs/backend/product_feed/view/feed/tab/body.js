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
 * Displays all ProductFeed body Information
 */
//{block name="backend/product_feed/view/feed/tab/body"}
Ext.define('Shopware.apps.ProductFeed.view.feed.tab.Body', {
    extend:'Ext.container.Container',
    alias:'widget.product_feed-feed-tab-body',
    title:'{s name=tab/title/body}Body{/s}',
    border: 0,
    padding: 10,
    cls: 'shopware-toolbar',
    layout: 'fit',
    fieldName: 'body',
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
        var me = this;
        return [
            {
                xtype: 'codemirrorfield',
                mode: 'smarty',
                anchor:'100%',
                name: me.fieldName,
            }
        ];
    }

});
//{/block}
