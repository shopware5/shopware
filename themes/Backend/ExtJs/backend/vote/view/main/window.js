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
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/vote/main}

/**
 * Shopware UI - Vote view main window
 *
 * This is the main window, which is rendered first.
 * It contains all other components.
 */
//{block name="backend/vote/view/main/window"}
Ext.define('Shopware.apps.Vote.view.main.Window', {
    extend: 'Enlight.app.Window',
    title: '{s name=window_title}Votes{/s}',
    cls: Ext.baseCSSPrefix + 'vote-window',
    alias: 'widget.vote-main-window',
    autoShow: true,
    layout: 'border',
    height: '90%',
    width: 925,
    autoScroll: true,
    stateful:true,
    stateId:'shopware-votes-window',

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.items = [
            {
                xtype: 'vote-main-list',
                voteStore: me.voteStore
            },{
                xtype: 'vote-main-toolbar'
            },{
                xtype: 'vote-main-infopanel'
            }
        ];
        me.callParent(arguments);
    }
});
//{/block}
