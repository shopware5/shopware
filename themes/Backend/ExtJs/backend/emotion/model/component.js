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
 * @package    Emotion
 * @subpackage Main
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Model - Emotion backend module.
 */

//{block name="backend/emotion/model/component"}
//{namespace name="backend/emotion/view/detail"}
Ext.define('Shopware.apps.Emotion.model.Component', {
    /**
     * Extends the standard Ext Model
     * @string
     */
    extend: 'Ext.data.Model',

    snippets: {
        //{block name="backend/emotion/model/component/snippets"}{/block}
    },

    /**
     * The fields used for this model
     * @array
     */
    fields: [
        //{block name="backend/emotion/model/component/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'pluginId', type: 'int', useNull: true },

        { name: 'fieldLabel', type: 'string', convert: function(value, record) {
            if (!value) {
                value = record.get('name');
            }

            if (record.snippets[value]) {
                return record.snippets[value];
            }
            return value;
        } },

        { name: 'name', type: 'string' },
        { name: 'description', type: 'string' },
        { name: 'xType', type: 'string' },
        { name: 'template', type: 'string' },
        { name: 'cls', type: 'string' }
    ],

    associations: [
        { type: 'hasMany', model: 'Shopware.apps.Emotion.model.Field', name: 'getFields', associationKey: 'fields' }
    ]

});
//{/block}
