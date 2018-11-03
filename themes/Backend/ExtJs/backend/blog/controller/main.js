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
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Controller - blog main backend module
 *
 * The blog module main controller handles the initialisation of the blog backend list.
 */
//{block name="backend/blog/controller/main"}
Ext.define('Shopware.apps.Blog.controller.Main', {

    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
    extend: 'Ext.app.Controller',

    mainWindow: null,

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the subapplication
     *
     * @return void
     */
    init: function() {
        var me = this;
        me.subApplication.treeStore =  me.subApplication.getStore('Tree').load();
        me.subApplication.listStore =  me.subApplication.getStore('List').load();

        me.subApplication.detailStore =  me.subApplication.getStore('Detail');
        me.subApplication.categoryPathStore =  me.subApplication.getStore('CategoryPath');
        me.subApplication.templateStore =  me.subApplication.getStore('Template');
        me.subApplication.commentStore =  me.subApplication.getStore('Comment');

        me.mainWindow = me.getView('main.Window').create({
            listStore: me.subApplication.listStore,
            treeStore: me.subApplication.treeStore,
            detailStore: me.subApplication.detailStore,
            categoryPathStore: me.subApplication.categoryPathStore,
            templateStore: me.subApplication.templateStore,
            commentStore: me.subApplication.commentStore

    });
        me.callParent(arguments);
    }
});
//{/block}
