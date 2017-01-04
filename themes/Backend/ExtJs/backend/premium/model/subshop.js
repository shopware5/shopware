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
 * @package    Premium
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * todo@all: Documentation
 */
//{block name="backend/premium/model/shop"}
Ext.define('Shopware.apps.Premium.model.Subshop', {
    /**
     * Extends the standard Ext Model
     * @string
     */
    extend:'Ext.data.Model',
    /**
    * Configure the data communication
    * @object
    */
    proxy: {
        type: 'ajax',
        /**
        * Configure the url mapping for the different
        * @object
        */
        api: {
            //read out all articles
            read: '{url controller="premium" action="getSubShops"}'
        },
        /**
        * Configure the data reader
        * @object
        */
        reader: {
            type: 'json',
            root: 'data'
        }
    },
    /**
     * The fields used for this model
     * @array
     */
    fields:[
        //{block name="backend/premium/model/shop/fields"}{/block}
        { name:'id', type:'int' },
        { name:'localeId', type:'int' },
        { name:'categoryId', type:'int' },
        { name:'name', type:'string' }
    ]
});

//{/block}
