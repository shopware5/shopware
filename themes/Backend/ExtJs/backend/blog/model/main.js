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
 * @package    Blog
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Model -  Blog backend module.
 *
 * The blog list model of the blog module represent a part of a data row of the s_blog or the
 * Shopware\Models\Blog\Blog doctrine model, with some additional data for the additional information panel.
 */
//{block name="backend/blog/model/main"}
Ext.define('Shopware.apps.Blog.model.Main', {
    /**
    * Extends the standard ExtJS 4
    * @string
    */
    extend : 'Ext.data.Model',
    /**
    * The fields used for this model
    * @array
    */
    fields : [
        //{block name="backend/blog/model/main/fields"}{/block}
        { name : 'id', type : 'int' },
        { name : 'title', type : 'string' },
        { name : 'shortDescription', type : 'string' },
        { name : 'description', type : 'string' },
        { name : 'active', type : 'boolean' },
        { name : 'views', type : 'int' },
        { name : 'displayDate', type : 'date' },
        { name : 'numberOfComments', type : 'int' }
    ],

    /**
    * Configure the data communication
    * @object
    */
    proxy : {
        type : 'ajax',
        api:{
            read:   '{url action=getList}',
            destroy:'{url action=deleteBlogArticle targetField=blogArticles}'
        },
        reader : {
            type : 'json',
            root : 'data',
            totalProperty: 'totalCount'
        }
    }
});
//{/block}
