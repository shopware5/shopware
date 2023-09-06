/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 *
 * @category   Shopware
 * @package    Article
 * @subpackage Category
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Model - Article backend module.
 */
//{block name="backend/article/model/price_variation"}
Ext.define('Shopware.apps.Article.model.PriceVariation', {

    /**
    * Extends the standard Ext Model
    * @string
    */
    extend: 'Ext.data.Model',

    /**
     * Fields array which contains the model fields
     * @array
     */
    fields: [
        //{block name="backend/article/model/price_variation/fields"}{/block}
        { name: 'id', type: 'integer', useNull: true },
        { name: 'option_names' },
        { name: 'isGross', type: 'integer' },
        { name: 'variation', type: 'float' },
        { name: 'configuratorSetId', type: 'integer' }
    ],

    /**
     * Configure the data communication
     * @object
     */
    proxy: {
        /**
         * Set proxy type to ajax
         * @string
         */
        type: 'ajax',

        /**
         * Configure the url mapping for the different
         * store operations based on
         * @object
         */
        api: {
            create: '{url controller="ArticlePriceVariation" action="createPriceVariation"}',
            update: '{url controller="ArticlePriceVariation" action="updatePriceVariation"}',
            destroy: '{url controller="ArticlePriceVariation" action="deletePriceVariation"}'
        },

        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    },

    associations: [
        { type: 'hasMany', model: 'Shopware.apps.Article.model.ConfiguratorOption', name: 'getOptions', associationKey: 'options' }
    ]
});
//{/block}
