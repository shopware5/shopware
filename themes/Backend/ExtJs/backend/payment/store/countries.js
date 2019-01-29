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
 * @package    Payment
 * @subpackage Store
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Store - Country list backend module.
 *
 * The countries store is used to display all available countries.
 */

//{block name="backend/payment/store/countries"}
Ext.define('Shopware.apps.Payment.store.Countries', {
    extend : 'Ext.data.Store',
    autoLoad : false,
    pageSize : 30,
    model : 'Shopware.apps.Payment.model.Country',

    remoteSort: true,
    sorters: [
        {
            property: 'countries.active',
            direction: 'DESC'
        },
        {
            property: 'countries.name',
            direction: 'ASC'
        }
    ]
});
//{/block}
