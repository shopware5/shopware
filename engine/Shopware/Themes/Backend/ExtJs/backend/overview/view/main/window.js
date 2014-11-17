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
 * @package    Overview
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/overview/view/main}

/**
 * todo@all: Documentation
 */
//{block name="backend/overview/view/main/window"}
Ext.define('Shopware.apps.Overview.view.main.Window', {
    extend: 'Enlight.app.Window',
    alias : 'widget.overview-main-window',
    title : '{s name=title}Overview{/s}',
    layout: 'fit',
    width: 800,
    height: '90%',
    stateful: true,
    stateId: 'shopware-overview-main-window',

    /**
     * Initializes the component and builds up the main interface
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.items = [{
            xtype: 'overview-main-grid',
            store: me.overviewStore
        }];

        me.callParent(arguments);
    }
});
//{/block}
