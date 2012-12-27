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
//{block name="backend/config/model/form/template"}
Ext.define('Shopware.apps.Config.model.form.Template', {
    extend: 'Shopware.apps.Base.model.Template',

    fields: [
		//{block name="backend/config/model/form/template/fields"}{/block}
        { name: 'description', type : 'string', useNull: true },
        { name: 'author', type : 'string', useNull: true },
        { name: 'license', type : 'string', useNull: true },
        { name: 'esi', type : 'boolean' },
        { name: 'styleSupport', type : 'boolean' },
        { name: 'emotion', type : 'boolean' },
        { name: 'enabled', type : 'boolean' },
        { name: 'preview', type : 'boolean' },
        { name: 'previewThumb', type : 'string', useNull: true },
        { name: 'previewFull', type : 'string', useNull: true }
    ]
});
//{/block}