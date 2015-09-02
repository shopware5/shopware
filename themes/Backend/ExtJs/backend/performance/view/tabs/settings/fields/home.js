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
 * @package    Customer
 * @subpackage Detail
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/performance/main}

/**
 * Check fieldSet
 */
//{block name="backend/performance/view/tabs/settings/fields/home"}
Ext.define('Shopware.apps.Performance.view.tabs.settings.fields.Home', {
    /**
     * Define that the base field set is an extension of the "Base" fieldSet
     * @string
     */
    extend:'Shopware.apps.Performance.view.tabs.settings.fields.Base',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.performance-tabs-settings-home',

    /**
     * Description of the fieldSet
     */
    caption: '{s name=tabs/settings/home/title}Home{/s}',

    layout: {
        type: 'vbox',
        align: 'stretch'
    },

    /**
     * Component event method which is fired when the component
     * is initials. The component is initials when the user
     * want to create a new customer or edit an existing customer
     * @return void
     */
    initComponent:function () {
        var me = this;

        me.items = me.getItems();

        me.callParent(arguments);
    },

    getItems: function() {
        var me = this,
            warning = '{s name=fieldset/main/warning}Zu jedem Menüpunkt erhalten Sie korrespondierende Informationen in unserem Wiki. Bevor Sie Einstellungen modifizieren, sollten Sie also die Hinweise in unserer Dokumentation beachten!{/s}',
            info = '{s name=fieldset/main/information}In diesem Bereich können Sie verschiedene Einstellungen vornehmen, die die Performance Ihrer Shopware-Installation betreffen.<br><br>Bitte beachten Sie auch unseren allgemeinen Performance-Guide unter <a target=link href=_blank>Performance Tipps Shopware 5</a>{/s}';

        return [
            {
                xtype: 'fieldset',
                title: '{s name=fieldset/main/headline}Performance Einstellungen{/s}',
                items: [
                    {
                        xtype: 'container',
                        border: false,
                        bodyPadding: 20,
                        style: 'font-weight: 700; line-height: 20px;',
                        html: '<span style="color: #4d4d4d;">' +  info + '</span><br><br>' + '<p style="color: #ba2323">' + warning + '</p>'
                    }
                ]
            },
            me.createGrid()

        ];
    },

    createGrid: function() {
        var me = this;

        return Ext.create('Ext.grid.Panel', {
            columns: me.createColumns(),
            flex: 1,
            minHeight: 150,
            border: false
        });
    },

    createColumns: function() {
        var me = this;

        return [
            {
                header: '{s name=fieldset/check/name}Name{/s}',
                dataIndex: 'name',
                flex: 1
            },
            {
                header: '{s name=fieldset/check/value}Value{/s}',
                dataIndex: 'valid',
                flex: 1,
                renderer: me.validRenderer
            }
        ];
    },

    validRenderer: function(value, metaData, record) {
        var me = this,
            sprite = 0;

        if (value === 2) {
            sprite = 'sprite-exclamation';
        } else if (value === 1) {
            sprite = 'sprite-tick';
        } else if (value === 0) {
            sprite = 'sprite-cross';
        }

        return Ext.String.format('<div class="[0]" title="[1]" style="width:16px; height:16px;"></div>', sprite, record.get('description'));
    }



});
//{/block}
