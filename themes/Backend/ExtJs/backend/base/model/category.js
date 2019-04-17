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

/**
 * Shopware Model - Global Stores and Models
 *
 * The category model represents a data row of the s_categories or the
 * Shopware\Models\Category\Category doctrine model.
 */
//{block name="backend/base/model/category"}
Ext.define('Shopware.apps.Base.model.Category', {

    /**
     * Defines an alternate name for this class.
     */
    alternateClassName: 'Shopware.model.Category',

    /**
     * Extends the standard Ext Model
     * @string
     */
    extend:'Shopware.data.Model',

    /**
     * unique id
     * @int
     */
    idProperty:'id',

    /**
     * The fields used for this model
     * @array
     */
    fields:[
        //{block name="backend/base/model/category/fields"}{/block}
        { name : 'id', type:'int' },
        { name : 'parent', type:'int' },
        { name : 'name', type:'string' },
        { name : 'position', type:'int' },
        { name : 'active', type:'boolean', defaultValue: true },
        { name : 'childrenCount', type: 'int' },

        // Some tree fields
        { name : 'text', type: 'string' },
        { name : 'cls', type: 'string' },
        { name : 'leaf', type: 'boolean' },
        { name : 'allowDrag', type: 'boolean' }
    ]
});
//{/block}
