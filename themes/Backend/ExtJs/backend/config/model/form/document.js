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
//{block name="backend/config/model/form/document"}
Ext.define('Shopware.apps.Config.model.form.Document', {
    extend:'Ext.data.Model',

    fields: [
        //{block name="backend/config/model/form/document/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'name',  type: 'string' },
        { name: 'key', type: 'string' },
        { name: 'description', type: 'string' },
        { name: 'template',  type: 'string' },
        { name: 'numbers',  type: 'string' },
        { name: 'left',  type: 'int' },
        { name: 'right',  type: 'int' },
        { name: 'top',  type: 'int' },
        { name: 'bottom',  type: 'int' },
        { name: 'pageBreak',  type: 'int' }
    ],

    associations: [{
        type: 'hasMany',
        model: 'Shopware.apps.Config.model.form.DocumentElement',
        name: 'getElements',
        associationKey: 'elements'
    }]
});
//{/block}
