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
 * Shopware UI - Global Stores and Models
 *
 * The supplier model represents a data row of the s_articles_supplier or the
 * Shopware\Models\Supplier\Supplier doctrine model.
 */
//{block name="backend/base/model/supplier"}
Ext.define('Shopware.apps.Base.model.Supplier', {

    /**
     * Defines an alternate name for this class.
     */
    alternateClassName: 'Shopware.model.Supplier',

    /**
    * Extends the standard Ext Model
    * @string
    */
    extend: 'Shopware.data.Model',

    /**
     * unique id
     * @int
     */
    idProperty : 'id',

    /**
    * The fields used for this model
    * @array
    */
    fields: [
        //{block name="backend/base/model/supplier/fields"}{/block}
        { name : 'id', type : 'int' },
        { name : 'name', type : 'string' },
        { name : 'image', type : 'string' },
        { name : 'link', type : 'string' },
        { name : 'description', type : 'string' }
    ]
});
//{/block}
