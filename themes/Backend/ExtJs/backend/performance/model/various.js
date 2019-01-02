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
 * @package    Performance
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Base config model which holds references to the config items
 */
//{block name="backend/performance/model/various"}
Ext.define('Shopware.apps.Performance.model.Various', {

    /**
     * Extends the standard Ext Model
     * @string
     */
    extend:'Ext.data.Model',

    /**
     * Contains the model fields
     * @array
     */
    fields:[
        //{block name="backend/performance/model/various/fields"}{/block}
        { name:'id', type:'int' },
        { name: 'disableShopwareStatistics', type: 'bool'},
        { name: 'LastArticles:lastarticles_show', type: 'bool'},
        { name: 'LastArticles:lastarticlestoshow', type: 'int'},
        { name: 'disableArticleNavigation', type: 'bool'},
        { name: 'http2Push', type: 'bool'},
        { name: 'minifyHtml', type: 'bool'}
    ]

});
//{/block}
