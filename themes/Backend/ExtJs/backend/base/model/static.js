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
 * @package    Base
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

//{block name="backend/base/model/static"}
Ext.define('Shopware.apps.Base.model.Static', {

    alternateClassName: 'Shopware.model.Static',

    extend: 'Shopware.data.Model',

    idProperty: 'id',

    fields: [
        //{block name="backend/base/model/static/fields"}{/block}
        { name: 'key', type: 'string' },
        { name: 'id', type: 'string', convert: function(value, record) { return record.get('key') || value; } },
        { name: 'active', type: 'boolean', defaultValue: true },
        { name: 'description', type: 'string' },
        { name: 'text', convert: function(value, record) { return record.get('name') ? record.get('name') : value; } },
        { name: 'helperId', type: 'int' },
        { name: 'tpl1variable', type: 'string' },
        { name: 'tpl1path', type: 'string' },
        { name: 'tpl2variable', type: 'string' },
        { name: 'tpl2path', type: 'string' },
        { name: 'tpl3variable', type: 'string' },
        { name: 'tpl3path', type: 'string' },
        { name: 'html', type: 'string' },
        { name: 'parentId', type: 'string' },
        { name: 'html', type: 'string' },
        { name: 'grouping', type: 'string' },
        { name: 'shopIds' },
        { name: 'position', type: 'int' },
        { name: 'link', type: 'string' },
        { name: 'target', type: 'string' },
        { name: 'pageTitle', type: 'string' },
        { name: 'metaKeywords', type: 'string' },
        { name: 'metaDescription', type: 'string' },
        { name: 'name', convert: function (value, record) { return record.get('description'); } },
    ]
});
//{/block}
