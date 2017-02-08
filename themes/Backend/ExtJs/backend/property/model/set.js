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
 * @package    Property
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * todo@all: Documentation
 */
//{block name="backend/property/model/set"}
Ext.define('Shopware.apps.Property.model.Set', {

    /**
     * Extends the standard ExtJS Model
     * @string
     */
    extend: 'Shopware.data.Model',

    /**
     * The fields used for this model
     *
     * @array
     */
    fields : [
        //{block name="backend/property/model/set/fields"}{/block}
        { name: 'id',         type: 'integer' },
        { name: 'name',       type: 'string' },
        { name: 'position',   type: 'integer' },
        { name: 'comparable', type: 'boolean' },
        { name: 'isOption',   type: 'boolean' },
        { name: 'sortMode',   type: 'integer' }
    ],

    /**
     * Configure the data communication
     * @object
     */
    proxy: {
        type: 'ajax',

        /**
         * Configure the url mapping for the different
         * store operations based on
         * @object
         */
        api: {
            read:    '{url controller="property" action="getSets"}',
            create:  '{url controller="property" action="createSet"}',
            update:  '{url controller="property" action="updateSet"}',
            destroy: '{url controller="property" action="deleteSet"}'
        },
        /**
         * Configure the data reader
         * @object
         */
        reader: {
            type: 'json',
            root: 'data'
        }
    }
});
//{/block}
