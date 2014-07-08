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
 * @package    Article
 * @subpackage Detail
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Article detail page - Sidebar
 * The accessory component is an extension of the sidebar.similar component.
 * These both components have the same fields and events. The accessory component
 * has an additional form field "display as bundle".
 * The events of this component handled in the detail controller.
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/view/detail/sidebar/accessory"}
Ext.define('Shopware.apps.Article.view.detail.sidebar.Accessory', {

    /**
     * Define that the accessory component is an extension of the similar accordion item.
     */
    extend: 'Shopware.apps.Article.view.detail.sidebar.Similar',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.article-sidebar-accessory',
    /**
     * Set css class for this component
     * @string
     */
    cls: Ext.baseCSSPrefix + 'article-sidebar-accessory',

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        title:'{s name=detail/sidebar/accessory/title}Accessory articles{/s}',
        notice:'{s name=detail/sidebar/accessory/notice}At this point you have the option of linking other articles with the current article. The linked articles are automatically displayed on the article detail page.{/s}',
        articleSearch:'{s name=detail/sidebar/accessory/article_search}Article{/s}',
        assignment: {
            field: '{s name=detail/sidebar/accessory/assignment_field}Assignment{/s}',
            box: '{s name=detail/sidebar/accessory/assignment_box}Assign each other{/s}'
        },
        bundle: {
            field: '{s name=detail/sidebar/accessory/bundle_field}Display{/s}',
            box: '{s name=detail/sidebar/accessory/bundle_box}Display as bundle{/s}'
        },
        button:'{s name=detail/sidebar/accessory/button}Add article{/s}',
        gridTitle:'{s name=detail/sidebar/accessory/grid_title}Assigned accessory articles{/s}',
        name:'{s name=detail/sidebar/accessory/name}Article name{/s}',
        edit:'{s name=detail/sidebar/accessory/edit}Edit entry{/s}',
        delete:'{s name=detail/sidebar/accessory/delete}Remove entry{/s}'
    },

    /**
     * Helper property which contains the name of the add event which fired when the user
     * clicks the button of the form panel
     */
    addEvent: 'addAccessoryArticle',

    /**
     * Helper property which contains the name of the remove event which fired when the user
     * clicks the action column of the grid panel
     */
    removeEvent: 'removeAccessoryArticle',

    listingName: 'accessory-listing',

    /**
     * Registers additional component events.
     */
    registerEvents: function() {
    	this.addEvents(
            /**
             * Event will be fired when the user want to add a similar article
             *
             * @event
             */
            'addAccessoryArticle',

            /**
             * Event will be fired when the user want to remove an assigned similar article
             *
             * @event
             */
            'removeAccessoryArticle'
    	);
    },

    /**
     * Override of the createFormItems function of the sidebar.Similar component.
     * The accessory component has an additional field named "bundle" to display
     * the assigned article as bundle article.
     * @return
     */
    createFormItems: function() {
        var me = this;
        me.articleSearch = me.createArticleSearch();

        return [
            me.articleSearch,
            {
                xtype: 'checkbox',
                name: 'cross',
                fieldLabel: me.snippets.assignment.field,
                boxLabel: me.snippets.assignment.box,
                inputValue: true,
                uncheckedValue: false
            },
            {
                xtype: 'checkbox',
                name: 'bundle',
                hidden: true,
                fieldLabel: me.snippets.bundle.field,
                boxLabel: me.snippets.bundle.box,
                inputValue: true,
                uncheckedValue: false
            },
            me.createAddButton()
        ]
    }
});
//{/block}
