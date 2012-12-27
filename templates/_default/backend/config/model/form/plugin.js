/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * todo@all: Documentation
 */
//{block name="backend/config/model/form/plugin"}
Ext.define('Shopware.apps.Config.model.form.Plugin', {
    extend:'Ext.data.Model',

    fields: [
		//{block name="backend/config/model/form/plugin/fields"}{/block}
        { name: 'id', type:'int' },
        { name: 'label', type:'string' },
        { name: 'name', type:'string' },
        { name: 'source', type:'string' },
        { name: 'namespace', type:'string' },
        { name: 'path', type:'string', convert: function(v, record) {
            return record.data.source + '/' + record.data.namespace + '/' + record.data.name;
        } },
        { name: 'author', type:'string' },
        { name: 'version', type:'string' },
        { name: 'updateVersion', type:'string' },
        { name: 'copyright', type:'string' },
        { name: 'description', type:'string' },
        { name: 'support', type:'string' },
        { name: 'link', type:'string' },
        { name: 'active', type:'boolean' },
        { name: 'added', type:'date' },
        { name: 'installed', type:'date' },
        { name: 'configFormId', type:'int', convert: function(v, record) {
            return v || record.raw.configForms && record.raw.configForms[0] && record.raw.configForms[0].id;
        } },
        { name: 'capabilityUpdate', type: 'boolean' },
        { name: 'capabilityEnable', type: 'boolean' },
        { name: 'capabilityInstall', type: 'boolean' }
    ]
});
//{/block}