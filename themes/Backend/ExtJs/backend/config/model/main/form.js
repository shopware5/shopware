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
 * @package    Shopware_Config
 * @subpackage Config
 * @version    $Id$
 * @author shopware AG
 */

/**
 * todo@all: Documentation
 */
//{block name="backend/config/model/main/form"}
Ext.define('Shopware.apps.Config.model.main.Form', {
    extend: 'Ext.data.Model',

    fields: [
        //{block name="backend/config/model/main/form/fields"}{/block}
        { name: 'id', type: 'int', useNull: true },
        { name: 'label', type: 'string' },
        { name: 'name', type: 'string' },
        { name: 'description', type: 'string' }
    ],

    associations: [
        {
        type: 'hasMany', model: 'Shopware.apps.Config.model.main.Element',
        name: 'getElements', associationKey: 'elements'
    },
    {
        type: 'hasMany', model: 'Shopware.apps.Config.model.main.FormTranslation',
        name: 'getTranslation', associationKey: 'translations'
    }
    ]
});
//{/block}
