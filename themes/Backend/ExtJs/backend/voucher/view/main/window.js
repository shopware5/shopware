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
 * @package    Voucher
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/voucher/view/main}

/**
 * Shopware UI - Voucher list main window.
 *
 * todo@all: Documentation
 */
//{block name="backend/voucher/view/main/window"}
Ext.define('Shopware.apps.Voucher.view.main.Window', {
    extend: 'Enlight.app.Window',
    title: '{s name=window/main_title}Voucher administration{/s}',
    alias: 'widget.voucher-main-window',
    border: false,
    autoShow: true,
    layout: 'border',
    height: '90%',
    width: 925,

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;
        me.items = [
            { xtype: 'voucher-voucher-list', listStore: me.listStore }
        ];

        me.callParent(arguments);
    }
});
//{/block}
