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

/**
 * Shopware Application
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */

//{namespace name=backend/theme/main}

//{block name="backend/theme/view/list/theme"}

Ext.define('Shopware.apps.Theme.view.list.Theme', {
    alias: 'widget.theme-listing',
    region: 'center',
    autoScroll: true,
    extend: 'Ext.panel.Panel',

    initComponent: function () {
        var me = this;

        me.items = [
            /*{if {acl_is_allowed privilege=uploadTheme}}*/
            me.createDropZone(),
            /*{/if}*/
            me.createInfoView()
        ];

        me.callParent(arguments);
    },

    createDropZone: function () {
        var me = this;

        me.dropZone = Ext.create('Shopware.app.FileUpload', {
            requestURL: '{url controller="Theme" action="upload"}',
            enablePreviewImage: false,
            showInput: false,
            dropZoneText: '{s name=drop_zone}Upload single theme using drag+drop (zip){/s}'
        });

        me.dropZone.snippets.messageTitle = '{s name=upload_title}Theme Manager{/s}';
        me.dropZone.snippets.messageText = '{s name=upload_message}Theme uploaded successfully{/s}';

        return me.dropZone;
    },

    createInfoView: function () {
        var me = this;

        me.infoView = Ext.create('Ext.view.View', {
            itemSelector: '.thumbnail',
            tpl: me.createTemplate(),
            store: me.store,
            cls: 'theme-listing',
            listeners: {
                render: Ext.bind(me.onAddInfoViewEvents, me)
            }
        });

        return me.infoView;
    },

    createTemplate: function () {
        var me = this;

        return new Ext.XTemplate(
            '{literal}{[this.getRows(values)]}',
            '<div class="x-clear"></div>{/literal}', {

                getRows: function (values) {
                    var me = this,
                        templatesByVersion = {
                            'shopware5': [],
                        },
                        output = '';

                    Ext.each(values, function(item) {
                        templatesByVersion['shopware5'].push(item);
                    });

                    Ext.iterate(templatesByVersion, function(name, values) {
                        output += me.getRow(name, values);
                    });

                    return output;
                },

                getRow: function (name, values) {
                    var me = this,
                        snippets = {
                            'shopware5': '{s name=designed_for_shopware5}Designed for Shopware 5{/s}'
                        };

                    if(values.length <= 0) {
                        return '';
                    }

                    return [
                        '<div class="theme--outer-container">',
                        '<div class="x-grid-group-hd x-grid-group-hd-collapsible">',
                        '<div class="x-grid-group-title">' + snippets[name] + '</div>',
                        '</div>',
                        '<div class="theme--container">',
                        me.getItem(values),
                        '<div class="x-clear"></div>',
                        '</div></div>'
                    ].join('');
                },

                getItem: function (values) {
                    var items = [];

                    Ext.each(values, function(theme) {
                        var itemTpl = '';

                        if(theme.enabled) {
                            itemTpl += '<div class="thumbnail enabled">';
                            itemTpl += '<div class="hint enabled"><span>{s name=enabled}Enabled{/s}</span></div>';
                        } else if(theme.preview) {
                            itemTpl += '<div class="thumbnail previewed">';
                            itemTpl += '<div class="hint preview"><span>{s name=preview_hint}Preview{/s}</span></div>';
                        } else {
                            itemTpl += '<div class="thumbnail">'
                        }

                        itemTpl += '<div class="thumb"><div class="inner-thumb">';

                        if(theme.screen) {
                            itemTpl += Ext.String.format('<img src="[0]" alt="[1]" />', theme.screen, theme.name);
                        }

                        if(theme.hasConfig) {
                            itemTpl += '<div class="mapping-config">&nbsp;</div>';
                        }

                        itemTpl += '<span class="x-editable">' + theme.name + '</span>';
                        itemTpl += '</div></div></div>';

                        items.push(itemTpl);
                    });

                    return items.join('');
                }
            }
        );
    },

    onAddInfoViewEvents: function() {
        var me = this,
            view = me.infoView,
            viewEl = view.getEl();

        viewEl.on('click', function(evt, target) {
            var el = Ext.get(target), parent, themeContainer;

            if(!el.hasCls('x-grid-group-title')) {
                return;
            }
            parent = el.parent('.theme--outer-container');
            themeContainer = parent.down('.theme--container');

            if(parent.hasCls('x-grid-group-hd-collapsed')) {
                parent.removeCls('x-grid-group-hd-collapsed');
                themeContainer.setStyle('display', 'block');
            } else {
                parent.addCls('x-grid-group-hd-collapsed');
                themeContainer.setStyle('display', 'none');
            }
        });
    }
});

//{/block}
