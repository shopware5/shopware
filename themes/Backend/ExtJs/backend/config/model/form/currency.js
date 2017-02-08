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

//{block name="backend/config/model/form/currency"}
Ext.define('Shopware.apps.Config.model.form.Currency', {
    extend: 'Shopware.apps.Base.model.Currency',

    fields: [
        //{block name="backend/config/model/form/currency/fields"}{/block}
        { name: 'symbol', type: 'string', useNull: true },
        { name: 'symbolPosition', type: 'int', useNull: true },
        { name: 'default', type: 'boolean' },
        { name: 'factor', type: 'float' },
        { name: 'position', type: 'int', useNull: true },
        { name: 'deletable', type: 'boolean', convert: function(v, r) { return !r.data.default; } }
    ]
});
//{/block}
