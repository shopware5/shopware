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
 * @package    Site
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

//{block name="backend/site/model/groups"}
Ext.define('Shopware.apps.Site.model.Groups', {
    extend: 'Ext.data.Model',
    idProperty: 'templateVariable',
    root: 'groups',
    fields: [
        //{block name="backend/site/model/groups/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'templateVariable', type: 'string', mapping: 'key' },
        { name: 'groupName', type: 'string', mapping: 'name' }
    ],

    proxy: {
        type: 'ajax',
        url: '{url action=getGroups}',
        reader: {
            type: 'json',
            root: 'groups'
        }
    }
});
//{/block}
