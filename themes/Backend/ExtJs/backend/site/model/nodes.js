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
 * @package    Site
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

//{block name="backend/site/model/nodes"}
Ext.define('Shopware.apps.Site.model.Nodes', {
    extend: 'Ext.data.Model',
    fields: [
        //{block name="backend/site/model/nodes/fields"}{/block}
        { name: 'key', type: 'string' },
        { name: 'id', type: 'string', convert: function(v, r) { return r.data.key || v; } },
        { name: 'active', type: 'boolean', defaultValue: true },
        { name: 'description', type: 'string' },
        { name: 'name', type: 'string' },
        { name: 'text', convert: function(v, r) { return r.data.name ? r.data.name : v; } },
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
        { name: 'metaDescription', type: 'string' }
    ],

    proxy: {
        type: 'ajax',
        api: {
            read: '{url action="getNodes"}',
            create: '{url action="saveSite"}',
            update: '{url action="saveSite"}',
            destroy: '{url action="deleteSite"}'
        },
        reader: {
            type: 'json',
            root: 'nodes'
        }
    }
});
//{/block}
