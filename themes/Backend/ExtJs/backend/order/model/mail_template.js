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
 * @package    Order
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Model - Order list backend module.
 *
 * Model that represents a mail template
 */
//{block name="backend/order/model/mail_template"}
Ext.define('Shopware.apps.Order.model.MailTemplate', {

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
        //{block name="backend/order/model/mail_template/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'name', type: 'string' },
        { name: 'mailtype', type: 'int' },
        { name: 'isHtml', type: 'boolean' },
        { name: 'fromMail', type: 'string' },
        { name: 'fromName', type: 'string' },
        { name: 'subject', type: 'string' },
        { name: 'content', type: 'string' },
        { name: 'contentHtml', type: 'string' },
        { name: 'context', type: 'auto' },
        { name: 'displayName', type: 'string' },
    ]

});
//{/block}
