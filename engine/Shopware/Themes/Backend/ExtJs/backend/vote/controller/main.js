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
 * @package    Vote
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Vote main controller
 *
 * This controller only creates the main-window and sets the voteStore
 */
//{block name="backend/vote/controller/main"}
Ext.define('Shopware.apps.Vote.controller.Main', {

    /**
    * Extend from the standard ExtJS 4
    * @string
    */
    extend: 'Ext.app.Controller',
    requires: [ 'Shopware.apps.Vote.controller.Vote' ],

    init: function() {
        var me = this;
        me.subApplication.voteStore = me.subApplication.getStore('Votes');
        me.subApplication.voteStore.load();

        me.mainWindow = me.getView('main.Window').create({
            voteStore: me.subApplication.voteStore
        });
        this.callParent(arguments);
    }
});
//{/block}
