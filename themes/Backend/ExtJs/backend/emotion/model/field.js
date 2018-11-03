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
//{block name="backend/emotion/model/field"}
Ext.define('Shopware.apps.Emotion.model.Field', {
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
        //{block name="backend/emotion/model/field/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'componentId', type: 'int' },
        { name: 'name', type: 'string' },
        { name: 'xType', type: 'string' },
        { name: 'valueType', type: 'string' },
        { name: 'fieldLabel', type: 'string' },
        { name: 'supportText', type: 'string' },
        { name: 'helpTitle', type: 'string' },
        { name: 'helpText', type: 'string' },
        { name: 'store', type: 'string', useNull: true, defaultValue: null },
        { name: 'displayField', type: 'string', useNull: true, defaultValue: null },
        { name: 'valueField', type: 'string', useNull: true, defaultValue: null },
        { name: 'allowBlank', type: 'int' },
        { name: 'defaultValue', type: 'string' },
        { name: 'translatable', type: 'int'},
        { name: 'position', type: 'int' }
    ]

});
//{/block}
