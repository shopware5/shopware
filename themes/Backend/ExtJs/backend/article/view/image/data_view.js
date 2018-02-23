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
 * @package    Article
 * @subpackage Detail
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/article/view/main}
//{block name="backend/article/view/image/data_view"}
Ext.define('Shopware.apps.Article.view.image.DataView', {
    extend: 'Ext.view.View',
    itemSelector: '.article-thumb-wrap',
    name: 'image-listing',
    emptyText: '{s name="image/list/empty_text"}No media found{/s}',
    multiSelect: true,
    padding: '10 10 20',
    flex: 1,
    autoScroll: true,

    initComponent: function () {
        this.dragViewSelectorPlugin = Ext.create('Ext.ux.DataView.DragSelector', {});
        this.plugins = [ this.dragViewSelectorPlugin ];

        this.listeners = {
            scope: this,
            itemclick: this.onItemClick
        };

        this.callParent(arguments);
    },

    refresh: function () {
        this.callParent(arguments);

        if (!this.dragViewSelectorPlugin.proxy) {
            return;
        }

        // Fixes an ExtJS issue, have a look at https://www.sencha.com/forum/showthread.php?226676-4-1-1-rc2-Ext-ux-DataView-DragSelector-bugs
        this.dragViewSelectorPlugin.proxy.destroy();
        delete this.dragViewSelectorPlugin.proxy;
    },

    /**
     * Event handler which will be fired when the user clicks an image in the media grid component. If the user clicks on the
     * gear icon, it will open up the assignment configuration window.
     *
     * @param { Ext.data.Record } record
     * @param { HTMLElement } el
     * @param { Number } index
     * @param { Event } event
     */
    onItemClick: function(record, el, index, event) {
        var target = event.target,
            $target = Ext.get(target);

        // Check if the user clicked on the actual settings gear, otherwise just select the media
        if (!$target || !$target.hasCls('mapping-config')) {
            return;
        }

        this.fireEvent('openSettingsForm', record);
    }
});
//{/block}
