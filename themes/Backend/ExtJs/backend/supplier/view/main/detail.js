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
 * @package    Supplier
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

/*{namespace name=backend/supplier/view/main}*/

/**
 * Shopware UI - Supplier Details
 *
 * This file represents a panel which displays the detail
 * informations for a specific supplier.
 */
//{block name="backend/supplier/view/main/detail"}
Ext.define('Shopware.apps.Supplier.view.main.Detail', {
    extend: 'Ext.panel.Panel',
    cls: 'detail-view',
    collapsed: true,
    collapsible: true,
    title: 'Details',
    region: 'east',
    width: 220,
    alias: 'widget.supplier-main-detail',

    /**
     * Init the main detail component.
     * @return void
     */
    initComponent: function() {
        var me = this;
        me.items = me.createDataView();
        me.callParent(arguments);
    },
    /**
     * Creates and returns a Ext.view.View to display detail information about an supplier.
     *
     * @return Ext.view.View
     */
    createDataView: function() {
        var me = this;

        me.dataView = Ext.create('Ext.Component', {
            tpl: me.getTemplate()
        });
        return me.dataView;
    },
    /**
     * Returns an array of strings. See ExtJS Templates
     * @return array of string
     */
    getTemplate : function() {
        return [
            '{literal}',
                // Check if we're having a logo
                '<tpl if="image">',
                    '<div class="supplier-logo">',
                        '<img src="{literal}{image}{/literal}" alt="{name}" style="max-height: 200px; max-width: 150px" />',
                    '</div>',
                '</tpl>',
                '<div class="supplier-info">',
                    '<div class="supplier-desc">{description}</div>',
                    // Check if we're having a link here
                    '<tpl if="link">',
                        '<p class="action">',
                            '{/literal}{s name=moreinfo}More information at{/s}{literal} <a href="{link}" title="{name}">{link}</a>',
                        '</p>',
                    '</tpl>',
                '</div>',
            '{/literal}'
        ];
    }
});
//{/block}
