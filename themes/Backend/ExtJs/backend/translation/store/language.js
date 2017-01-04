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
 * @package    Translation
 * @subpackage Store
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware - Translation Manager Language Store
 *
 * Model for the translatable languages.
 */

//{block name="backend/translation/store/language"}
Ext.define('Shopware.apps.Translation.store.Language',
/** @lends Ext.data.TreeStore# */
{
    /**
     * The parent class that this class extends
     * @string
     */
    extend: 'Ext.data.TreeStore',

    /**
     * Name of the Model associated with this store
     * @string
     */
    model: 'Shopware.apps.Translation.model.Language',

    /**
     * Remove previously existing child nodes before loading.
     * @boolean
     */
    clearOnLoad: false,

    /**
     * Indicates if the store's load method is automatically called after creation.
     * @boolean
     */
    autoLoad: false
});
//{/block}
