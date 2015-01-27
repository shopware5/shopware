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
 * Shopware Model
 *
 * This Model is extended from the Ext.data.Model to add the category attributes
 *
 */
//{block name="backend/category/model/attribute"}
Ext.define('Shopware.apps.Category.model.Attribute', {

    /**
     * Extends the standard Ext Model
     * @string
     */
    extend:'Shopware.model.Category',
    /**
     * Configure the data communication
     * @object
     */
    fields:[
		//{block name="backend/category/model/attribute/fields"}{/block}
        { name : 'id', type: 'int' },
        { name : 'categoryId', type: 'int', useNull: true },
        { name : 'attribute1', type: 'string', useNull:true, defaultValue: null },
        { name : 'attribute2', type: 'string', useNull:true, defaultValue: null },
        { name : 'attribute3', type: 'string', useNull:true, defaultValue: null },
        { name : 'attribute4', type: 'string', useNull:true, defaultValue: null },
        { name : 'attribute5', type: 'string', useNull:true, defaultValue: null },
        { name : 'attribute6', type: 'string', useNull:true, defaultValue: null }
    ]
});
//{/block}
