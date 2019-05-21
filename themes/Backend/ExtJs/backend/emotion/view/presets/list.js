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
//{namespace name=backend/emotion/presets/presets}
//{block name="backend/emotion/presets/list"}
Ext.define('Shopware.apps.Emotion.view.presets.List', {
    alias: 'widget.presets-list',
    region: 'center',
    autoScroll: true,
    extend: 'Ext.panel.Panel',

    config: {
        selectedPreset: null,
        initialSelectionId: null,
        initialSelection: true
    },

    initComponent: function () {
        var me = this;

        me.items = [
            {
                xtype: 'image',
                width: '100%',
                style: 'cursor: pointer;',
                src: Ext.String.format('{link file="themes/Backend/ExtJs/backend/_resources/resources/themes/images/shopware-ui/emotion_preset_teaser_[0].png"}', Ext.userLanguage !== 'de' ? 'en' : Ext.userLanguage),
                listeners: {
                    boxready: function(cmp) {
                        cmp.el.on('click', me.openPluginManager);
                    }
                }
            },
            me.createInfoView()
        ];

        me.dockedItems = me.buildDockedItems();

        me.addEvents('emotionpresetselect');
        me.addEvents('showpresetdetails');

        me.store.on('load', function() {
            me.selectedPreset = null;
            if (me.store.getCount() > 0) {
                if (me.initialSelection && me.initialSelectionId) {
                    me.infoView.getSelectionModel().select(me.store.getById(me.initialSelectionId));
                    me.selectedPreset = me.infoView.getSelectionModel().getSelection()[0];
                    me.initialSelection = false;
                }

                if (me.selectedPreset) {
                    if (me.down('#deletebutton')) {
                        me.down('#deletebutton').setDisabled(!me.selectedPreset.get('custom'));
                    }
                }
            }
            me.fireEvent('showpresetdetails', me.selectedPreset);
        });

        me.callParent(arguments);
    },

    openPluginManager: function() {
        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.PluginManager',
            action: 'Listing',
            params: {
                filter: [
                    { property: 'emotionPreset', value: 1 }
                ]
            }
        }, undefined, function() {
            Ext.Function.defer(function () {
                Shopware.app.Application.fireEvent('reload-local-listing');
                Shopware.app.Application.fireEvent('load-update-listing');
            }, 2000);
        });
    },

    createInfoView: function () {
        var me = this;

        me.infoView = Ext.create('Ext.view.View', {
            itemSelector: '.thumbnail',
            tpl: me.createTemplate(),
            store: me.store,
            cls: 'emotion-listing',
            listeners: {
                render: Ext.bind(me.addGroupHeaderEvent, me),
                boxready: function(view) {
                    view.getEl().on('click', function() {
                        me.openPluginManager();
                    }, me, {
                        delegate: 'a'
                    });
                },
                // because of custom tpl with grouping we cannot trust selection model here
                // and have to use the data-preset-id attribute
                itemclick: function(view, record, item, index, e) {
                    me.setSelectedPreset(view, record, item, index, e);
                    me.showDetails();
                },
                itemdblclick: function(view, record, item, index, e) {
                    me.setSelectedPreset(view, record, item, index, e);
                    me.fireEvent('emotionpresetselect');
                },
                selectionchange: function(view, selection) {
                    if (me.down('#deletebutton')) {
                        me.down('#deletebutton').setDisabled(selection.length === 0 || (me.selectedPreset && !me.selectedPreset.get('custom')));
                    }
                    if (selection.length === 0) {
                        me.selectedPreset = null;
                        me.showDetails();
                    }
                }
            }
        });

        return me.infoView;
    },

    buildDockedItems: function() {
        var me = this;

        me.topToolbar = Ext.create('Ext.Toolbar', {
            dock: 'top',
            ui: 'shopware-ui',
            items: [
            /*{if {acl_is_allowed privilege=delete}}*/
            {
                xtype: 'button',
                itemId: 'deletebutton',
                iconCls: 'sprite-minus-circle',
                text: '{s name=presetlist/delete_preset}{/s}',
                disabled: true,
                handler: function() {
                    me.fireEvent('deletepreset', me.store, me.selectedPreset);
                }
            },
            /*{/if}*/
            '->', {
                xtype: 'textfield',
                cls: 'searchfield',
                emptyText: '{s name=search}Search...{/s}',
                width: 170,
                enableKeyEvents: true,
                listeners: {
                    change: Ext.bind(me.search, me)
                }
            }]
        });

        return [
            me.topToolbar
        ];
    },

    /**
     * @param { Ext.form.field.Text } field
     * @param { string } term
     */
    search: function(field, term) {
        var me = this;

        term = term.trim();
        if (term.length <= 0) {
            me.store.clearFilter();
            return;
        }
        me.store.clearFilter();

        me.store.filter({
            filterFn: function(item) {
                return item.get('label').toLowerCase().indexOf(term.toLowerCase()) >= 0;
            }
        });
    },

    setSelectedPreset: function(view, record, item, index, e) {
        var me = this,
            targetNode = e.getTarget(null, 10, true),
            selectorElement = targetNode.findParent('div.thumbnail', 50, true),
            presetId = parseInt(selectorElement.getAttribute('data-preset-id'));

        me.selectedPreset = me.store.getById(presetId);
    },

    createTemplate: function () {
        var panel = this;

        return new Ext.XTemplate(
            '{literal}{[this.getPresetRows(values)]}{/literal}',
            '<div class="x-clear"></div>',
            {
                getPresetRows: function(values) {
                    var me = this,
                        sortedPresets,
                        i, count, output = '';

                    sortedPresets = {
                        installed: [],
                        custom: []
                    };

                    for (i = 0, count = values.length; i < count; i++) {
                        var preset = values[i];
                        if (preset.custom) {
                            sortedPresets['custom'].push(preset);
                        } else {
                            sortedPresets['installed'].push(preset);
                        }
                    }

                    if (panel.initialSelection && sortedPresets['installed'].length > 0) {
                        panel.initialSelectionId = sortedPresets['installed'][0]['id'];
                    } else if (panel.initialSelection && sortedPresets['custom'].length > 0) {
                        panel.initialSelectionId = sortedPresets['custom'][0]['id'];
                    }

                    Ext.Object.each(sortedPresets, function(key, value) {
                        var name = '{s name=installed_shopping_world_presets}{/s}';
                        if (key === 'custom') {
                            name = '{s name=created_shopping_world_presets}{/s}'
                        }

                        if (value.length > 0) {
                            output += me.getPresets(value, name);
                        } else {
                            output += me.getTeaserComponent(key, name);
                        }
                    }, me);

                    return output;
                },
                getPresets: function (values, name) {
                    var me = this;

                    return '<div class="preset--outer-container">' +
                               '<div class="x-grid-group-hd x-grid-group-hd-collapsible">' +
                                   '<div class="x-grid-group-title">' + name + '</div>' +
                               '</div>' +
                               '<div class="preset--container">' +
                                   me.getItem(values) +
                                   '<div class="x-clear"></div>' +
                               '</div>' +
                           '</div>';
                },
                getTeaserComponent: function(group, name) {
                    var header = '{s name=no_presets_installed}{/s}',
                        message = Ext.String.format('[0] <br><a href="#">[1]</a>', '{s name=no_presets_installed_message}{/s}', '{s name=discover_now}{/s}');

                    if (group === 'custom') {
                        header = '{s name=no_presets_created}{/s}';
                        message = '{s name=no_presets_created_message}{/s}';
                    }

                    return '<div class="preset--outer-container">' +
                                '<div class="x-grid-group-hd x-grid-group-hd-collapsible">' +
                                    '<div class="x-grid-group-title">' + name + '</div>' +
                                '</div>' +
                                '<div class="preset-teaser-container">' +
                                    '<fieldset class="x-fieldset x-base-field-set x-fieldset-with-title x-fieldset-with-legend x-fieldset-default preset-teaser-fieldset">' +
                                        '<legend class="x-fieldset-header x-fieldset-header-default">' +
                                            '<div class="x-component x-fieldset-header-text x-component-default">' + header +'</div>' +
                                            '<div class="x-clear"></div>' +
                                        '</legend>' +
                                        '<div class="x-fieldset-body preset-teaser-fieldset-body">'+ message + '</div>' +
                                    '</fieldset>' +
                                '</div>' +
                            '</div>';
                },
                getItem: function (values) {
                    var items = [];

                    Ext.each(values, function(preset) {
                        var itemTpl = '';

                        if (preset.premium) {
                            itemTpl += '<div class="thumbnail premium" data-preset-id="' + preset.id + '">';
                            itemTpl += '<div class="hint premium"><span>{s name=premium_hint}{/s}</span></div>';
                        } else {
                            itemTpl += '<div class="thumbnail" data-preset-id="' + preset.id + '">';
                        }

                        itemTpl += '<div class="thumb"><div class="inner-preset-thumb">';

                        if (preset.thumbnailUrl) {
                            itemTpl += Ext.String.format('<img src="[0]" alt="[1]" />', preset.thumbnailUrl, preset.label);
                        } else {
                            itemTpl += Ext.String.format('<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="[0]" />', preset.label);
                        }

                        itemTpl += '<span class="x-editable">' + preset.label + '</span>';
                        itemTpl += '</div></div></div>';

                        items.push(itemTpl);
                    });

                    return items.join('');
                }
            }
        );
    },

    addGroupHeaderEvent: function() {
        var me = this,
            view = me.infoView,
            viewEl = view.getEl();

        viewEl.on('click', function(e, target) {
            var el = Ext.get(target),
                parent, presetContainer;

            if (!el.hasCls('x-grid-group-title')) {
                return;
            }
            parent = el.parent('.preset--outer-container');
            presetContainer = parent.down('.preset--container');

            if(parent.hasCls('x-grid-group-hd-collapsed')) {
                parent.removeCls('x-grid-group-hd-collapsed');
                presetContainer.setStyle('display', 'block');
            } else {
                parent.addCls('x-grid-group-hd-collapsed');
                presetContainer.setStyle('display', 'none');
            }
        });
    },

    showDetails: function() {
        var me = this;

        me.fireEvent('showpresetdetails', me.selectedPreset);
    }
});

//{/block}
