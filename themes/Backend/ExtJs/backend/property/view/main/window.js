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
 * @package    Property
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/property/view/main}
//{block name="backend/property/view/main/window"}
Ext.define('Shopware.apps.Property.view.main.Window', {
    extend: 'Enlight.app.Window',
    alias : 'widget.property-main-window',
    title : '{s name=title}Article properties{/s}',
    width: '70%',
    height: '50%',
    minHeight: 400,
    stateful: true,
    stateId: 'shopware-property-main-window',
    layout: 'fit',
    autoScroll: true,

    /**
     * Initializes the component and builds up the main interface
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.items = [
            {
                xtype: 'container',
                minWidth: 915,
                layout: {
                    type: 'hbox',
                    pack: 'start',
                    align: 'stretch'
                },
                items: [{
                    xtype: 'property-main-setGrid',
                    setStore: me.setStore,
                    region:'west',
                    split: true,
                    flex: 3
                }, {
                    xtype: 'splitter'
                }, {
                    xtype: 'property-main-setAssignGrid',
                    setAssignStore: me.setAssignStore,
                    split: true,
                    region:'center',
                    flex: 2
                }, {
                    xtype: 'splitter'
                }, {
                    bodyStyle: 'border-left: 0 none',
                    style: 'border-left: 0 none',
                    xtype: 'property-main-groupGrid',
                    groupStore: me.groupStore,
                    split: true,
                    region:'west',
                    flex: 3
                }, {
                    xtype: 'splitter'
                }, {
                    xtype: 'property-main-optionGrid',
                    optionStore: me.optionStore,
                    split: true,
                    flex: 2,
                    region:'center',
                    disabled: true
                }]
            }
        ];

        me.callParent(arguments);
    }
});
//{/block}
