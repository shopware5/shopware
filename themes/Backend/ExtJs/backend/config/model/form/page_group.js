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
//{block name="backend/config/model/form/page_group"}
Ext.define('Shopware.apps.Config.model.form.PageGroup', {
    extend: 'Ext.data.Model',
    fields: [
        //{block name="backend/config/model/form/page_group/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'name', type: 'string' },
        { name: 'key', type: 'string' },
        { name: 'mappingId', type: 'int', convert: function(v, record) {
            if (v === null) {
                return null;
            }

            if (Ext.typeOf(v) === 'number') {
                return v;
            }

            if (record.raw && record.raw.mapping && record.raw.mapping.id) {
                return record.raw.mapping.id;
            }

            return v;
        }, useNull: true },
        { name: 'mapping', type: 'string', convert: function(v, record) {
            return (v && v.name) || v;
        }, useNull: true },
        { name: 'active', type: 'boolean' }
    ]
});
//{/block}
