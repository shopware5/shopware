/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @package    LiveShopping
 * @subpackage ExtJs
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware ExtJs model
 */
//{block name="backend/article/model/live_shopping/price"}
Ext.define('Shopware.apps.Article.model.live_shopping.Price', {
    /**
     * Extends the standard Ext Model
     * @string
     */
    extend: 'Shopware.apps.Article.model.Price',

    /**
     * The fields used for this model
     * @array
     */
    fields: [
        //{block name="backend/article/model/live_shopping/price/fields"}{/block}
        { name: 'endPrice', type: 'float' }
    ]


});
//{/block}
