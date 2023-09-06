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
 * @package    Base
 * @subpackage Store
 * @version    $Id$
 * @author shopware AG
 */

//{block name="backend/base/store/landing_page"}
Ext.define('Shopware.apps.Base.store.LandingPage', {

    alternateClassName: 'Shopware.store.LandingPage',

    extend: 'Ext.data.Store',

    storeId: 'base.LandingPage',

    remoteSort: true,

    remoteFilter: true,

    model: 'Shopware.apps.Base.model.LandingPage',

    proxy: {
        type: 'ajax',

        url: '{url action=getLandingPages}',

        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    },
}).create();
//{/block}
