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
 * @category    Shopware
 * @package     Emotion
 * @subpackage  View
 * @version     $Id$
 * @author      shopware AG
 */

/**
 * Shopware Model - Emotion backend module.
 */
//{block name="backend/emotion/model/element"}
Ext.define('Shopware.apps.Emotion.model.EmotionElement', {

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
        //{block name="backend/emotion/model/element/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'emotionId', type: 'int' },
        { name: 'componentId', type: 'int' },
        { name: 'startRow', type: 'int', defaultValue: 1 },
        { name: 'startCol', type: 'int', defaultValue: 1 },
        { name: 'endRow', type: 'int', defaultValue: 1 },
        { name: 'endCol', type: 'int', defaultValue: 1 },
        { name: 'cssClass', type: 'string' },
        { name: 'data' }
    ],

    associations: [
        { type: 'hasMany', model: 'Shopware.apps.Emotion.model.Component', name: 'getComponent', associationKey: 'component'},
        { type: 'hasMany', model: 'Shopware.apps.Emotion.model.Viewport', name: 'getViewports', associationKey: 'viewports'}
    ]
});
//{/block}
