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
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/canceled_order/controller/main}

/**
 * Shopware Controller - Basket Controller
 * controls the basket grid and its events
 */
//{block name="backend/canceled_order/controller/basket"}
Ext.define('Shopware.apps.CanceledOrder.controller.Basket', {

    extend: 'Ext.app.Controller',

    /**
     * A template method that is called when your application boots. It is called before the Application's
     * launch function is executed so gives a hook point to run any code before your Viewport is created.
     */
    init: function() {
        var me = this;

        me.control({
            'canceled-order-tabs-baskets-articles': {
                openArticle: me.onOpenArticle
            }
        });

        me.callParent(arguments);
    },

    /**
     * Callback function for openArticle. Will open the Article subApplication.
     *
     * @param record
     * @return
     */
    onOpenArticle: function(record) {
        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.Article',
            action: 'detail',
            params: {
                articleId: record.get('id')
            }
        });
    }

});
//{/block}
