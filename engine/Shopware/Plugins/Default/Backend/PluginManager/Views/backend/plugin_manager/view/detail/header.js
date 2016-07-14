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
 * @package    PluginManager
 * @subpackage Detail
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/plugin_manager/translation}

//{block name="backend/plugin_manager/view/detail/header"}
Ext.define('Shopware.apps.PluginManager.view.detail.Header', {
    extend: 'Ext.container.Container',
    cls: 'store-plugin-detail-headline-container-content',
    layout: {
        type: 'hbox',
        align: 'stretch'
    },

    initComponent: function() {
        var me = this, certified = null, producer = null;

        if (!me.plugin) {
            return;
        }

        if (me.plugin.get('certified')) {
            certified = Ext.create('Ext.Component', {
                cls: 'headline-certified',
                html: '<span class="icon">&nbsp;</span><span class="text">{s name="certified"}Certified{/s}</span>'
            });
        }

        if (me.plugin['getProducerStore'] instanceof Ext.data.Store) {
            var record = me.plugin['getProducerStore'].first();

            producer = Ext.create('Ext.Component', {
                cls: 'headline-author',
                margin: '5 0 0',
                html: '<span class="prefix">{s name="from_producer"}Developed by{/s}:</span> ' + record.get('name')
            });
        }

        var image = null;

        if (me.plugin.get('iconPath')) {
            image = {
                xtype: 'component',
                cls: 'headline-image',
                width: 128,
                height: 128,
                html: '<img src="'+ me.plugin.get('iconPath') +'" />'
            };
        }

        me.items = [image, {
            xtype: 'container',
            cls: 'headline-right-container',
            flex: 1,
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            items: [
                {
                    xtype: 'component',
                    html: '<div>'+ me.plugin.get('label') + '</div>',
                    cls: 'headline-name',
                    width: 750
                },
                certified,
                producer
            ]
        }];

        me.callParent(arguments);
    }
});
//{/block}