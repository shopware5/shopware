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
 * @category    Shopware
 * @package     Emotion
 * @subpackage  View
 * @version     $Id$
 * @author      shopware AG
 */

//{namespace name=backend/emotion/view/main}
/**
 * Emotion Translation Mapping Window
 */
//{block name="backend/emotion/view/translation/window"}
Ext.define('Shopware.apps.Emotion.view.translation.Window', {
    extend: 'Enlight.app.Window',
    alias: 'widget.emotion-translation-window',

    border: false,
    autoShow: true,
    modal: true,

    config: {
        emotionId: null,
        emotionTranslations: null
    },

    title: '{s name=emotion/translation/window_title}{/s}',
    height: '50%',
    width: '50%',

    layout: 'fit',

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.items = me.buildItems();

        me.dockedItems = [{
            xtype: 'toolbar',
            dock: 'bottom',
            ui: 'shopware-ui',
            items: ['->', {
                text: '{s name=emotion/translation/use_mapping}{/s}',
                cls: 'primary',
                handler: function() {
                    var translations = me.appendMapping();
                    me.resumeImport(translations);
                }
            }]
        }];

        me.callParent(arguments);

        var data = me.prepareTranslationData();

        me.down('grid').getStore().loadData(data);

    },

    buildItems: function() {
        var me = this;

        me.infoView = Ext.create('Ext.Container', {
            padding: 20,
            flex: 1,
            html: '<p><i>' + '{s name=emotion/translation/info_text}{/s}' + '</i></p>'
        });

        me.mappingGrid = Ext.create('Ext.grid.Panel', {
            flex: 3,
            border: false,
            store: Ext.create('Ext.data.Store', {
                fields: [
                    { name: 'locale' },
                    { name: 'shop' },
                    { name: 'mappedShopId', type: 'int', useNull: true },
                    { name: 'mappedShop' }
                ],
                proxy: {
                    type: 'memory',
                    reader: {
                        type: 'json',
                        root: 'data'
                    }
                }
            }),
            plugins: [
                Ext.create('Ext.grid.plugin.CellEditing', {
                    clicksToEdit: 1,
                    listeners: {
                        scope: me,
                        beforeedit: me.onBeforeEdit,
                        edit: me.onEdit
                    }
                })
            ],
            columns: {
                items: [{
                    header: '{s name=emotion/translation/locale}{/s}',
                    dataIndex: 'locale',
                    flex: 1
                }, {
                    header: '{s name=emotion/translation/original_mapping}{/s}',
                    dataIndex: 'shop',
                    flex: 2
                }, {
                    header: '{s name=emotion/translation/assigned_mapping}{/s}',
                    dataIndex: 'mappedShopId',
                    flex: 2,
                    editor: {
                        xtype: 'combo',
                        store: Ext.create('Ext.data.Store', {
                            fields: ['shop', { name: 'id', type: 'int'} ],
                            proxy: {
                                type: 'memory',
                                reader: {
                                    type: 'json'
                                }
                            }
                        }),
                        queryMode: 'local',
                        triggerAction: 'all',
                        displayField: 'shop',
                        valueField: 'id'
                    },
                    renderer: function(value, meta, record) {
                        if (Ext.isEmpty(value)) {
                            meta.style = 'opacity: 0.5; font-weight: italic;';

                            return '{s name=emotion/translation/no_import_info}{/s}';
                        }

                        return record.get('mappedShop');
                    }
                }]
            }
        });

        return [{
            xtype: 'container',
            padding: 5,
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            items: [me.infoView, me.mappingGrid]
        }];
    },

    prepareTranslationData: function() {
        var me = this,
            translations = Ext.JSON.decode(me.emotionTranslations),
            data = new Ext.util.MixedCollection();

        Ext.each(translations, function(translation) {
            if (!data.containsKey(translation['shop'])) {
                data.add(translation['shop'], {
                    locale: translation['locale'],
                    shop: translation['shop']
                });
            }
        });

        return data.getRange();
    },

    onBeforeEdit: function(editor, e) {
        var me = this,
            record = e.record,
            store = e.grid.store,
            shops = me.shops[record.get('locale')],
            combo = e.column.getEditor(),
            data = [];

        Ext.Object.each(shops, function(key, value) {
            var rec = store.findRecord('mappedShopId', value);
            if (!rec || record === rec) {
                data.push({
                    shop: key,
                    id: value
                });
            }
        });

        combo.getStore().loadData(data);
    },

    onEdit: function(editor, e) {
        var record = e.record,
            combo = e.column.getEditor(),
            shopRecord = combo.findRecordByValue(e.value);

        record.set('mappedShopId', null);
        record.set('mappedShop', '');

        if (shopRecord) {
            record.set('mappedShopId', e.value);
            record.set('mappedShop', shopRecord.get('shop'));
        }
        record.commit();
    },

    appendMapping: function() {
        var me = this,
            mappingStore = me.mappingGrid.getStore(),
            translations = Ext.JSON.decode(me.emotionTranslations),
            mappedTranslations = [];

        Ext.each(translations, function(translation, index) {
            var mappingRecord,
                mappingIdx = mappingStore.findBy(function(record, index) {
                    return !Ext.isEmpty(record.get('mappedShop'))
                        && record.get('locale') === translation['locale']
                        && record.get('shop') === translation['shop'];
                });

            if (mappingIdx !== -1) {
                mappingRecord = mappingStore.getAt(mappingIdx);
                translation['objectlanguage'] = mappingRecord.get('mappedShopId');
                mappedTranslations.push(translation);
            }
        });

        return Ext.JSON.encode(mappedTranslations);
    },

    resumeImport: function(emotionTranslations) {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller=Emotion action=importTranslations}',
            jsonData: {
                emotionId: me.emotionId,
                emotionTranslations: emotionTranslations,
                autoMapping: false
            },
            callback: function(options, success, response) {
                var result = Ext.JSON.decode(response.responseText);

                if (!result.success) {
                    Shopware.Notification.createGrowlMessage(
                        '{s name=emotion/translation/import_error_title}{/s}',
                        '{s name=emotion/translation/import_error_msg}{/s}'
                    );
                }
                me.close();
            }
        });
    }
});
//{/block}
