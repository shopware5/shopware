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
 * @package    Base
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Model - Global Stores and Models
 *
 * The article_with_variants model is based on the article model. This model is able to hold article variants.
 */
//{block name="backend/base/model/variant"}
Ext.define('Shopware.apps.Base.model.Variant', {
    /**
     * Extends the standard Ext Model
     * @string
     */
    extend:'Shopware.data.Model',

    /**
     * The fields used for this model
     * @array
     */
    fields: [
        { name: 'id', type: 'int' },
        { name: 'name', type: 'string' },
        { name: 'articleId', type: 'int' },
        { name: 'additionalText', type: 'string' },
        { name: 'supplierName', type: 'string'},
        { name: 'supplierId', type: 'int' },
        { name: 'ordernumber', type: 'string' },
        { name: 'inStock', type: 'string' },
        { name: 'active', type: 'int' },


        //mapping fields for ExtJS
        { name: 'number', type: 'string', mapping: 'ordernumber' },
    ]
});
//{/block}
