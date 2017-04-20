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
 * Shopware Model - Banner
 *
 * Backend - Management for Banner. Create | Modify | Delete.
 * Standard banner model
 */
//{block name="backend/banner/model/banner"}
Ext.define('Shopware.apps.Banner.model.BannerDetail', {
    /**
     * Extends the default extjs 4 model
     * @string
     */
    extend : 'Ext.data.Model',
    /**
     * Set an alias to make the handling a bit easier
     * @string
     */
    alias : 'model.bannermodel',
    /**
     * Defined items used by that model
     *
     * We have to have a splitted date time object here.
     * One part is used as date and the other part is used as time - this is because
     * the form has two separate fields - one for the date and one for the time.
     *
     * @array
     */
    fields : [
        //{block name="backend/banner/model/banner/fields"}{/block}
        { name : 'id',              type: 'int' },
        { name : 'description',     type: 'string' },
        { name : 'validFromDate', type: 'date', dateFormat: 'd.m.Y' },
        { name : 'validFromTime', type: 'date', dateFormat: 'H:i' },
        { name : 'validToDate',   type: 'date', dateFormat: 'd.m.Y' },
        { name : 'validToTime',   type: 'date', dateFormat: 'H:i' },
        { name : 'link',            type: 'string' },
        { name : 'image',             type: 'string' },
        { name : 'media-manager-selection', type: 'string' },
        { name : 'linkTarget',     type: 'string' },
        { name : 'categoryId',      type: 'int' },
        { name : 'extension',       type: 'string' }
    ],

    /**
     * defines the field for the unique identifier - id is default.
     *
     * @int
     */
    idProperty : 'id',
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
