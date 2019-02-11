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
 * The blog comment model of the blog module represent a data row of the s_blog_comments or the
 * Shopware\Models\Blog\Comment doctrine model, with some additional data for the additional information panel.
 */
//{block name="backend/blog/model/comment"}
Ext.define('Shopware.apps.Blog.model.Comment', {
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
        //{block name="backend/blog/model/comment/fields"}{/block}
        { name : 'id', type : 'int' },
        { name : 'name', type : 'string' },
        { name : 'headline', type : 'string' },
        { name : 'content', type : 'string' },
        { name : 'creationDate', type : 'date' },
        { name : 'active', type : 'boolean' },
        { name : 'points', type : 'float' },
        { name : 'eMail', type : 'string' },
        { name : 'shopId', type : 'int', useNull: true },
    ],
    /**
    * If the name of the field is 'id' extjs assumes autmagical that
    * this field is an unique identifier.
    */
    idProperty : 'id',
    /**
    * Configure the data communication
    * @object
    */
    proxy : {
        type : 'ajax',
        api:{
            read:   '{url action=getBlogComments}',
            update: '{url action=updateBlogComment targetField=blogComments}',
            destroy:'{url action=deleteBlogComment targetField=blogComments}'
        },
        reader : {
            type : 'json',
            root : 'data',
            totalProperty: 'totalCount'
        }
    }
});
//{/block}
