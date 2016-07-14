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
 * @package    Banner
 * @subpackage Banner
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Store - Banner
 *
 * Backend - Defines the banner store
 *
 * This store will be loaded automatically and will just request 30 items at once.
 * It will utilize the Banner Model @see Banner Model
 */
//{block name="backend/banner/store/banner"}
Ext.define('Shopware.apps.Banner.store.Banner', {
    extend : 'Ext.data.Store',
    id:'bannerStore',
    autoLoad : false,
    pageSize : 30,
    model : 'Shopware.apps.Banner.model.BannerDetail',
    /**
     * Defines the proxies where the data will later be loaded
     * @obj
     */
    proxy : {
        type : 'ajax',
        api : {
            read    : '{url controller="banner" action="getAllBanners"}',
            update  : '{url controller="banner" action="updateBanner"}',
            create  : '{url controller="banner" action="createBanner"}',
            destroy : '{url controller="banner" action="deleteBanner" targetField=banners}'
        },
        // Data will be delivered as json and sits in the field data
        reader : {
            type : 'json',
            root : 'data'
        }
    }
});
//{/block}
