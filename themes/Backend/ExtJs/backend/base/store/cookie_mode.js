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
 * @subpackage Store
 * @version    $Id$
 * @author shopware AG
 */


Ext.define('Shopware.apps.Base.store.CookieMode', {
    extend: 'Ext.data.Store',
    model: 'Shopware.apps.Base.model.CookieMode',

    alternateClassName: 'Shopware.store.CookieMode',
    storeId: 'base.CookieMode',

    data: [
        {
            id: 0, // Shopware\Components\Privacy\CookieRemoveSubscriber::COOKIE_MODE_NOTICE
            name: '{s name="privacy/cookie_mode_option_0" namespace="backend/application/main"}{/s}'
        },
        {
            id: 1, // Shopware\Components\Privacy\CookieRemoveSubscriber::COOKIE_MODE_TECHNICAL
            name: '{s name="privacy/cookie_mode_option_1" namespace="backend/application/main"}{/s}'
        },
        {
            id: 2, // Shopware\Components\Privacy\CookieRemoveSubscriber::COOKIE_MODE_ALL
            name: '{s name="privacy/cookie_mode_option_2" namespace="backend/application/main"}{/s}'
        }
    ]
});
