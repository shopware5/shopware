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
 * @package    Notification
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */


/**
 * Shopware Model -  notification article
 * The notification article model of the notification module contains some additional statistics data for the article grid.
 */
//{block name="backend/notification/model/article"}
Ext.define('Shopware.apps.Notification.model.Article', {

    /**
     * Extends the standard ExtJS 4
     * @string
     */
    extend: 'Ext.data.Model',

    /**
     * The fields used for this model
     *
     * @array
     */
    fields:[
        //{block name="backend/notification/model/article/fields"}{/block}
        { name:'name', type:'string' },
        { name:'number', type:'string' },
        { name:'registered', type:'int' },
        { name:'notNotified', type:'int' }
    ],

    /**
     * Configure the data communication
     * @object
     */
    proxy: {
        type: 'ajax',

        /**
         * Configure the url mapping for the different
         * store operations based on
         * @object
         */
        api: {
            read: '{url controller="notification" action="getArticleList"}'
        },

        /**
         * Configure the data reader
         * @object
         */
        reader : {
            type : 'json',
            root : 'data',
            totalProperty: 'totalCount'
        }
    }
});
//{/block}
