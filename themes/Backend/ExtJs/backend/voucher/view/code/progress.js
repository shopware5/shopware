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
 * @package    Order
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/voucher/view/code}

/**
 * Shopware UI - Voucher list main window.
 */
//{block name="backend/voucher/view/code/progress"}
Ext.define('Shopware.apps.Voucher.view.code.Progress', {
    extend:'Enlight.app.Window',
    alias:'widget.voucher-code-progress-window',
    width:550,
    height:80,
    footerButton:false,
    stateful:true,
    autoShow: true,
    layout: {
        align: 'stretch',
        type: 'vbox'
    },
    modal: true,
    bodyPadding: 10,
    closable: false,
    resizable: false,
    maximizable: false,
    minimizable: false,


    initComponent: function () {
        var me = this;

        me.items = [
            me.createProgressBar()
        ];
        me.title = "{s name=detail_codes/progress/title}Generating individual voucher codes{/s}";
        me.callParent(arguments);
    },

    /**
     * Creates the progress which displays the progress status for the document creation.
     */
    createProgressBar: function() {
        return Ext.create('Ext.ProgressBar', {
            animate: true,
            margin: '0 0 15',
            style: 'border-width: 1px !important;',
            cls:'left-align'
        });
    }

});
//{/block}
