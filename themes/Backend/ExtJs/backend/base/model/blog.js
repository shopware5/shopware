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

//{block name="backend/base/model/blog"}
Ext.define('Shopware.apps.Base.model.Blog', {

    alternateClassName: 'Shopware.model.Blog',

    extend: 'Shopware.data.Model',

    idProperty: 'id',

    fields: [
        //{block name="backend/base/model/blog/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'title', type: 'string' },
        { name: 'shortDescription', type: 'string' },
        { name: 'description', type: 'string' },
        { name: 'active', type: 'boolean' },
        { name: 'views', type: 'int' },
        { name: 'displayDate', type: 'date' },
        { name: 'numberOfComments', type: 'int' },
        { name: 'name', convert: function (value, record) { return record.get('title'); } },
    ]
});
//{/block}
