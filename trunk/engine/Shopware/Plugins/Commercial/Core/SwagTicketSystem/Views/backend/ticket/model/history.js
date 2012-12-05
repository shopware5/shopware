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
 * @package    TicketSystem
 * @subpackage Main
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */
//{block name="backend/ticket/model/history"}
Ext.define('Shopware.apps.Ticket.model.History', {
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
		//{block name="backend/ticket/model/history/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'ticketId', type: 'int' },
        { name: 'email', type: 'string' },
        { name: 'swUser', type: 'string' },
        { name: 'subject', type: 'string' },
        { name: 'message', type: 'string' },
        { name: 'receipt', type: 'date' },
        { name: 'support_type', type: 'string' },
        { name: 'receiver', type: 'string' },
        { name: 'direction', type: 'string' }
    ]
});
//{/block}
