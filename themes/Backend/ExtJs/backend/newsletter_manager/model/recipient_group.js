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
 * @package    NewsletterManager
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Model - recipientGroup model
 * This model holds summarizes recipient group with number of recipients and name of the group
 * Additionally there is a groupKey field. This will help to tell apart newsletter groups and customer groups
 */
//{block name="backend/newsletter_manager/model/recipient_group"}
Ext.define('Shopware.apps.NewsletterManager.model.RecipientGroup', {
    /**
     * Extends the standard Ext Model
     * @string
     */
    extend: 'Ext.data.Model',

    /**
     * The fields used for this model
     * @array
     */
    fields: [
        //{block name="backend/newsletter_manager/model/recipient_group/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'internalId', type: 'int' },
        { name: 'streamId', type: 'int', useNull: true, defaultValue: null },
        { name: 'number', type: 'int' },
        { name: 'name', type: 'string' },
        { name: 'groupkey', type: 'string' },
        { name: 'isCustomerGroup', type: 'boolean' }
    ]

});
//{/block}
