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
 * @package    Emotion
 * @subpackage Main
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Model - Emotion backend module.
 */
//{block name="backend/emotion/model/grid"}
Ext.define('Shopware.apps.Emotion.model.Grid', {
    /**
     * Extends the standard Ext Model
     * @string
     */
    extend: 'Ext.data.Model',

    /**
     * The fields used for this model
     * @array
     */
    fields: [
		//{block name="backend/emotion/model/grid/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'name', type: 'string' },
        { name: 'cols', type: 'int', defaultValue: 4 },
        { name: 'rows', type: 'int', defaultValue: 20 },
        { name: 'cellHeight', type: 'int', defaultValue: 185 },
        { name: 'articleHeight', type: 'int', defaultValue: 2 },
        { name: 'gutter', type: 'int', defaultValue: 10 }
    ],

    /**
     * Configure the data communication
     * @object
     */
    proxy:{
        /**
         * Set proxy type to ajax
         * @string
         */
        type:'ajax',

        /**
         * Configure the url mapping for the different
         * store operations based on
         * @object
         */
        api: {
            create: '{url action="createGrid"}',
            update: '{url action="updateGrid"}',
            destroy: '{url action="deleteGrid"}'
        },

        /**
         * Configure the data reader
         * @object
         */
        reader: {
            type:'json',
            root:'data'
        }
    }
});
//{/block}
