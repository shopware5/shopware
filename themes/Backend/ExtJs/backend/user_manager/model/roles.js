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
 * Shopware Backend - Auth Main Model
 *
 * todo@all: Documentation
 */
//{block name="backend/user_manager/model/roles"}
Ext.define('Shopware.apps.UserManager.model.Roles', {
    extend: 'Ext.data.Model',
    fields: [
        //{block name="backend/user_manager/model/roles/fields"}{/block}
        'id', 'parentId', 'name', 'description', 'source', { name: 'enabled', type: 'bool' }, { name: 'admin', type: 'bool' }],
    proxy: {
        type: 'ajax',
        api: {
            read: '{url controller="UserManager" action="getRoles"}',
            create: '{url controller="UserManager" action="updateRole"}',
            update: '{url controller="UserManager" action="updateRole"}',
            destroy: '{url controller="UserManager" action="deleteRole"}'
        },
        reader: {
            type: 'json',
            root: 'data',
            totalProperty:'total'
        }
    }
});
//{/block}
