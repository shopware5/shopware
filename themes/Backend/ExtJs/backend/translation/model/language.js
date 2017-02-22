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
 * @package    Translation
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware - Translation Manager Language Model
 *
 * Model for the translatable languages.
 */

//{block name="backend/translation/model/language"}
Ext.define('Shopware.apps.Translation.model.Language',
/** @lends Ext.data.Model# */
{
    /**
     * The parent class that this class extends
     * @string
     */
    extend: 'Ext.data.Model',

    /**
     * The fields for this model.
     * @array
     */
    fields: [
        //{block name="backend/translation/model/language/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'text', convert: function(v, record) { return record.data.name; } },
        { name: 'leaf', convert: function(v, record) { return record.data.childrenCount <= 0; } },
        { name: 'name', type: 'string' },
        { name: 'default', type: 'boolean' },
        { name: 'childrenCount', type: 'int' },
        { name: 'expanded', type: 'boolean', defaultValue: true, persist: false }
    ],

    /**
     * The proxy to use for this model.
     * @object
     */
    proxy: {
        type: 'ajax',
        url: '{url action=getLanguages}',

        /**
         * The Ext.data.reader.Reader to use to decode the server's response or data read from client.
         * @object
         */
        reader: {
            type: 'json',
            root: 'data',
            idProperty: 'id',
            totalProperty: 'total'
        }
    }
});
//{/block}
