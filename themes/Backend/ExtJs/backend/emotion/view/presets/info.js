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
 */

//{namespace name=backend/emotion/presets/presets}

/**
 * Shopware Application
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */

//{block name="backend/emotion/presets/info"}
Ext.define('Shopware.apps.Emotion.view.presets.Info', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.preset-info-panel',

    cls: 'emotion-info-panel',
    layout: 'fit',

    initComponent: function() {
        var me = this;

        me.items = me.buildItems();

        me.callParent(arguments);
    },

    buildItems: function() {
        var me = this;

        me.infoView = Ext.create('Ext.view.View', {
            tpl: me.createTemplate(),
            autoScroll: true,
            padding: 5,
            style: 'color: #6c818f;font-size:11px',
            emptyText: '<div style="font-size:13px; text-align: center;">{s name="info_panel/empty_text"}{/s}</div>',
            deferEmptyText: false
        });

        return me.infoView;
    },

    createTemplate: function() {
        return new Ext.XTemplate(
            '<tpl for=".">',
                '<div class="item">',
                    '{literal}<div class="screen"><img src="{previewUrl}" alt="{label}" /></div>{/literal}',
                    '<div class="info-item"> <p class="label">{s name=name}{/s}:</p> <p class="value">{literal}{label}{/literal}</p></div>',
                    '<div class="info-item"> <p class="label">{s name=description}{/s}:</p> <p class="value">{literal}{description}{/literal}</p></div>',
                    '<tpl if="this.hasRequiredPlugins(values.requiredPlugins)">',
                        '<div class="info-item">',
                            '<p class="label" style="margin-bottom: 5px;">{s name=required_plugins}{/s}:</p>',
                            '<ul>',
                                '<tpl for="requiredPlugins">',
                                    '<li class="required-plugin" style="cursor: pointer; margin-left: 10px; margin-bottom: 5px;" id="{literal}{name}{/literal}" data-in-store="{literal}{in_store}{/literal}">' +
                                        '<tpl if="valid">' +
                                            '<span style="width: 16px; height: 16px; display: inline-block; line-height: 15px;" class="sprite-ui-check-box">&nbsp;</span>' +
                                        '<tpl else>' +
                                            '<span style="width: 16px; height: 16px; display: inline-block; line-height: 15px;" class="sprite-ui-check-box-uncheck">&nbsp;</span>' +
                                        '</tpl>',
                                        '<span>{literal}{[this.getPluginName(values)]} ({version}){/literal}</span>' +
                                    '</li>',
                                '</tpl>',
                            '</ul>',
                        '</div>',
                    '</tpl>',
                '</div>',
            '</tpl>',
            {
                hasRequiredPlugins: function(requiredPlugins) {
                    return requiredPlugins.length > 0;
                }
            },
            {
                getPluginName: function(values) {
                    return values.plugin_label || values.name;
                }
            }
        );
    },

    updateInfoView: function(record) {
        var me = this;

        if (record && record.getData()) {
            me.infoView.update(record.getData());
            var $el = me.infoView.getEl();
            var plugins = $el.query('.required-plugin');

            Ext.each(plugins, function(item) {
                item = Ext.get(item);
                item.on('click', function() {
                    var inStore = item.getAttribute('data-in-store') === "true";
                    if (!inStore) {
                        return;
                    }
                    Shopware.app.Application.addSubApplication({
                        name: 'Shopware.apps.PluginManager',
                        params: {
                            hidden: true,
                            displayPlugin: item.id
                        }
                    });
                });
            });

        } else {
            me.infoView.update('<div class="item" style="">{s name="info_panel/empty_text"}{/s}</div>');
        }
    }
});
//{/block}
