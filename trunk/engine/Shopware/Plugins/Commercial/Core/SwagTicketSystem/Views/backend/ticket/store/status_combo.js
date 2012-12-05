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
 * @subpackage Status
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/ticket/main}
//{block name="backend/ticket/store/status_combo"}
Ext.define('Shopware.apps.Ticket.store.StatusCombo', {
    /**
     * Extend for the standard ExtJS 4
     * @string
     */
    extend:'Shopware.apps.Ticket.store.Status',

    /**
     * A config object containing one or more event handlers to be added to this object during initialization
     * @object
     */
    listeners: {
        /**
         * Fires whenever records have been prefetched
         * used to add some default values to the combobox
         *
         * @event load
         * @param [object] store - Ext.data.Store
         * @return void
         */
        load: function(store) {
            store.insert(0, Ext.create('Shopware.apps.Ticket.model.Status', {
                id: 0,
                description: '{s name=status_store/no_assigment}No assignment{/s}'
            }));
        }
    }
});
//{/block}

