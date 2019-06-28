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

//{block name="backend/category/model/manual_sorting"}
Ext.define('Shopware.apps.Category.model.ManualSorting', {

    /**
     * Extends the standard Ext Model
     * @string
     */
    extend:'Ext.data.Model',
    /**
     * Configure the data communication
     * @object
     */
    fields:[
        //{block name="backend/category/model/manual_sorting/fields"}{/block}
        { name : 'id', type: 'int' },
        { name : 'active', type: 'boolean' },
        { name : 'name', type: 'string' },
        { name : 'thumbnail', type: 'string', useNull: true, defaultValue: null },
        { name : 'position', type: 'int', useNull: true, defaultValue: null },
        { name : 'price', type: 'string' },
    ]
});
//{/block}
