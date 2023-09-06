/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 *
 * @category   Shopware
 * @package    NewsletterManager
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Model - Settings model
 * The settings model holds various fields needed for the newsletter settings
 */
//{block name="backend/newsletter_manager/model/settings"}
Ext.define('Shopware.apps.NewsletterManager.model.Settings', {
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
        //{block name="backend/newsletter_manager/model/settings/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'subject', type: 'string' },
        { name: 'senderId', type: 'int', defaultValue: 1 },
        { name: 'customerGroup', type: 'string', defaultValue: '' },
        { name: 'languageId', type: 'int',  defaultValue: 1 },
        { name: 'recipients', type: 'int', defaultValue: 0 },
        { name: 'dispatch', type: 'int', defaultValue: 1 },
        { name: 'content', type: 'string' },

        { name: 'senderName', type: 'string' },
        { name: 'senderMail', type: 'string' },
        { name: 'subject', type: 'string' },
        { name: 'plaintext', type: 'boolean' }
    ]

});
//{/block}
