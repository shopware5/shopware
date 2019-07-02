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
 * @subpackage Store
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Backend - User Manager Detail model
 *
 * The detail model is used to load a role with all assigned privileges. The model
 * is only used to assign the selected tree nodes of the user_manager/view/rules/tree.js component
 * to the selected role.
 */
//{block name="backend/user_manager/store/rules"}
Ext.define('Shopware.apps.UserManager.store.Rules', {
    extend: 'Ext.data.TreeStore',
    /**
     * Enable remote filtering
     * @boolean
     */
    remoteFilter:true,
    model : 'Shopware.apps.UserManager.model.Rules'
});
//{/block}
