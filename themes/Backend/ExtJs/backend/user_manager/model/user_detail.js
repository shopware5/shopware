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
 * @package    UserManager
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware User-Manager - User details model
 *
 * todo@all: Documentation
 */
//{block name="backend/user_manager/model/detail"}
Ext.define('Shopware.apps.UserManager.model.UserDetail', {
    extend: 'Ext.data.Model',
    fields: [
        //{block name="backend/user_manager/model/detail/fields"}{/block}
        'id',
        'username',
        'localeId',
        'roleId',
        'lastlogin',
        'name',
        'email',
        'active',
        'apiKey',
        'failedlogins',
        'password',
        { name: 'extendedEditor', type: 'boolean'},
        { name: 'disabledCache', type: 'boolean'},
        { name: 'lockedUntil', type: 'date'},
        {
            name: 'locked',
            type: 'boolean',
            convert: function (defaultValue, record) {
                var lockedUntil = record.get('lockedUntil') ? record.get('lockedUntil').getTime() : 0;
                var currentDate = new Date();
                return lockedUntil >= currentDate.getTime();
            }
        }
    ],
    proxy: {
        type: 'ajax',
        api: {
            read: '{url controller="UserManager" action="getUserDetails"}',
            create: '{url controller="UserManager" action="updateUser"}',
            update: '{url controller="UserManager" action="updateUser"}',
            destroy: '{url controller="UserManager" action="deleteUser"}'
        },
        reader: {
            type: 'json',
            root: 'data'
        }
    },
    validations: [
        { field: 'username', type: 'length', min: 6 },
        { field: 'password', type: 'length', min: 6 },
        { field: 'name', type: 'length', min: 6 }
    ]
});
//{/block}
