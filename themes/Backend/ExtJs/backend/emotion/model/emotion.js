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
 *
 * The emotion model contains all data for one shopware emotion.
 * The data used for the backend listing and the detail page.
 */
//{block name="backend/emotion/model/emotion"}
Ext.define('Shopware.apps.Emotion.model.Emotion', {
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
        //{block name="backend/emotion/model/emotion/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'parentId', type: 'int', useNull: true, defaultValue: null },
        { name: 'groupingState', type: 'string' },

        { name: 'templateId', type: 'int', useNull: true, defaultValue: 1 },
        { name: 'active', type: 'boolean' },
        { name: 'showListing', type: 'boolean' },
        { name: 'listingVisibility', type: 'string' },
        { name: 'name', type: 'string' },

        { name: 'position', type: 'int', useNull: false, defaultValue: 1 },
        { name: 'device', type: 'string' },
        { name: 'fullscreen', type: 'int' },

        { name: 'rows', type: 'int', defaultValue: 20 },
        { name: 'cols', type: 'int', defaultValue: 4 },
        { name: 'cellSpacing', type: 'int', defaultValue: 10 },
        { name: 'cellHeight', type: 'int', defaultValue: 185 },
        { name: 'articleHeight', type: 'int', defaultValue: 2 },

        { name: 'validFrom', type: 'date', dateFormat: 'd.m.Y', useNull: true },
        { name: 'validTo', type: 'date', dateFormat: 'd.m.Y', useNull: true },
        { name: 'validToTime', type: 'date', dateFormat: 'H:i', useNull: true },
        { name: 'validFromTime', type: 'date', dateFormat: 'H:i', useNull: true },
        { name: 'userId', type: 'int' },
        { name: 'createDate', type: 'date', useNull: true },
        { name: 'modified', type: 'date', useNull: true },
        { name: 'template', type: 'string', defaultValue: 'Standard' },

        { name: 'isLandingPage', type: 'boolean' },
        { name: 'link', type: 'string' },
        { name: 'seoTitle', type: 'string' },
        { name: 'seoKeywords', type: 'string' },
        { name: 'seoDescription', type: 'string' },
        { name: 'categoriesNames', type: 'string' },
        { name: 'categories', type: 'array' },
        { name: 'mode', type: 'string', defaultValue: 'fluid' },
        { name: 'customerStreamIds', type: 'string', useNull: true, defaultValue: null },
        { name: 'replacement', type: 'string', useNull: true, defaultValue: null },
        { name: 'emotionGroup', persist: false },
        { name: 'selectedCategory', persist: false }
    ],

    associations: [
        { type: 'hasMany', model: 'Shopware.apps.Emotion.model.EmotionElement', name: 'getElements', associationKey: 'elements'},
        { type: 'hasMany', model: 'Shopware.apps.Emotion.model.EmotionShop', name:'getShops', associationKey:'shops'}
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
            create: '{url action="save"}',
            update: '{url action="save"}',
            destroy: '{url action="delete"}'
        },

        /**
         * Configure the data reader
         * @object
         */
        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    }

});
//{/block}
